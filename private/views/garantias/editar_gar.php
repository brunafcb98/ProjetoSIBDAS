<?php  
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();
require_once __DIR__ . '/../../includes/validacoes.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

//Desencriptação e validar ID garantia
$idGarantiaEncrypted = $_GET['id_garantia'] ?? null;
$idGarantia = aes_decrypt($idGarantiaEncrypted);

if (!$idGarantia || !is_numeric($idGarantia)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

//Para ir buscar a data de aquisiçao do equip, de modo a assegurar que a data de inicio de garantia é posterior
try {
    $ligacaoTemp = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
    $stmtTemp = $ligacaoTemp->prepare("
        SELECT e.data_aquisicao 
        FROM equipamentos e
        INNER JOIN garantias_contratos g ON g.id_equipamento = e.id
        WHERE g.id = :id_garantia
    ");
    $stmtTemp->bindParam(':id_garantia', $idGarantia, PDO::PARAM_INT);
    $stmtTemp->execute();
    $dataAquisicaoEquipamento = $stmtTemp->fetchColumn();
    $ligacaoTemp = null;
} catch (PDOException $e) {
    $dataAquisicaoEquipamento = '';
}

//Detetar submissao via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaDataInicioGarantia    = $_POST['data_inicio_garantia'] ?? '';
    $novaDataFimGarantia       = $_POST['data_fim_garantia'] ?? '';
    $novoTemContratoManutencao = isset($_POST['tem_contrato_manutencao']) ? 1 : 0;
    $novoTipoContrato          = $_POST['tipo_contrato'] ?? '';
    $novaPeriodicidade         = $_POST['periodicidade'] ?? '';
    $novaEntidadeResponsavel   = $_POST['entidade_responsavel'] ?? '';
    $novasObservacoesGarantia  = $_POST['observacoes_garantia'] ?? '';

   // Por defeito, mantém o ficheiro que já existia
    $novoCaminhoFicheiroGarantia = $_POST['caminho_ficheiro_atual'] ?? '';

    // Processar o upload do ficheiro (só a parte de MOVER, sem repetir validação)
    if (empty(validar_ficheiro_upload($_FILES['ficheiro_garantia'], false)) && !empty($_FILES['ficheiro_garantia']['name'])) {
        $pastaDestino = __DIR__ . '/../../../assets/uploads/';

        // Apaga o ficheiro antigo, se existir
        if (!empty($novoCaminhoFicheiroGarantia) && file_exists($pastaDestino . $novoCaminhoFicheiroGarantia)) {
            unlink($pastaDestino . $novoCaminhoFicheiroGarantia);
        }

        $extensao = strtolower(pathinfo($_FILES['ficheiro_garantia']['name'], PATHINFO_EXTENSION));
        $novoCaminhoFicheiroGarantia = uniqid('gar_') . '.' . $extensao;
        move_uploaded_file($_FILES['ficheiro_garantia']['tmp_name'], $pastaDestino . $novoCaminhoFicheiroGarantia);
    }

    $erros = array_merge(
        validar_datas_garantia($novaDataInicioGarantia, $novaDataFimGarantia),
        validar_data_inicio_vs_aquisicao($novaDataInicioGarantia, $dataAquisicaoEquipamento),
        validar_tipo_contrato($novoTipoContrato, $novoTemContratoManutencao),
        validar_periodicidade($novaPeriodicidade, $novoTemContratoManutencao),
        validar_observacoes_garantia($novasObservacoesGarantia),
        validar_entidade_responsavel($novaEntidadeResponsavel, $novaDataInicioGarantia, $novaDataFimGarantia, $novoTemContratoManutencao),
        validar_contexto_garantia($novaDataInicioGarantia, $novaDataFimGarantia, $novoTemContratoManutencao),
        validar_ficheiro_upload($_FILES['ficheiro_garantia'], false)
    );

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
                MYSQL_USERNAME,
                MYSQL_PASSWORD
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Converte valores vazios em NULL antes de atualizar (campos opcionais) para evitar erros
            $idFornecedorParam  = !empty($novaEntidadeResponsavel) && is_numeric($novaEntidadeResponsavel) ? $novaEntidadeResponsavel : null;
            $dataInicioParam    = !empty($novaDataInicioGarantia) ? $novaDataInicioGarantia : null;
            $dataFimParam       = !empty($novaDataFimGarantia) ? $novaDataFimGarantia : null;
            $tipoContratoParam  = $novoTemContratoManutencao && !empty($novoTipoContrato) ? $novoTipoContrato : null;
            $periodicidadeParam = $novoTemContratoManutencao && !empty($novaPeriodicidade) ? $novaPeriodicidade : null;

            $stmt = $ligacao->prepare("
                UPDATE garantias_contratos 
                SET data_inicio_garantia    = :data_inicio_garantia,
                    data_fim_garantia       = :data_fim_garantia,
                    tem_contrato_manutencao = :tem_contrato_manutencao,
                    tipo_contrato           = :tipo_contrato,
                    periodicidade           = :periodicidade,
                    id_fornecedor           = :id_fornecedor,
                    observacoes             = :observacoes,
                    caminho_ficheiro        = :caminho_ficheiro
                WHERE id = :id AND apagado = 0
            ");

            $stmt->bindParam(':data_inicio_garantia',    $dataInicioParam,             PDO::PARAM_STR);
            $stmt->bindParam(':data_fim_garantia',       $dataFimParam,                PDO::PARAM_STR);
            $stmt->bindParam(':tem_contrato_manutencao', $novoTemContratoManutencao,   PDO::PARAM_INT);
            $stmt->bindParam(':tipo_contrato',           $tipoContratoParam,           PDO::PARAM_STR);
            $stmt->bindParam(':periodicidade',           $periodicidadeParam,          PDO::PARAM_STR);
            $stmt->bindParam(':id_fornecedor',           $idFornecedorParam,           PDO::PARAM_INT);
            $stmt->bindParam(':observacoes',             $novasObservacoesGarantia,    PDO::PARAM_STR);
            $stmt->bindParam(':caminho_ficheiro',        $novoCaminhoFicheiroGarantia, PDO::PARAM_STR);
            $stmt->bindParam(':id',                      $idGarantia,                  PDO::PARAM_INT);

            $stmt->execute();

            // Vai buscar o id_equipamento desta garantia, para redirecionar ao detalhes.php certo
            $stmtEquip = $ligacao->prepare("SELECT id_equipamento FROM garantias_contratos WHERE id = :id");
            $stmtEquip->bindParam(':id', $idGarantia, PDO::PARAM_INT);
            $stmtEquip->execute();
            $idEquipamentoGar = $stmtEquip->fetchColumn();

            header('Location: ' . BASE_URL . '/private/views/equipamentos/detalhes.php?id_equipamento=' . aes_encrypt($idEquipamentoGar));
            exit;

        } catch (PDOException $err) {
            $erros[] = "Erro ao atualizar a garantia/contrato: " . $err->getMessage();
        }
    }
}

