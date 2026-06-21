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

// Função para inicializar os gráficos da dashboard
function iniciarGraficos(categoriaLabels, categoriaData, localizacaoLabels, localizacaoData) {
    new Chart(document.getElementById('graficoCategoria'), {
        type: 'bar',
        data: {
            labels: categoriaLabels,
            datasets: [{
                label: 'Equipamentos',
                data: categoriaData,
                backgroundColor: '#0096a6'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    new Chart(document.getElementById('graficoLocalizacao'), {
        type: 'pie',
        data: {
            labels: localizacaoLabels,
            datasets: [{
                data: localizacaoData,
                backgroundColor: ['#0096a6', '#00b8c9', '#66d4dd', '#a3e4ea', '#ffb74d', '#ff8a65', '#9575cd', '#4db6ac']
            }]
        },
        options: {
            maintainAspectRatio: false,
            aspectRatio: 1.3,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 12, font: { size: 11 } }
                }
            }
        }
    });
}