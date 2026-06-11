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
                    <i class="fa-solid fa-map-marker-alt me-2"></i>
                    <strong>Listagem de Localizações</strong>
                </h2>
                <a href="novo_local.php" class="btn btn-success">
                    <i class="fa-solid fa-plus me-1"></i> Nova localização
                </a>
            </div>
            <hr>
            <p class="text-muted">Não existem localizações registadas.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Edifício</th>
                            <th>Piso</th>
                            <th>Serviço</th>
                            <th>Internamento / Sala / Gabinete</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>[edificio]</td>
                            <td>[piso]</td>
                            <td>[servico]</td>
                            <td>[sala]</td>
                            <td class="text-center">
                                <a href="editar_local.php" class="btn btn-sm btn-outline-warning me-1">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <a href="apagar_local.php" class="btn btn-sm btn-outline-danger">
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