//SELECT da garantia
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->prepare("SELECT * FROM garantias_contratos WHERE id = :id AND apagado = 0");
    $stmt->bindParam(':id', $idGarantia, PDO::PARAM_INT);
    $stmt->execute();

    $garantia = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$garantia) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

    // Carregar fornecedores disponíveis
    $fornecedoresDisponiveis = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $garantia = null;
    $fornecedoresDisponiveis = [];
}

$ligacao = null;

// Encripta o id_equipamento para usar no link do botão Cancelar
$idEquipamentoEncrypted = aes_encrypt($garantia->id_equipamento);
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-pen-to-square me-2"></i> Editar Garantia / Contrato</strong></h2>
                        <hr>
                        <form action="editar_gar.php?id_garantia=<?= $idGarantiaEncrypted ?>" method="post" enctype="multipart/form-data" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="data_inicio_garantia" class="form-label">Data de Início da Garantia </label>
                                    <input type="text" class="form-control" name="data_inicio_garantia" id="data_inicio_garantia" 
                                        value="<?= htmlspecialchars($garantia->data_inicio_garantia ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="data_fim_garantia" class="form-label">Data de Fim da Garantia </label>
                                    <input type="text" class="form-control" name="data_fim_garantia" id="data_fim_garantia" 
                                        value="<?= htmlspecialchars($garantia->data_fim_garantia ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="tem_contrato_manutencao" id="tem_contrato_manutencao" value="1"
                                            <?= $garantia->tem_contrato_manutencao == 1 ? 'checked' : '' ?>>
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
                                        <option value="" <?= empty($garantia->tipo_contrato) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="manutencao_preventiva" <?= $garantia->tipo_contrato == 'manutencao_preventiva' ? 'selected' : '' ?>>Manutenção Preventiva</option>
                                        <option value="manutencao_corretiva" <?= $garantia->tipo_contrato == 'manutencao_corretiva' ? 'selected' : '' ?>>Manutenção Corretiva</option>
                                        <option value="manutencao_completa" <?= $garantia->tipo_contrato == 'manutencao_completa' ? 'selected' : '' ?>>Manutenção Completa (Preventiva + Corretiva)</option>
                                        <option value="outro" <?= $garantia->tipo_contrato == 'outro' ? 'selected' : '' ?>>Outro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="select_periodicidade" class="form-label">Periodicidade</label>
                                    <select class="form-select" name="periodicidade" id="select_periodicidade">
                                        <option value="" <?= empty($garantia->periodicidade) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="mensal" <?= $garantia->periodicidade == 'mensal' ? 'selected' : '' ?>>Mensal</option>
                                        <option value="trimestral" <?= $garantia->periodicidade == 'trimestral' ? 'selected' : '' ?>>Trimestral</option>
                                        <option value="semestral" <?= $garantia->periodicidade == 'semestral' ? 'selected' : '' ?>>Semestral</option>
                                        <option value="anual" <?= $garantia->periodicidade == 'anual' ? 'selected' : '' ?>>Anual</option>
                                        <option value="nao_aplicavel" <?= $garantia->periodicidade == 'nao_aplicavel' ? 'selected' : '' ?>>Não Aplicável</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_entidade_responsavel" class="form-label">Entidade Responsável </label>
                                    <select class="form-select" name="entidade_responsavel" id="select_entidade_responsavel">
                                        <option value="">-- Nenhuma --</option>
                                        <?php foreach ($fornecedoresDisponiveis as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" <?= $garantia->id_fornecedor == $fornecedor['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fornecedor['nome_empresa']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="ficheiro_garantia" class="form-label">Ficheiro</label>
                                    <input type="file" class="form-control" name="ficheiro_garantia" id="ficheiro_garantia" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    <?php if (!empty($garantia->caminho_ficheiro)): ?>
                                        <small class="text-muted">
                                            Ficheiro atual: 
                                            <a href="<?= BASE_URL ?>/assets/uploads/<?= htmlspecialchars($garantia->caminho_ficheiro) ?>" target="_blank">
                                                <?= htmlspecialchars($garantia->caminho_ficheiro) ?>
                                            </a>
                                            (deixa em branco para manter)
                                        </small>
                                    <?php endif; ?>
                                    <input type="hidden" name="caminho_ficheiro_atual" value="<?= htmlspecialchars($garantia->caminho_ficheiro ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes_garantia" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_garantia" id="texto_observacoes_garantia" 
                                        value="<?= htmlspecialchars($garantia->observacoes ?? '') ?>">
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

                            <!-- Mensagem de erro -->
                            <?php if (!empty($erros)): ?> 
                                <div class="alert alert-danger text-center" role="alert"> 
                                    <?php foreach ($erros as $erro): ?> 
                                        <div><?= htmlspecialchars($erro) ?></div> 
                                    <?php endforeach; ?> 
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