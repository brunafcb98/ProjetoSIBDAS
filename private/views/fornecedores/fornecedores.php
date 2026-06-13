<?php  
// -------------------------------------------------------------------- 
// SEGURANÇA: Proteção de acesso à página. 
// Este ficheiro deve ser acedido apenas por utilizadores autenticados. 
// Caso não exista sessão iniciada, o utilizador será redirecionado para o login.
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged(); // Inicia a sessão (se necessário) e verifica se o utilizador está autenticado 
?> 

<?php include '../../includes/header.php'; ?> 
<?php include '../../includes/nav.php'; ?> 

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
            <p class="text-muted">Não existem fornecedores registados.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
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
                        <tr>
                            <td>[nome_empresa]</td>
                            <td>[tipo_fornecedor]</td>
                            <td>[contacto]</td>
                            <td>[email]</td>
                            <td>[pessoa_contacto]</td>
                            <td>[nº_pessoa_contacto]</td>
                            <td class="text-center">
                                <a href="detalhes_f.php" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="editar_f.php" class="btn btn-sm btn-outline-warning me-1">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <a href="apagar_f.php" class="btn btn-sm btn-outline-danger">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
