<?php  
// -------------------------------------------------------------------- 
// SEGURANÇA: Proteção de acesso à página. 
// Este ficheiro deve ser acedido apenas por utilizadores autenticados. 
// Caso não exista sessão iniciada, o utilizador será redirecionado para o login.
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged(); // Inicia a sessão (se necessário) e verifica se o utilizador está autenticado 
require_once __DIR__ . '/../../includes/validacoes.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

//Desencriptação e validar ID fornecedor
$idFornecedorEncrypted = $_GET['id_fornecedor'] ?? null;
$idFornecedor = aes_decrypt($idFornecedorEncrypted);

if (!$idFornecedor || !is_numeric($idFornecedor)) {
    header('Location: ' . BASE_URL . '/private/views/fornecedores/fornecedores.php');
    exit;
}

// Detetar submissão via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoNome            = $_POST['nome_fornecedor'] ?? '';
    $novoNif             = $_POST['nif_fornecedor'] ?? '';
    $novoTipo            = $_POST['tipo_fornecedor'] ?? '';
    $novaMorada          = $_POST['morada_fornecedor'] ?? '';
    $novoTelefone        = $_POST['telefone_fornecedor'] ?? '';
    $novoEmail           = $_POST['email_fornecedor'] ?? '';
    $novoWebsite         = $_POST['website_fornecedor'] ?? '';
    $novaPessoaContacto  = $_POST['pessoa_contacto_fornecedor'] ?? '';
    $novoTelefoneContacto = $_POST['telefone_contacto_fornecedor'] ?? '';
    $novasObservacoes    = $_POST['observacoes_fornecedor'] ?? '';

    $erros = array_merge(
        validar_nome_fornecedor($novoNome),
        validar_nif($novoNif),
        validar_tipo_fornecedor($novoTipo),
        validar_morada_fornecedor($novaMorada),
        validar_telefone($novoTelefone),
        validar_email($novoEmail),
        validar_website($novoWebsite),
        validar_pessoa_contacto($novaPessoaContacto),
        validar_telefone_contacto($novoTelefoneContacto),
        validar_observacoes_fornecedor($novasObservacoes)
    );

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
                MYSQL_USERNAME,
                MYSQL_PASSWORD
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $ligacao->prepare("
                UPDATE fornecedores
                SET nome_empresa              = :nome,
                    nif                       = :nif,
                    tipo                      = :tipo,
                    morada                    = :morada,
                    telefone                  = :telefone,
                    email                     = :email,
                    website                   = :website,
                    pessoa_contacto           = :pessoa_contacto,
                    telefone_pessoa_contacto  = :telefone_contacto,
                    observacoes               = :observacoes
                WHERE id = :id AND apagado = 0
            ");

            $stmt->bindParam(':nome',             $novoNome,             PDO::PARAM_STR);
            $stmt->bindParam(':nif',              $novoNif,              PDO::PARAM_STR);
            $stmt->bindParam(':tipo',             $novoTipo,             PDO::PARAM_STR);
            $stmt->bindParam(':morada',           $novaMorada,           PDO::PARAM_STR);
            $stmt->bindParam(':telefone',         $novoTelefone,         PDO::PARAM_STR);
            $stmt->bindParam(':email',            $novoEmail,            PDO::PARAM_STR);
            $stmt->bindParam(':website',          $novoWebsite,          PDO::PARAM_STR);
            $stmt->bindParam(':pessoa_contacto',  $novaPessoaContacto,   PDO::PARAM_STR);
            $stmt->bindParam(':telefone_contacto',$novoTelefoneContacto, PDO::PARAM_STR);
            $stmt->bindParam(':observacoes',      $novasObservacoes,     PDO::PARAM_STR);
            $stmt->bindParam(':id',               $idFornecedor,         PDO::PARAM_INT);

            $stmt->execute();

            header('Location: ' . BASE_URL . '/private/views/fornecedores/fornecedores.php');
            exit;

        } catch (PDOException $err) {
            $erros[] = "Erro ao atualizar o fornecedor: " . $err->getMessage();
        }
    }
}

