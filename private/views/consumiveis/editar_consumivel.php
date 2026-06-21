<?php  
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();
require_once __DIR__ . '/../../includes/validacoes.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

//Desencriptação e validar ID consumível
$idConsumivelEncrypted = $_GET['id_consumivel'] ?? null;
$idConsumivel = aes_decrypt($idConsumivelEncrypted);

if (!$idConsumivel || !is_numeric($idConsumivel)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

//Detetar submissao via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoNome         = $_POST['nome_consumivel'] ?? '';
    $novaQuantidade   = $_POST['quantidade_consumivel'] ?? '';
    $novoFornecedor   = $_POST['fornecedor_consumivel'] ?? '';
    $novasObservacoes = $_POST['observacoes_consumivel'] ?? '';

    $novoNome         = trim($novoNome);
    $novaQuantidade   = trim($novaQuantidade);
    $novasObservacoes = trim($novasObservacoes);

    $erros = array_merge(
        validar_designacao($novoNome),
        validar_quantidade($novaQuantidade),
        validar_fornecedor_consumivel($novoFornecedor),
        validar_observacoes($novasObservacoes)
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
                UPDATE consumiveis 
                SET nome         = :nome,
                    quantidade   = :quantidade,
                    id_fornecedor = :id_fornecedor,
                    observacoes  = :observacoes
                WHERE id = :id AND apagado = 0
            ");

            $stmt->bindParam(':nome',          $novoNome,         PDO::PARAM_STR);
            $stmt->bindParam(':quantidade',    $novaQuantidade,   PDO::PARAM_INT);
            $stmt->bindParam(':id_fornecedor', $novoFornecedor,   PDO::PARAM_INT);
            $stmt->bindParam(':observacoes',   $novasObservacoes, PDO::PARAM_STR);
            $stmt->bindParam(':id',            $idConsumivel,     PDO::PARAM_INT);

            $stmt->execute();

            // Vai buscar o id_equipamento_pai deste consumível, para redirecionar ao detalhes.php certo
            $stmtPai = $ligacao->prepare("SELECT id_equipamento_pai FROM consumiveis WHERE id = :id");
            $stmtPai->bindParam(':id', $idConsumivel, PDO::PARAM_INT);
            $stmtPai->execute();
            $idEquipamentoPai = $stmtPai->fetchColumn();

            $_SESSION['toast_success'] = 'Consumível atualizado com sucesso.';

            header('Location: ' . BASE_URL . '/private/views/equipamentos/detalhes.php?id_equipamento=' . aes_encrypt($idEquipamentoPai));
            exit;

        } catch (PDOException $err) {
            $erros[] = "Ocorreu um erro na ligação à base de dados. Por favor, tente novamente mais tarde.";
        }
    }
}

//SELECT do consumível
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $ligacao->prepare("SELECT * FROM consumiveis WHERE id = :id AND apagado = 0");
    $stmt->bindParam(':id', $idConsumivel, PDO::PARAM_INT);
    $stmt->execute();

    $consumivel = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$consumivel) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

    // Carregar fornecedores de consumíveis disponíveis
    $fornecedoresConsumiveis = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE tipo = 'consumiveis' AND apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $consumivel = null;
    $fornecedoresConsumiveis = [];
}

$ligacao = null;

// Encripta o id_equipamento_pai para usar no botão Cancelar, evitando expor o ID no URL
$idEquipamentoEncrypted = aes_encrypt($consumivel->id_equipamento_pai);
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-pen-to-square me-2"></i> Editar Consumível</strong></h2>
                        <hr>
                        <form action="editar_consumivel.php?id_consumivel=<?= $idConsumivelEncrypted ?>" method="post" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Código</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($consumivel->codigo) ?>" disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="texto_nome_consumivel" class="form-label">Nome do Consumível</label>
                                    <input type="text" class="form-control" name="nome_consumivel" id="texto_nome_consumivel" 
                                        value="<?= htmlspecialchars($consumivel->nome) ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_quantidade_consumivel" class="form-label">Quantidade</label>
                                    <input type="text" class="form-control" name="quantidade_consumivel" id="texto_quantidade_consumivel" 
                                        value="<?= htmlspecialchars($consumivel->quantidade) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="select_fornecedor_consumivel" class="form-label">Fornecedor</label>
                                    <select class="form-select" name="fornecedor_consumivel" id="select_fornecedor_consumivel">
                                        <option value="" <?= empty($consumivel->id_fornecedor) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <?php foreach ($fornecedoresConsumiveis as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" <?= $consumivel->id_fornecedor == $fornecedor['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fornecedor['nome_empresa']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes_consumivel" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_consumivel" id="texto_observacoes_consumivel" 
                                        value="<?= htmlspecialchars($consumivel->observacoes ?? '') ?>">
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