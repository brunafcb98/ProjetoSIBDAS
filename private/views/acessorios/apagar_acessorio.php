<?php  
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();

// Desencriptação e validação do ID acessório
$idAcessorioEncrypted = $_GET['id_acessorio'] ?? null;
$idAcessorio = aes_decrypt($idAcessorioEncrypted);

if (!$idAcessorio || !is_numeric($idAcessorio)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

//Vai buscar dados do acessório
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("
        SELECT a.*, e.designacao AS equipamento_designacao
        FROM acessorios a
        LEFT JOIN equipamentos e ON a.id_equipamento_pai = e.id
        WHERE a.id = :id AND a.apagado = 0
    ");
    $stmt->bindParam(':id', $idAcessorio, PDO::PARAM_INT);
    $stmt->execute();

    $acessorio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$acessorio) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

} catch (PDOException $err) {
    echo "<p class='text-danger'>Ocorreu um erro na ligação à base de dados. Por favor, tente novamente mais tarde.</p>";
    exit;
}

//para uso no botão Não
$idEquipamentoEncrypted = aes_encrypt($acessorio['id_equipamento_pai']);
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

                    <p class="mb-2 fs-5">Deseja eliminar o acessório?</p>
                    <h4 class="mb-4"><strong><?= htmlspecialchars($acessorio['nome']) ?></strong></h4>

                    <div class="mb-4">
                        <span class="d-block mb-1"><i class="fa-solid fa-barcode me-2"></i>Código: <strong><?= htmlspecialchars($acessorio['codigo']) ?></strong></span>
                        <span class="d-block"><i class="fa-solid fa-stethoscope me-2"></i>Equipamento: <strong><?= htmlspecialchars($acessorio['equipamento_designacao']) ?></strong></span>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="../equipamentos/detalhes.php?id_equipamento=<?= $idEquipamentoEncrypted ?>" class="btn btn-outline-secondary px-4">
                            <i class="fa-solid fa-xmark me-2"></i>Não
                        </a>
                        <a href="confirmar_apagar_acessorio.php?id_acessorio=<?= urlencode($idAcessorioEncrypted) ?>" class="btn btn-danger px-4">
                            <i class="fa-solid fa-check me-2"></i>Sim
                        </a>
                    </div>

                </div>
            </div>
        </main>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>