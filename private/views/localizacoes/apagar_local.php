<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-center mt-4">
                <div class="card w-100 shadow rounded text-center p-4" style="max-width: 700px;">

                    <div class="text-warning display-4 mb-3">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <p class="mb-2 fs-5">Deseja eliminar a localização?</p>
                    <h4 class="mb-4"><strong>Serviço de Medicina</strong></h4>

                    <div class="mb-4">
                        <span class="d-block mb-1"><i class="fa-solid fa-building me-2"></i>Edifício: <strong>Edifício Principal</strong></span>
                        <span class="d-block mb-1"><i class="fa-solid fa-layer-group me-2"></i>Piso: <strong>Piso 3</strong></span>
                        <span class="d-block"><i class="fa-solid fa-door-open me-2"></i>Internamento / Sala / Gabinete: <strong>Internamento A</strong></span>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="localizacoes.php" class="btn btn-outline-secondary px-4">
                            <i class="fa-solid fa-xmark me-2"></i>Não
                        </a>
                        <a href="#" class="btn btn-danger px-4">
                            <i class="fa-solid fa-check me-2"></i>Sim
                        </a>
                    </div>

                </div>
            </div>
        </main>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>
