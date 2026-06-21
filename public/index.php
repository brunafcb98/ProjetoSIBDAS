<?php
$conteudo = json_decode(file_get_contents(__DIR__ . '/../private/conteudo.json'), true);
?>
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
            <h2><?= htmlspecialchars($conteudo['home']['titulo']) ?></h2>
            <img src="../assets/images/Home.png" alt="Imagem de Equipamentos Clínicos">
            <p><?= htmlspecialchars($conteudo['home']['texto1']) ?></p>
            <p><?= htmlspecialchars($conteudo['home']['texto2']) ?></p>
            <a href="#contacto" class="button">Solicitar Demo</a>
        </div>
    </section>

    <!--Secção "Sobre Nós" -->
    <section class="sobre-nos-container" id="sobre-nos"> 
        <div class="sobre-nos-titulo"> 
            <h2><?= htmlspecialchars($conteudo['sobre_nos']['titulo']) ?></h2>
            <p><?= htmlspecialchars($conteudo['sobre_nos']['texto']) ?></p>
        </div> 

        <?php
        $imagens_sobre_nos = [
            'https://picsum.photos/id/1048/300',
            '../assets/images/missão.avif',
            '../assets/images/Valores.jpg'
        ];
        ?>
        <div class="sobre-nos-cards">
            <?php foreach ($conteudo['sobre_nos']['cards'] as $i => $card) : ?>
                <div class="sobre-card">
                    <div class="sobre-card-texto">
                        <h3><?= htmlspecialchars($card['titulo']) ?></h3>
                        <p><?= htmlspecialchars($card['texto']) ?></p>
                    </div>
                    <img src="<?= htmlspecialchars($imagens_sobre_nos[$i]) ?>" alt="<?= htmlspecialchars($card['titulo']) ?>"> 
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!--Secção "Serviços" -->
    <section id="servicos"> 
        <!--Barra colorida no topo-->
        <div class="servicos-barra">
            <h2><?= htmlspecialchars($conteudo['servicos']['titulo']) ?></h2>
        </div>
    
        <!--cards com serviços-->
        <?php
        $icones_servicos = ['fa-hospital', 'fa-file-medical', 'fa-chart-line'];
        ?>
        <div class="servicos-container"> 
            <?php foreach ($conteudo['servicos']['cards'] as $i => $card) : ?>
                <div class="servico"> 
                    <i class="fa-solid <?= $icones_servicos[$i] ?> fa-6x"></i> 
                    <h3><?= htmlspecialchars($card['titulo']) ?></h3> 
                    <p><?= htmlspecialchars($card['texto']) ?></p>
                </div> 
            <?php endforeach; ?>
        </div> 
    </section> 

    <!--Secção "Vantagens"-->
    <section id="vantagens">
        <div class="vantagens-container">
            <h2><?= htmlspecialchars($conteudo['vantagens']['titulo']) ?></h2>

            <!--Cards tambem dentro do container de vantagens-->
            <?php
            $icones_vantagens = ['fa-database', 'fa-chart-line', 'fa-triangle-exclamation', 'fa-shield-halved'];
            ?>
            <?php foreach ($conteudo['vantagens']['cards'] as $i => $card) : ?>
                <div class="vantagem">
                    <div class="vantagem-icone">   
                        <i class="fa-solid <?= $icones_vantagens[$i] ?> fa-4x"></i>
                    </div>
                    <h3><?= htmlspecialchars($card['titulo']) ?></h3>
                    <p><?= htmlspecialchars($card['texto']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
                
    <!--Secção "Funcionalidades" -->
    <section class="container-funcionalidades" id="funcionalidades">
        <h2><?= htmlspecialchars($conteudo['funcionalidades']['titulo']) ?></h2>    
        
        <?php foreach ($conteudo['funcionalidades']['artigos'] as $artigo) : ?>
            <article>
                <p><i class="fa-solid fa-circle-chevron-right fa-lg"></i> <strong><?= htmlspecialchars($artigo['titulo']) ?></strong></p>
                <p><?= htmlspecialchars($artigo['texto']) ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <!--Secção "Contacto"-->
    <section id="contacto"> 
        <h2><?= htmlspecialchars($conteudo['contacto']['titulo']) ?></h2> 
        <p><?= htmlspecialchars($conteudo['contacto']['texto']) ?></p> 
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
            <p><?= $conteudo['rodape']['localizacao'] ?></p> 
        </div> 
        <div class="footer-section"> 
            <strong>HORÁRIO</strong> 
            <p><?= htmlspecialchars($conteudo['rodape']['horario_semana']) ?></p> 
            <p><?= htmlspecialchars($conteudo['rodape']['horario_sabado']) ?></p> 
            <p><?= htmlspecialchars($conteudo['rodape']['horario_domingo']) ?></p> 
        </div> 
        <div class="footer-section">
            <strong>CONTACTOS</strong> 
            <p>Email: <?= htmlspecialchars($conteudo['rodape']['email']) ?></p> 
            <p>Telefone: <?= htmlspecialchars($conteudo['rodape']['telefone']) ?></p> 
        </div> 
        <div class="footer-bottom">
            <p>&copy; 2026 EquipFlow | Clinical Systems. Todos os direitos reservados.</p>
        </div>
        
    </footer>

</body>
</html>