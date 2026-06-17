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

// Ligação à base de dados e obtenção dos dados do equipamento
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("SELECT * FROM fornecedores WHERE id = :id AND apagado = 0");
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

// Mapa de tradução: converte o valor técnico guardado na BD para o texto a apresentar ao utilizador.
// Mesma lógica aplicada na listagem (fornecedores.php)
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
                <div class="card w-100 shadow rounded" style="max-width: 900px;">
                    <div class="card-body">
                        <h2 class="mb-4">
                            <strong><i class="fa-solid fa-truck me-2"></i> Detalhes do Fornecedor</strong>
                        </h2>
                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome da Empresa</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($fornecedor['nome_empresa']) ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">NIF</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($fornecedor['nif']) ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Morada</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($fornecedor['morada'] ?? '—') ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Telefone</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($fornecedor['telefone']) ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($fornecedor['email']) ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Website</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($fornecedor['website'] ?? '—') ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Fornecedor</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($tipos[$fornecedor['tipo']]) ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pessoa de Contacto</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($fornecedor['pessoa_contacto'] ?? '—') ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Telefone da Pessoa de Contacto</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($fornecedor['telefone_pessoa_contacto'] ?? '—') ?></p>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Observações</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($fornecedor['observacoes'] ?? '—') ?></p>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="fornecedores.php" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-1"></i> Voltar
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </main>

    </div>
</div>

<?php include '../../includes/footer.php'; ?> 