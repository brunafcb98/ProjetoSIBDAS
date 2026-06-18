<?php 
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();

// Desencriptação e validação do ID localização
$idLocalizacaoEncrypted = $_GET['id_localizacao'] ?? null;
$idLocalizacao = aes_decrypt($idLocalizacaoEncrypted);

if (!$idLocalizacao || !is_numeric($idLocalizacao)) {
    header('Location: ' . BASE_URL . '/private/views/localizacoes/localizacoes.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("UPDATE localizacoes SET apagado = 1 WHERE id = :id");
    $stmt->bindParam(':id', $idLocalizacao, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['toast_success'] = 'Localização desativada com sucesso.';

    header('Location: ' . BASE_URL . '/private/views/localizacoes/localizacoes.php');
    exit;

} catch (PDOException $err) {
    $_SESSION['toast_error'] = 'Erro ao desativar a localização.';
    header('Location: ' . BASE_URL . '/private/views/localizacoes/localizacoes.php');
    exit;
}
?>