<?php
// buscar_senha.php
include '../propriedades/config.php';
require '../requires/header.php';

header('Content-Type: application/json');

// Verificar se o usuário está logado
$usuario_logado_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_logado_id) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

// Verificar método de requisição
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id_senha = $_GET['id'];

// Validar se o ID é numérico
if (!is_numeric($id_senha)) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    // Buscar senha descriptografada diretamente no MySQL
    $stmt = $pdo->prepare("
        SELECT id_senha, nome, site_origem, usuario_origem, 
               AES_DECRYPT(senha, '" . $chave_segredo . "') AS senha,
               id_categoria, identificador
        FROM tb02_senhas
        WHERE id_senha = ? AND identificador = ?
    ");
    $stmt->execute([$id_senha, $usuario_logado_id]);
    $senha = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($senha) {
        echo json_encode([
            'success' => true,
            'senha' => $senha
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Senha não encontrada ou você não tem permissão para acessá-la'
        ]);
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar senha: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro no banco de dados'
    ]);
}
?>
