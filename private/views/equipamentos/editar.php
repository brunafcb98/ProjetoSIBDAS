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
//Desencriptação e validar ID equipamento
$idEquipamentoEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEquipamentoEncrypted);

if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

// Ligação temporária só para verificar se o Número de Série já existe
try {
    $ligacaoTemp = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
} catch (PDOException $e) {
    $ligacaoTemp = null;
}

//Detetar submissao via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaDesignacao   = $_POST['designacao_equipamento'] ?? '';
    $novaCategoria    = $_POST['categoria_equipamento'] ?? '';
    $novaMarca        = $_POST['marca_equipamento'] ?? '';
    $novoModelo       = $_POST['modelo_equipamento'] ?? '';
    $novoNserie       = $_POST['nserie_equipamento'] ?? '';
    $novoFabricante   = $_POST['fabricante_equipamento'] ?? '';
    $novaData         = $_POST['dataquisicao_equipamento'] ?? '';
    $novoAno          = $_POST['anofabrico_equipamento'] ?? null;
    $novoCusto        = $_POST['custo_equipamento'] ?? null;
    $novoTipoEntrada  = $_POST['tipoentrada_equipamento'] ?? '';
    $novoEstado       = $_POST['estado_equipamento'] ?? '';
    $novaCriticidade  = $_POST['criticidade_equipamento'] ?? '';
    $novaLocalizacao  = $_POST['localizacao_equipamento'] ?? '';
    $novasObservacoes = $_POST['observacoes_equipamento'] ?? '';
    $novoFornecedorFabricante   = $_POST['fornecedor_fabricante_equipamento'] ?? '';
    $novoFornecedorDistribuidor = $_POST['fornecedor_distribuidor_equipamento'] ?? '';
    $novoFornecedorAssistencia  = $_POST['fornecedor_assistencia_equipamento'] ?? '';


    $erros = array_merge(
        validar_designacao($novaDesignacao),
        validar_categoria($novaCategoria),
        validar_marca($novaMarca),
        validar_modelo($novoModelo),
        validar_nserie($novoNserie),
        validar_nserie_unico($novoNserie, $ligacaoTemp, $idEquipamento),
        validar_fabricante($novoFabricante),
        validar_data_aquisicao($novaData, $novoAno),
        validar_ano_fabrico($novoAno),
        validar_custo($novoCusto),
        validar_tipo_entrada($novoTipoEntrada),
        validar_estado($novoEstado),
        validar_criticidade($novaCriticidade),
        validar_localizacao($novaLocalizacao),
        validar_observacoes($novasObservacoes),
        validar_fornecedores_associados($novoFornecedorFabricante, $novoFornecedorDistribuidor, $novoFornecedorAssistencia)
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
                UPDATE equipamentos 
                SET designacao      = :designacao,
                    categoria       = :categoria,
                    marca           = :marca,
                    modelo          = :modelo,
                    numero_serie    = :nserie,
                    fabricante      = :fabricante,
                    data_aquisicao  = :data,
                    ano_fabrico     = :ano,
                    custo_aquisicao = :custo,
                    tipo_entrada    = :tipo_entrada,
                    estado          = :estado,
                    criticidade     = :criticidade,
                    id_localizacao  = :localizacao,
                    observacoes     = :observacoes
                WHERE id = :id AND apagado = 0
            ");

            $stmt->bindParam(':designacao',   $novaDesignacao,   PDO::PARAM_STR);
            $stmt->bindParam(':categoria',    $novaCategoria,    PDO::PARAM_STR);
            $stmt->bindParam(':marca',        $novaMarca,        PDO::PARAM_STR);
            $stmt->bindParam(':modelo',       $novoModelo,       PDO::PARAM_STR);
            $stmt->bindParam(':nserie',       $novoNserie,       PDO::PARAM_STR);
            $stmt->bindParam(':fabricante',   $novoFabricante,   PDO::PARAM_STR);
            $stmt->bindParam(':data',         $novaData,         PDO::PARAM_STR);
            $stmt->bindParam(':ano',          $novoAno,          PDO::PARAM_INT);
            $stmt->bindParam(':custo',        $novoCusto,        PDO::PARAM_STR);
            $stmt->bindParam(':tipo_entrada', $novoTipoEntrada,  PDO::PARAM_STR);
            $stmt->bindParam(':estado',       $novoEstado,       PDO::PARAM_STR);
            $stmt->bindParam(':criticidade',  $novaCriticidade,  PDO::PARAM_STR);
            $stmt->bindParam(':localizacao',  $novaLocalizacao,  PDO::PARAM_INT);
            $stmt->bindParam(':observacoes',  $novasObservacoes, PDO::PARAM_STR);
            $stmt->bindParam(':id',           $idEquipamento,    PDO::PARAM_INT);

            $stmt->execute();

            // Gerir associações de fornecedores
            $fornecedoresParaGuardar = [
                'fabricante'   => $_POST['fornecedor_fabricante_equipamento'] ?? '',
                'distribuidor' => $_POST['fornecedor_distribuidor_equipamento'] ?? '',
                'assistencia'  => $_POST['fornecedor_assistencia_equipamento'] ?? '',
            ];

            foreach ($fornecedoresParaGuardar as $tipo => $idFornecedor) {
                // Ver se já existe associação para este tipo
                $stmtVerifica = $ligacao->prepare("SELECT id_fornecedor FROM equipamento_fornecedor WHERE id_equipamento = :id_equipamento AND tipo = :tipo");
                $stmtVerifica->bindParam(':id_equipamento', $idEquipamento, PDO::PARAM_INT);
                $stmtVerifica->bindParam(':tipo',           $tipo,          PDO::PARAM_STR);
                $stmtVerifica->execute();
                $associacaoAtual = $stmtVerifica->fetchColumn();

                $escolheuFornecedor = !empty($idFornecedor) && is_numeric($idFornecedor);

                if ($associacaoAtual && $escolheuFornecedor) {
                    // Já existia — atualiza se mudou
                    if ($associacaoAtual != $idFornecedor) {
                        $stmtForn = $ligacao->prepare("UPDATE equipamento_fornecedor SET id_fornecedor = :id_fornecedor WHERE id_equipamento = :id_equipamento AND tipo = :tipo");
                        $stmtForn->bindParam(':id_fornecedor',  $idFornecedor,  PDO::PARAM_INT);
                        $stmtForn->bindParam(':id_equipamento', $idEquipamento, PDO::PARAM_INT);
                        $stmtForn->bindParam(':tipo',           $tipo,          PDO::PARAM_STR);
                        $stmtForn->execute();
                    }
                } elseif (!$associacaoAtual && $escolheuFornecedor) {
                    // Não existia — insere
                    $stmtForn = $ligacao->prepare("INSERT INTO equipamento_fornecedor (id_equipamento, id_fornecedor, tipo) VALUES (:id_equipamento, :id_fornecedor, :tipo)");
                    $stmtForn->bindParam(':id_equipamento', $idEquipamento, PDO::PARAM_INT);
                    $stmtForn->bindParam(':id_fornecedor',  $idFornecedor,  PDO::PARAM_INT);
                    $stmtForn->bindParam(':tipo',           $tipo,          PDO::PARAM_STR);
                    $stmtForn->execute();
                } elseif ($associacaoAtual && !$escolheuFornecedor) {
                    // Existia mas escolheu Indefinido — apaga
                    $stmtForn = $ligacao->prepare("DELETE FROM equipamento_fornecedor WHERE id_equipamento = :id_equipamento AND tipo = :tipo");
                    $stmtForn->bindParam(':id_equipamento', $idEquipamento, PDO::PARAM_INT);
                    $stmtForn->bindParam(':tipo',           $tipo,          PDO::PARAM_STR);
                    $stmtForn->execute();
                }
                // Se !$associacaoAtual && !$escolheuFornecedor → não faz nada (era indefinido e continua indefinido)
            }

            // Mensagem de sucesso e redirecionamento (opcional) 
            header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
            exit;

        } catch (PDOException $err) {
            $erros[] = "Erro ao atualizar o equipamento: " . $err->getMessage();
        }
    }
}

