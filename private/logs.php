<?php  
require_once __DIR__ . '/includes/funcoes.php'; 
redirect_if_not_logged();

// Apenas administradores podem aceder a esta página, mesmo escrevendo o URL diretamente
if (($_SESSION['profile'] ?? '') !== 'administrador') {
    header('Location: ' . BASE_URL . '/private/dashboard.php');
    exit;
}

include 'includes/header.php'; 
include 'includes/nav.php'; 

// LIGAÇÃO À BASE DE DADOS E EXECUÇÃO DA QUERY
try { 
    $ligacao = new PDO( 
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8", 
        MYSQL_USERNAME, 
        MYSQL_PASSWORD 
    ); 
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

    $resultados = $ligacao->query("
        SELECT l.*, u.email 
        FROM logs l
        LEFT JOIN utilizadores u ON l.id_utilizador = u.id
        ORDER BY l.data_hora DESC
    ")->fetchAll(PDO::FETCH_OBJ); 
    $erro = ''; 
 
} catch (PDOException $err) { 
    $erro = "Aconteceu um erro na ligação."; 
    $resultados = []; 
} 
 
$ligacao = null;
?>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">
                    <i class="fa-solid fa-list-check me-2"></i>
                    <strong>Registo de Eventos</strong>
                </h2>
            </div>
            <hr>

            <?php if (!empty($erro)) : ?> 
                <p class="text-center text-danger"><?= $erro ?></p> 
            <?php else : ?> 
                <?php if (count($resultados) == 0) : ?> 
                    <p class="text-muted">Não existem eventos registados.</p> 
                <?php else : ?> 

                    <div class="table-responsive">
                        <table id="tabela-logs" class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Tipo de Evento</th>
                                    <th>Utilizador</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultados as $log) : ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i:s', strtotime($log->data_hora)) ?></td>
                                        <td>
                                            <?php
                                            $tiposEvento = [
                                                'login_sucesso'              => 'Login com sucesso',
                                                'login_falhado'               => 'Login falhado',
                                                'erro_bd'                     => 'Erro de base de dados',
                                                'equipamento_criado'          => 'Equipamento criado',
                                                'equipamento_editado'         => 'Equipamento editado',
                                                'equipamento_desativado'      => 'Equipamento desativado',
                                                'fornecedor_criado'           => 'Fornecedor criado',
                                                'fornecedor_editado'          => 'Fornecedor editado',
                                                'fornecedor_desativado'       => 'Fornecedor desativado',
                                                'localizacao_criada'          => 'Localização criada',
                                                'localizacao_editada'         => 'Localização editada',
                                                'localizacao_desativada'      => 'Localização desativada'
                                            ];
                                            echo htmlspecialchars($tiposEvento[$log->tipo_evento] ?? $log->tipo_evento);
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($log->email ?? '—') ?></td>
                                        <td><?= htmlspecialchars($log->descricao ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>

    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabela-logs').DataTable({
            pageLength: 10,
            order: [[0, 'desc']],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pagingType: "full_numbers",
            language: {
                decimal: "",
                emptyTable: "Sem dados disponíveis na tabela.",
                info: "Mostrando _START_ até _END_ de _TOTAL_ registos",
                infoEmpty: "Mostrando 0 até 0 de 0 registos",
                infoFiltered: "(Filtrando _MAX_ total de registos)",
                infoPostFix: "",
                thousands: ",",
                lengthMenu: "Mostrando _MENU_ registos por página.",
                loadingRecords: "Carregando...",
                processing: "Processando...",
                search: "Filtrar:",
                zeroRecords: "Nenhum registro encontrado.",
                paginate: {
                    first: "Primeira",
                    last: "Última",
                    next: "Seguinte",
                    previous: "Anterior"
                },
                aria: {
                    sortAscending: ": ative para classificar a coluna em ordem crescente.",
                    sortDescending: ": ative para classificar a coluna em ordem decrescente."
                }
            }
        });
    })
</script>

<?php include 'includes/footer.php'; ?>
