<?php  
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();
require_once __DIR__ . '/../../includes/validacoes.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

//Desencriptação e validar ID documento
$idDocumentoEncrypted = $_GET['id_documento'] ?? null;
$idDocumento = aes_decrypt($idDocumentoEncrypted);

if (!$idDocumento || !is_numeric($idDocumento)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

//Detetar submissao via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoTipoDocumento   = $_POST['tipo_documento'] ?? '';
    $novoNomeDocumento   = $_POST['nome_documento'] ?? '';
    $novaDataDocumento   = $_POST['data_documento'] ?? '';
    $novaDataValidade    = $_POST['data_validade'] ?? '';
    $novoFornecedorDoc   = $_POST['fornecedor_documento'] ?? '';

   // Por defeito, mantém o ficheiro que já existia
    $novoCaminhoFicheiro = $_POST['caminho_ficheiro_atual'] ?? '';

    // Processar o upload do ficheiro (só a parte de MOVER, sem repetir validação)
    if (empty(validar_ficheiro_upload($_FILES['ficheiro_documento'], false)) && !empty($_FILES['ficheiro_documento']['name'])) {
        $pastaDestino = __DIR__ . '/../../../assets/uploads/';

        // Apaga o ficheiro antigo, se existir
        if (!empty($novoCaminhoFicheiro) && file_exists($pastaDestino . $novoCaminhoFicheiro)) {
            unlink($pastaDestino . $novoCaminhoFicheiro);
        }

        $extensao = strtolower(pathinfo($_FILES['ficheiro_documento']['name'], PATHINFO_EXTENSION));
        $novoCaminhoFicheiro = uniqid('doc_') . '.' . $extensao;
        move_uploaded_file($_FILES['ficheiro_documento']['tmp_name'], $pastaDestino . $novoCaminhoFicheiro);
    }

    $erros = array_merge(
        validar_tipo_documento($novoTipoDocumento),
        validar_nome_documento($novoNomeDocumento),
        validar_data_documento($novaDataDocumento),
        validar_data_validade($novaDataValidade, $novaDataDocumento),
        validar_ficheiro_upload($_FILES['ficheiro_documento'], false)
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
            $idFornecedorParam = !empty($novoFornecedorDoc) && is_numeric($novoFornecedorDoc) ? $novoFornecedorDoc : null;
            $dataValidadeParam = !empty($novaDataValidade) ? $novaDataValidade : null;

            $stmt = $ligacao->prepare("
                UPDATE documentos 
                SET tipo_documento   = :tipo_documento,
                    nome_documento   = :nome_documento,
                    data_documento   = :data_documento,
                    data_validade    = :data_validade,
                    id_fornecedor    = :id_fornecedor,
                    caminho_ficheiro = :caminho_ficheiro
                WHERE id = :id AND apagado = 0
            ");

            $stmt->bindParam(':tipo_documento',   $novoTipoDocumento,   PDO::PARAM_STR);
            $stmt->bindParam(':nome_documento',   $novoNomeDocumento,   PDO::PARAM_STR);
            $stmt->bindParam(':data_documento',   $novaDataDocumento,   PDO::PARAM_STR);
            $stmt->bindParam(':data_validade',    $dataValidadeParam,   PDO::PARAM_STR);
            $stmt->bindParam(':id_fornecedor',    $idFornecedorParam,   PDO::PARAM_INT);
            $stmt->bindParam(':caminho_ficheiro', $novoCaminhoFicheiro, PDO::PARAM_STR);
            $stmt->bindParam(':id',               $idDocumento,         PDO::PARAM_INT);

            $stmt->execute();

            // Vai buscar o id_equipamento deste documento, para redirecionar ao detalhes.php certo
            $stmtEquip = $ligacao->prepare("SELECT id_equipamento FROM documentos WHERE id = :id");
            $stmtEquip->bindParam(':id', $idDocumento, PDO::PARAM_INT);
            $stmtEquip->execute();
            $idEquipamentoDoc = $stmtEquip->fetchColumn();

            header('Location: ' . BASE_URL . '/private/views/equipamentos/detalhes.php?id_equipamento=' . aes_encrypt($idEquipamentoDoc));
            exit;

        } catch (PDOException $err) {
            $erros[] = "Erro ao atualizar o documento: " . $err->getMessage();
        }
    }
}