//SELECT do equipamento
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparar e executar a query com segurança
    // AND apagado = 0 garante que equipamentos com soft delete não são editáveis
    $stmt = $ligacao->prepare("SELECT * FROM equipamentos WHERE id = :id AND apagado = 0");
    $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmt->execute();

    $equipamento = $stmt->fetch(PDO::FETCH_OBJ);

    // Se não encontrou o equipamento, redireciona
    if (!$equipamento) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

   // Carregar fornecedores disponíveis por tipo
    $fabricantes    = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE tipo = 'fabricante'   AND apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);
    $distribuidores = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE tipo = 'distribuidor' AND apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);
    $assistencias   = $ligacao->query("SELECT id, nome_empresa FROM fornecedores WHERE tipo = 'assistencia'  AND apagado = 0 ORDER BY nome_empresa ASC")->fetchAll(PDO::FETCH_ASSOC);
   
    // Ir buscar os fornecedores já associados a este equipamento
    $stmtAssoc = $ligacao->prepare("SELECT tipo, id_fornecedor FROM equipamento_fornecedor WHERE id_equipamento = :id");
    $stmtAssoc->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtAssoc->execute();
    $associados = $stmtAssoc->fetchAll(PDO::FETCH_ASSOC);

    // Organizar por tipo
    $fornecedoresAssociados = [];
    foreach ($associados as $assoc) {
        $fornecedoresAssociados[$assoc['tipo']] = $assoc['id_fornecedor'];
    }

} catch (PDOException $err) {
    $erro = "Erro na ligação à base de dados.";
    $equipamento = null;
    $fornecedoresAssociados = [];
    $fabricantes = $distribuidores = $assistencias = [];
}

