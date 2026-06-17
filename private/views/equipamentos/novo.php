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
    $codigo       = $_POST["codigo_equipamento"] ?? "";
    $designacao   = $_POST["designacao_equipamento"] ?? "";
    $categoria    = $_POST["categoria_equipamento"] ?? "";
    $marca        = $_POST["marca_equipamento"] ?? "";
    $modelo       = $_POST["modelo_equipamento"] ?? "";
    $nserie       = $_POST["nserie_equipamento"] ?? "";
    $fabricante   = $_POST["fabricante_equipamento"] ?? "";
    $dataquisicao = $_POST["dataquisicao_equipamento"] ?? "";
    $anofabrico   = $_POST["anofabrico_equipamento"] ?? "";
    $custo        = $_POST["custo_equipamento"] ?? null;
    $tipoentrada  = $_POST["tipoentrada_equipamento"] ?? "";
    $estado       = $_POST["estado_equipamento"] ?? "";
    $criticidade  = $_POST["criticidade_equipamento"] ?? "";
    $localizacao  = $_POST["localizacao_equipamento"] ?? "";
    $observacoes  = $_POST["observacoes_equipamento"] ?? "";

    // 2. Trim
    $codigo       = trim($codigo);
    $designacao   = trim($designacao);
    $marca        = trim($marca);
    $modelo       = trim($modelo);
    $nserie       = trim($nserie);
    $fabricante   = trim($fabricante);
    $dataquisicao = trim($dataquisicao);
    $anofabrico   = trim($anofabrico);
    $custo        = trim($custo);
    $observacoes  = trim($observacoes);

    // 3. Validar os dados
    $erros = [];    //para erros de validação 
    $erro_sistema = ""; //Para erros de SQL (PDO) 

    $erros = array_merge(
        validar_codigo($codigo),
        validar_designacao($designacao),
        validar_categoria($categoria),
        validar_marca($marca),
        validar_modelo($modelo),
        validar_nserie($nserie),
        validar_fabricante($fabricante),
        validar_data_aquisicao($dataquisicao, $anofabrico),
        validar_ano_fabrico($anofabrico),
        validar_custo($custo),
        validar_tipo_entrada($tipoentrada),
        validar_estado($estado),
        validar_criticidade($criticidade),
        validar_localizacao($localizacao),
        validar_observacoes($observacoes)
    );

    /* 
    if (empty($codigo)) {
        $erros[] = "O campo Código Interno é obrigatório.";
    }   elseif (!preg_match('/^\d+\.\d{3}\.\d{2}$/', $codigo)) {
            $erros[] = "O campo Código Interno é inválido. Use o formato XX.XXX.XX (ex: 04.002.01).";
    }

    if (empty($designacao)) {
        $erros[] = "O campo Designação é obrigatório.";
    } elseif (preg_match('/^\d+$/', $designacao)) {
        $erros[] = "O campo Designação não pode conter apenas números.";
    }

    if (empty($categoria) || $categoria == "Escolha uma opção") {
        $erros[] = "O campo Categoria é obrigatório.";
    }

    if (empty($marca)) {
        $erros[] = "O campo Marca é obrigatório.";
    } elseif (preg_match('/\d/', $marca)) {
        $erros[] = "O campo Marca não pode conter números.";
    }

    if (empty($modelo)) {
        $erros[] = "O campo Modelo é obrigatório.";
    }

    if (empty($nserie)) {
        $erros[] = "O campo Número de Série é obrigatório.";
    }

    if (empty($fabricante)) {
        $erros[] = "O campo Fabricante é obrigatório.";
    } elseif (preg_match('/\d/', $fabricante)) {
        $erros[] = "O campo Fabricante não pode conter números.";
    }

    if (empty($dataquisicao)) {
        $erros[] = "O campo Data de Aquisição é obrigatório.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataquisicao)) {
        $erros[] = "Formato de data inválido. Use AAAA-MM-DD.";
    } else {
        $partes = explode('-', $dataquisicao); 
        // Verificar se a data é real e existe
        if (!checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0])) {
            $erros[] = "Data de aquisição inválida.";
        } elseif ($dataquisicao > date('Y-m-d')) {
            $erros[] = "A data de aquisição não pode ser posterior à data atual.";
        } elseif (!empty($anofabrico) && (int)date('Y', strtotime($dataquisicao)) < (int)$anofabrico) {
            $erros[] = "A data de aquisição não pode ser anterior ao ano de fabrico.";
        }
    }

    if (empty($anofabrico)) {
        $erros[] = "O campo Ano de Fabrico é obrigatório.";
    } elseif (!preg_match('/^\d{4}$/', $anofabrico)) {
        $erros[] = "Ano de fabrico inválido. Use o formato AAAA.";
    } elseif ((int)$anofabrico > (int)date('Y')) {
        $erros[] = "O ano de fabrico não pode ser posterior ao ano atual.";
    }

    if (!empty($custo) && !is_numeric($custo)) {
        $erros[] = "O custo de aquisição deve ser um valor numérico.";
    } elseif (!empty($custo) && (float)$custo <= 0) {
        $erros[] = "O custo de aquisição deve ser maior que 0.";
    }

    if (empty($tipoentrada) || $tipoentrada == "Escolha uma opção") {
        $erros[] = "O campo Tipo de Entrada é obrigatório.";
    }

    if (empty($estado) || $estado == "Escolha uma opção") {
        $erros[] = "O campo Estado é obrigatório.";
    }

    if (empty($criticidade) || $criticidade == "Escolha uma opção") {
        $erros[] = "O campo Criticidade é obrigatório.";
    }

    if (empty($localizacao) || $localizacao == "Escolha uma opção") {
        $erros[] = "O campo Localização é obrigatório.";
    }

    if (!empty($observacoes) && strlen($observacoes) > 500) {
        $erros[] = "As observações não podem exceder 500 caracteres.";
    }
    */
    // 4. Normalizar dados
    if (empty($erros)) {
        $designacao  = ucwords(strtolower($designacao));
        $marca       = ucwords(strtolower($marca));
        $nserie      = strtoupper($nserie);
        $fabricante  = ucwords(strtolower($fabricante));
        $observacoes = ucfirst(strtolower($observacoes));
    }
    /*
        // Dados normalizados (para teste)
    echo "<p><strong>Dados normalizados:</strong></p>";
    echo "<ul>";
    echo "<li>Designação: $designacao</li>";
    echo "<li>Marca: $marca</li>";
    echo "<li>Nº Série: $nserie</li>";
    echo "<li>Fabricante: $fabricante</li>";
    echo "<li>Observações: $observacoes</li>";
    echo "</ul>";
    */
    /*
    // Depuração: mostrar os erros recolhidos
    echo "<pre>";
    print_r($erros);
    echo "</pre>";
    */

    // Se não houver erros, guardar na base de dados
    if (empty($erros)) {
        try {
            $ligacao = new PDO(
                "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
                MYSQL_USERNAME,
                MYSQL_PASSWORD
            );

            $sql = "INSERT INTO equipamentos (
                codigo_interno, designacao, categoria, marca, modelo, numero_serie,
                fabricante, data_aquisicao, ano_fabrico, custo_aquisicao, tipo_entrada,
                estado, criticidade, id_localizacao, observacoes
            ) VALUES (
                :codigo, :designacao, :categoria, :marca, :modelo, :nserie,
                :fabricante, :dataquisicao, :anofabrico, :custo, :tipoentrada,
                :estado, :criticidade, :localizacao, :observacoes
            )";

            $stmt = $ligacao->prepare($sql);
            $stmt->execute([
                ':codigo'       => $codigo,
                ':designacao'   => $designacao,
                ':categoria'    => $categoria,
                ':marca'        => $marca,
                ':modelo'       => $modelo,
                ':nserie'       => $nserie,
                ':fabricante'   => $fabricante,
                ':dataquisicao' => $dataquisicao,
                ':anofabrico'   => $anofabrico,
                ':custo'        => $custo,
                ':tipoentrada'  => $tipoentrada,
                ':estado'       => $estado,
                ':criticidade'  => $criticidade,
                ':localizacao'  => $localizacao,
                ':observacoes'  => $observacoes
            ]);

            header('Location: equipamentos.php');
            exit;
        } catch (PDOException $err) {
            $erro_sistema = "Erro ao gravar os dados: " . $err->getMessage();
        }

        $ligacao = null;
    }
}
?>

