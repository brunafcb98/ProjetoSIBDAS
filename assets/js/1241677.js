// Função para animar os números do dashboard
function animarNumero(id, valorFinal) {
    let valorAtual = 0; // Começa em 0
    const incremento = Math.ceil(valorFinal / 50); // Calcula o incremento

    // Atualiza o número a cada 30 milissegundos
    const intervalo = setInterval(function() {
        valorAtual += incremento; // Incrementa o valor atual
        if (valorAtual >= valorFinal) { // Verifica se chegou ao valor final
            valorAtual = valorFinal; // Garante que não ultrapassa o valor final
            clearInterval(intervalo); // Para o intervalo
        }
        document.getElementById(id).textContent = valorAtual; // Atualiza o número no ecrã
    }, 30);
}

// Quando a página termina de carregar, inicia as animações
document.addEventListener("DOMContentLoaded", function() {
    if (document.getElementById("total")) animarNumero("total", 148);
    if (document.getElementById("ativos")) animarNumero("ativos", 112);
    if (document.getElementById("manutencao")) animarNumero("manutencao", 18);
    if (document.getElementById("inativos")) animarNumero("inativos", 9);
    if (document.getElementById("criticos")) animarNumero("criticos", 24);
    if (document.getElementById("suporte")) animarNumero("suporte", 31);
});
