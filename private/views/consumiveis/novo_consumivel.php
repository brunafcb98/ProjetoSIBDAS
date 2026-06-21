<?php  
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();
require_once __DIR__ . '/../../includes/validacoes.php';

//Desencriptação e validar ID equipamento (pai)
$idEquipamentoEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEquipamentoEncrypted);

if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

// Ir buscar os dados do equipamento pai (precisamos do código)
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmtPai = $ligacao->prepare("SELECT codigo_interno FROM equipamentos WHERE id = :id AND apagado = 0");
    $stmtPai->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtPai->execute();
    $equipamentoPai = $stmtPai->fetch(PDO::FETCH_ASSOC);

    if (!$equipamentoPai) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

    // Remove o sufixo ".00" do código do pai, para obter só o prefixo (ex: "04.002")
    $prefixoCodigo = preg_replace('/\.\d{2}$/', '', $equipamentoPai['codigo_interno']);

    // Calcular o próximo sufixo disponível para este pai (sufixo incremental, dentro da própria tabela consumiveis)
    $stmtCodigos = $ligacao->prepare("SELECT codigo FROM consumiveis WHERE id_equipamento_pai = :id");
    $stmtCodigos->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtCodigos->execute();
    $codigosExistentes = $stmtCodigos->fetchAll(PDO::FETCH_COLUMN);

    $maxSufixo = 0;
    foreach ($codigosExistentes as $codigoExistente) {
        $sufixo = (int) substr($codigoExistente, strrpos($codigoExistente, '-') + 1);
        $maxSufixo = max($maxSufixo, $sufixo);
    }
    $proximoSufixo = str_pad($maxSufixo + 1, 2, '0', STR_PAD_LEFT);
    $proximoCodigo = "CONS-{$prefixoCodigo}-{$proximoSufixo}";

    // Carregar fornecedores de consumíveis disponíveis
    $fornecedoresConsumiveis = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE tipo = 'consumiveis' AND apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);

    $ligacao = null;

} catch (PDOException $err) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recolher dados
    $nomeConsumivel        = $_POST["nome_consumivel"] ?? "";
    $quantidadeConsumivel  = $_POST["quantidade_consumivel"] ?? "";
    $fornecedorConsumivel  = $_POST["fornecedor_consumivel"] ?? "";
    $observacoesConsumivel = $_POST["observacoes_consumivel"] ?? "";

    // 2. Trim
    $nomeConsumivel        = trim($nomeConsumivel);
    $quantidadeConsumivel  = trim($quantidadeConsumivel);
    $observacoesConsumivel = trim($observacoesConsumivel);

    // 3. Validar os dados
    $erros = [];
    $erro_sistema = "";

    $erros = array_merge(
        validar_designacao($nomeConsumivel),
        validar_quantidade($quantidadeConsumivel),
        validar_fornecedor_consumivel($fornecedorConsumivel),
        validar_observacoes($observacoesConsumivel)
    );

    // 4. Se não houver erros, guardar na base de dados
    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
                MYSQL_USERNAME,
                MYSQL_PASSWORD
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "INSERT INTO consumiveis (
                id_equipamento_pai, codigo, nome, quantidade, id_fornecedor, observacoes
            ) VALUES (
                :id_equipamento_pai, :codigo, :nome, :quantidade, :id_fornecedor, :observacoes
            )";

            $stmt = $ligacao->prepare($sql);
            $stmt->execute([
                ':id_equipamento_pai' => $idEquipamento,
                ':codigo'             => $proximoCodigo,
                ':nome'               => $nomeConsumivel,
                ':quantidade'         => $quantidadeConsumivel,
                ':id_fornecedor'      => $fornecedorConsumivel,
                ':observacoes'        => $observacoesConsumivel
            ]);

            // Guarda mensagem de sucesso para o Toast aparecer
            $_SESSION['toast_success'] = 'Consumível adicionado com sucesso.';

            header('Location: ' . BASE_URL . '/private/views/equipamentos/detalhes.php?id_equipamento=' . $idEquipamentoEncrypted);
            exit;

        } catch (PDOException $err) {
            $erro_sistema = "Ocorreu um erro na ligação à base de dados. Por favor, tente novamente mais tarde.";
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-boxes-stacked me-2"></i> Adicionar Consumível</strong></h2>
                        <hr>
                        <form action="#" method="post" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Código (gerado automaticamente)</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($proximoCodigo) ?>" disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="texto_nome_consumivel" class="form-label">Nome do Consumível</label>
                                    <input type="text" class="form-control" name="nome_consumivel" id="texto_nome_consumivel" 
                                        value="<?= htmlspecialchars($_POST['nome_consumivel'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_quantidade_consumivel" class="form-label">Quantidade</label>
                                    <input type="text" class="form-control" name="quantidade_consumivel" id="texto_quantidade_consumivel" 
                                        value="<?= htmlspecialchars($_POST['quantidade_consumivel'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="select_fornecedor_consumivel" class="form-label">Fornecedor</label>
                                    <select class="form-select" name="fornecedor_consumivel" id="select_fornecedor_consumivel">
                                        <option value="" <?= empty($_POST['fornecedor_consumivel']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <?php foreach ($fornecedoresConsumiveis as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" <?= (($_POST['fornecedor_consumivel'] ?? '') == $fornecedor['id']) ? 'selected' : '' ?>>
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
                                        value="<?= htmlspecialchars($_POST['observacoes_consumivel'] ?? '') ?>">
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