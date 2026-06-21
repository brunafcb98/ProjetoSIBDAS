<?php 
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();

// Desencriptação e validação do ID consumível
$idConsumivelEncrypted = $_GET['id_consumivel'] ?? null;
$idConsumivel = aes_decrypt($idConsumivelEncrypted);

if (!$idConsumivel || !is_numeric($idConsumivel)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    // Vai buscar o id_equipamento_pai deste consumível, antes de apagar, para saber para onde redirecionar
    $stmtPai = $ligacao->prepare("SELECT id_equipamento_pai FROM consumiveis WHERE id = :id");
    $stmtPai->bindParam(':id', $idConsumivel, PDO::PARAM_INT);
    $stmtPai->execute();
    $idEquipamentoPai = $stmtPai->fetchColumn();

    if (!$idEquipamentoPai) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

    $stmt = $ligacao->prepare("UPDATE consumiveis SET apagado = 1 WHERE id = :id");
    $stmt->bindParam(':id', $idConsumivel, PDO::PARAM_INT);
    $stmt->execute();

    // Guarda mensagem de sucesso para o Toast aparecer
    $_SESSION['toast_success'] = 'Consumível desativado com sucesso.';

    header('Location: ' . BASE_URL . '/private/views/equipamentos/detalhes.php?id_equipamento=' . aes_encrypt($idEquipamentoPai));
    exit;

} catch (PDOException $err) {
    $_SESSION['toast_error'] = 'Erro ao desativar o consumível.';
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}
?>