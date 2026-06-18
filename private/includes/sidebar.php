<!-- Sidebar -->
        <aside class="col-md-3 col-lg-2 text-white p-3 min-vh-100">
            <h4>Menu</h4>
            <nav>
                <a href="/sibdas/1241677/equipflow/private/dashboard.php" class="nav-link text-white px-0 mb-2 d-block">
                    <i class="fas fa-home me-2"></i> Dashboard
                </a>

                <?php if ($_SESSION['profile'] === 'administrador') : ?>
                    <a href="/sibdas/1241677/equipflow/private/views/equipamentos/equipamentos.php" class="nav-link text-white px-0 mb-2 d-block">
                        <i class="fas fa-stethoscope me-2"></i> Equipamentos
                    </a>
                    <a href="/sibdas/1241677/equipflow/private/views/localizacoes/localizacoes.php" class="nav-link text-white px-0 mb-2 d-block">
                        <i class="fas fa-map-marker-alt me-2"></i> Localizações
                    </a>
                    <a href="/sibdas/1241677/equipflow/private/views/fornecedores/fornecedores.php" class="nav-link text-white px-0 mb-2 d-block">
                        <i class="fas fa-truck me-2"></i> Fornecedores
                    </a>
                <?php endif; ?>

                 <?php if ($_SESSION['profile'] === 'tecnico') : ?>
                    <a href="/sibdas/1241677/equipflow/private/views/equipamentos/equipamentos.php" class="nav-link text-white px-0 mb-2 d-block">
                        <i class="fas fa-stethoscope me-2"></i> Equipamentos
                    </a>
                    <a href="/sibdas/1241677/equipflow/private/views/localizacoes/localizacoes.php" class="nav-link text-white px-0 mb-2 d-block">
                        <i class="fas fa-map-marker-alt me-2"></i> Localizações
                    </a>
                    <a href="/sibdas/1241677/equipflow/private/views/fornecedores/fornecedores.php" class="nav-link text-white px-0 mb-2 d-block">
                        <i class="fas fa-truck me-2"></i> Fornecedores
                    </a>
                <?php endif; ?>

            </nav>
        </aside>