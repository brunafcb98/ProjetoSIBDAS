<?php 
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();

// Desencriptação e validação do ID fornecedor
$idFornecedorEncrypted = $_GET['id_fornecedor'] ?? null;
$idFornecedor = aes_decrypt($idFornecedorEncrypted);

if (!$idFornecedor || !is_numeric($idFornecedor)) {
    header('Location: ' . BASE_URL . '/private/views/fornecedores/fornecedores.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    $stmt = $ligacao->prepare("UPDATE fornecedores SET apagado = 1 WHERE id = :id");
    $stmt->bindParam(':id', $idFornecedor, PDO::PARAM_INT);
    $stmt->execute();

    //logs
    $stmtUser = $ligacao->prepare("SELECT id FROM utilizadores WHERE email = :email");
    $stmtUser->execute([':email' => $_SESSION['utilizador']]);
    $idUtilizador = $stmtUser->fetchColumn();

    $stmtLog = $ligacao->prepare("INSERT INTO logs (id_utilizador, tipo_evento, descricao) VALUES (:id_utilizador, 'fornecedor_desativado', :descricao)");
    $stmtLog->execute([
        ':id_utilizador' => $idUtilizador,
        ':descricao'     => 'Fornecedor desativado (id: ' . $idFornecedor . ')'
    ]);

    // Guarda mensagem de sucesso para o Toast aparecer na lista
    $_SESSION['toast_success'] = 'Fornecedor desativado com sucesso.';

    header('Location: ' . BASE_URL . '/private/views/fornecedores/fornecedores.php');
    exit;

} catch (PDOException $err) {
    // Guarda mensagem de erro para o Toast aparecer na lista
    $_SESSION['toast_error'] = 'Erro ao desativar o fornecedor.';
    header('Location: ' . BASE_URL . '/private/views/fornecedores/fornecedores.php');
    exit;
}
?>