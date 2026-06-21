<?php
require_once 'includes/funcoes.php';
redirect_if_not_logged();
start_session();

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

$erro_sistema = '';

try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ---------- INDICADORES MÍNIMOS ----------
    $total_equipamentos = $ligacao->query("SELECT COUNT(*) FROM equipamentos WHERE apagado = 0")->fetchColumn();
    $equipamentos_ativos = $ligacao->query("SELECT COUNT(*) FROM equipamentos WHERE apagado = 0 AND estado = 'ativo'")->fetchColumn();
    $equipamentos_manutencao = $ligacao->query("SELECT COUNT(*) FROM equipamentos WHERE apagado = 0 AND estado = 'manutencao'")->fetchColumn();
    $equipamentos_inativos = $ligacao->query("SELECT COUNT(*) FROM equipamentos WHERE apagado = 0 AND estado = 'inativo'")->fetchColumn();

    // ---------- INDICADORES ADICIONAIS ----------
    $equipamentos_criticos = $ligacao->query("SELECT COUNT(*) FROM equipamentos WHERE apagado = 0 AND criticidade = 'alta'")->fetchColumn();
    $equipamentos_suporte = $ligacao->query("SELECT COUNT(*) FROM equipamentos WHERE apagado = 0 AND criticidade = 'suporte_vida'")->fetchColumn();

    // ---------- GARANTIA A EXPIRAR (30 dias) ----------
    $garantia_a_expirar_lista = $ligacao->query("
        SELECT e.codigo_interno, e.designacao, g.data_fim_garantia
        FROM garantias_contratos g
        INNER JOIN equipamentos e ON e.id = g.id_equipamento
        WHERE e.apagado = 0
          AND g.apagado = 0
          AND g.data_fim_garantia IS NOT NULL
          AND g.data_fim_garantia BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY g.data_fim_garantia ASC
    ")->fetchAll(PDO::FETCH_OBJ);
    $garantia_a_expirar = count($garantia_a_expirar_lista);

    // ---------- GARANTIA EXPIRADA ----------
    $garantia_expirada_lista = $ligacao->query("
        SELECT e.codigo_interno, e.designacao, g.data_fim_garantia
        FROM garantias_contratos g
        INNER JOIN equipamentos e ON e.id = g.id_equipamento
        WHERE e.apagado = 0
          AND g.apagado = 0
          AND g.data_fim_garantia IS NOT NULL
          AND g.data_fim_garantia < CURDATE()
        ORDER BY g.data_fim_garantia DESC
    ")->fetchAll(PDO::FETCH_OBJ);
    $garantia_expirada = count($garantia_expirada_lista);

    // ---------- SEM DOCUMENTAÇÃO (subconsulta correlacionada NOT EXISTS) ----------
    $sem_documentacao_lista = $ligacao->query("
        SELECT e.codigo_interno, e.designacao
        FROM equipamentos e
        WHERE e.apagado = 0
          AND NOT EXISTS (
              SELECT 1 FROM documentos d WHERE d.id_equipamento = e.id AND d.apagado = 0
          )
        ORDER BY e.codigo_interno ASC
    ")->fetchAll(PDO::FETCH_OBJ);
    $sem_documentacao = count($sem_documentacao_lista);

    // ---------- COM DOCUMENTAÇÃO ----------
    $com_documentacao_lista = $ligacao->query("
        SELECT DISTINCT e.codigo_interno, e.designacao
        FROM equipamentos e
        INNER JOIN documentos d ON d.id_equipamento = e.id
        WHERE e.apagado = 0
          AND d.apagado = 0
        ORDER BY e.codigo_interno ASC
    ")->fetchAll(PDO::FETCH_OBJ);
    $com_documentacao = count($com_documentacao_lista);

    // ---------- DADOS PARA OS GRÁFICOS ----------
    $por_categoria = $ligacao->query("
        SELECT categoria, COUNT(*) AS total
        FROM equipamentos
        WHERE apagado = 0
        GROUP BY categoria
        ORDER BY total DESC
    ")->fetchAll();

    $por_localizacao = $ligacao->query("
        SELECT l.servico AS localizacao, COUNT(e.id) AS total
        FROM equipamentos e
        INNER JOIN localizacoes l ON l.id = e.id_localizacao
        WHERE e.apagado = 0
          AND l.apagado = 0
        GROUP BY l.servico
        ORDER BY total DESC
    ")->fetchAll();

} catch (PDOException $err) {
    $erro_sistema = "Ocorreu um erro na ligação à base de dados. Por favor, tente novamente mais tarde.";
    $total_equipamentos = $equipamentos_ativos = $equipamentos_manutencao = $equipamentos_inativos = 0;
    $equipamentos_criticos = $equipamentos_suporte = 0;
    $garantia_a_expirar = $garantia_expirada = $sem_documentacao = $com_documentacao = 0;
    $garantia_a_expirar_lista = $garantia_expirada_lista = $sem_documentacao_lista = $com_documentacao_lista = [];
    $por_categoria = $por_localizacao = [];
}

$ligacao = null;
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<?php if (!empty($success_message)) : ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div id="toastSuccess" class="toast align-items-center text-bg-success border-0 show" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($success_message) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 col-lg-10 p-4">
            <section>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0"><strong><i class="fa-solid fa-house"></i> Dashboard</strong></h2>
                </div>
                <hr>
            </section>

            <?php if (!empty($erro_sistema)) : ?>

                <p class="text-center text-danger"><?= $erro_sistema ?></p>

            <?php else : ?>

                <!-- Linha 1 -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card home-card text-center p-3">
                            <h3><i class="fa-solid fa-stethoscope"></i> <span id="total">0</span></h3>
                            <p>Total de Equipamentos</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card home-card text-center p-3">
                            <h3><i class="fa-solid fa-wrench"></i> <span id="manutencao">0</span></h3>
                            <p>Em Manutenção</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card home-card text-center p-3">
                            <h3><i class="fa-solid fa-circle-xmark"></i> <span id="inativos">0</span></h3>
                            <p>Inativos</p>
                        </div>
                    </div>
                </div>

                <!-- Linha 2 -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card home-card text-center p-3">
                            <h3><i class="fa-solid fa-circle-check"></i> <span id="ativos">0</span></h3>
                            <p>Ativos</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card home-card text-center p-3">
                            <h3><i class="fa-solid fa-triangle-exclamation"></i> <span id="criticos">0</span></h3>
                            <p>Criticidade Elevada</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card home-card text-center p-3">
                            <h3><i class="fa-solid fa-heart-pulse"></i> <span id="suporte">0</span></h3>
                            <p>Suporte de Vida</p>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-md-5">
                        <div class="card p-3">
                            <h6 class="text-center fw-bold text-uppercase mb-3" style="color:#0096a6; letter-spacing: 0.5px;">
                                Equipamentos por Categoria
                            </h6>
                            <div style="height: 240px;">
                                <canvas id="graficoCategoria"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card p-3">
                            <h6 class="text-center fw-bold text-uppercase mb-3" style="color:#0096a6; letter-spacing: 0.5px;">
                                Equipamentos por Localização
                            </h6>
                            <div style="height: 280px;">
                                <canvas id="graficoLocalizacao"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabelas -->
                <div class="row mb-4">

                    <!-- Garantias -->
                    <div class="col-md-6">
                        <div class="card p-3 h-100">
                            <h6 class="fw-bold text-uppercase mb-3" style="color:#0096a6; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-file-circle-exclamation"></i> Garantias a Acompanhar
                            </h6>
                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Designação</th>
                                            <th>Fim Garantia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-warning">
                                            <td colspan="3"><strong>A Expirar (30 dias): <span id="garantiaAExpirar">0</span></strong></td>
                                        </tr>
                                        <?php if (empty($garantia_a_expirar_lista)) : ?>
                                            <tr><td colspan="3" class="text-muted text-center">Nenhum equipamento nesta situação.</td></tr>
                                        <?php else : ?>
                                            <?php foreach ($garantia_a_expirar_lista as $item) : ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item->codigo_interno) ?></td>
                                                    <td><?= htmlspecialchars($item->designacao) ?></td>
                                                    <td><?= date('d-m-Y', strtotime($item->data_fim_garantia)) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <tr class="table-danger">
                                            <td colspan="3"><strong>Garantia Expirada: <span id="garantiaExpirada">0</span></strong></td>
                                        </tr>
                                        <?php if (empty($garantia_expirada_lista)) : ?>
                                            <tr><td colspan="3" class="text-muted text-center">Nenhum equipamento nesta situação.</td></tr>
                                        <?php else : ?>
                                            <?php foreach ($garantia_expirada_lista as $item) : ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item->codigo_interno) ?></td>
                                                    <td><?= htmlspecialchars($item->designacao) ?></td>
                                                    <td><?= date('d-m-Y', strtotime($item->data_fim_garantia)) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Documentação -->
                    <div class="col-md-6">
                        <div class="card p-3 h-100">
                            <h6 class="fw-bold text-uppercase mb-3" style="color:#0096a6; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-file-circle-question"></i> Documentação dos Equipamentos
                            </h6>
                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Designação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-secondary">
                                            <td colspan="2"><strong>Sem Documentação: <span id="semDocumentacao">0</span></strong></td>
                                        </tr>
                                        <?php if (empty($sem_documentacao_lista)) : ?>
                                            <tr><td colspan="2" class="text-muted text-center">Todos têm documentação.</td></tr>
                                        <?php else : ?>
                                            <?php foreach ($sem_documentacao_lista as $item) : ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item->codigo_interno) ?></td>
                                                    <td><?= htmlspecialchars($item->designacao) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <tr class="table-success">
                                            <td colspan="2"><strong>Com Documentação: <span id="comDocumentacao">0</span></strong></td>
                                        </tr>
                                        <?php if (empty($com_documentacao_lista)) : ?>
                                            <tr><td colspan="2" class="text-muted text-center">Nenhum equipamento com documentação.</td></tr>
                                        <?php else : ?>
                                            <?php foreach ($com_documentacao_lista as $item) : ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item->codigo_interno) ?></td>
                                                    <td><?= htmlspecialchars($item->designacao) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            <?php endif; ?>

        </main>

    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/1241677.js"></script>

<?php if (empty($erro_sistema)) : ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    animarNumero("total", <?= $total_equipamentos ?>);
    animarNumero("ativos", <?= $equipamentos_ativos ?>);
    animarNumero("manutencao", <?= $equipamentos_manutencao ?>);
    animarNumero("inativos", <?= $equipamentos_inativos ?>);
    animarNumero("criticos", <?= $equipamentos_criticos ?>);
    animarNumero("suporte", <?= $equipamentos_suporte ?>);
    animarNumero("garantiaExpirada", <?= $garantia_expirada ?>);
    animarNumero("garantiaAExpirar", <?= $garantia_a_expirar ?>);
    animarNumero("semDocumentacao", <?= $sem_documentacao ?>);
    animarNumero("comDocumentacao", <?= $com_documentacao ?>);

    iniciarGraficos(
        <?= json_encode(array_column($por_categoria, 'categoria')) ?>,
        <?= json_encode(array_column($por_categoria, 'total')) ?>,
        <?= json_encode(array_column($por_localizacao, 'localizacao')) ?>,
        <?= json_encode(array_column($por_localizacao, 'total')) ?>
    );
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
