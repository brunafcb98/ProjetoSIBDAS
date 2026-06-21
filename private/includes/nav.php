<?php 
// Verifica se a sessão ainda não foi iniciada 
if (session_status() == PHP_SESSION_NONE) { 
session_start(); // Inicia a sessão 
} 
// Verifica se o utilizador está autenticado 
if (!isset($_SESSION['utilizador'])) { 
// Se não estiver autenticado, redireciona para o formulário de login 
header('Location: ../public/login.php'); 
exit; // Encerra o script 
} 
// A partir daqui, o utilizador está autenticado 
// Podemos usar livremente os dados da sessão 
$nome = $_SESSION['utilizador']; 
?> 

<!-- Navbar --> 
<header class="container-fluid text-dark">
    <!-- Logo e nome da empresa -->
    <div class="row align-items-center" >
        <div class="col-6 d-flex align-items-center p-3">
            <a href="/sibdas/1241677/equipflow/private/dashboard.php">
                <img src="/sibdas/1241677/equipflow/assets/images/Logo1.png" alt="Logo da empresa" class="img-fluid me-3" style="max-height: 80px;">
            </a>
            <!--<h3 class="mb-0">EquipFlow | Clinical Systems</h3>-->
        </div>
        <div class="col-6 text-end p-3">
            <div class="dropdown"> 
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"> 
                    <i class="fa-regular fa-user me-2"></i> <?= htmlspecialchars($nome) ?> 
                </button> 
                <ul class="dropdown-menu dropdown-menu-end"> 
                    <?php if (($_SESSION['profile'] ?? '') === 'administrador'): ?>
                    <li><a class="dropdown-item" href="/sibdas/1241677/equipflow/private/editar_pagina_publica.php">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Editar Página Pública
                    </a></li>
                    <?php endif; ?>
    
                    <?php if (($_SESSION['profile'] ?? '') === 'administrador'): ?>
                    <li><a class="dropdown-item" href="/sibdas/1241677/equipflow/private/logs.php">
                        <i class="fa-solid fa-list-check me-2"></i>Registo de Eventos
                    </a></li>
                    <?php endif; ?>

                    <li> 
                        <hr class="dropdown-divider"> 
                    </li> 
                    <li><a class="dropdown-item" href="/sibdas/1241677/equipflow/public/logout.php"><i 
                                class="fa-solid fa-right-from-bracket me-2"></i>Sair</a></li> 
                </ul> 
            </div> 
        </div>
    </div>
</header>