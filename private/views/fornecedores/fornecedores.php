<?php  
// -------------------------------------------------------------------- 
// SEGURANÇA: Proteção de acesso à página. 
// Este ficheiro deve ser acedido apenas por utilizadores autenticados. 
// Caso não exista sessão iniciada, o utilizador será redirecionado para o login.
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged(); // Inicia a sessão (se necessário) e verifica se o utilizador está autenticado 


include '../../includes/header.php'; 
include '../../includes/nav.php'; 

// LIGAÇÃO À BASE DE DADOS E EXECUÇÃO DA QUERY
try { 
    $ligacao = new PDO( 
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8", 
        MYSQL_USERNAME, 
        MYSQL_PASSWORD 
    ); 
 
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
 
    $resultados = $ligacao->query("SELECT * FROM fornecedores WHERE apagado = 0")->fetchAll(PDO::FETCH_OBJ); 
    $erro = ''; 
 
} catch (PDOException $err) { 
    $erro = "Aconteceu um erro na ligação."; 
    $resultados = []; 
} 
 
// Fecha a ligação 
$ligacao = null;
?>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar --> 
        <?php include '../../includes/sidebar.php'; ?>

        <!-- Conteúdo Principal --> 
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">
                    <i class="fa-solid fa-truck me-2"></i> 
                    <strong>Listagem de Fornecedores</strong>
                </h2>
                <a href="novo_f.php" class="btn btn-success">
                    <i class="fa-solid fa-plus me-1"></i> Novo fornecedor
                </a>
            </div>
            <hr>

            <?php if (!empty($erro)) : ?> 
                <p class="text-center text-danger"><?= $erro ?></p> 
            <?php else : ?> 
                <?php if (count($resultados) == 0) : ?> 
                    <p class="text-muted">Não existem fornecedores registados.</p> 
                <?php else : ?> 

                    <div class="table-responsive">
                        <table id="tabela-fornecedores" class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Contacto</th>
                                    <th>Email</th>
                                    <th>Pessoa de Contacto</th>
                                    <th>Telefone</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultados as $fornecedores) : ?>
                                    <tr>
                                        <td><?= $fornecedores->nome_empresa ?></td>
                                        <td>
                                            <?php
                                            $tipos = [
                                                'fabricante'   => 'Fabricante',
                                                'distribuidor' => 'Distribuidor / Fornecedor Comercial',
                                                'assistencia'  => 'Empresa de Assistência Técnica',
                                                'consumiveis'  => 'Fornecedor de Consumíveis / Acessórios'
                                            ];
                                            echo $tipos[$fornecedores->tipo] ?? $fornecedores->tipo;
                                            ?>
                                        </td>
                                        <td class="text-center"> 
                                            <?= $fornecedores->telefone ?> 
                                        </td> 
                                        <td><?= $fornecedores->email ?></td>
                                        <td><?= $fornecedores->pessoa_contacto ?></td>
                                        <td class="text-center"> 
                                            <?= $fornecedores->telefone_pessoa_contacto ?> 
                                        </td> 
                                        <td class="text-center">
                                            <a href="detalhes_f.php" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="editar_f.php?id_fornecedor=<?= aes_encrypt($fornecedores->id) ?>" class="btn btn-sm btn-outline-warning me-1">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                            <a href="apagar_f.php" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?> <!-- Fecha o if (count($resultados) == 0) --> 
            <?php endif; ?> <!-- Fecha o if (!empty($erro)) -->

            <div class="col"> 
            <p class="mb-5">Total: <strong> <?= count($resultados) ?> </strong></p> 
            </div> 
        
        </div>
    </div>
</div>

<script> 
    // tradução para português 
    $(document).ready(function() { 
        // datatable 
        $('#tabela-fornecedores').DataTable({ 
            pageLength: 5, 
            lengthMenu: [[5, 7, 10, 25, 40], [5, 7, 10, 25, 40]],
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

<?php include '../../includes/footer.php'; ?>
