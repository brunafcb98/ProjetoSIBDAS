<?php
// --------------------------------------------------------------------
// COMPONENTE REUTILIZÁVEL: TOAST DE FEEDBACK (sucesso ou erro)
// --------------------------------------------------------------------
// Este ficheiro é incluído nas páginas de listagem (equipamentos.php,
// fornecedores.php, localizacoes.php) para mostrar uma notificação 
// temporária ao utilizador depois de uma operação (ex: apagar/desativar).
// 
// Funciona em conjunto com os ficheiros confirmar_apagar*.php, que 
// guardam a mensagem na sessão antes de redirecionar para a lista.
// --------------------------------------------------------------------

// Inicializa as variáveis que vão controlar se o Toast aparece ou não,
// e com que cor/ícone (sucesso = verde, erro = vermelho)
$toast_message = '';
$toast_type = '';

// Verifica se existe uma mensagem de SUCESSO guardada na sessão
// (definida, por exemplo, em confirmar_apagar.php depois do UPDATE correr bem)
if (!empty($_SESSION['toast_success'])) {
    $toast_message = $_SESSION['toast_success'];
    $toast_type = 'success';

    // Remove da sessão depois de ler, para a mensagem não voltar a 
    // aparecer se o utilizador recarregar a página (F5)
    unset($_SESSION['toast_success']);

// Caso não haja mensagem de sucesso, verifica se existe uma de ERRO
// (definida, por exemplo, no catch de uma PDOException)
} elseif (!empty($_SESSION['toast_error'])) {
    $toast_message = $_SESSION['toast_error'];
    $toast_type = 'error';
    unset($_SESSION['toast_error']);
}
?>

<?php if (!empty($toast_message)): ?>
<!-- 
    Container do Toast: fixa a notificação no topo, a meio da pagina (start 50) e ajusta posicionamento ao meio
    z-index alto garante que aparece por cima de todo o resto do conteúdo.
-->
<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1100;">
    
    <!-- 
        O Toast em si. A classe "toast" é o componente Bootstrap.
        A cor de fundo (bg-success / bg-danger) é escolhida dinamicamente 
        com base no tipo de mensagem (sucesso ou erro).
        role="alert" e aria-live ajudam leitores de ecrã a anunciar a mensagem.
    -->
    <div id="feedbackToast" 
         class="toast align-items-center text-white <?= $toast_type === 'success' ? 'bg-success' : 'bg-danger' ?> border-0" 
         role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <!-- Ícone diferente consoante o tipo: check (sucesso) ou alerta (erro) -->
                <i class="fa-solid <?= $toast_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> me-2"></i>
                <?= htmlspecialchars($toast_message) ?>
            </div>
            <!-- Botão para o utilizador fechar manualmente antes do tempo (delay) acabar -->
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    // Espera que toda a página HTML esteja carregada antes de tentar mostrar o Toast
    document.addEventListener('DOMContentLoaded', function () {
        // Vai buscar o elemento HTML do Toast pelo seu id
        var toastEl = document.getElementById('feedbackToast');

        // Cria uma instância do componente Toast do Bootstrap, associada a esse elemento.
        // "delay: 4000" define que desaparece automaticamente depois de 4000ms (4 segundos).
        var toast = new bootstrap.Toast(toastEl, { delay: 4000 });

        // Manda o Toast aparecer (por defeito, um Toast Bootstrap está escondido)
        toast.show();
    });
</script>
<?php endif; ?>