<?php 
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();

// Desencriptação e validação do ID documento
$idDocumentoEncrypted = $_GET['id_documento'] ?? null;
$idDocumento = aes_decrypt($idDocumentoEncrypted);

if (!$idDocumento || !is_numeric($idDocumento)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    // Vai buscar o id_equipamento deste documento, antes de apagar, para saber para onde redirecionar
    $stmtEquip = $ligacao->prepare("SELECT id_equipamento FROM documentos WHERE id = :id");
    $stmtEquip->bindParam(':id', $idDocumento, PDO::PARAM_INT);
    $stmtEquip->execute();
    $idEquipamentoDoc = $stmtEquip->fetchColumn();

    if (!$idEquipamentoDoc) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

    $stmt = $ligacao->prepare("UPDATE documentos SET apagado = 1 WHERE id = :id");
    $stmt->bindParam(':id', $idDocumento, PDO::PARAM_INT);
    $stmt->execute();

    //Logs
    // Vai buscar o id do utilizador autenticado (a sessão só guarda o email)
    $stmtUser = $ligacao->prepare("SELECT id FROM utilizadores WHERE email = :email");
    $stmtUser->execute([':email' => $_SESSION['utilizador']]);
    $idUtilizador = $stmtUser->fetchColumn();

    // Regista o evento na tabela de logs
    $stmtLog = $ligacao->prepare("INSERT INTO logs (id_utilizador, tipo_evento, descricao) VALUES (:id_utilizador, 'documento_desativado', :descricao)");
    $stmtLog->execute([
        ':id_utilizador' => $idUtilizador,
        ':descricao'     => 'Documento desativado (id: ' . $idDocumento . ')'
    ]);

    // Guarda mensagem de sucesso para o Toast aparecer
    $_SESSION['toast_success'] = 'Documento desativado com sucesso.';

    header('Location: ' . BASE_URL . '/private/views/equipamentos/detalhes.php?id_equipamento=' . aes_encrypt($idEquipamentoDoc));
    exit;

} catch (PDOException $err) {
    $_SESSION['toast_error'] = 'Erro ao desativar o documento.';
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}
?>