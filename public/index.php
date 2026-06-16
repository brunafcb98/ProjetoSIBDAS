<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquipFlow | Clinical Systems</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/images/icone1.png" type="image/png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!--Font Awesome (local)-->
    <link rel="stylesheet" href="../assets/fontawesome/all.min.css">

    <!-- CSS - estilos da pagina -->
    <link rel="stylesheet" href="../assets/css/1241677.css">
</head>
<body>
    <!-- Navegação --> 
    <nav class="bng-navbar">
        <!-- Logo e nome da empresa -->
        <div>
            <a href="#home">
                <img src="../assets/images/Logo1.png" alt="Logo da empresa">
            </a>
            <!--<h3>EquipFlow | Clinical Systems</h3>-->
        </div>

        <!--Menus de navegação-->
        <div class="container-navegacao">
            <a href="#home">Home</a>
            <a href="#sobre-nos">Sobre Nós</a>
            <a href="#servicos">Serviços</a>
            <a href="#vantagens">Vantagens</a>
            <a href="#funcionalidades">Funcionalidades</a>
            <a href="#contacto">Contacto</a>
        </div>

        <!-- Área de cliente -->
        <div class="nav-cliente">
            <a href="login.php">Área de Cliente</a>
        </div>
    </nav>

    <!-- Seção "Conteudo da pagina" --> 
    <!--Secção "Home"-->
    <section class="container-texto-generico" id="home">
        <div class="home-content">
            <h2>Gestão de Equipamentos Clínicos</h2>
            <img src="../assets/images/Home.png" alt="Imagem de Equipamentos Clínicos">
            <p>Na EquipFlow, transformamos a gestão de equipamentos clínicos. Com a nossa plataforma inovadora, oferecemos soluções inteligentes para otimizar o fluxo de trabalho, melhorar a eficiência e garantir a segurança dos pacientes.</p>
            <p>Junte-se a nós nesta jornada rumo a um futuro mais conectado e eficiente na área da saúde.</p>
            <a href="#contacto" class="button">Solicitar Demo</a>
        </div>
    </section>

    <!--Secção "Sobre Nós" -->
    <section class="sobre-nos-container" id="sobre-nos"> 
        <div class="sobre-nos-titulo"> 
            <h2>Sobre Nós</h2>
            <p>A EquipFlow é uma empresa dedicada à gestão, manutenção e monitorização de equipamentos hospitalares. Fundada por uma equipa de profissionais especializados na área da saúde e tecnologia, trabalhamos lado a lado com instituições de saúde para garantir a fiabilidade dos equipamentos, a segurança dos profissionais e a continuidade dos cuidados ao cliente.</p>
        </div> 

        <div class="sobre-nos-cards">
            <div class="sobre-card">
                <div class="sobre-card-texto">
                    <h3>Visão</h3>
                    <p>Ser referência nacional na gestão de equipamentos hospitalares, contribuindo para um sistema de saúde mais eficiente e seguro.</p>
                </div>
                <img src="https://picsum.photos/id/1048/300" alt="Visão"> 
            </div>

            <div class="sobre-card">
                <div class="sobre-card-texto">
                    <h3>Missão</h3>
                    <p>Garantir o funcionamento contínuo e seguro dos equipamentos médicos, através de uma gestão rigorosa e de um serviço técnico de excelência.</p>
                </div>
                <img src="../assets/images/missão.avif" alt="Missão"> 
            </div>

            <div class="sobre-card">
                <div class="sobre-card-texto">
                    <h3>Valores</h3>
                    <p>Rigor, responsabilidade, inovação e compromisso com a saúde das pessoas.</p>
                </div>
                <img src="../assets/images/Valores.jpg" alt="Valores">
            </div>
        </div>
    </section>

    <!--Secção "Serviços" -->
    <section id="servicos"> 
        <!--Barra colorida no topo-->
        <div class="servicos-barra">
            <h2>Serviços</h2>
        </div>
    
        <!--cards com serviços-->
        <div class="servicos-container"> 
            <div class="servico"> 
                <i class="fa-solid fa-hospital fa-6x"></i> 
                <h3>Gestão de Equipamentos Hospitalares</h3> 
                <p>Registe, consulte e atualize toda a informação relativa aos equipamentos médicos da instituição, garantindo rastreabilidade e controlo total do parque tecnológico.</p>
            </div> 
            <div class="servico"> 
                <i class="fa-solid fa-file-medical fa-6x"></i> 
                <h3>Gestão Documental e Fornecedores</h3> 
                <p>Centralize toda a documentação técnica e administrativa associada aos seus equipamentos, fornecedores e fabricantes, num único repositório de fácil acesso.</p> 
            </div> 
            <div class="servico"> 
                <i class="fa-solid fa-chart-line fa-6x"></i> 
                <h3>Monitorização e Controlo</h3> 
                <p>Acompanhe o estado global do parque tecnológico da instituição através de dashboards, alertas e relatórios detalhados.</p> 
            </div> 
        </div> 
    </section> 

    <!--Secção "Vantagens"-->
    <section id="vantagens">
        <div class="vantagens-container">
            <h2>Vantagens da Gestão de Equipamentos Clínicos</h2>

            <!--Cards tambem dentro do container de vantagens-->
            <div class="vantagem">
                <div class="vantagem-icone">   
                    <i class="fa-solid fa-database fa-4x"></i>
                </div>
                <h3>Informação Centralizada</h3>
                <p>Reúna todo o histórico, localização e estado operacional dos equipamentos da instituição num único repositório de informação, eliminando a necessidade de procura em múltiplas fontes.</p>
            </div>

            <div class="vantagem">
                <div class="vantagem-icone">
                    <i class="fa-solid fa-chart-line fa-4x"></i>
                </div>
                <h3>Decisões Estratégicas</h3>
                <p>Acompanhe indicadores e relatórios do parque tecnológico em tempo real, permitindo o planeamento de investimentos e renovações com dados exatos.</p>
            </div>

            <div class="vantagem">
                <div class="vantagem-icone">
                    <i class="fa-solid fa-triangle-exclamation fa-4x"></i>
                </div>
                <h3>Prioridade Clínica</h3>
                <p>Identifique instantaneamente os equipamentos de suporte de vida ou de alto risco através de alertas e destaques visuais, garantindo uma intervenção técnica prioritária.</p>
            </div>

            <div class="vantagem">
                <div class="vantagem-icone">
                    <i class="fa-solid fa-shield-halved fa-4x"></i>
                </div>
                <h3>Conformidade e Segurança</h3>
                <p>Garanta o cumprimento das normas e regulamentações aplicáveis, minimizando riscos operacionais e assegurando o acesso imediato a alertas e documentação técnica.</p>
            </div>
        </div>
    </section>
                
    <!--Secção "Funcionalidades" -->
    <section class="container-funcionalidades" id="funcionalidades">
        <h2>O Que Faz o Nosso Sistema</h2>    
        
        <article>
            <p><i class="fa-solid fa-circle-chevron-right fa-lg"></i> <strong>Inventário Dinâmico</strong></p>
            <p>Registe e catalogue todo o parque tecnológico biomédico. Pesquise por marca ou modelo e aceda ao histórico e estado operacional de cada dispositivo médico.</p>
        </article>

        <article>
            <p><i class="fa-solid fa-circle-chevron-right fa-lg"></i> <strong>Alertas de Criticidade</strong></p>
            <p>Identifique de forma imediata os dispositivos de suporte de vida ou de alto risco através de destaques visuais automáticos, garantindo uma intervenção técnica prioritária.</p>
        </article>

        <article>
            <p><i class="fa-solid fa-circle-chevron-right fa-lg"></i> <strong>Controlo de Empréstimos</strong></p>
            <p>Registe e acompanhe o fluxo e a transferência de dispositivos médicos entre serviços do hospital, evitando perdas, duplicações e garantindo a rastreabilidade de cada equipamento.</p>
        </article>

        <article>
            <p><i class="fa-solid fa-circle-chevron-right fa-lg"></i> <strong>Gestão Documental</strong></p>
            <p>Associe manuais técnicos, contratos de fornecedores e certificados de calibração à ficha de cada equipamento, identificando automaticamente documentos em falta.</p>
        </article>

        <article>
            <p><i class="fa-solid fa-circle-chevron-right fa-lg"></i> <strong>Gestão de Localizações</strong></p>
            <p>Mapeie a distribuição dos equipamentos pelos diferentes serviços e localizações, permitindo à equipa de engenharia clínica localizar e intervir em qualquer equipamento de forma rápida e eficaz.</p>
        </article>

        <article> 
            <p><i class="fa-solid fa-circle-chevron-right fa-lg"></i> <strong>Painéis Estatísticos</strong></p>
            <p>Aceda a uma área com gráficos dinâmicos sobre a distribuição dos equipamentos por categoria, estado e criticidade, apoiando o planeamento e a tomada de decisão do hospital.</p>
        </article>
    </section>

    <!--Secção "Contacto"-->
    <section id="contacto"> 
        <h2>Contacto</h2> 
        <p>Entre em contacto connosco para solicitar uma demonstração ou esclarecer qualquer questão.</p> 
        <form id="contactForm"> 
            <label for="nome">Nome:</label> 
            <input type="text" id="nome" name="nome" required> 

            <label for="email">Email:</label> 
            <input type="email" id="email" name="email" required> 

            <label for="instituicao">Instituição:</label>
            <input type="text" id="instituicao" name="instituicao" required>

            <label for="mensagem">Mensagem:</label> 
            <textarea id="mensagem" name="mensagem" rows="4" required></textarea> 

            <button type="submit">Enviar Pedido</button> 
        </form> 
    </section> 


    <!-- Rodapé --> 
    <footer class="footer-container">
        <div class="footer-section"> 
            <strong>LOCALIZAÇÃO</strong> 
            <p> Avenida do Sol <br> 4150-301, Porto <br> Portugal</p> 
        </div> 
        <div class="footer-section"> 
            <strong>HORÁRIO</strong> 
            <p>2ª a 6ª Feira: 9h — 18h</p> 
            <p>Sábado: 9h — 13h</p> 
            <p>Domingo e Feriados: Encerrado</p> 
        </div> 
        <div class="footer-section">
            <strong>CONTACTOS</strong> 
            <p>Email: geral@EquipFlow.pt</p> 
            <p>Telefone: +351 220 999 000</p> 
        </div> 
        <div class="footer-bottom">
            <p>&copy; 2026 EquipFlow | Clinical Systems. Todos os direitos reservados.</p>
        </div>
        
    </footer>

</body>
</html>