// Fecha a ligação
$ligacao = null;

/*
// Para testar (temporário) - verificar se o ID do equipamento desencriptado corresponde ao da BD
echo $idEquipamento;
*/
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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-pen-to-square me-2"></i> Atualização de Dados - Equipamentos</strong></h2>
                        <hr>
                        <form action="editar.php?id_equipamento=<?= $idEquipamentoEncrypted ?>" method="post" novalidate>

                            <!-- Identificação do equipamento -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="texto_codigo" class="form-label">Código Interno de Inventário</label>
                                    <input type="text" class="form-control" id="texto_codigo" 
                                        value="<?= htmlspecialchars($equipamento->codigo_interno) ?>" disabled> 
                                </div>
                            
                                <div class="col-md-8">
                                    <label for="texto_designacao" class="form-label">Designação do Equipamento</label>
                                    <input type="text" class="form-control" name="designacao_equipamento" id="texto_designacao" 
                                        value="<?= htmlspecialchars($equipamento->designacao) ?>" required> 
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="select_categoria" class="form-label">Categoria</label>
                                    <select class="form-select" name="categoria_equipamento" id="select_categoria">
                                       <option value="" <?= empty($equipamento->categoria) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="monitorizacao" <?= $equipamento->categoria == 'monitorizacao' ? 'selected' : '' ?>>Monitorização</option>
                                        <option value="suporte_vida" <?= $equipamento->categoria == 'suporte_vida' ? 'selected' : '' ?>>Suporte de Vida</option>
                                        <option value="terapia" <?= $equipamento->categoria == 'terapia' ? 'selected' : '' ?>>Terapia</option>
                                        <option value="diagnostico" <?= $equipamento->categoria == 'diagnostico' ? 'selected' : '' ?>>Diagnóstico</option>
                                        <option value="laboratorio" <?= $equipamento->categoria == 'laboratorio' ? 'selected' : '' ?>>Laboratório</option>
                                        <option value="esterilizacao" <?= $equipamento->categoria == 'esterilizacao' ? 'selected' : '' ?>>Esterilização</option>
                                        <option value="reabilitacao" <?= $equipamento->categoria == 'reabilitacao' ? 'selected' : '' ?>>Reabilitação</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_marca" class="form-label">Marca</label>
                                    <input type="text" class="form-control" name="marca_equipamento" id="texto_marca" 
                                        value="<?= htmlspecialchars($equipamento->marca) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_fabricante" class="form-label">Fabricante</label>
                                    <input type="text" class="form-control" id="texto_fabricante" 
                                        value="<?= htmlspecialchars($equipamento->fabricante) ?>"
                                        <?= !empty($fornecedoresAssociados['fabricante']) ? 'disabled' : '' ?>>
                                    <input type="hidden" name="fabricante_equipamento" id="hidden_fabricante" 
                                        value="<?= htmlspecialchars($equipamento->fabricante) ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="texto_modelo" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" name="modelo_equipamento" id="texto_modelo" 
                                        value="<?= htmlspecialchars($equipamento->modelo) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_nserie" class="form-label">Número de Série</label>
                                    <input type="text" class="form-control" name="nserie_equipamento" id="texto_nserie" 
                                        value="<?= htmlspecialchars($equipamento->numero_serie) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_anofabrico" class="form-label">Ano de Fabrico</label>
                                    <input type="text" class="form-control" name="anofabrico_equipamento" id="texto_anofabrico" 
                                        value="<?= htmlspecialchars($equipamento->ano_fabrico) ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="texto_dataquisicao" class="form-label">Data de Aquisição</label>
                                    <input type="text" class="form-control" name="dataquisicao_equipamento" id="data_aquisicao" 
                                        value="<?= htmlspecialchars($equipamento->data_aquisicao) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_custo" class="form-label">Custo de Aquisição <small>(€)</small></label>
                                    <input type="text" class="form-control" name="custo_equipamento" id="texto_custo" 
                                        value="<?= htmlspecialchars($equipamento->custo_aquisicao) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="select_tipoentrada" class="form-label">Tipo de Entrada</label>
                                    <select class="form-select" name="tipoentrada_equipamento" id="select_tipoentrada">
                                        <option value="" <?= empty($equipamento->tipo_entrada) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="compra" <?= $equipamento->tipo_entrada == 'compra' ? 'selected' : '' ?>>Compra</option>
                                        <option value="doacao" <?= $equipamento->tipo_entrada == 'doacao' ? 'selected' : '' ?>>Doação</option>
                                        <option value="aluguer" <?= $equipamento->tipo_entrada == 'aluguer' ? 'selected' : '' ?>>Aluguer</option>
                                        <option value="emprestimo" <?= $equipamento->tipo_entrada == 'emprestimo' ? 'selected' : '' ?>>Empréstimo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="select_estado" class="form-label">Estado</label>
                                    <select class="form-select" name="estado_equipamento" id="select_estado">
                                        <option value="" <?= empty($equipamento->estado) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="ativo" <?= $equipamento->estado == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                        <option value="manutencao" <?= $equipamento->estado == 'manutencao' ? 'selected' : '' ?>>Em Manutenção</option>
                                        <option value="inativo" <?= $equipamento->estado == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                        <option value="calibracao" <?= $equipamento->estado == 'calibracao' ? 'selected' : '' ?>>Em Calibração</option>
                                        <option value="quarentena" <?= $equipamento->estado == 'quarentena' ? 'selected' : '' ?>>Em Quarentena</option>
                                        <option value="abatido" <?= $equipamento->estado == 'abatido' ? 'selected' : '' ?>>Abatido</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="select_criticidade" class="form-label">Criticidade</label>
                                    <select class="form-select" name="criticidade_equipamento" id="select_criticidade">
                                        <option value="" <?= empty($equipamento->criticidade) ? 'selected' : '' ?>>Escolha uma opção</option>
                                        <option value="baixa" <?= $equipamento->criticidade == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                                        <option value="media" <?= $equipamento->criticidade == 'media' ? 'selected' : '' ?>>Média</option>
                                        <option value="alta" <?= $equipamento->criticidade == 'alta' ? 'selected' : '' ?>>Alta</option>
                                        <option value="suporte_vida" <?= $equipamento->criticidade == 'suporte_vida' ? 'selected' : '' ?>>Suporte de Vida</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="select_localizacao" class="form-label">Localização</label>
                                    <select class="form-select" name="localizacao_equipamento" id="select_localizacao" required>
                                        <option value="" <?= empty($equipamento->id_localizacao) ? 'selected' : '' ?>>Escolha uma opção</option>
                                            <?php foreach ($localizacoes as $loc): ?>
                                                <option value="<?= $loc['id'] ?>" <?= $equipamento->id_localizacao == $loc['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($loc['servico']) ?> - <?= htmlspecialchars($loc['sala_internamento_gabinete']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_equipamento" id="texto_observacoes" 
                                        value="<?= htmlspecialchars($equipamento->observacoes ?? '') ?>">                
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
                                            <option value="<?= $fornecedor['id'] ?>" <?= ($fornecedoresAssociados['fabricante'] ?? '') == $fornecedor['id'] ? 'selected' : '' ?>>
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
                                            <option value="<?= $fornecedor['id'] ?>" <?= ($fornecedoresAssociados['distribuidor'] ?? '') == $fornecedor['id'] ? 'selected' : '' ?>>
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
                                            <option value="<?= $fornecedor['id'] ?>" <?= ($fornecedoresAssociados['assistencia'] ?? '') == $fornecedor['id'] ? 'selected' : '' ?>>
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
            campoTextoFabricante.value = textoSelecionado;
            campoTextoFabricante.disabled = true;
            campoHiddenFabricante.value = textoSelecionado;
        } else {
            campoTextoFabricante.disabled = false;
            campoTextoFabricante.value = '';
            campoHiddenFabricante.value = '';
        }
    }

    selectFabricante.addEventListener('change', atualizarFabricante);

    campoTextoFabricante.addEventListener('input', function () {
        campoHiddenFabricante.value = campoTextoFabricante.value;
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
