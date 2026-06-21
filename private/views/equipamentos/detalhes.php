<?php  
// -------------------------------------------------------------------- 
// SEGURANÇA: Proteção de acesso à página. 
// Este ficheiro deve ser acedido apenas por utilizadores autenticados. 
// Caso não exista sessão iniciada, o utilizador será redirecionado para o login.
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged(); // Inicia a sessão (se necessário) e verifica se o utilizador está autenticado 

//Desencriptação e validar ID equipamento
$idEquipamentoEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEquipamentoEncrypted);

if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

// Ligação à base de dados e obtenção dos dados do equipamento
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("
        SELECT e.*, l.servico, l.sala_internamento_gabinete 
        FROM equipamentos e 
        LEFT JOIN localizacoes l ON e.id_localizacao = l.id
        WHERE e.id = :id AND e.apagado = 0
    ");
    $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmt->execute();

    $equipamento = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ir buscar os fornecedores associados a este equipamento
    $stmtFornecedores = $ligacao->prepare("
        SELECT ef.tipo, f.nome_empresa, f.telefone, f.email
        FROM equipamento_fornecedor ef
        JOIN fornecedores f ON ef.id_fornecedor = f.id
        WHERE ef.id_equipamento = :id
    ");
    $stmtFornecedores->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtFornecedores->execute();
    $fornecedoresAssociados = $stmtFornecedores->fetchAll(PDO::FETCH_ASSOC);

    // Organizar por tipo
    $fornecedoresDetalhes = [];
    foreach ($fornecedoresAssociados as $fornecedor) {
        $fornecedoresDetalhes[$fornecedor['tipo']] = $fornecedor;
    }

    // Ir buscar os documentos associados a este equipamento
    // O administrador vê todos os documentos (incluindo desativados).
    // O técnico vê apenas os documentos ativos (apagado = 0).
    $filtroApagadoDoc = ($_SESSION['profile'] === 'administrador') ? '' : 'AND d.apagado = 0';

    $stmtDocumentos = $ligacao->prepare("
        SELECT d.*, f.nome_empresa
        FROM documentos d
        LEFT JOIN fornecedores f ON d.id_fornecedor = f.id
        WHERE d.id_equipamento = :id $filtroApagadoDoc
        ORDER BY d.data_documento DESC
    ");
    $stmtDocumentos->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtDocumentos->execute();
    $documentosEquipamento = $stmtDocumentos->fetchAll(PDO::FETCH_ASSOC);

    // Ir buscar as garantias/contratos associadas a este equipamento
    // O administrador vê todas (incluindo desativadas).
    // O técnico vê apenas as ativas (apagado = 0).
    $filtroApagadoGar = ($_SESSION['profile'] === 'administrador') ? '' : 'AND g.apagado = 0';

    $stmtGarantias = $ligacao->prepare("
        SELECT g.*, f.nome_empresa
        FROM garantias_contratos g
        LEFT JOIN fornecedores f ON g.id_fornecedor = f.id
        WHERE g.id_equipamento = :id $filtroApagadoGar
        ORDER BY g.id DESC
    ");
    $stmtGarantias->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtGarantias->execute();
    $garantiasEquipamento = $stmtGarantias->fetchAll(PDO::FETCH_ASSOC);

    // Ir buscar os acessórios associados a este equipamento
    $filtroApagadoAcess = ($_SESSION['profile'] === 'administrador') ? '' : 'AND apagado = 0';

    $stmtAcessorios = $ligacao->prepare("
        SELECT * FROM acessorios
        WHERE id_equipamento_pai = :id $filtroApagadoAcess
        ORDER BY codigo
    ");
    $stmtAcessorios->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtAcessorios->execute();
    $acessoriosEquipamento = $stmtAcessorios->fetchAll(PDO::FETCH_ASSOC);

    // Ir buscar os consumíveis associados a este equipamento
    $filtroApagadoCons = ($_SESSION['profile'] === 'administrador') ? '' : 'AND c.apagado = 0';

    $stmtConsumiveis = $ligacao->prepare("
        SELECT c.*, f.nome_empresa
        FROM consumiveis c
        LEFT JOIN fornecedores f ON c.id_fornecedor = f.id
        WHERE c.id_equipamento_pai = :id $filtroApagadoCons
        ORDER BY c.codigo
    ");
    $stmtConsumiveis->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmtConsumiveis->execute();
    $consumiveisEquipamento = $stmtConsumiveis->fetchAll(PDO::FETCH_ASSOC);

    if (!$equipamento) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

} catch (PDOException $err) {
    echo "<p class='text-danger'>Erro: " . $err->getMessage() . "</p>";
    exit;
}

// Mapas de tradução: convertem os valores técnicos guardados na BD (em minúsculas, sem espaços) para o texto formatado a apresentar ao utilizador. 
// Mesma lógica aplicada na listagem e detalhes
$categorias = [
    'monitorizacao' => 'Monitorização',
    'suporte_vida'  => 'Suporte de Vida',
    'terapia'       => 'Terapia',
    'diagnostico'   => 'Diagnóstico',
    'laboratorio'   => 'Laboratório',
    'esterilizacao' => 'Esterilização',
    'reabilitacao'  => 'Reabilitação'
];
$estados = [
    'ativo'       => 'Ativo',
    'manutencao'  => 'Em Manutenção',
    'inativo'     => 'Inativo',
    'calibracao'  => 'Em Calibração',
    'quarentena'  => 'Em Quarentena',
    'abatido'     => 'Abatido'
];
$criticidades = [
    'baixa'        => 'Baixa',
    'media'        => 'Média',
    'alta'         => 'Alta',
    'suporte_vida' => 'Suporte de Vida'
];
$tipos_entrada = [
    'compra'      => 'Compra',
    'doacao'      => 'Doação',
    'aluguer'     => 'Aluguer',
    'emprestimo'  => 'Empréstimo'
];
$tipos_fornecedor = [
    'fabricante'   => 'Fabricante',
    'consumiveis'  => 'Fornecedor de Consumíveis / Acessórios',
    'distribuidor' => 'Distribuidor / Fornecedor Comercial',
    'assistencia'  => 'Empresa de Assistência Técnica'
];
$tipos_documento = [
    'manual'                   => 'Manual de Instruções',
    'certificado_calibracao'   => 'Certificado de Calibração',
    'fatura'                   => 'Fatura de Compra',
    'ficha_tecnica'             => 'Ficha Técnica',
    'certificado_conformidade' => 'Certificado de Conformidade',
    'outro'                     => 'Outro'
];
$tipos_contrato = [
    'garantia_fabricante'   => 'Garantia de Fabricante',
    'manutencao_preventiva' => 'Manutenção Preventiva',
    'manutencao_corretiva'  => 'Manutenção Corretiva',
    'manutencao_completa'   => 'Manutenção Completa',
    'outro'                 => 'Outro'
];
$periodicidades = [
    'mensal'        => 'Mensal',
    'trimestral'    => 'Trimestral',
    'semestral'     => 'Semestral',
    'anual'         => 'Anual',
    'nao_aplicavel' => 'Não Aplicável'
];

?>

<?php include '../../includes/header.php'; ?> 
<?php include '../../includes/nav.php'; ?> 

<?php include '../../includes/toast.php'; ?>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-center mt-4">
                <div class="card w-100 shadow rounded" style="max-width: 1200px;">
                    <div class="card-body">
                        <h2 class="mb-4">
                            <strong><i class="fa-solid fa-stethoscope me-2"></i> Detalhes do Equipamento</strong>
                            <?php if ($equipamento['estado'] === 'ativo'): ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php elseif ($equipamento['estado'] === 'inativo'): ?>
                                <span class="badge bg-warning">Inativo</span>
                            <?php elseif ($equipamento['estado'] === 'abatido'): ?>
                                <span class="badge bg-danger">Abatido</span>
                            <?php elseif ($equipamento['estado'] === 'manutencao'): ?>
                                <span class="badge bg-secondary">Em Manutenção</span>
                            <?php elseif ($equipamento['estado'] === 'calibracao'): ?>
                                <span class="badge bg-secondary">Em Calibração</span>
                            <?php elseif ($equipamento['estado'] === 'quarentena'): ?>
                                <span class="badge bg-secondary">Em Quarentena</span>
                            <?php endif; ?>
                        </h2>
                        <hr>

                        <!-- Navegação das abas -->
                        <ul class="nav nav-tabs mb-3" id="equipamentoTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="detalhes-tab" data-bs-toggle="tab" data-bs-target="#detalhes" type="button" role="tab">
                                    <i class="fa-solid fa-circle-info me-1"></i> Detalhes
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="fornecedores-tab" data-bs-toggle="tab" data-bs-target="#fornecedores" type="button" role="tab">
                                    <i class="fa-solid fa-truck-medical me-1"></i> Fornecedores
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab">
                                    <i class="fa-solid fa-file me-1"></i> Documentos
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="garantias-tab" data-bs-toggle="tab" data-bs-target="#garantias" type="button" role="tab">
                                    <i class="fa-solid fa-shield-halved me-1"></i> Garantias / Contratos
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="acessorios-tab" data-bs-toggle="tab" data-bs-target="#acessorios" type="button" role="tab">
                                    <i class="fa-solid fa-puzzle-piece me-1"></i> Acessórios
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="consumiveis-tab" data-bs-toggle="tab" data-bs-target="#consumiveis" type="button" role="tab">
                                    <i class="fa-solid fa-boxes-stacked me-1"></i> Consumíveis
                                </button>
                            </li>
                        </ul>

                        <!-- Conteúdo das abas -->
                        <div class="tab-content" id="equipamentoTabsContent">

                            <!-- Aba Detalhes -->
                            <div class="tab-pane fade show active" id="detalhes" role="tabpanel">

                                <!-- Grupo: Identificação -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Código Interno de Inventário</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['codigo_interno']) ?></p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Designação do Equipamento</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['designacao']) ?></p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Categoria</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($categorias[$equipamento['categoria']]) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Marca</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['marca']) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Fabricante</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['fabricante']) ?></p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Modelo</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['modelo']) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Número de Série</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['numero_serie']) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Ano de Fabrico</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['ano_fabrico']) ?></p>
                                    </div>
                                </div>

                                <hr>

                                <!-- Grupo: Aquisição -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Data de Aquisição</label>
                                        <p class="form-control-plaintext"><?= date('d/m/Y', strtotime($equipamento['data_aquisicao'])) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Custo de Aquisição</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars(number_format((float)$equipamento['custo_aquisicao'], 2, ',', ' ')) ?> €</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Tipo de Entrada</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($tipos_entrada[$equipamento['tipo_entrada']]) ?></p>
                                    </div>
                                </div>

                                <hr>

                                 <!-- Grupo: Estado -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Estado</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($estados[$equipamento['estado']]) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Criticidade</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($criticidades[$equipamento['criticidade']]) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Localização</label>
                                        <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['servico'] . ' - ' . $equipamento['sala_internamento_gabinete']) ?></p>
                                    </div>
                                </div>

                                <hr>
                                <div class="mb-5">
                                    <label class="form-label fw-bold">Observações</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['observacoes'] ?? '—') ?></p>
                                </div>
                            </div>


                            <!-- Aba Fornecedores -->
                            <div class="tab-pane fade" id="fornecedores" role="tabpanel">
                                <div class="row mt-3">
                                    <?php
                                    $papeis = [
                                        'fabricante'   => ['label' => 'Fabricante',                             'icon' => 'fa-industry'],
                                        'distribuidor' => ['label' => 'Distribuidor / Fornecedor Comercial',     'icon' => 'fa-truck'],
                                        'assistencia'  => ['label' => 'Empresa de Assistência Técnica',          'icon' => 'fa-screwdriver-wrench'],
                                    ];
                                    foreach ($papeis as $tipo => $info): ?>
                                        <div class="col-md-4 mb-4">
                                            <div class="card h-100 border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold">
                                                        <i class="fa-solid <?= $info['icon'] ?> me-2 text-primary"></i>
                                                        <?= $info['label'] ?>
                                                    </h6>
                                                    <?php if (isset($fornecedoresDetalhes[$tipo])): ?>
                                                        <p class="mb-1"><?= htmlspecialchars($fornecedoresDetalhes[$tipo]['nome_empresa']) ?></p>
                                                        <p class="mb-1 text-muted small"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($fornecedoresDetalhes[$tipo]['telefone']) ?></p>
                                                        <p class="mb-0 text-muted small"><i class="fa-solid fa-envelope me-1"></i><?= htmlspecialchars($fornecedoresDetalhes[$tipo]['email']) ?></p>
                                                    <?php else: ?>
                                                        <p class="text-muted fst-italic">Indefinido</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Aba Documentos -->
                            <div class="tab-pane fade" id="documentos" role="tabpanel">
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="../documentos/novo_doc.php?id_equipamento=<?= aes_encrypt($idEquipamento) ?>" class="btn btn-primary">
                                        <i class="fa-solid fa-plus me-1"></i> Adicionar Documento
                                    </a>
                                </div>

                                <?php if (count($documentosEquipamento) == 0): ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="fa-solid fa-file fa-2x mb-3"></i>
                                        <p>Sem documentos associados.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Tipo</th>
                                                    <th>Nome</th>
                                                    <th>Data</th>
                                                    <th>Validade</th>
                                                    <th>Fornecedor</th>
                                                    <th>Ficheiro</th>
                                                    <th class="text-center">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($documentosEquipamento as $doc): ?>
                                                    <tr class="<?= $doc['apagado'] == 1 ? 'table-secondary text-muted' : '' ?>">
                                                        <td><?= htmlspecialchars($tipos_documento[$doc['tipo_documento']] ?? $doc['tipo_documento']) ?></td>
                                                        <td>
                                                            <?= htmlspecialchars($doc['nome_documento']) ?>
                                                            <?php if ($doc['apagado'] == 1): ?>
                                                                <span class="badge bg-secondary ms-1">Desativado</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= date('d/m/Y', strtotime($doc['data_documento'])) ?></td>
                                                        <td><?= !empty($doc['data_validade']) ? date('d/m/Y', strtotime($doc['data_validade'])) : '—' ?></td>
                                                        <td><?= htmlspecialchars($doc['nome_empresa'] ?? '—') ?></td>
                                                        <td>
                                                            <?php if (!empty($doc['caminho_ficheiro'])): ?>
                                                                <a href="<?= BASE_URL ?>/assets/uploads/<?= htmlspecialchars($doc['caminho_ficheiro']) ?>" target="_blank">
                                                                    <i class="fa-solid fa-file-arrow-down me-1"></i> Abrir
                                                                </a>
                                                            <?php else: ?>
                                                                —
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center text-nowrap">
                                                            <a href="../documentos/editar_doc.php?id_documento=<?= aes_encrypt($doc['id']) ?>" class="btn btn-sm btn-outline-warning me-1">
                                                                <i class="fa-regular fa-pen-to-square"></i>
                                                            </a>
                                                            <a href="../documentos/apagar_doc.php?id_documento=<?= aes_encrypt($doc['id']) ?>" class="btn btn-sm btn-outline-danger">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>


                            <!-- Aba Garantias / Contratos -->
                            <div class="tab-pane fade" id="garantias" role="tabpanel">
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="../garantias/novo_gar.php?id_equipamento=<?= aes_encrypt($idEquipamento) ?>" class="btn btn-primary">
                                        <i class="fa-solid fa-plus me-1"></i> Adicionar Garantia/Contrato
                                    </a>
                                </div>

                                <?php if (count($garantiasEquipamento) == 0): ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="fa-solid fa-shield-halved fa-2x mb-3"></i>
                                        <p>Sem garantias ou contratos associados.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Garantia (Início - Fim)</th>
                                                    <th>Contrato Manutenção</th>
                                                    <th>Tipo</th>
                                                    <th>Periodicidade</th>
                                                    <th>Entidade Responsável</th>
                                                    <th>Observações</th>
                                                    <th>Ficheiro</th>
                                                    <th class="text-center">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($garantiasEquipamento as $gar): ?>
                                                    <tr class="<?= $gar['apagado'] == 1 ? 'table-secondary text-muted' : '' ?>">
                                                        <td>
                                                            <?php if (!empty($gar['data_inicio_garantia'])): ?>
                                                                <?= date('d/m/Y', strtotime($gar['data_inicio_garantia'])) ?> a <?= date('d/m/Y', strtotime($gar['data_fim_garantia'])) ?>
                                                            <?php else: ?>
                                                                —
                                                            <?php endif; ?>
                                                            <?php if ($gar['apagado'] == 1): ?>
                                                                <span class="badge bg-secondary ms-1">Desativado</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= $gar['tem_contrato_manutencao'] == 1 ? 'Sim' : 'Não' ?></td>
                                                        <td><?= !empty($gar['tipo_contrato']) ? htmlspecialchars($tipos_contrato[$gar['tipo_contrato']] ?? $gar['tipo_contrato']) : '—' ?></td>
                                                        <td><?= !empty($gar['periodicidade']) ? htmlspecialchars($periodicidades[$gar['periodicidade']] ?? $gar['periodicidade']) : '—' ?></td>
                                                        <td><?= htmlspecialchars($gar['nome_empresa'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($gar['observacoes'] ?? '—') ?></td>
                                                        <td>
                                                            <?php if (!empty($gar['caminho_ficheiro'])): ?>
                                                                <a href="<?= BASE_URL ?>/assets/uploads/<?= htmlspecialchars($gar['caminho_ficheiro']) ?>" target="_blank">
                                                                    <i class="fa-solid fa-file-arrow-down me-1"></i> Abrir
                                                                </a>
                                                            <?php else: ?>
                                                                —
                                                            <?php endif; ?>
                                                        </td>

                                                        <td class="text-center text-nowrap">
                                                            <a href="../garantias/editar_gar.php?id_garantia=<?= aes_encrypt($gar['id']) ?>" class="btn btn-sm btn-outline-warning me-1">
                                                                <i class="fa-regular fa-pen-to-square"></i>
                                                            </a>
                                                            <a href="../garantias/apagar_gar.php?id_garantia=<?= aes_encrypt($gar['id']) ?>" class="btn btn-sm btn-outline-danger">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Aba Acessórios -->
                            <div class="tab-pane fade" id="acessorios" role="tabpanel">
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="../acessorios/novo_acessorio.php?id_equipamento=<?= aes_encrypt($idEquipamento) ?>" class="btn btn-primary">
                                        <i class="fa-solid fa-plus me-1"></i> Adicionar Acessório
                                    </a>
                                </div>

                                <?php if (count($acessoriosEquipamento) == 0): ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="fa-solid fa-puzzle-piece fa-2x mb-3"></i>
                                        <p>Sem acessórios associados.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Nome</th>
                                                    <th>Marca</th>
                                                    <th>Fabricante</th>
                                                    <th>Modelo</th>
                                                    <th>Nº Série</th>
                                                    <th>Estado</th>
                                                    <th>Observações</th>
                                                    <th class="text-center">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($acessoriosEquipamento as $acessorio): ?>
                                                    <tr class="<?= $acessorio['apagado'] == 1 ? 'table-secondary text-muted' : '' ?>">
                                                        <td><?= htmlspecialchars($acessorio['codigo']) ?></td>
                                                        <td>
                                                            <?= htmlspecialchars($acessorio['nome']) ?>
                                                            <?php if ($acessorio['apagado'] == 1): ?>
                                                                <span class="badge bg-secondary ms-1">Desativado</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($acessorio['marca'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($acessorio['fabricante'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($acessorio['modelo'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($acessorio['numero_serie'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($estados[$acessorio['estado']] ?? $acessorio['estado']) ?></td>
                                                        <td><?= htmlspecialchars($acessorio['observacoes'] ?? '—') ?></td>
                                                        <td class="text-center text-nowrap">
                                                            <a href="../acessorios/editar_acessorio.php?id_acessorio=<?= aes_encrypt($acessorio['id']) ?>" class="btn btn-sm btn-outline-warning me-1">
                                                                <i class="fa-regular fa-pen-to-square"></i>
                                                            </a>
                                                            <a href="../acessorios/apagar_acessorio.php?id_acessorio=<?= aes_encrypt($acessorio['id']) ?>" class="btn btn-sm btn-outline-danger">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Aba Consumíveis -->
                            <div class="tab-pane fade" id="consumiveis" role="tabpanel">
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="../consumiveis/novo_consumivel.php?id_equipamento=<?= aes_encrypt($idEquipamento) ?>" class="btn btn-primary">
                                        <i class="fa-solid fa-plus me-1"></i> Adicionar Consumível
                                    </a>
                                </div>

                                <?php if (count($consumiveisEquipamento) == 0): ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="fa-solid fa-boxes-stacked fa-2x mb-3"></i>
                                        <p>Sem consumíveis associados.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Nome</th>
                                                    <th>Quantidade</th>
                                                    <th>Fornecedor</th>
                                                    <th>Observações</th>
                                                    <th class="text-center">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($consumiveisEquipamento as $consumivel): ?>
                                                    <tr class="<?= $consumivel['apagado'] == 1 ? 'table-secondary text-muted' : '' ?>">
                                                        <td><?= htmlspecialchars($consumivel['codigo']) ?></td>
                                                        <td>
                                                            <?= htmlspecialchars($consumivel['nome']) ?>
                                                            <?php if ($consumivel['apagado'] == 1): ?>
                                                                <span class="badge bg-secondary ms-1">Desativado</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($consumivel['quantidade']) ?></td>
                                                        <td><?= htmlspecialchars($consumivel['nome_empresa'] ?? '—') ?></td>
                                                        <td><?= htmlspecialchars($consumivel['observacoes'] ?? '—') ?></td>
                                                        <td class="text-center text-nowrap">
                                                            <a href="../consumiveis/editar_consumivel.php?id_consumivel=<?= aes_encrypt($consumivel['id']) ?>" class="btn btn-sm btn-outline-warning me-1">
                                                                <i class="fa-regular fa-pen-to-square"></i>
                                                            </a>
                                                            <a href="../consumiveis/apagar_consumivel.php?id_consumivel=<?= aes_encrypt($consumivel['id']) ?>" class="btn btn-sm btn-outline-danger">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="equipamentos.php" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-1"></i> Voltar
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </main>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>