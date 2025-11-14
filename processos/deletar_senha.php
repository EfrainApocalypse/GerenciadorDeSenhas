<?php
// deletar_senha.php
include '../propriedades/config.php';
include '../requires/header.php';

header('Content-Type: application/json');

// Verificar método de requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Ler dados JSON da requisição
$input = json_decode(file_get_contents('php://input'), true);

// Verificar se os dados foram enviados corretamente
if ($input === null) {
    echo json_encode(['success' => false, 'message' => 'Dados JSON inválidos']);
    exit;
}

if (!isset($input['id_senha']) || empty($input['id_senha'])) {
    echo json_encode(['success' => false, 'message' => 'ID da senha não fornecido']);
    exit;
}

$id_senha = $input['id_senha'];

// Validar se o ID é numérico
if (!is_numeric($id_senha)) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    // Primeiro, verificar se a senha existe e pertence ao usuário
    $stmt_check = $pdo->prepare("
        SELECT id_senha, nome FROM tb02_senhas 
        WHERE id_senha = ? AND identificador = ?
    ");
    $stmt_check->execute([$id_senha, $_SESSION['usuario_id']]);
    $senha_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$senha_existente) {
        echo json_encode([
            'success' => false, 
            'message' => 'Senha não encontrada ou você não tem permissão para excluí-la'
        ]);
        exit;
    }
    
    // Excluir a senha
    $stmt = $pdo->prepare("
        DELETE FROM tb02_senhas 
        WHERE id_senha = ? AND identificador = ?
    ");
    $result = $stmt->execute([$id_senha, $_SESSION['usuario_id']]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Senha "' . $senha_existente['nome'] . '" excluída com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao excluir a senha'
        ]);
    }
} catch (PDOException $e) {
    // Log do erro para debug
    error_log("Erro ao deletar senha: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro no banco de dados'
    ]);
}
?>