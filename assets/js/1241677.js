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
    animarNumero("total", 148);       // Total de equipamentos
    animarNumero("ativos", 112);      // Equipamentos ativos
    animarNumero("manutencao", 18);   // Equipamentos em manutenção
    animarNumero("inativos", 9);      // Equipamentos inativos
    animarNumero("criticos", 24);     // Equipamentos de criticidade elevada
    animarNumero("suporte", 31);      // Equipamentos de suporte de vida
});
