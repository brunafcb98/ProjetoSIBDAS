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
                        <h2 class="mb-4"><strong><i class="fa-solid fa-truck me-2"></i> Inserir novo fornecedor</strong></h2>
                        <hr>

                        <form action="#" method="post" novalidate>

                            <!-- Dados da empresa -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_nome" class="form-label">Nome da Empresa</label>
                                    <input type="text" class="form-control" name="nome_fornecedor" id="texto_nome" list="empresas" required>
                                    <datalist id="empresas">
                                        <option value="Philips Healthcare">
                                        <option value="Dräger">
                                        <option value="B. Braun">
                                        <option value="Siemens">
                                        <option value="Zoll Medical">
                                        <option value="Medtronic">
                                        <option value="Pentax Medical">
                                        <option value="Fresenius Medical Care">
                                    </datalist>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_nif" class="form-label">NIF</label>
                                    <input type="text" class="form-control" name="nif_fornecedor" id="texto_nif" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="select_tipo" class="form-label">Tipo de Fornecedor</label>
                                    <select class="form-select" name="tipo_fornecedor" id="select_tipo">
                                        <option selected>Escolha uma opção</option>
                                        <option value="fabricante">Fabricante</option>
                                        <option value="distribuidor">Distribuidor / Fornecedor Comercial</option>
                                        <option value="assistencia">Empresa de Assistência Técnica</option>
                                        <option value="consumiveis">Fornecedor de Consumíveis / Acessórios</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_morada" class="form-label">Morada <small>(Nº Porta, Andar)</small></label>
                                    <input type="text" class="form-control" name="morada_fornecedor" id="texto_morada">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_telefone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control" name="telefone_fornecedor" id="texto_telefone" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email_fornecedor" id="texto_email" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_website" class="form-label">Website</label>
                                    <input type="text" class="form-control" name="website_fornecedor" id="texto_website">
                                </div>
                            </div>

                            <!-- Pessoa de contacto -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_pessoa_contacto" class="form-label">Pessoa de Contacto</label>
                                    <input type="text" class="form-control" name="pessoa_contacto_fornecedor" id="texto_pessoa_contacto">
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_telefone_contacto" class="form-label">Telefone da Pessoa de Contacto</label>
                                    <input type="text" class="form-control" name="telefone_contacto_fornecedor" id="texto_telefone_contacto">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_fornecedor" id="texto_observacoes">
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="d-flex justify-content-end gap-2 mb-4">
                                <a href="fornecedores.php" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-regular fa-floppy-disk me-1"></i> Guardar
                                </button>
                            </div>

                            <!-- Mensagem de erro -->
                            <div class="alert alert-danger text-center" role="alert">
                                Erro simples
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>