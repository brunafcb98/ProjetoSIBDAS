<?php  
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();

// Desencriptação e validação do ID documento
$idDocumentoEncrypted = $_GET['id_documento'] ?? null;
$idDocumento = aes_decrypt($idDocumentoEncrypted);

if (!$idDocumento || !is_numeric($idDocumento)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

//Vai buscar dados do documento
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("
        SELECT d.*, e.designacao AS equipamento_designacao
        FROM documentos d
        LEFT JOIN equipamentos e ON d.id_equipamento = e.id
        WHERE d.id = :id AND d.apagado = 0
    ");
    $stmt->bindParam(':id', $idDocumento, PDO::PARAM_INT);
    $stmt->execute();

    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$documento) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

} catch (PDOException $err) {
    echo "<p class='text-danger'>Erro: " . $err->getMessage() . "</p>";
    exit;
}

$tipos_documento = [
    'manual'                   => 'Manual de Instruções',
    'certificado_calibracao'   => 'Certificado de Calibração',
    'fatura'                   => 'Fatura de Compra',
    'ficha_tecnica'             => 'Ficha Técnica',
    'certificado_conformidade' => 'Certificado de Conformidade',
    'outro'                     => 'Outro'
];

//para uso no botão Não
$idEquipamentoEncrypted = aes_encrypt($documento['id_equipamento']);
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
                <div class="card w-100 shadow rounded text-center p-4" style="max-width: 700px;">

                    <div class="text-warning display-4 mb-3">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <p class="mb-2 fs-5">Deseja eliminar o documento?</p>
                    <h4 class="mb-4"><strong><?= htmlspecialchars($documento['nome_documento']) ?></strong></h4>

                    <div class="mb-4">
                        <span class="d-block mb-1"><i class="fa-solid fa-file me-2"></i>Tipo: <strong><?= htmlspecialchars($tipos_documento[$documento['tipo_documento']]) ?></strong></span>
                        <span class="d-block mb-1"><i class="fa-solid fa-calendar me-2"></i>Data: <strong><?= date('d/m/Y', strtotime($documento['data_documento'])) ?></strong></span>
                        <span class="d-block"><i class="fa-solid fa-stethoscope me-2"></i>Equipamento: <strong><?= htmlspecialchars($documento['equipamento_designacao']) ?></strong></span>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="../equipamentos/detalhes.php?id_equipamento=<?= $idEquipamentoEncrypted ?>" class="btn btn-outline-secondary px-4">
                            <i class="fa-solid fa-xmark me-2"></i>Não
                        </a>
                        <a href="confirmar_apagar_doc.php?id_documento=<?= urlencode($idDocumentoEncrypted) ?>" class="btn btn-danger px-4">
                            <i class="fa-solid fa-check me-2"></i>Sim
                        </a>
                    </div>

                </div>
            </div>
        </main>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>