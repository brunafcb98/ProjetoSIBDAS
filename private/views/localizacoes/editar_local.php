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

//Desencriptação e validar ID localização
$idLocalizacaoEncrypted = $_GET['id_localizacao'] ?? null;
$idLocalizacao = aes_decrypt($idLocalizacaoEncrypted);

if (!$idLocalizacao || !is_numeric($idLocalizacao)) {
    header('Location: ' . BASE_URL . '/private/views/localizacoes/localizacoes.php');
    exit;
}

//Detetar submissao via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoEdificio = $_POST['edificio_localizacao'] ?? '';
    $novoPiso     = $_POST['piso_localizacao'] ?? '';
    $novoServico  = $_POST['servico_localizacao'] ?? '';
    $novaSala     = $_POST['sala_localizacao'] ?? '';

    $erros = array_merge(
        validar_edificio($novoEdificio),
        validar_piso($novoPiso),
        validar_servico($novoServico),
        validar_sala($novaSala)
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
                UPDATE localizacoes
                SET edificio = :edificio,
                    piso     = :piso,
                    servico  = :servico,
                    sala_internamento_gabinete = :sala
                WHERE id = :id AND apagado = 0
            ");

            $stmt->bindParam(':edificio', $novoEdificio, PDO::PARAM_STR);
            $stmt->bindParam(':piso',     $novoPiso,     PDO::PARAM_STR);
            $stmt->bindParam(':servico',  $novoServico,  PDO::PARAM_STR);
            $stmt->bindParam(':sala',     $novaSala,     PDO::PARAM_STR);
            $stmt->bindParam(':id',       $idLocalizacao, PDO::PARAM_INT);

            $stmt->execute();

            // Mensagem de sucesso e redirecionamento (opcional) 
            header('Location: ' . BASE_URL . '/private/views/localizacoes/localizacoes.php');
            exit;

        } catch (PDOException $err) {
            $erros[] = "Erro ao atualizar a localização: " . $err->getMessage();
        }
    }
}


//SELECT da localizaçao
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparar e executar a query com segurança
    // AND apagado = 0 garante que localizações com soft delete não são editáveis
    $stmt = $ligacao->prepare("SELECT * FROM localizacoes WHERE id = :id AND apagado = 0");
    $stmt->bindParam(':id', $idLocalizacao, PDO::PARAM_INT);
    $stmt->execute();

    $localizacao = $stmt->fetch(PDO::FETCH_OBJ);

    // Se não encontrou a localização, redireciona
    if (!$localizacao) {
        header('Location: ' . BASE_URL . '/private/views/localizacoes/localizacoes.php');
        exit;
    }

} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $localizacao = null;
}

// Fecha a ligação
$ligacao = null;

/*
// Para testar (temporário) -  verificar se o ID da localização desencriptada corresponde ao da BD
echo $idLocalizacao;
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-pen-to-square me-2"></i> Atualização de Dados - Localização</strong></h2>
                        <hr>

                        <form action="editar_local.php?id_localizacao=<?= $idLocalizacaoEncrypted ?>" method="post" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_edificio" class="form-label">Edifício</label>
                                    <select class="form-select" name="edificio_localizacao" id="select_edificio" required>
                                        <option value="" <?= empty($localizacao->edificio) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="Edifício Principal" <?= $localizacao->edificio == 'Edifício Principal' ? 'selected' : '' ?>>Edifício Principal</option>
                                        <option value="Edifício A" <?= $localizacao->edificio == 'Edifício A' ? 'selected' : '' ?>>Edifício A</option>
                                        <option value="Edifício B" <?= $localizacao->edificio == 'Edifício B' ? 'selected' : '' ?>>Edifício B</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="select_piso" class="form-label">Piso</label>
                                    <select class="form-select" name="piso_localizacao" id="select_piso" required>
                                        <option value="" <?= empty($localizacao->piso) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="Piso 0" <?= $localizacao->piso == 'Piso 0' ? 'selected' : '' ?>>Piso 0</option>
                                        <option value="Piso 1" <?= $localizacao->piso == 'Piso 1' ? 'selected' : '' ?>>Piso 1</option>
                                        <option value="Piso 2" <?= $localizacao->piso == 'Piso 2' ? 'selected' : '' ?>>Piso 2</option>
                                        <option value="Piso 3" <?= $localizacao->piso == 'Piso 3' ? 'selected' : '' ?>>Piso 3</option>
                                        <option value="Piso 4" <?= $localizacao->piso == 'Piso 4' ? 'selected' : '' ?>>Piso 4</option>
                                        <option value="Piso 5" <?= $localizacao->piso == 'Piso 5' ? 'selected' : '' ?>>Piso 5</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_servico" class="form-label">Serviço</label>
                                    <input type="text" class="form-control" name="servico_localizacao" id="texto_servico" 
                                        value="<?= htmlspecialchars($localizacao->servico) ?>" list="servicos" required>
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
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_sala" class="form-label">Internamento / Sala / Gabinete</label>
                                    <input type="text" class="form-control" name="sala_localizacao" id="texto_sala" 
                                        value="<?= htmlspecialchars($localizacao->sala_internamento_gabinete) ?>" required>
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
