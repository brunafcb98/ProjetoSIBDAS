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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recolher dados
    $tipoDocumento     = $_POST["tipo_documento"] ?? "";
    $nomeDocumento     = $_POST["nome_documento"] ?? "";
    $dataDocumento     = $_POST["data_documento"] ?? "";
    $dataValidade      = $_POST["data_validade"] ?? "";
    $fornecedorDoc     = $_POST["fornecedor_documento"] ?? "";

    // 2. Trim
    $nomeDocumento   = trim($nomeDocumento);
    $dataDocumento   = trim($dataDocumento);
    $dataValidade    = trim($dataValidade);

    // 2.5 Processar o upload do ficheiro (só a parte de MOVER, sem repetir validação)
    $caminhoFicheiro = "";
    if (empty(validar_ficheiro_upload($_FILES['ficheiro_documento'], true)) && !empty($_FILES['ficheiro_documento']['name'])) {
        $pastaDestino = __DIR__ . '/../../../assets/uploads/';
        $extensao = strtolower(pathinfo($_FILES['ficheiro_documento']['name'], PATHINFO_EXTENSION));
        $caminhoFicheiro = uniqid('doc_') . '.' . $extensao;
        move_uploaded_file($_FILES['ficheiro_documento']['tmp_name'], $pastaDestino . $caminhoFicheiro);
    }

    // 3. Validar os dados
    $erros = [];
    $erro_sistema = "";

    $erros = array_merge(
        validar_tipo_documento($tipoDocumento),
        validar_nome_documento($nomeDocumento),
        validar_data_documento($dataDocumento),
        validar_data_validade($dataValidade, $dataDocumento),
        validar_ficheiro_upload($_FILES['ficheiro_documento'], true)
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
            $idFornecedorParam = !empty($fornecedorDoc) && is_numeric($fornecedorDoc) ? $fornecedorDoc : null;
            $dataValidadeParam = !empty($dataValidade) ? $dataValidade : null;

            $sql = "INSERT INTO documentos (
                id_equipamento, id_fornecedor, tipo_documento, nome_documento,
                data_documento, data_validade, caminho_ficheiro
            ) VALUES (
                :id_equipamento, :id_fornecedor, :tipo_documento, :nome_documento,
                :data_documento, :data_validade, :caminho_ficheiro
            )";

            $stmt = $ligacao->prepare($sql);
            $stmt->execute([
                ':id_equipamento'   => $idEquipamento,
                ':id_fornecedor'    => $idFornecedorParam,
                ':tipo_documento'   => $tipoDocumento,
                ':nome_documento'   => $nomeDocumento,
                ':data_documento'   => $dataDocumento,
                ':data_validade'    => $dataValidadeParam,
                ':caminho_ficheiro' => $caminhoFicheiro
            ]);

            $idNovoDocumento = $ligacao->lastInsertId();

            // Vai buscar o id do utilizador autenticado
            $stmtUser = $ligacao->prepare("SELECT id FROM utilizadores WHERE email = :email");
            $stmtUser->execute([':email' => $_SESSION['utilizador']]);
            $idUtilizador = $stmtUser->fetchColumn();

            // Regista o evento na tabela de logs
            $stmtLog = $ligacao->prepare("INSERT INTO logs (id_utilizador, tipo_evento, descricao) VALUES (:id_utilizador, 'documento_criado', :descricao)");
            $stmtLog->execute([
                ':id_utilizador' => $idUtilizador,
                ':descricao'     => 'Documento criado (id: ' . $idNovoDocumento . ', equipamento: ' . $idEquipamento . ')'
            ]);

            // Guarda mensagem de sucesso para o Toast aparecer
            $_SESSION['toast_success'] = 'Documento adicionado com sucesso.';

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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-file me-2"></i> Adicionar Documento</strong></h2>
                        <hr>
                        <form action="#" method="post" enctype="multipart/form-data" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_tipo_documento" class="form-label">Tipo de Documento</label>
                                    <select class="form-select" name="tipo_documento" id="select_tipo_documento">
                                        <option value="" <?= empty($_POST['tipo_documento']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="manual" <?= (($_POST['tipo_documento'] ?? '') == 'manual') ? 'selected' : '' ?>>Manual de Instruções</option>
                                        <option value="certificado_calibracao" <?= (($_POST['tipo_documento'] ?? '') == 'certificado_calibracao') ? 'selected' : '' ?>>Certificado de Calibração</option>
                                        <option value="fatura" <?= (($_POST['tipo_documento'] ?? '') == 'fatura') ? 'selected' : '' ?>>Fatura de Compra</option>
                                        <option value="ficha_tecnica" <?= (($_POST['tipo_documento'] ?? '') == 'ficha_tecnica') ? 'selected' : '' ?>>Ficha Técnica</option>
                                        <option value="certificado_conformidade" <?= (($_POST['tipo_documento'] ?? '') == 'certificado_conformidade') ? 'selected' : '' ?>>Certificado de Conformidade</option>
                                        <option value="outro" <?= (($_POST['tipo_documento'] ?? '') == 'outro') ? 'selected' : '' ?>>Outro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_nome_documento" class="form-label">Nome do Documento</label>
                                    <input type="text" class="form-control" name="nome_documento" id="texto_nome_documento" 
                                        value="<?= htmlspecialchars($_POST['nome_documento'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="data_documento" class="form-label">Data do Documento</label>
                                    <input type="text" class="form-control" name="data_documento" id="data_documento" 
                                        value="<?= htmlspecialchars($_POST['data_documento'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="data_validade" class="form-label">Data de Validade <small>(opcional)</small></label>
                                    <input type="text" class="form-control" name="data_validade" id="data_validade" 
                                        value="<?= htmlspecialchars($_POST['data_validade'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_fornecedor_documento" class="form-label">Fornecedor Relacionado <small>(opcional)</small></label>
                                    <select class="form-select" name="fornecedor_documento" id="select_fornecedor_documento">
                                        <option value="">-- Nenhum --</option>
                                        <?php foreach ($fornecedoresDisponiveis as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" <?= (($_POST['fornecedor_documento'] ?? '') == $fornecedor['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fornecedor['nome_empresa']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="ficheiro_documento" class="form-label">Ficheiro</label>
                                    <input type="file" class="form-control" name="ficheiro_documento" id="ficheiro_documento" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
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
flatpickr("#data_documento", {
    dateFormat: "Y-m-d"
});
flatpickr("#data_validade", {
    dateFormat: "Y-m-d"
});
</script>

<?php include '../../includes/footer.php'; ?>