<?php  
// -------------------------------------------------------------------- 
// SEGURANÇA: Proteção de acesso à página. 
// Este ficheiro deve ser acedido apenas por utilizadores autenticados. 
// Caso não exista sessão iniciada, o utilizador será redirecionado para o login.
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged(); // Inicia a sessão (se necessário) e verifica se o utilizador está autenticado 
require_once __DIR__ . '/../../includes/validacoes.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recolher dados
    $nome             = $_POST["nome_fornecedor"] ?? "";
    $nif              = $_POST["nif_fornecedor"] ?? "";
    $tipo             = $_POST["tipo_fornecedor"] ?? "";
    $morada           = $_POST["morada_fornecedor"] ?? "";
    $telefone         = $_POST["telefone_fornecedor"] ?? "";
    $email            = $_POST["email_fornecedor"] ?? "";
    $website          = $_POST["website_fornecedor"] ?? "";
    $pessoa_contacto  = $_POST["pessoa_contacto_fornecedor"] ?? "";
    $telefone_contacto = $_POST["telefone_contacto_fornecedor"] ?? "";
    $observacoes      = $_POST["observacoes_fornecedor"] ?? "";

    // 2. Trim
    $nome             = trim($nome);
    $nif              = trim($nif);
    $tipo             = trim($tipo);
    $morada           = trim($morada);
    $telefone         = trim($telefone);
    $email            = trim($email);
    $website          = trim($website);
    $pessoa_contacto  = trim($pessoa_contacto);
    $telefone_contacto = trim($telefone_contacto);
    $observacoes      = trim($observacoes);

    // 3. Validar os dados
    $erros = [];    //para erros de validação 
    $erro_sistema = ""; //Para erros de SQL (PDO) 

    $erros = array_merge(
        validar_nome_fornecedor($nome),
        validar_nif($nif),
        validar_tipo_fornecedor($tipo),
        validar_morada_fornecedor($morada),
        validar_telefone($telefone),
        validar_email($email),
        validar_website($website),
        validar_pessoa_contacto($pessoa_contacto),
        validar_telefone_contacto($telefone_contacto),
        validar_observacoes_fornecedor($observacoes)
    );

    /* 
    if (empty($nome)) {
        $erros[] = "O campo Nome da Empresa é obrigatório.";
    }

    if (empty($nif)) {
        $erros[] = "O campo NIF é obrigatório.";
    } elseif (!preg_match('/^\d{9}$/', $nif)) {
        $erros[] = "O NIF é inválido. Deve ter exactamente 9 dígitos.";
    }

    if (empty($tipo) || $tipo == "Escolha uma opção") {
        $erros[] = "O campo Tipo de Fornecedor é obrigatório.";
    }

    if (empty($morada)) { 
        $erros[] = "O campo Morada é obrigatório."; 
    } 

    if (empty($telefone)) {
        $erros[] = "O campo Telefone é obrigatório.";
    } elseif (!preg_match('/^[29][0-9]{8}$/', $telefone)) {
        $erros[] = "O Telefone é inválido. Deve começar por 2 ou 9 e ter 9 dígitos.";
    }

    if (empty($email)) {
        $erros[] = "O campo Email é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O endereço de email não é válido.";
    }

    if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $erros[] = "O website não é válido. Use o formato https://www.exemplo.com.";
    }

    if (empty($pessoa_contacto)) {
        $erros[] = "O campo Pessoa de Contacto é obrigatório.";
    } elseif (preg_match('/\d/', $pessoa_contacto)) {
        $erros[] = "O campo Pessoa de Contacto não pode conter números.";
    }

    if (empty($telefone_contacto)) {
        $erros[] = "O campo Telefone da Pessoa de Contacto é obrigatório.";
    } elseif (!preg_match('/^[29][0-9]{8}$/', $telefone_contacto)) {
        $erros[] = "O Telefone da Pessoa de Contacto é inválido. Deve começar por 2 ou 9 e ter 9 dígitos.";
    }

    if (!empty($observacoes) && strlen($observacoes) > 500) {
        $erros[] = "As observações não podem exceder 500 caracteres.";
    }
    */
    // 4. Normalizar dados
    if (empty($erros)) {
        $nome            = ucwords(strtolower($nome));
        $morada          = ucfirst(strtolower($morada));
        $email           = strtolower($email);
        $pessoa_contacto = ucwords(strtolower($pessoa_contacto));
        $observacoes = ucfirst(strtolower($observacoes));
    }

    /*
        // Dados normalizados (para teste)
    echo "<p><strong>Dados normalizados:</strong></p>";
    echo "<ul>";
    echo "<li>Nome: $nome</li>";
    echo "<li>Morada: $morada</li>";
    echo "<li>Email: $email</li>";
    echo "<li>Pessoa de Contacto: $pessoa_contacto</li>";
    echo "<li>Observações: $observacoes</li>";
    echo "</ul>";
    */
    /*
    // Depuração: mostrar os erros recolhidos
    echo "<pre>";
    print_r($erros);
    echo "</pre>";
    */

    // 5. Se não houver erros, guardar na base de dados
    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
                MYSQL_USERNAME,
                MYSQL_PASSWORD
            );

            $sql = "INSERT INTO fornecedores (
                nome_empresa, nif, tipo, morada, telefone, email,
                website, pessoa_contacto, telefone_pessoa_contacto, observacoes
            ) VALUES (
                :nome, :nif, :tipo, :morada, :telefone, :email,
                :website, :pessoa_contacto, :telefone_contacto, :observacoes
            )";

            $stmt = $ligacao->prepare($sql);
            $stmt->execute([
                ':nome'              => $nome,
                ':nif'               => $nif,
                ':tipo'              => $tipo,
                ':morada'            => $morada,
                ':telefone'          => $telefone,
                ':email'             => $email,
                ':website'           => $website,
                ':pessoa_contacto'   => $pessoa_contacto,
                ':telefone_contacto' => $telefone_contacto,
                ':observacoes'       => $observacoes
            ]);

            // Id do fornecedor que acabou de ser inserido
            $idNovoFornecedor = $ligacao->lastInsertId();

            // Vai buscar o id do utilizador autenticado (a sessão só guarda o email)
            $stmtUser = $ligacao->prepare("SELECT id FROM utilizadores WHERE email = :email");
            $stmtUser->execute([':email' => $_SESSION['utilizador']]);
            $idUtilizador = $stmtUser->fetchColumn();

            // Regista o evento na tabela de logs
            $stmtLog = $ligacao->prepare("INSERT INTO logs (id_utilizador, tipo_evento, descricao) VALUES (:id_utilizador, 'fornecedor_criado', :descricao)");
            $stmtLog->execute([
                ':id_utilizador' => $idUtilizador,
                ':descricao'     => 'Fornecedor criado (id: ' . $idNovoFornecedor . ')'
            ]);

            $_SESSION['toast_success'] = 'Fornecedor criado com sucesso.';

            header('Location: fornecedores.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao gravar os dados: " . $err->getMessage();
        }

        $ligacao = null;
    }
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-truck me-2"></i> Inserir novo fornecedor</strong></h2>
                        <hr>

                        <form action="#" method="post" novalidate>

                            <!-- Dados da empresa -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_nome" class="form-label">Nome da Empresa</label>
                                    <input type="text" class="form-control" name="nome_fornecedor" id="texto_nome" list="empresas"
                                        value="<?= htmlspecialchars($_POST['nome_fornecedor'] ?? '') ?>">
                                    <datalist id="empresas">
                                        <option value="Philips Healthcare">
                                        <option value="Dräger">
                                        <option value="B. Braun">
                                        <option value="Siemens">
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
                                        value="<?= htmlspecialchars($_POST['nif_fornecedor'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="select_tipo" class="form-label">Tipo de Fornecedor</label>
                                    <select class="form-select" name="tipo_fornecedor" id="select_tipo">
                                        <option value="" <?= empty($_POST['tipo_fornecedor']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="fabricante" <?= (($_POST['tipo_fornecedor'] ?? '') == 'fabricante') ? 'selected' : '' ?>>Fabricante</option>
                                        <option value="distribuidor" <?= (($_POST['tipo_fornecedor'] ?? '') == 'distribuidor') ? 'selected' : '' ?>>Distribuidor / Fornecedor Comercial</option>
                                        <option value="assistencia" <?= (($_POST['tipo_fornecedor'] ?? '') == 'assistencia') ? 'selected' : '' ?>>Empresa de Assistência Técnica</option>
                                        <option value="consumiveis" <?= (($_POST['tipo_fornecedor'] ?? '') == 'consumiveis') ? 'selected' : '' ?>>Fornecedor de Consumíveis / Acessórios</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_morada" class="form-label">Morada <small>(Nº Porta, Andar)</small></label>
                                    <input type="text" class="form-control" name="morada_fornecedor" id="texto_morada"
                                        value="<?= htmlspecialchars($_POST['morada_fornecedor'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_telefone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control" name="telefone_fornecedor" id="texto_telefone"
                                        value="<?= htmlspecialchars($_POST['telefone_fornecedor'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email_fornecedor" id="texto_email"
                                        value="<?= htmlspecialchars($_POST['email_fornecedor'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_website" class="form-label">Website</label>
                                    <input type="text" class="form-control" name="website_fornecedor" id="texto_website"
                                        value="<?= htmlspecialchars($_POST['website_fornecedor'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Pessoa de contacto -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_pessoa_contacto" class="form-label">Pessoa de Contacto</label>
                                    <input type="text" class="form-control" name="pessoa_contacto_fornecedor" id="texto_pessoa_contacto"
                                        value="<?= htmlspecialchars($_POST['pessoa_contacto_fornecedor'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_telefone_contacto" class="form-label">Telefone da Pessoa de Contacto</label>
                                    <input type="text" class="form-control" name="telefone_contacto_fornecedor" id="texto_telefone_contacto"
                                        value="<?= htmlspecialchars($_POST['telefone_contacto_fornecedor'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_fornecedor" id="texto_observacoes"
                                        value="<?= htmlspecialchars($_POST['observacoes_fornecedor'] ?? '') ?>">
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

<?php include '../../includes/footer.php'; ?>