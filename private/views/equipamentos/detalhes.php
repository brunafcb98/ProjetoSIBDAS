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
                <div class="card w-100 shadow rounded" style="max-width: 900px;">
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

                        <div class="mb-3">
                            <label class="form-label fw-bold">Código Interno de Inventário</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['codigo_interno']) ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Designação do Equipamento</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['designacao']) ?></p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Categoria</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($categorias[$equipamento['categoria']]) ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marca</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['marca']) ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Modelo</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['modelo']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Número de Série</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['numero_serie']) ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Fabricante</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['fabricante']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Data de Aquisição</label>
                                <p class="form-control-plaintext"><?= date('d/m/Y', strtotime($equipamento['data_aquisicao'])) ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ano de Fabrico</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['ano_fabrico']) ?></p>
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

                        <div class="mb-4">
                            <label class="form-label fw-bold">Observações</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($equipamento['observacoes'] ?? '—') ?></p>
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