<?php
// Carregar localizações disponíveis
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
    $localizacoes = $ligacao->query("SELECT id, servico, sala_internamento_gabinete FROM localizacoes WHERE apagado = 0 ORDER BY servico ASC")->fetchAll(PDO::FETCH_ASSOC);
    $ligacao = null;
} catch (PDOException $e) {
    $localizacoes = [];
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-stethoscope me-2"></i> Inserir novo equipamento</strong></h2>
                        <hr>
                        <form action="#" method="post" novalidate>

                            <!-- Identificação do equipamento -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_codigo" class="form-label">Código Interno de Inventário</label>
                                    <input type="text" class="form-control" name="codigo_equipamento" id="texto_codigo" 
                                        value="<?= htmlspecialchars($_POST['codigo_equipamento'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_designacao" class="form-label">Designação do Equipamento</label>
                                    <input type="text" class="form-control" name="designacao_equipamento" id="texto_designacao"
                                        value="<?= htmlspecialchars($_POST['designacao_equipamento'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_categoria" class="form-label">Categoria</label>
                                    <select class="form-select" name="categoria_equipamento" id="select_categoria">
                                        <option value="" <?= empty($_POST['categoria_equipamento']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="monitorizacao" <?= (($_POST['categoria_equipamento'] ?? '') == 'monitorizacao') ? 'selected' : '' ?>>Monitorização</option>
                                        <option value="suporte_vida" <?= (($_POST['categoria_equipamento'] ?? '') == 'suporte_vida') ? 'selected' : '' ?>>Suporte de Vida</option>
                                        <option value="terapia" <?= (($_POST['categoria_equipamento'] ?? '') == 'terapia') ? 'selected' : '' ?>>Terapia</option>
                                        <option value="diagnostico" <?= (($_POST['categoria_equipamento'] ?? '') == 'diagnostico') ? 'selected' : '' ?>>Diagnóstico</option>
                                        <option value="laboratorio" <?= (($_POST['categoria_equipamento'] ?? '') == 'laboratorio') ? 'selected' : '' ?>>Laboratório</option>
                                        <option value="esterilizacao" <?= (($_POST['categoria_equipamento'] ?? '') == 'esterilizacao') ? 'selected' : '' ?>>Esterilização</option>
                                        <option value="reabilitacao" <?= (($_POST['categoria_equipamento'] ?? '') == 'reabilitacao') ? 'selected' : '' ?>>Reabilitação</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_marca" class="form-label">Marca</label>
                                    <input type="text" class="form-control" name="marca_equipamento" id="texto_marca" 
                                        value="<?= htmlspecialchars($_POST['marca_equipamento'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_modelo" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" name="modelo_equipamento" id="texto_modelo" 
                                        value="<?= htmlspecialchars($_POST['modelo_equipamento'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_nserie" class="form-label">Número de Série</label>
                                    <input type="text" class="form-control" name="nserie_equipamento" id="texto_nserie" 
                                        value="<?= htmlspecialchars($_POST['nserie_equipamento'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_fabricante" class="form-label">Fabricante</label>
                                    <input type="text" class="form-control" name="fabricante_equipamento" id="texto_fabricante" list="fabricantes"
                                        value="<?= htmlspecialchars($_POST['fabricante_equipamento'] ?? '') ?>">
                                    <datalist id="fabricantes">
                                        <option value="Philips">
                                        <option value="B. Braun">
                                        <option value="Siemens">
                                        <option value="Dräger">
                                        <option value="Medtronic">
                                        <option value="Baxter">
                                    </datalist>
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_dataquisicao" class="form-label">Data de Aquisição</label>
                                    <input type="text" class="form-control" name="dataquisicao_equipamento" id="data_aquisicao" 
                                        value="<?= htmlspecialchars($_POST['dataquisicao_equipamento'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="texto_anofabrico" class="form-label">Ano de Fabrico</label>
                                    <input type="text" class="form-control" name="anofabrico_equipamento" id="texto_anofabrico" 
                                        value="<?= htmlspecialchars($_POST['anofabrico_equipamento'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_custo" class="form-label">Custo de Aquisição <small>(€)</small></label>
                                    <input type="text" class="form-control" name="custo_equipamento" id="texto_custo" 
                                        value="<?= htmlspecialchars($_POST['custo_equipamento'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="select_tipoentrada" class="form-label">Tipo de Entrada</label>
                                    <select class="form-select" name="tipoentrada_equipamento" id="select_tipoentrada">
                                        <option value="" <?= empty($_POST['tipoentrada_equipamento']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="compra" <?= (($_POST['tipoentrada_equipamento'] ?? '') == 'compra') ? 'selected' : '' ?>>Compra</option>
                                        <option value="doacao" <?= (($_POST['tipoentrada_equipamento'] ?? '') == 'doacao') ? 'selected' : '' ?>>Doação</option>
                                        <option value="aluguer" <?= (($_POST['tipoentrada_equipamento'] ?? '') == 'aluguer') ? 'selected' : '' ?>>Aluguer</option>
                                        <option value="emprestimo" <?= (($_POST['tipoentrada_equipamento'] ?? '') == 'emprestimo') ? 'selected' : '' ?>>Empréstimo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="select_estado" class="form-label">Estado</label>
                                    <select class="form-select" name="estado_equipamento" id="select_estado">
                                        <option value="" <?= empty($_POST['estado_equipamento']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="ativo" <?= (($_POST['estado_equipamento'] ?? '') == 'ativo') ? 'selected' : '' ?>>Ativo</option>
                                        <option value="manutencao" <?= (($_POST['estado_equipamento'] ?? '') == 'manutencao') ? 'selected' : '' ?>>Em Manutenção</option>
                                        <option value="inativo" <?= (($_POST['estado_equipamento'] ?? '') == 'inativo') ? 'selected' : '' ?>>Inativo</option>
                                        <option value="calibracao" <?= (($_POST['estado_equipamento'] ?? '') == 'calibracao') ? 'selected' : '' ?>>Em Calibração</option>
                                        <option value="quarentena" <?= (($_POST['estado_equipamento'] ?? '') == 'quarentena') ? 'selected' : '' ?>>Em Quarentena</option>
                                        <option value="abatido" <?= (($_POST['estado_equipamento'] ?? '') == 'abatido') ? 'selected' : '' ?>>Abatido</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="select_criticidade" class="form-label">Criticidade</label>
                                    <select class="form-select" name="criticidade_equipamento" id="select_criticidade">
                                        <option value="" <?= empty($_POST['criticidade_equipamento']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="baixa" <?= (($_POST['criticidade_equipamento'] ?? '') == 'baixa') ? 'selected' : '' ?>>Baixa</option>
                                        <option value="media" <?= (($_POST['criticidade_equipamento'] ?? '') == 'media') ? 'selected' : '' ?>>Média</option>
                                        <option value="alta" <?= (($_POST['criticidade_equipamento'] ?? '') == 'alta') ? 'selected' : '' ?>>Alta</option>
                                        <option value="suporte_vida" <?= (($_POST['criticidade_equipamento'] ?? '') == 'suporte_vida') ? 'selected' : '' ?>>Suporte de Vida</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="select_localizacao" class="form-label">Localização</label>
                                    <select class="form-select" name="localizacao_equipamento" id="select_localizacao">
                                        <option value="" <?= empty($_POST['localizacao_equipamento']) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <?php foreach ($localizacoes as $loc): ?>
                                            <option value="<?= $loc['id'] ?>" <?= (($_POST['localizacao_equipamento'] ?? '') == $loc['id']) ? 'selected' : '' ?>>
                                                <?= $loc['servico'] ?> - <?= $loc['sala_internamento_gabinete'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_equipamento" id="texto_observacoes" 
                                        value="<?= htmlspecialchars($_POST['observacoes_equipamento'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="d-flex justify-content-end gap-2 mb-4">
                                <a href="equipamentos.php" class="btn btn-outline-secondary">
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

<script>
flatpickr("#data_aquisicao", {
    dateFormat: "Y-m-d"
});
</script>

<?php include '../../includes/footer.php'; ?>