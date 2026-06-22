<?php  
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();
require_once __DIR__ . '/../../includes/validacoes.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

//Desencriptação e validar ID acessório
$idAcessorioEncrypted = $_GET['id_acessorio'] ?? null;
$idAcessorio = aes_decrypt($idAcessorioEncrypted);

if (!$idAcessorio || !is_numeric($idAcessorio)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

//Detetar submissao via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoNome         = $_POST['nome_acessorio'] ?? '';
    $novoModelo       = $_POST['modelo_acessorio'] ?? '';
    $novoNserie       = $_POST['nserie_acessorio'] ?? '';
    $novoEstado       = $_POST['estado_acessorio'] ?? '';
    $novasObservacoes = $_POST['observacoes_acessorio'] ?? '';

    $novoNome         = trim($novoNome);
    $novoModelo       = trim($novoModelo);
    $novoNserie       = trim($novoNserie);
    $novasObservacoes = trim($novasObservacoes);

    $erros = array_merge(
        validar_designacao($novoNome),
        validar_nserie($novoNserie),
        validar_estado($novoEstado),
        validar_observacoes($novasObservacoes)
    );
    // Modelo é opcional para acessórios — sem validação de obrigatoriedade

    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
                MYSQL_USERNAME,
                MYSQL_PASSWORD
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $modeloParam = !empty($novoModelo) ? $novoModelo : null;
            $nserieParam = !empty($novoNserie) ? $novoNserie : null;

            $stmt = $ligacao->prepare("
                UPDATE acessorios 
                SET nome         = :nome,
                    modelo       = :modelo,
                    numero_serie = :nserie,
                    estado       = :estado,
                    observacoes  = :observacoes
                WHERE id = :id AND apagado = 0
            ");

            $stmt->bindParam(':nome',        $novoNome,         PDO::PARAM_STR);
            $stmt->bindParam(':modelo',      $modeloParam,      PDO::PARAM_STR);
            $stmt->bindParam(':nserie',      $nserieParam,      PDO::PARAM_STR);
            $stmt->bindParam(':estado',      $novoEstado,       PDO::PARAM_STR);
            $stmt->bindParam(':observacoes', $novasObservacoes, PDO::PARAM_STR);
            $stmt->bindParam(':id',          $idAcessorio,      PDO::PARAM_INT);

            $stmt->execute();

            // Vai buscar o id_equipamento_pai deste acessório, para redirecionar ao detalhes.php certo
            $stmtPai = $ligacao->prepare("SELECT id_equipamento_pai FROM acessorios WHERE id = :id");
            $stmtPai->bindParam(':id', $idAcessorio, PDO::PARAM_INT);
            $stmtPai->execute();
            $idEquipamentoPai = $stmtPai->fetchColumn();

            $_SESSION['toast_success'] = 'Acessório atualizado com sucesso.';

            header('Location: ' . BASE_URL . '/private/views/equipamentos/detalhes.php?id_equipamento=' . aes_encrypt($idEquipamentoPai));
            exit;

        } catch (PDOException $err) {
            $erros[] = "Ocorreu um erro na ligação à base de dados. Por favor, tente novamente mais tarde.";
        }
    }
}

//SELECT do acessório
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->prepare("SELECT * FROM acessorios WHERE id = :id AND apagado = 0");
    $stmt->bindParam(':id', $idAcessorio, PDO::PARAM_INT);
    $stmt->execute();

    $acessorio = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$acessorio) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $acessorio = null;
}

$ligacao = null;

// Encripta o id_equipamento_pai para usar no botão Cancelar, evitando expor o ID no URL
$idEquipamentoEncrypted = aes_encrypt($acessorio->id_equipamento_pai);
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-pen-to-square me-2"></i> Editar Acessório</strong></h2>
                        <hr>
                        <form action="editar_acessorio.php?id_acessorio=<?= $idAcessorioEncrypted ?>" method="post" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Código</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($acessorio->codigo) ?>" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Marca</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($acessorio->marca ?? '—') ?>" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fabricante</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($acessorio->fabricante ?? '—') ?>" disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="texto_nome_acessorio" class="form-label">Nome do Acessório</label>
                                    <input type="text" class="form-control" name="nome_acessorio" id="texto_nome_acessorio" 
                                        value="<?= htmlspecialchars($acessorio->nome) ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="texto_modelo_acessorio" class="form-label">Modelo <small>(opcional)</small></label>
                                    <input type="text" class="form-control" name="modelo_acessorio" id="texto_modelo_acessorio" 
                                        value="<?= htmlspecialchars($acessorio->modelo ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_nserie_acessorio" class="form-label">Número de Série</label>
                                    <input type="text" class="form-control" name="nserie_acessorio" id="texto_nserie_acessorio" 
                                        value="<?= htmlspecialchars($acessorio->numero_serie ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="select_estado_acessorio" class="form-label">Estado</label>
                                    <select class="form-select" name="estado_acessorio" id="select_estado_acessorio">
                                        <option value="ativo" <?= $acessorio->estado == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                        <option value="manutencao" <?= $acessorio->estado == 'manutencao' ? 'selected' : '' ?>>Em Manutenção</option>
                                        <option value="inativo" <?= $acessorio->estado == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                        <option value="calibracao" <?= $acessorio->estado == 'calibracao' ? 'selected' : '' ?>>Em Calibração</option>
                                        <option value="quarentena" <?= $acessorio->estado == 'quarentena' ? 'selected' : '' ?>>Em Quarentena</option>
                                        <option value="abatido" <?= $acessorio->estado == 'abatido' ? 'selected' : '' ?>>Abatido</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes_acessorio" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_acessorio" id="texto_observacoes_acessorio" 
                                        value="<?= htmlspecialchars($acessorio->observacoes ?? '') ?>">
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="d-flex justify-content-end gap-2 mb-4">
                                <a href="../equipamentos/detalhes.php?id_equipamento=<?= $idEquipamentoEncrypted ?>" class="btn btn-outline-secondary">
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
