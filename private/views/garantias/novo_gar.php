<?php  
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();
require_once __DIR__ . '/../../includes/validacoes.php';

//Desencriptação e validar ID equipamento
$idEquipamentoEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEquipamentoEncrypted);

if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

// Vai buscar a data de aquisição do equipamento, para validar a data de garantia
try {
    $ligacaoTemp = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
    $stmtTemp = $ligacaoTemp->prepare("SELECT data_aquisicao FROM equipamentos WHERE id = :id");
    $stmtTemp->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtTemp->execute();
    $dataAquisicaoEquipamento = $stmtTemp->fetchColumn();
    $ligacaoTemp = null;
} catch (PDOException $e) {
    $dataAquisicaoEquipamento = '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recolher dados
    // Verifica se a checkbox foi marcada (checkboxes só vêm no $_POST se estiverem marcadas; se não vier nada, assume-se que está desmarcada e grava-se 0)
    // isset - verifica se a chave existe no array
    $dataInicioGarantia   = $_POST["data_inicio_garantia"] ?? "";
    $dataFimGarantia      = $_POST["data_fim_garantia"] ?? "";
    $temContratoManutencao = isset($_POST["tem_contrato_manutencao"]) ? 1 : 0;
    $tipoContrato         = $_POST["tipo_contrato"] ?? "";
    $periodicidade        = $_POST["periodicidade"] ?? "";
    $entidadeResponsavel  = $_POST["entidade_responsavel"] ?? "";
    $observacoesGarantia  = $_POST["observacoes_garantia"] ?? "";

    // 2. Trim
    $dataInicioGarantia  = trim($dataInicioGarantia);
    $dataFimGarantia     = trim($dataFimGarantia);
    $observacoesGarantia = trim($observacoesGarantia);

    // 2.5 Processar o upload do ficheiro (só a parte de MOVER, sem repetir validação)
    $caminhoFicheiroGarantia = "";
    if (empty(validar_ficheiro_upload($_FILES['ficheiro_garantia'], false)) && !empty($_FILES['ficheiro_garantia']['name'])) {
        $pastaDestino = __DIR__ . '/../../../assets/uploads/';
        $extensao = strtolower(pathinfo($_FILES['ficheiro_garantia']['name'], PATHINFO_EXTENSION));
        $caminhoFicheiroGarantia = uniqid('gar_') . '.' . $extensao;
        move_uploaded_file($_FILES['ficheiro_garantia']['tmp_name'], $pastaDestino . $caminhoFicheiroGarantia);
    }

    // 3. Validar os dados
    $erros = [];
    $erro_sistema = "";

    $erros = array_merge(
        validar_datas_garantia($dataInicioGarantia, $dataFimGarantia),
        validar_data_inicio_vs_aquisicao($dataInicioGarantia, $dataAquisicaoEquipamento),
        validar_tipo_contrato($tipoContrato, $temContratoManutencao),
        validar_periodicidade($periodicidade, $temContratoManutencao),
        validar_observacoes_garantia($observacoesGarantia),
        validar_entidade_responsavel($entidadeResponsavel, $dataInicioGarantia, $dataFimGarantia, $temContratoManutencao),
        validar_contexto_garantia($dataInicioGarantia, $dataFimGarantia, $temContratoManutencao),
        validar_ficheiro_upload($_FILES['ficheiro_garantia'], false)
    );

    // 4. Se não houver erros, guardar na base de dados
    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
                MYSQL_USERNAME,
                MYSQL_PASSWORD
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Converte valores vazios em NULL antes de inserir (campos opcionais) para evitar erros
            $idFornecedorParam = !empty($entidadeResponsavel) && is_numeric($entidadeResponsavel) ? $entidadeResponsavel : null;
            $dataInicioParam   = !empty($dataInicioGarantia) ? $dataInicioGarantia : null;
            $dataFimParam      = !empty($dataFimGarantia) ? $dataFimGarantia : null;
            // Se não tem contrato de manutenção, ignora tipo_contrato e periodicidade (gravam NULL)
            $tipoContratoParam  = $temContratoManutencao && !empty($tipoContrato) ? $tipoContrato : null;
            $periodicidadeParam = $temContratoManutencao && !empty($periodicidade) ? $periodicidade : null;

           $sql = "INSERT INTO garantias_contratos (
                id_equipamento, id_fornecedor, data_inicio_garantia, data_fim_garantia,
                tem_contrato_manutencao, tipo_contrato, periodicidade, observacoes, caminho_ficheiro
            ) VALUES (
                :id_equipamento, :id_fornecedor, :data_inicio_garantia, :data_fim_garantia,
                :tem_contrato_manutencao, :tipo_contrato, :periodicidade, :observacoes, :caminho_ficheiro
            )";

            $stmt = $ligacao->prepare($sql);
            $stmt->execute([
                ':id_equipamento'          => $idEquipamento,
                ':id_fornecedor'           => $idFornecedorParam,
                ':data_inicio_garantia'    => $dataInicioParam,
                ':data_fim_garantia'       => $dataFimParam,
                ':tem_contrato_manutencao' => $temContratoManutencao,
                ':tipo_contrato'           => $tipoContratoParam,
                ':periodicidade'           => $periodicidadeParam,
                ':observacoes'             => $observacoesGarantia,
                ':caminho_ficheiro'        => !empty($caminhoFicheiroGarantia) ? $caminhoFicheiroGarantia : null
            ]);

            $idNovaGarantia = $ligacao->lastInsertId();

            // Vai buscar o id do utilizador autenticado
            $stmtUser = $ligacao->prepare("SELECT id FROM utilizadores WHERE email = :email");
            $stmtUser->execute([':email' => $_SESSION['utilizador']]);
            $idUtilizador = $stmtUser->fetchColumn();

            // Regista o evento na tabela de logs
            $stmtLog = $ligacao->prepare("INSERT INTO logs (id_utilizador, tipo_evento, descricao) VALUES (:id_utilizador, 'garantia_criada', :descricao)");
            $stmtLog->execute([
                ':id_utilizador' => $idUtilizador,
                ':descricao'     => 'Garantia/Contrato criado (id: ' . $idNovaGarantia . ', equipamento: ' . $idEquipamento . ')'
            ]);

            // Guarda mensagem de sucesso para o Toast aparecer
            $_SESSION['toast_success'] = 'Garantia/Contrato adicionado com sucesso.';

            header('Location: ' . BASE_URL . '/private/views/equipamentos/detalhes.php?id_equipamento=' . $idEquipamentoEncrypted);
            exit;

        } catch (PDOException $err) {
            $erro_sistema = "Erro ao gravar os dados: " . $err->getMessage();
        }

        $ligacao = null;
    }
}
?>

