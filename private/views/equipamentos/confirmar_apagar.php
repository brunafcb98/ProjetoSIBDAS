<?php 
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();

// Desencriptação e validação do ID equipamento
$idEquipamentoEncrypted = $_GET['id_equipamento'] ?? null;
$idEquipamento = aes_decrypt($idEquipamentoEncrypted);

if (!$idEquipamento || !is_numeric($idEquipamento)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("UPDATE equipamentos SET apagado = 1 WHERE id = :id");
    $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
    $stmt->execute();

    // Guarda mensagem de sucesso para o Toast aparecer na lista
    $_SESSION['toast_success'] = 'Equipamento desativado com sucesso.';

    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;

} catch (PDOException $err) {
    // Guarda mensagem de erro para o Toast aparecer na lista
    $_SESSION['toast_error'] = 'Erro ao desativar o equipamento.';
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}
?>