//SELECT do documento
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->prepare("SELECT * FROM documentos WHERE id = :id AND apagado = 0");
    $stmt->bindParam(':id', $idDocumento, PDO::PARAM_INT);
    $stmt->execute();

    $documento = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$documento) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

    // Carregar fornecedores disponíveis
    $fornecedoresDisponiveis = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $documento = null;
    $fornecedoresDisponiveis = [];
}

$ligacao = null;

// Encripta o id_equipamento para usar no botão Cancelar, evitando expor o ID no URL
$idEquipamentoEncrypted = aes_encrypt($documento->id_equipamento);
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-pen-to-square me-2"></i> Editar Documento</strong></h2>
                        <hr>
                        <form action="editar_doc.php?id_documento=<?= $idDocumentoEncrypted ?>" method="post" enctype="multipart/form-data" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_tipo_documento" class="form-label">Tipo de Documento</label>
                                    <select class="form-select" name="tipo_documento" id="select_tipo_documento">
                                        <option value="" <?= empty($documento->tipo_documento) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="manual" <?= $documento->tipo_documento == 'manual' ? 'selected' : '' ?>>Manual de Instruções</option>
                                        <option value="certificado_calibracao" <?= $documento->tipo_documento == 'certificado_calibracao' ? 'selected' : '' ?>>Certificado de Calibração</option>
                                        <option value="fatura" <?= $documento->tipo_documento == 'fatura' ? 'selected' : '' ?>>Fatura de Compra</option>
                                        <option value="ficha_tecnica" <?= $documento->tipo_documento == 'ficha_tecnica' ? 'selected' : '' ?>>Ficha Técnica</option>
                                        <option value="certificado_conformidade" <?= $documento->tipo_documento == 'certificado_conformidade' ? 'selected' : '' ?>>Certificado de Conformidade</option>
                                        <option value="outro" <?= $documento->tipo_documento == 'outro' ? 'selected' : '' ?>>Outro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_nome_documento" class="form-label">Nome do Documento</label>
                                    <input type="text" class="form-control" name="nome_documento" id="texto_nome_documento" 
                                        value="<?= htmlspecialchars($documento->nome_documento) ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="data_documento" class="form-label">Data do Documento</label>
                                    <input type="text" class="form-control" name="data_documento" id="data_documento" 
                                        value="<?= htmlspecialchars($documento->data_documento) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="data_validade" class="form-label">Data de Validade <small>(opcional)</small></label>
                                    <input type="text" class="form-control" name="data_validade" id="data_validade" 
                                        value="<?= htmlspecialchars($documento->data_validade ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_fornecedor_documento" class="form-label">Fornecedor Relacionado <small>(opcional)</small></label>
                                    <select class="form-select" name="fornecedor_documento" id="select_fornecedor_documento">
                                        <option value="">-- Nenhum --</option>
                                        <?php foreach ($fornecedoresDisponiveis as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" <?= $documento->id_fornecedor == $fornecedor['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fornecedor['nome_empresa']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="ficheiro_documento" class="form-label">Ficheiro</label>
                                    <input type="file" class="form-control" name="ficheiro_documento" id="ficheiro_documento" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    <?php if (!empty($documento->caminho_ficheiro)): ?>
                                        <small class="text-muted">
                                            Ficheiro atual: 
                                            <a href="<?= BASE_URL ?>/assets/uploads/<?= htmlspecialchars($documento->caminho_ficheiro) ?>" target="_blank">
                                                <?= htmlspecialchars($documento->caminho_ficheiro) ?>
                                            </a>
                                            (deixa em branco para manter)
                                        </small>
                                    <?php endif; ?>
                                    <input type="hidden" name="caminho_ficheiro_atual" value="<?= htmlspecialchars($documento->caminho_ficheiro) ?>">
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
flatpickr("#data_documento", {
    dateFormat: "Y-m-d"
});
flatpickr("#data_validade", {
    dateFormat: "Y-m-d"
});
</script>

<?php include '../../includes/footer.php'; ?>