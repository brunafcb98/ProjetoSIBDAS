<?php 
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged();

// Desencriptação e validação do ID garantia
$idGarantiaEncrypted = $_GET['id_garantia'] ?? null;
$idGarantia = aes_decrypt($idGarantiaEncrypted);

if (!$idGarantia || !is_numeric($idGarantia)) {
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}

try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );

    // Vai buscar o id_equipamento desta garantia, antes de apagar, para saber para onde redirecionar
    $stmtEquip = $ligacao->prepare("SELECT id_equipamento FROM garantias_contratos WHERE id = :id");
    $stmtEquip->bindParam(':id', $idGarantia, PDO::PARAM_INT);
    $stmtEquip->execute();
    $idEquipamentoGar = $stmtEquip->fetchColumn();

    if (!$idEquipamentoGar) {
        header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
        exit;
    }

    $stmt = $ligacao->prepare("UPDATE garantias_contratos SET apagado = 1 WHERE id = :id");
    $stmt->bindParam(':id', $idGarantia, PDO::PARAM_INT);
    $stmt->execute();

    //Logs
    // Vai buscar o id do utilizador autenticado (a sessão só guarda o email)
    $stmtUser = $ligacao->prepare("SELECT id FROM utilizadores WHERE email = :email");
    $stmtUser->execute([':email' => $_SESSION['utilizador']]);
    $idUtilizador = $stmtUser->fetchColumn();

    // Regista o evento na tabela de logs
    $stmtLog = $ligacao->prepare("INSERT INTO logs (id_utilizador, tipo_evento, descricao) VALUES (:id_utilizador, 'garantia_desativada', :descricao)");
    $stmtLog->execute([
        ':id_utilizador' => $idUtilizador,
        ':descricao'     => 'Garantia/Contrato desativado (id: ' . $idGarantia . ')'
    ]);

    // Guarda mensagem de sucesso para o Toast aparecer
    $_SESSION['toast_success'] = 'Garantia/Contrato desativado com sucesso.';

    header('Location: ' . BASE_URL . '/private/views/equipamentos/detalhes.php?id_equipamento=' . aes_encrypt($idEquipamentoGar));
    exit;

} catch (PDOException $err) {
    $_SESSION['toast_error'] = 'Erro ao desativar a garantia/contrato.';
    header('Location: ' . BASE_URL . '/private/views/equipamentos/equipamentos.php');
    exit;
}
?>