//SELECT do fornecedor
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparar e executar a query com segurança
    // AND apagado = 0 garante que fornecedores com soft delete não são editáveis
    $stmt = $ligacao->prepare("SELECT * FROM fornecedores WHERE id = :id AND apagado = 0");
    $stmt->bindParam(':id', $idFornecedor, PDO::PARAM_INT);
    $stmt->execute();

    $fornecedor = $stmt->fetch(PDO::FETCH_OBJ);

    // Se não encontrou o fornecedor, redireciona
    if (!$fornecedor) {
        header('Location: ' . BASE_URL . '/private/views/fornecedores/fornecedores.php');
        exit;
    }

} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $fornecedor = null;
}

// Fecha a ligação
$ligacao = null;
/*
// Para testar (temporário) - verificar se o ID do equipamento desencriptado corresponde ao da BD
echo $idFornecedor;
*/
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-pen-to-square me-2"></i> Atualização de Dados - Fornecedor</strong></h2>
                        <hr>

                        <form action="editar_f.php?id_fornecedor=<?= $idFornecedorEncrypted ?>" method="post" novalidate>

                            <!-- Dados da empresa -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_nome" class="form-label">Nome da Empresa</label>
                                    <input type="text" class="form-control" name="nome_fornecedor" id="texto_nome" 
                                        value="<?= htmlspecialchars($fornecedor->nome_empresa) ?>" list="empresas" required>
                                    <datalist id="empresas">
                                        <option value="Philips Healthcare">
                                        <option value="Dräger">
                                        <option value="B. Braun">
                                        <option value="Siemens Healthineers">
                                        <option value="Zoll Medical">
                                        <option value="Medtronic">
                                        <option value="Pentax Medical">
                                        <option value="Fresenius Medical Care">
                                    </datalist>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_nif" class="form-label">NIF</label>
                                    <input type="text" class="form-control" name="nif_fornecedor" id="texto_nif" 
                                        value="<?= htmlspecialchars($fornecedor->nif) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="select_tipo" class="form-label">Tipo de Fornecedor</label>
                                    <select class="form-select" name="tipo_fornecedor" id="select_tipo">
                                        <option value="" <?= empty($fornecedor->tipo) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="fabricante" <?= $fornecedor->tipo == 'fabricante' ? 'selected' : '' ?>>Fabricante</option>
                                        <option value="distribuidor" <?= $fornecedor->tipo == 'distribuidor' ? 'selected' : '' ?>>Distribuidor / Fornecedor Comercial</option>
                                        <option value="assistencia" <?= $fornecedor->tipo == 'assistencia' ? 'selected' : '' ?>>Empresa de Assistência Técnica</option>
                                        <option value="consumiveis" <?= $fornecedor->tipo == 'consumiveis' ? 'selected' : '' ?>>Fornecedor de Consumíveis</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_morada" class="form-label">Morada <small>(Nº Porta, Andar)</small></label>
                                    <input type="text" class="form-control" name="morada_fornecedor" id="texto_morada" 
                                        value="<?= htmlspecialchars($fornecedor->morada ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_telefone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control" name="telefone_fornecedor" id="texto_telefone" 
                                        value="<?= htmlspecialchars($fornecedor->telefone) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email_fornecedor" id="texto_email" 
                                        value="<?= htmlspecialchars($fornecedor->email) ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_website" class="form-label">Website</label>
                                    <input type="text" class="form-control" name="website_fornecedor" id="texto_website" 
                                        value="<?= htmlspecialchars($fornecedor->website ?? '') ?>">
                                </div>
                            </div>

                            <!-- Pessoa de contacto -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_pessoa_contacto" class="form-label">Pessoa de Contacto</label>
                                    <input type="text" class="form-control" name="pessoa_contacto_fornecedor" id="texto_pessoa_contacto" 
                                        value="<?= htmlspecialchars($fornecedor->pessoa_contacto ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_telefone_contacto" class="form-label">Telefone da Pessoa de Contacto</label>
                                    <input type="text" class="form-control" name="telefone_contacto_fornecedor" id="texto_telefone_contacto" 
                                        value="<?= htmlspecialchars($fornecedor->telefone_pessoa_contacto ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_fornecedor" id="texto_observacoes" 
                                        value="<?= htmlspecialchars($fornecedor->observacoes ?? '') ?>">
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="d-flex justify-content-end gap-2 mb-4">
                                <a href="fornecedores.php" class="btn btn-outline-secondary">
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

<?php include '../../includes/footer.php'; ?>