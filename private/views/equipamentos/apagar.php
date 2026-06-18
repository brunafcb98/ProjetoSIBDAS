<?php  
// -------------------------------------------------------------------- 
// SEGURANÇA: Proteção de acesso à página. 
// Este ficheiro deve ser acedido apenas por utilizadores autenticados. 
// Caso não exista sessão iniciada, o utilizador será redirecionado para o login.
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged(); // Inicia a sessão (se necessário) e verifica se o utilizador está autenticado 

// Desencriptação e validação do ID equipamento
$idEquipamentoEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEquipamentoEncrypted);

if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

//Vai buscar dados do equipamento
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("
        SELECT e.codigo_interno, e.designacao, e.marca, e.estado,
               l.servico, l.sala_internamento_gabinete
        FROM equipamentos e
        LEFT JOIN localizacoes l ON e.id_localizacao = l.id
        WHERE e.id = :id AND e.apagado = 0
    ");
    $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmt->execute();

    $equipamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipamento) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

} catch (PDOException $err) {
    echo "<p class='text-danger'>Erro: " . $err->getMessage() . "</p>";
    exit;
}

$estados = [
    'ativo'       => 'Ativo',
    'manutencao'  => 'Em Manutenção',
    'inativo'     => 'Inativo',
    'calibracao'  => 'Em Calibração',
    'quarentena'  => 'Em Quarentena',
    'abatido'     => 'Abatido'
];
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

                    <p class="mb-2 fs-5">Deseja eliminar o equipamento?</p>
                    <h4 class="mb-4"><strong><?= htmlspecialchars($equipamento['designacao']) ?></strong></h4>

                    <div class="mb-4">
                        <span class="d-block mb-1"><i class="fa-solid fa-barcode me-2"></i>Código: <strong><?= htmlspecialchars($equipamento['codigo_interno']) ?></strong></span>
                        <span class="d-block mb-1"><i class="fa-solid fa-tag me-2"></i>Marca: <strong><?= htmlspecialchars($equipamento['marca']) ?></strong></span>
                        <span class="d-block mb-1"><i class="fa-solid fa-circle-info me-2"></i>Estado: <strong><strong><?= htmlspecialchars($estados[$equipamento['estado']]) ?></strong></span>
                        <span class="d-block"><i class="fa-solid fa-map-marker-alt me-2"></i>Localização: <strong><strong><?= htmlspecialchars($equipamento['servico'] . ' - ' . $equipamento['sala_internamento_gabinete']) ?></strong></span>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="equipamentos.php" class="btn btn-outline-secondary px-4">
                            <i class="fa-solid fa-xmark me-2"></i>Não
                        </a>
                        <a href="confirmar_apagar.php?id_equipamento=<?= urlencode($idEquipamentoEncrypted) ?>" class="btn btn-danger px-4">
                            <i class="fa-solid fa-check me-2"></i>Sim
                        </a>
                    </div>

                </div>
            </div>
        </main>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>