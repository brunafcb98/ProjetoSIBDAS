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
    $edificio    = $_POST["edificio_localizacao"] ?? "";
    $piso        = $_POST["piso_localizacao"] ?? "";
    $servico     = $_POST["servico_localizacao"] ?? "";
    $sala        = $_POST["sala_localizacao"] ?? "";

    // 2. Trim
    $edificio    = trim($edificio);
    $piso        = trim($piso);
    $servico     = trim($servico);
    $sala        = trim($sala);

    // 3. Validar os dados
    $erros = [];    //para erros de validação 
    $erro_sistema = ""; //Para erros de SQL (PDO) 

    $erros = array_merge(
        validar_edificio($edificio),
        validar_piso($piso),
        validar_servico($servico),
        validar_sala($sala)
    );

    /*
    if (empty($edificio) || $edificio == "Escolha uma opção") {
        $erros[] = "O campo Edifício é obrigatório.";
    }

    if (empty($piso) || $piso == "Escolha uma opção") {
        $erros[] = "O campo Piso é obrigatório.";
    }

    if (empty($servico)) {
        $erros[] = "O campo Serviço é obrigatório.";
    } elseif (preg_match('/^\d+$/', $servico)) {
        $erros[] = "O campo Serviço não pode conter apenas números.";
    }

    if (empty($sala)) {
        $erros[] = "O campo Sala / Gabinete é obrigatório.";
    } elseif (strlen($sala) > 100) {
        $erros[] = "O campo Sala não pode exceder 100 caracteres.";
    }
    */
    // 4. Normalizar dados
    if (empty($erros)) {
        $servico = ucwords(strtolower($servico));
        $sala    = ucwords(strtolower($sala));
    }

    // Dados normalizados (para teste)
    /*
    echo "<p><strong>Dados normalizados:</strong></p>";
    echo "<ul>";
    echo "<li>Edifício: $edificio</li>";
    echo "<li>Piso: $piso</li>";
    echo "<li>Serviço: $servico</li>";
    echo "<li>Sala: $sala</li>";
    echo "</ul>";
    */

    // Depuração: mostrar os erros recolhidos
    /*
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

            $sql = "INSERT INTO localizacoes (
                edificio, piso, servico, sala_internamento_gabinete
            ) VALUES (
                :edificio, :piso, :servico, :sala
            )";

            $stmt = $ligacao->prepare($sql);
            $stmt->execute([
                ':edificio' => $edificio,
                ':piso'     => $piso,
                ':servico'  => $servico,
                ':sala'     => $sala
            ]);

            // Id da localização que acabou de ser inserida
            $idNovaLocalizacao = $ligacao->lastInsertId();

            // Vai buscar o id do utilizador autenticado (a sessão só guarda o email)
            $stmtUser = $ligacao->prepare("SELECT id FROM utilizadores WHERE email = :email");
            $stmtUser->execute([':email' => $_SESSION['utilizador']]);
            $idUtilizador = $stmtUser->fetchColumn();

            // Regista o evento na tabela de logs
            $stmtLog = $ligacao->prepare("INSERT INTO logs (id_utilizador, tipo_evento, descricao) VALUES (:id_utilizador, 'localizacao_criada', :descricao)");
            $stmtLog->execute([
                ':id_utilizador' => $idUtilizador,
                ':descricao'     => 'Localização criada (id: ' . $idNovaLocalizacao . ')'
            ]);

            $_SESSION['toast_success'] = 'Localização criada com sucesso.';

            header('Location: localizacoes.php');
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-map-marker-alt me-2"></i> Inserir nova localização</strong></h2>
                        <hr>

                        <form action="#" method="post" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_edificio" class="form-label">Edifício</label>
                                    <select class="form-select" name="edificio_localizacao" id="select_edificio">
                                        <option value="" <?= empty($_POST['edificio_localizacao']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="Edifício Principal" <?= (($_POST['edificio_localizacao'] ?? '') == 'Edifício Principal') ? 'selected' : '' ?>>Edifício Principal</option>
                                        <option value="Edifício A" <?= (($_POST['edificio_localizacao'] ?? '') == 'Edifício A') ? 'selected' : '' ?>>Edifício A</option>
                                        <option value="Edifício B" <?= (($_POST['edificio_localizacao'] ?? '') == 'Edifício B') ? 'selected' : '' ?>>Edifício B</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="select_piso" class="form-label">Piso</label>
                                    <select class="form-select" name="piso_localizacao" id="select_piso">
                                        <option value="" <?= empty($_POST['piso_localizacao']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="Piso 0" <?= (($_POST['piso_localizacao'] ?? '') == 'Piso 0') ? 'selected' : '' ?>>Piso 0</option>
                                        <option value="Piso 1" <?= (($_POST['piso_localizacao'] ?? '') == 'Piso 1') ? 'selected' : '' ?>>Piso 1</option>
                                        <option value="Piso 2" <?= (($_POST['piso_localizacao'] ?? '') == 'Piso 2') ? 'selected' : '' ?>>Piso 2</option>
                                        <option value="Piso 3" <?= (($_POST['piso_localizacao'] ?? '') == 'Piso 3') ? 'selected' : '' ?>>Piso 3</option>
                                        <option value="Piso 4" <?= (($_POST['piso_localizacao'] ?? '') == 'Piso 4') ? 'selected' : '' ?>>Piso 4</option>
                                        <option value="Piso 5" <?= (($_POST['piso_localizacao'] ?? '') == 'Piso 5') ? 'selected' : '' ?>>Piso 5</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_servico" class="form-label">Serviço</label>
                                    <input type="text" class="form-control" name="servico_localizacao" id="texto_servico" list="servicos"
                                         value="<?= htmlspecialchars($_POST['servico_localizacao'] ?? '') ?>">
                                    <datalist id="servicos">
                                        <option value="Unidade de Cuidados Intensivos">
                                        <option value="Unidade de Cuidados Intermédios">
                                        <option value="Urgência">
                                        <option value="Serviço de Medicina">
                                        <option value="Serviço de Cirurgia">
                                        <option value="Pediatria">
                                        <option value="Bloco Operatório">
                                        <option value="Imagiologia">
                                        <option value="Gastroenterologia">
                                        <option value="Laboratório">
                                        <option value="Farmácia">
                                        <option value="Administração">
                                    </datalist>
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_sala" class="form-label">Internamento / Sala / Gabinete</label>
                                    <input type="text" class="form-control" name="sala_localizacao" id="texto_sala"
                                        value="<?= htmlspecialchars($_POST['sala_localizacao'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="d-flex justify-content-end gap-2 mb-4">
                                <a href="localizacoes.php" class="btn btn-outline-secondary">
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