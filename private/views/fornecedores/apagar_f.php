<?php  
// -------------------------------------------------------------------- 
// SEGURANÇA: Proteção de acesso à página. 
// Este ficheiro deve ser acedido apenas por utilizadores autenticados. 
// Caso não exista sessão iniciada, o utilizador será redirecionado para o login.
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged(); // Inicia a sessão (se necessário) e verifica se o utilizador está autenticado 

// Desencriptação e validação do ID fornecedor
$idFornecedorEncrypted = $_GET['id_fornecedor'] ?? null;
$idFornecedor = aes_decrypt($idFornecedorEncrypted);

if (!$idFornecedor || !is_numeric($idFornecedor)) {
    header('Location: ' . BASE_URL . '/private/views/fornecedores/fornecedores.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("SELECT nome_empresa, tipo, pessoa_contacto FROM fornecedores WHERE id = :id AND apagado = 0");
    $stmt->bindParam(':id', $idFornecedor, PDO::PARAM_INT);
    $stmt->execute();

    $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fornecedor) {
        header('Location: ' . BASE_URL . '/private/views/fornecedores/fornecedores.php');
        exit;
    }

} catch (PDOException $err) {
    echo "<p class='text-danger'>Erro: " . $err->getMessage() . "</p>";
    exit;
}

$tipos = [
    'fabricante'   => 'Fabricante',
    'distribuidor' => 'Distribuidor / Fornecedor Comercial',
    'assistencia'  => 'Empresa de Assistência Técnica',
    'consumiveis'  => 'Fornecedor de Consumíveis / Acessórios'
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

                    <p class="mb-2 fs-5">Deseja eliminar o fornecedor?</p>
                    <h4 class="mb-4"><strong><?= htmlspecialchars($fornecedor['nome_empresa']) ?></strong></h4>

                    <div class="mb-4">
                        <span class="d-block mb-1"><i class="fa-solid fa-tag me-2"></i>Tipo: <strong><?= htmlspecialchars($tipos[$fornecedor['tipo']]) ?></strong></span>
                        <span class="d-block"><i class="fa-solid fa-user me-2"></i>Pessoa de Contacto: <strong><?= htmlspecialchars($fornecedor['pessoa_contacto'] ?? '—') ?></strong></span>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="fornecedores.php" class="btn btn-outline-secondary px-4">
                            <i class="fa-solid fa-xmark me-2"></i>Não
                        </a>
                        <a href="confirmar_apagar_f.php?id_fornecedor=<?= urlencode($idFornecedorEncrypted) ?>" class="btn btn-danger px-4">
                            <i class="fa-solid fa-check me-2"></i>Sim
                        </a>
                    </div>

                </div>
            </div>
        </main>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>