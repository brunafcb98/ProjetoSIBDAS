<?php  
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();

// Desencriptação e validação do ID garantia
$idGarantiaEncrypted = $_GET['id_garantia'] ?? null;
$idGarantia = aes_decrypt($idGarantiaEncrypted);

if (!$idGarantia || !is_numeric($idGarantia)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

//Vai buscar dados da garantia
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("
        SELECT g.*, e.designacao AS equipamento_designacao
        FROM garantias_contratos g
        LEFT JOIN equipamentos e ON g.id_equipamento = e.id
        WHERE g.id = :id AND g.apagado = 0
    ");
    $stmt->bindParam(':id', $idGarantia, PDO::PARAM_INT);
    $stmt->execute();

    $garantia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$garantia) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

} catch (PDOException $err) {
    echo "<p class='text-danger'>Erro: " . $err->getMessage() . "</p>";
    exit;
}

$tipos_contrato = [
    'garantia_fabricante'   => 'Garantia de Fabricante',
    'manutencao_preventiva' => 'Manutenção Preventiva',
    'manutencao_corretiva'  => 'Manutenção Corretiva',
    'manutencao_completa'   => 'Manutenção Completa',
    'outro'                 => 'Outro'
];

//para uso no botão Não
$idEquipamentoEncrypted = aes_encrypt($garantia['id_equipamento']);
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

                    <p class="mb-2 fs-5">Deseja eliminar esta garantia/contrato?</p>
                    <h4 class="mb-4"><strong><?= htmlspecialchars($garantia['equipamento_designacao']) ?></strong></h4>

                    <div class="mb-4">
                        <?php if (!empty($garantia['data_inicio_garantia'])): ?>
                            <span class="d-block mb-1"><i class="fa-solid fa-calendar me-2"></i>Garantia: <strong><?= date('d/m/Y', strtotime($garantia['data_inicio_garantia'])) ?> a <?= date('d/m/Y', strtotime($garantia['data_fim_garantia'])) ?></strong></span>
                        <?php endif; ?>
                        <span class="d-block mb-1"><i class="fa-solid fa-screwdriver-wrench me-2"></i>Contrato de Manutenção: <strong><?= $garantia['tem_contrato_manutencao'] == 1 ? 'Sim' : 'Não' ?></strong></span>
                        <?php if ($garantia['tem_contrato_manutencao'] == 1 && !empty($garantia['tipo_contrato'])): ?>
                            <span class="d-block"><i class="fa-solid fa-file-contract me-2"></i>Tipo: <strong><?= htmlspecialchars($tipos_contrato[$garantia['tipo_contrato']] ?? $garantia['tipo_contrato']) ?></strong></span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="../equipamentos/detalhes.php?id_equipamento=<?= $idEquipamentoEncrypted ?>" class="btn btn-outline-secondary px-4">
                            <i class="fa-solid fa-xmark me-2"></i>Não
                        </a>
                        <a href="confirmar_apagar_gar.php?id_garantia=<?= urlencode($idGarantiaEncrypted) ?>" class="btn btn-danger px-4">
                            <i class="fa-solid fa-check me-2"></i>Sim
                        </a>
                    </div>

                </div>
            </div>
        </main>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>