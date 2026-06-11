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
                    <i class="fa-solid fa-stethoscope me-2"></i>
                    <strong>Listagem de Equipamentos</strong>
                </h2>
                <a href="novo.php" class="btn btn-success">
                    <i class="fa-solid fa-plus me-1"></i> Novo equipamento
                </a>
            </div>
            <hr>
            <p class="text-muted">Não existem equipamentos registados.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Designação</th>
                            <th>Categoria</th>
                            <th>Marca</th>
                            <th>Estado</th>
                            <th>Criticidade</th>
                            <th>Localização</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>[codigo_interno]</td>
                            <td>[designação]</td>
                            <td>[categoria]</td>
                            <td>[marca]</td>
                            <td>[estado]</td>
                            <td>[criticidade]</td>
                            <td>[localização]</td>
                            <td class="text-center">
                                <a href="detalhes.php" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="editar.php" class="btn btn-sm btn-outline-warning me-1">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <a href="apagar.php" class="btn btn-sm btn-outline-danger">
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