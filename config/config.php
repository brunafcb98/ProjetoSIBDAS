<?php 

define('BASE_URL', '/sibdas/1241677/equipflow');

// Configurações da base de dados
define('MYSQL_HOST',     'vsgate-s1.dei.isep.ipp.pt');
define('MYSQL_PORT',     '10464');
define('MYSQL_DATABASE', 'db1241677');
define('MYSQL_USERNAME', '1241677');
define('MYSQL_PASSWORD', 'barbosa_677');
define('MYSQL_AES_KEY',  'equipflow_key_1241677');

// Configurações globais da aplicação 
 
define('APP_NAME', 'EquipFlow | Clinical Systems'); 
define('APP_VERSION', '1.0.0'); 
define('APP_COPYRIGHT', '© 2026 EquipFlow. Todos os direitos reservados.'); 


// Segurança – Encriptação com OpenSSL 

define('OPENSSL_METHOD', 'AES-256-CBC'); // Algoritmo simétrico robusto 
define('OPENSSL_KEY',    'H0SDRQzIGqclX2kbYBk9xspdn9U5f3Wa'); // Chave de 32 caracteres 
define('OPENSSL_IV',     'BzKAbjuREsHgnw56');                 // Vetor de inicialização (16 caracteres) 