<?php
// Carregar fornecedores disponíveis (todos os tipos, sem filtro, já que aqui é só informativo)
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
    $fornecedoresDisponiveis = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);
    $ligacao = null;
} catch (PDOException $e) {
    $fornecedoresDisponiveis = [];
}
?>

<?php include '../../includes/header.php'; ?> 
<?php include '../../includes/nav.php'; ?> 

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-center mt-4">
                <div class="card w-100 shadow rounded" style="max-width: 1200px;">
                    <div class="card-body">
                        <h2 class="mb-4"><strong><i class="fa-solid fa-shield-halved me-2"></i> Adicionar Garantia / Contrato</strong></h2>
                        <hr>
                        <form action="#" method="post" enctype="multipart/form-data" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="data_inicio_garantia" class="form-label">Data de Início da Garantia </label>
                                    <input type="text" class="form-control" name="data_inicio_garantia" id="data_inicio_garantia" 
                                        value="<?= htmlspecialchars($_POST['data_inicio_garantia'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="data_fim_garantia" class="form-label">Data de Fim da Garantia </label>
                                    <input type="text" class="form-control" name="data_fim_garantia" id="data_fim_garantia" 
                                        value="<?= htmlspecialchars($_POST['data_fim_garantia'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="tem_contrato_manutencao" id="tem_contrato_manutencao" value="1"
                                            <?= !empty($_POST['tem_contrato_manutencao']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="tem_contrato_manutencao">
                                            Existe contrato de manutenção
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_tipo_contrato" class="form-label">Tipo de Contrato</label>
                                    <select class="form-select" name="tipo_contrato" id="select_tipo_contrato">
                                        <option value="" <?= empty($_POST['tipo_contrato']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="manutencao_preventiva" <?= (($_POST['tipo_contrato'] ?? '') == 'manutencao_preventiva') ? 'selected' : '' ?>>Manutenção Preventiva</option>
                                        <option value="manutencao_corretiva" <?= (($_POST['tipo_contrato'] ?? '') == 'manutencao_corretiva') ? 'selected' : '' ?>>Manutenção Corretiva</option>
                                        <option value="manutencao_completa" <?= (($_POST['tipo_contrato'] ?? '') == 'manutencao_completa') ? 'selected' : '' ?>>Manutenção Completa (Preventiva + Corretiva)</option>
                                        <option value="outro" <?= (($_POST['tipo_contrato'] ?? '') == 'outro') ? 'selected' : '' ?>>Outro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="select_periodicidade" class="form-label">Periodicidade</label>
                                    <select class="form-select" name="periodicidade" id="select_periodicidade">
                                        <option value="" <?= empty($_POST['periodicidade']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="mensal" <?= (($_POST['periodicidade'] ?? '') == 'mensal') ? 'selected' : '' ?>>Mensal</option>
                                        <option value="trimestral" <?= (($_POST['periodicidade'] ?? '') == 'trimestral') ? 'selected' : '' ?>>Trimestral</option>
                                        <option value="semestral" <?= (($_POST['periodicidade'] ?? '') == 'semestral') ? 'selected' : '' ?>>Semestral</option>
                                        <option value="anual" <?= (($_POST['periodicidade'] ?? '') == 'anual') ? 'selected' : '' ?>>Anual</option>
                                        <option value="nao_aplicavel" <?= (($_POST['periodicidade'] ?? '') == 'nao_aplicavel') ? 'selected' : '' ?>>Não Aplicável</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_entidade_responsavel" class="form-label">Entidade Responsável </label>
                                    <select class="form-select" name="entidade_responsavel" id="select_entidade_responsavel">
                                        <option value="">-- Nenhuma --</option>
                                        <?php foreach ($fornecedoresDisponiveis as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" <?= (($_POST['entidade_responsavel'] ?? '') == $fornecedor['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fornecedor['nome_empresa']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="ficheiro_garantia" class="form-label">Ficheiro </label>
                                    <input type="file" class="form-control" name="ficheiro_garantia" id="ficheiro_garantia" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes_garantia" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_garantia" id="texto_observacoes_garantia" 
                                        value="<?= htmlspecialchars($_POST['observacoes_garantia'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="d-flex justify-content-end gap-2 mb-4">
                                <a href="../equipamentos/detalhes.php?id_equipamento=<?= $idEquipamentoEncrypted ?>" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-regular fa-floppy-disk me-1"></i> Guardar
                                </button>
                            </div>

                            <!-- Área de erros -->
                            <?php if (!empty($erros)): ?> 
                                <div class="alert alert-danger" role="alert"> 
                                    <strong>Foram encontrados os seguintes erros:</strong> 
                                    <ul class="mb-0"> 
                                        <?php foreach ($erros as $erro): ?> 
                                            <li><?= htmlspecialchars($erro) ?></li> 
                                        <?php endforeach; ?> 
                                    </ul> 
                                </div> 
                            <?php endif; ?> 
                            <?php if (!empty($erro_sistema)): ?>
                                <div class="alert alert-danger">
                                    <strong>Erro:</strong>
                                    <p><?= htmlspecialchars($erro_sistema) ?></p>
                                </div>
                            <?php endif; ?>

                        </form>
                    </div>
                </div>
            </div>
        </main>

    </div>
</div>

<script>
flatpickr("#data_inicio_garantia", {
    dateFormat: "Y-m-d"
});
flatpickr("#data_fim_garantia", {
    dateFormat: "Y-m-d"
});
</script>

<?php include '../../includes/footer.php'; ?>