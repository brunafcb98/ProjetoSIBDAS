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
        <main class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-center mt-4">
                <div class="card w-100 shadow rounded" style="max-width: 900px;">
                    <div class="card-body">
                        <h2 class="mb-4">
                            <strong><i class="fa-solid fa-truck me-2"></i> Detalhes do Fornecedor</strong>
                        </h2>
                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome da Empresa</label>
                            <p class="form-control-plaintext">Philips Healthcare</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">NIF</label>
                            <p class="form-control-plaintext">500123456</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Morada</label>
                            <p class="form-control-plaintext">Rua da Saúde, nº25, Lisboa</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Telefone</label>
                            <p class="form-control-plaintext">213456789</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <p class="form-control-plaintext">geral@philips-healthcare.pt</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Website</label>
                            <p class="form-control-plaintext">www.philips.com/healthcare</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Fornecedor</label>
                            <p class="form-control-plaintext">Fabricante</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pessoa de Contacto</label>
                            <p class="form-control-plaintext">João Ferreira</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Telefone da Pessoa de Contacto</label>
                            <p class="form-control-plaintext">912345678</p>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Observações</label>
                            <p class="form-control-plaintext">Fornecedor principal de equipamentos de monitorização.</p>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="fornecedores.php" class="btn btn-outline-secondary">
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