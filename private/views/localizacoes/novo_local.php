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
                <div class="card w-100 shadow rounded" style="max-width: 1200px;">
                    <div class="card-body">
                        <h2 class="mb-4"><strong><i class="fa-solid fa-map-marker-alt me-2"></i> Inserir nova localização</strong></h2>
                        <hr>

                        <form action="#" method="post" novalidate>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_edificio" class="form-label">Edifício</label>
                                    <select class="form-select" name="edificio_localizacao" id="select_edificio" required>
                                        <option selected>Escolha uma opção</option>
                                        <option value="edificio_principal">Edifício Principal</option>
                                        <option value="edificio_a">Edifício A</option>
                                        <option value="edificio_b">Edifício B</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="select_piso" class="form-label">Piso</label>
                                    <select class="form-select" name="piso_localizacao" id="select_piso" required>
                                        <option selected>Escolha uma opção</option>
                                        <option value="piso_0">Piso 0</option>
                                        <option value="piso_1">Piso 1</option>
                                        <option value="piso_2">Piso 2</option>
                                        <option value="piso_3">Piso 3</option>
                                        <option value="piso_4">Piso 4</option>
                                        <option value="piso_5">Piso 5</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_servico" class="form-label">Serviço</label>
                                    <input type="text" class="form-control" name="servico_localizacao" id="texto_servico" list="servicos" required>
                                    <datalist id="servicos">
                                        <option value="Unidade de Cuidados Intensivos">
                                        <option value="Unidade de Cuidados Intermédios">
                                        <option value="Urgência">
                                        <option value="Serviço de Medicina">
                                        <option value="Serviço de Cirurgia">
                                        <option value="Pediatria">
                                        <option value="Bloco Operatório">
                                        <option value="Imagiologia">
                                        <option value="Gastroenterologia">
                                        <option value="Laboratório">
                                        <option value="Farmácia">
                                        <option value="Administração">
                                    </datalist>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_sala" class="form-label">Internamento / Sala / Gabinete</label>
                                    <input type="text" class="form-control" name="sala_localizacao" id="texto_sala">
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="d-flex justify-content-end gap-2 mb-4">
                                <a href="localizacoes.php" class="btn btn-outline-secondary">
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