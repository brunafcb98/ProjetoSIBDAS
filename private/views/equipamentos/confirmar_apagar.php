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

    //Logs
    // Vai buscar o id do utilizador autenticado (a sessão só guarda o email)
    $stmtUser = $ligacao->prepare("SELECT id FROM utilizadores WHERE email = :email");
    $stmtUser->execute([':email' => $_SESSION['utilizador']]);
    $idUtilizador = $stmtUser->fetchColumn();

    // Regista o evento na tabela de logs
    $stmtLog = $ligacao->prepare("INSERT INTO logs (id_utilizador, tipo_evento, descricao) VALUES (:id_utilizador, 'equipamento_desativado', :descricao)");
    $stmtLog->execute([
        ':id_utilizador' => $idUtilizador,
        ':descricao'     => 'Equipamento desativado (id: ' . $idEquipamento . ')'
    ]);
    
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