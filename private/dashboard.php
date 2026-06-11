<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 col-lg-10 p-4">
            <section>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0"><strong><i class="fa-solid fa-house"></i> Dashboard</strong></h2>
                </div>
                <hr>
            </section>

            <!-- Linha 1 -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card home-card text-center p-3">
                        <h3><i class="fa-solid fa-stethoscope"></i> <span id="total">0</span></h3>
                        <p>Total de Equipamentos</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card home-card text-center p-3">
                        <h3><i class="fa-solid fa-circle-check"></i> <span id="ativos">0</span></h3>
                        <p>Ativos</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card home-card text-center p-3">
                        <h3><i class="fa-solid fa-wrench"></i> <span id="manutencao">0</span></h3>
                        <p>Em Manutenção</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card home-card text-center p-3">
                        <h3><i class="fa-solid fa-circle-xmark"></i> <span id="inativos">0</span></h3>
                        <p>Inativos</p>
                    </div>
                </div>
            </div>

            <!-- Linha 2 -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card home-card text-center p-3">
                        <h3><i class="fa-solid fa-triangle-exclamation"></i> <span id="criticos">0</span></h3>
                        <p>Criticidade Elevada</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card home-card text-center p-3">
                        <h3><i class="fa-solid fa-heart-pulse"></i> <span id="suporte">0</span></h3>
                        <p>Suporte de Vida</p>
                    </div>
                </div>
            </div>

        </main>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
