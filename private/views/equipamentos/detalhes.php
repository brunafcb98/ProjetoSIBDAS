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
                            <strong><i class="fa-solid fa-stethoscope me-2"></i> Detalhes do Equipamento</strong>
                        </h2>
                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Código Interno de Inventário</label>
                            <p class="form-control-plaintext">EQ-2022-001</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Designação do Equipamento</label>
                            <p class="form-control-plaintext">Monitor Multiparamétrico</p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Categoria</label>
                                <p class="form-control-plaintext">Monitorização</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marca</label>
                                <p class="form-control-plaintext">Philips</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Modelo</label>
                                <p class="form-control-plaintext">IntelliVue MP5</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Número de Série</label>
                                <p class="form-control-plaintext">MP5-2022-45873</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Fabricante</label>
                                <p class="form-control-plaintext">Philips</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Data de Aquisição</label>
                                <p class="form-control-plaintext">2022-03-15</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ano de Fabrico</label>
                                <p class="form-control-plaintext">2022</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Custo de Aquisição</label>
                                <p class="form-control-plaintext">12 500 €</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tipo de Entrada</label>
                                <p class="form-control-plaintext">Compra</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Estado</label>
                                <p class="form-control-plaintext">Ativo</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Criticidade</label>
                                <p class="form-control-plaintext">Suporte de Vida</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Localização</label>
                                <p class="form-control-plaintext">[Localização 1]</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Observações</label>
                            <p class="form-control-plaintext">Equipamento em bom estado de funcionamento.</p>
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