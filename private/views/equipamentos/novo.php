<?php  
// -------------------------------------------------------------------- 
// SEGURANÇA: Proteção de acesso à página. 
// Este ficheiro deve ser acedido apenas por utilizadores autenticados. 
// Caso não exista sessão iniciada, o utilizador será redirecionado para o login.
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged(); // Inicia a sessão (se necessário) e verifica se o utilizador está autenticado 
require_once __DIR__ . '/../../includes/validacoes.php';

// Calcular o próximo código de equipamento disponível (incremental, mantém o primeiro grupo, sobe o segundo até 999)
try {
    $ligacaoCodigo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
    $ligacaoCodigo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmtCodigos = $ligacaoCodigo->query("SELECT codigo_interno FROM equipamentos");
    $codigosExistentes = $stmtCodigos->fetchAll(PDO::FETCH_COLUMN);

    $maiorCombinado = 0;
    foreach ($codigosExistentes as $codigoExistente) {
        $partes = explode('.', $codigoExistente);
        if (count($partes) >= 2 && is_numeric($partes[0]) && is_numeric($partes[1])) {
            $combinado = ((int) $partes[0]) * 1000 + ((int) $partes[1]);
            $maiorCombinado = max($maiorCombinado, $combinado);
        }
    }

    $proximoCombinado = $maiorCombinado + 1;
    $primeiraParcela = intdiv($proximoCombinado, 1000);
    $segundaParcela = $proximoCombinado % 1000;

    $proximoCodigo = sprintf('%02d.%03d.00', $primeiraParcela, $segundaParcela);

} catch (PDOException $err) {
    header('Location: equipamentos.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recolher dados
    $codigo       = $proximoCodigo;
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
    $fornecedorFabricante   = $_POST['fornecedor_fabricante_equipamento'] ?? '';
    $fornecedorDistribuidor = $_POST['fornecedor_distribuidor_equipamento'] ?? '';
    $fornecedorAssistencia  = $_POST['fornecedor_assistencia_equipamento'] ?? '';

    // 2. Trim
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
        validar_designacao($designacao),
        validar_categoria($categoria),
        validar_marca($marca),
        validar_modelo($modelo),
        validar_nserie($nserie),
        validar_nserie_unico($nserie, $ligacaoCodigo),
        validar_fabricante($fabricante),
        validar_data_aquisicao($dataquisicao, $anofabrico),
        validar_ano_fabrico($anofabrico),
        validar_custo($custo),
        validar_tipo_entrada($tipoentrada),
        validar_estado($estado),
        validar_criticidade($criticidade),
        validar_localizacao($localizacao),
        validar_observacoes($observacoes),
        validar_fornecedores_associados($fornecedorFabricante, $fornecedorDistribuidor, $fornecedorAssistencia)
    );

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

            // Id do equipamento que acabou de ser inserido
            $idNovoEquipamento = $ligacao->lastInsertId();

            // Vai buscar o id do utilizador autenticado (a sessão só guarda o email)
            $stmtUser = $ligacao->prepare("SELECT id FROM utilizadores WHERE email = :email");
            $stmtUser->execute([':email' => $_SESSION['utilizador']]);
            $idUtilizador = $stmtUser->fetchColumn();

            // Regista o evento na tabela de logs
            $stmtLog = $ligacao->prepare("INSERT INTO logs (id_utilizador, tipo_evento, descricao) VALUES (:id_utilizador, 'equipamento_criado', :descricao)");
            $stmtLog->execute([
                ':id_utilizador' => $idUtilizador,
                ':descricao'     => 'Equipamento criado (id: ' . $idNovoEquipamento . ')'
            ]);

            // Inserir associações de fornecedores (só se não for "indefinido")
            $fornecedoresParaInserir = [
                'fabricante'   => $_POST['fornecedor_fabricante_equipamento'] ?? '',
                'distribuidor' => $_POST['fornecedor_distribuidor_equipamento'] ?? '',
                'assistencia'  => $_POST['fornecedor_assistencia_equipamento'] ?? '',
            ];

            foreach ($fornecedoresParaInserir as $tipo => $idFornecedor) {
                if (!empty($idFornecedor) && is_numeric($idFornecedor)) {
                    $stmtForn = $ligacao->prepare("
                        INSERT INTO equipamento_fornecedor (id_equipamento, id_fornecedor, tipo)
                        VALUES (:id_equipamento, :id_fornecedor, :tipo)
                    ");
                    $stmtForn->execute([
                        ':id_equipamento' => $idNovoEquipamento,
                        ':id_fornecedor'  => $idFornecedor,
                        ':tipo'           => $tipo
                    ]);
                }
            }

            $_SESSION['toast_success'] = 'Equipamento criado com sucesso.';

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

<?php
// Carregar fornecedores disponíveis por tipo
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
    $fabricantes   = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE tipo = 'fabricante'   AND apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);
    $distribuidores = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE tipo = 'distribuidor' AND apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);
    $assistencias  = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE tipo = 'assistencia'  AND apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);
    $ligacao = null;
} catch (PDOException $e) {
    $fabricantes = $distribuidores = $assistencias = [];
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
                                <div class="col-md-4">
                                    <label class="form-label">Código Interno de Inventário</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($proximoCodigo) ?>" disabled>
                                </div>
                                <div class="col-md-8">
                                    <label for="texto_designacao" class="form-label">Designação do Equipamento</label>
                                    <input type="text" class="form-control" name="designacao_equipamento" id="texto_designacao"
                                        value="<?= htmlspecialchars($_POST['designacao_equipamento'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
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
                                <div class="col-md-4">
                                    <label for="texto_marca" class="form-label">Marca</label>
                                    <input type="text" class="form-control" name="marca_equipamento" id="texto_marca" 
                                        value="<?= htmlspecialchars($_POST['marca_equipamento'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_fabricante" class="form-label">Fabricante</label>
                                    <input type="text" class="form-control" id="texto_fabricante" 
                                        value="<?= htmlspecialchars($_POST['fabricante_equipamento'] ?? '') ?>">
                                    <input type="hidden" name="fabricante_equipamento" id="hidden_fabricante" 
                                        value="<?= htmlspecialchars($_POST['fabricante_equipamento'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="texto_modelo" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" name="modelo_equipamento" id="texto_modelo" 
                                        value="<?= htmlspecialchars($_POST['modelo_equipamento'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_nserie" class="form-label">Número de Série</label>
                                    <input type="text" class="form-control" name="nserie_equipamento" id="texto_nserie" 
                                        value="<?= htmlspecialchars($_POST['nserie_equipamento'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_anofabrico" class="form-label">Ano de Fabrico</label>
                                    <input type="text" class="form-control" name="anofabrico_equipamento" id="texto_anofabrico" 
                                        value="<?= htmlspecialchars($_POST['anofabrico_equipamento'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="texto_dataquisicao" class="form-label">Data de Aquisição</label>
                                    <input type="text" class="form-control" name="dataquisicao_equipamento" id="data_aquisicao" 
                                        value="<?= htmlspecialchars($_POST['dataquisicao_equipamento'] ?? '') ?>" required>
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

                            <!-- Fornecedores Associados -->
                            <hr>
                            <h5 class="mb-3"><i class="fa-solid fa-truck-medical me-2"></i>Fornecedores Associados</h5>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="select_fornecedor_fabricante" class="form-label">Fabricante</label>
                                    <select class="form-select" name="fornecedor_fabricante_equipamento" id="select_fornecedor_fabricante">
                                        <option value="">-- Indefinido --</option>
                                        <?php foreach ($fabricantes as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" <?= (($_POST['fornecedor_fabricante_equipamento'] ?? '') == $fornecedor['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fornecedor['nome_empresa']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="select_fornecedor_distribuidor" class="form-label">Distribuidor / Fornecedor Comercial</label>
                                    <select class="form-select" name="fornecedor_distribuidor_equipamento" id="select_fornecedor_distribuidor">
                                        <option value="">-- Indefinido --</option>
                                        <?php foreach ($distribuidores as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" <?= (($_POST['fornecedor_distribuidor_equipamento'] ?? '') == $fornecedor['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fornecedor['nome_empresa']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="select_fornecedor_assistencia" class="form-label">Empresa de Assistência Técnica</label>
                                    <select class="form-select" name="fornecedor_assistencia_equipamento" id="select_fornecedor_assistencia">
                                        <option value="">-- Indefinido --</option>
                                        <?php foreach ($assistencias as $fornecedor): ?>
                                            <option value="<?= $fornecedor['id'] ?>" <?= (($_POST['fornecedor_assistencia_equipamento'] ?? '') == $fornecedor['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fornecedor['nome_empresa']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
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

// Sincroniza o campo "Fabricante" (texto) com o select de Fornecedor Fabricante
document.addEventListener('DOMContentLoaded', function () {
    const selectFabricante = document.getElementById('select_fornecedor_fabricante');
    const campoTextoFabricante = document.getElementById('texto_fabricante');
    const campoHiddenFabricante = document.getElementById('hidden_fabricante');

    function atualizarFabricante() {
        const valorSelecionado = selectFabricante.value;
        const textoSelecionado = selectFabricante.options[selectFabricante.selectedIndex].text;

        if (valorSelecionado !== '') {
            // Um fornecedor foi escolhido → preenche e bloqueia o campo de texto
            campoTextoFabricante.value = textoSelecionado;
            campoTextoFabricante.disabled = true;
            campoHiddenFabricante.value = textoSelecionado;
        } else {
            // Indefinido → liberta o campo para edição manual
            campoTextoFabricante.disabled = false;
            campoTextoFabricante.value = '';
            campoHiddenFabricante.value = '';
        }
    }

    // Atualiza sempre que o select mudar
    selectFabricante.addEventListener('change', atualizarFabricante);

    // Mantém o campo hidden sincronizado se o utilizador escrever manualmente (caso Indefinido)
    campoTextoFabricante.addEventListener('input', function () {
        campoHiddenFabricante.value = campoTextoFabricante.value;
    });

    // Corre uma vez ao carregar a página, para o caso de já existir um valor pré-selecionado
    atualizarFabricante();
});
</script>

<?php include '../../includes/footer.php'; ?>