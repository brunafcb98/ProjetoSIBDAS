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

// Ir buscar os dados do equipamento pai (precisamos do código, marca e fabricante)
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmtPai = $ligacao->prepare("SELECT codigo_interno, marca, fabricante FROM equipamentos WHERE id = :id AND apagado = 0");
    $stmtPai->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtPai->execute();
    $equipamentoPai = $stmtPai->fetch(PDO::FETCH_ASSOC);

    if (!$equipamentoPai) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

    /// Calcular o próximo código disponível para este pai (sufixo incremental)
    $stmtCodigos = $ligacao->prepare("SELECT codigo FROM acessorios WHERE id_equipamento_pai = :id");
    $stmtCodigos->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtCodigos->execute();
    $codigosExistentes = $stmtCodigos->fetchAll(PDO::FETCH_COLUMN);

    $maxSufixo = 0;
    foreach ($codigosExistentes as $codigoExistente) {
        $sufixo = (int) substr($codigoExistente, strrpos($codigoExistente, '.') + 1);
        $maxSufixo = max($maxSufixo, $sufixo);
    }

    // Remove o sufixo ".00" do código do pai, para obter só o prefixo (ex: "04.002")
    $prefixoCodigo = preg_replace('/\.\d{2}$/', '', $equipamentoPai['codigo_interno']);

    $proximoSufixo = str_pad($maxSufixo + 1, 2, '0', STR_PAD_LEFT);
    $proximoCodigo = $prefixoCodigo . '.' . $proximoSufixo;

    $ligacao = null;

} catch (PDOException $err) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recolher dados
    $nomeAcessorio       = $_POST["nome_acessorio"] ?? "";
    $modeloAcessorio     = $_POST["modelo_acessorio"] ?? "";
    $nserieAcessorio     = $_POST["nserie_acessorio"] ?? "";
    $estadoAcessorio     = $_POST["estado_acessorio"] ?? "";
    $observacoesAcessorio = $_POST["observacoes_acessorio"] ?? "";

    // 2. Trim
    $nomeAcessorio        = trim($nomeAcessorio);
    $modeloAcessorio      = trim($modeloAcessorio);
    $nserieAcessorio      = trim($nserieAcessorio);
    $observacoesAcessorio = trim($observacoesAcessorio);

    // 3. Validar os dados (reaproveita as validações já existentes para equipamentos)
    $erros = [];
    $erro_sistema = "";

    $erros = array_merge(
        validar_designacao($nomeAcessorio),
        validar_nserie($nserieAcessorio),
        validar_estado($estadoAcessorio),
        validar_observacoes($observacoesAcessorio)
    );
    // Modelo é opcional para acessórios — sem validação de obrigatoriedade

    // 4. Se não houver erros, guardar na base de dados
    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
                MYSQL_USERNAME,
                MYSQL_PASSWORD
            );
            $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Converte campos opcionais vazios em NULL
            $modeloParam = !empty($modeloAcessorio) ? $modeloAcessorio : null;
            $nserieParam = !empty($nserieAcessorio) ? $nserieAcessorio : null;

            $sql = "INSERT INTO acessorios (
                id_equipamento_pai, codigo, nome, marca, fabricante, modelo, numero_serie, estado, observacoes
            ) VALUES (
                :id_equipamento_pai, :codigo, :nome, :marca, :fabricante, :modelo, :nserie, :estado, :observacoes
            )";

            $stmt = $ligacao->prepare($sql);
            $stmt->execute([
                ':id_equipamento_pai' => $idEquipamento,
                ':codigo'             => $proximoCodigo,
                ':nome'               => $nomeAcessorio,
                ':marca'              => $equipamentoPai['marca'],
                ':fabricante'         => $equipamentoPai['fabricante'],
                ':modelo'             => $modeloParam,
                ':nserie'             => $nserieParam,
                ':estado'             => $estadoAcessorio,
                ':observacoes'        => $observacoesAcessorio
            ]);

            // Guarda mensagem de sucesso para o Toast aparecer
            $_SESSION['toast_success'] = 'Acessório adicionado com sucesso.';

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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-puzzle-piece me-2"></i> Adicionar Acessório</strong></h2>
                        <hr>
                        <form action="#" method="post" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Código (gerado automaticamente)</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($proximoCodigo) ?>" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Marca</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($equipamentoPai['marca']) ?>" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fabricante</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($equipamentoPai['fabricante']) ?>" disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="texto_nome_acessorio" class="form-label">Nome do Acessório</label>
                                    <input type="text" class="form-control" name="nome_acessorio" id="texto_nome_acessorio" 
                                        value="<?= htmlspecialchars($_POST['nome_acessorio'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="texto_modelo_acessorio" class="form-label">Modelo <small>(opcional)</small></label>
                                    <input type="text" class="form-control" name="modelo_acessorio" id="texto_modelo_acessorio" 
                                        value="<?= htmlspecialchars($_POST['modelo_acessorio'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_nserie_acessorio" class="form-label">Número de Série </label>
                                    <input type="text" class="form-control" name="nserie_acessorio" id="texto_nserie_acessorio" 
                                        value="<?= htmlspecialchars($_POST['nserie_acessorio'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="select_estado_acessorio" class="form-label">Estado</label>
                                    <select class="form-select" name="estado_acessorio" id="select_estado_acessorio">
                                        <option value="ativo" <?= (($_POST['estado_acessorio'] ?? 'ativo') == 'ativo') ? 'selected' : '' ?>>Ativo</option>
                                        <option value="manutencao" <?= (($_POST['estado_acessorio'] ?? '') == 'manutencao') ? 'selected' : '' ?>>Em Manutenção</option>
                                        <option value="inativo" <?= (($_POST['estado_acessorio'] ?? '') == 'inativo') ? 'selected' : '' ?>>Inativo</option>
                                        <option value="calibracao" <?= (($_POST['estado_acessorio'] ?? '') == 'calibracao') ? 'selected' : '' ?>>Em Calibração</option>
                                        <option value="quarentena" <?= (($_POST['estado_acessorio'] ?? '') == 'quarentena') ? 'selected' : '' ?>>Em Quarentena</option>
                                        <option value="abatido" <?= (($_POST['estado_acessorio'] ?? '') == 'abatido') ? 'selected' : '' ?>>Abatido</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes_acessorio" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_acessorio" id="texto_observacoes_acessorio" 
                                        value="<?= htmlspecialchars($_POST['observacoes_acessorio'] ?? '') ?>">
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