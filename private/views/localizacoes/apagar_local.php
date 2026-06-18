<?php  
// -------------------------------------------------------------------- 
// SEGURANÇA: Proteção de acesso à página. 
// Este ficheiro deve ser acedido apenas por utilizadores autenticados. 
// Caso não exista sessão iniciada, o utilizador será redirecionado para o login.
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged(); // Inicia a sessão (se necessário) e verifica se o utilizador está autenticado 

// Desencriptação e validação do ID localização
$idLocalizacaoEncrypted = $_GET['id_localizacao'] ?? null;
$idLocalizacao = aes_decrypt($idLocalizacaoEncrypted);

if (!$idLocalizacao || !is_numeric($idLocalizacao)) {
    header('Location: ' . BASE_URL . '/private/views/localizacoes/localizacoes.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("SELECT edificio, piso, servico, sala_internamento_gabinete FROM localizacoes WHERE id = :id AND apagado = 0");
    $stmt->bindParam(':id', $idLocalizacao, PDO::PARAM_INT);
    $stmt->execute();

    $localizacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$localizacao) {
        header('Location: ' . BASE_URL . '/private/views/localizacoes/localizacoes.php');
        exit;
    }

    // Conta quantos equipamentos ativos ainda estão associados a esta localização (para informar antes de apagar de que deve alterar a localizaçao destes equipamentos)
    $stmtCount = $ligacao->prepare("SELECT COUNT(*) AS total FROM equipamentos WHERE id_localizacao = :id AND apagado = 0");
    $stmtCount->bindParam(':id', $idLocalizacao, PDO::PARAM_INT);
    $stmtCount->execute();
    $totalEquipamentos = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

} catch (PDOException $err) {
    echo "<p class='text-danger'>Erro: " . $err->getMessage() . "</p>";
    exit;
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
                <div class="card w-100 shadow rounded text-center p-4" style="max-width: 700px;">

                    <div class="text-warning display-4 mb-3">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <p class="mb-2 fs-5">Deseja eliminar a localização?</p>
                    <h4 class="mb-4"><strong><?= htmlspecialchars($localizacao['servico']) ?></strong></h4>

                    <div class="mb-4">
                        <span class="d-block mb-1"><i class="fa-solid fa-building me-2"></i>Edifício: <strong><?= htmlspecialchars($localizacao['edificio']) ?></strong></span>
                        <span class="d-block mb-1"><i class="fa-solid fa-layer-group me-2"></i>Piso: <strong><?= htmlspecialchars($localizacao['piso']) ?></strong></span>
                        <span class="d-block"><i class="fa-solid fa-door-open me-2"></i>Internamento / Sala / Gabinete: <strong><?= htmlspecialchars($localizacao['sala_internamento_gabinete']) ?></strong></span>
                    </div>

                    <?php if ($totalEquipamentos > 0): ?>
                        <div class="alert alert-warning text-start">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>
                            Atenção: existe(m) <strong><?= $totalEquipamentos ?></strong> equipamento(s) associado(s) a esta localização. 
                            Na página de Equipamentos, proceda à alteração da localização dos mesmos, usando o botão editar.
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="localizacoes.php" class="btn btn-outline-secondary px-4">
                            <i class="fa-solid fa-xmark me-2"></i>Não
                        </a>
                        <a href="confirmar_apagar_local.php?id_localizacao=<?= urlencode($idLocalizacaoEncrypted) ?>" class="btn btn-danger px-4">
                            <i class="fa-solid fa-check me-2"></i>Sim
                        </a>
                    </div>

                </div>
            </div>
        </main>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>
