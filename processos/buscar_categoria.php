<?php
// buscar_categoria.php
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

$id_categoria = $_GET['id'];

// Validar se o ID é numérico
if (!is_numeric($id_categoria)) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    // Buscar categoria
    $stmt = $pdo->prepare("
        SELECT id_categoria, tipo, descricao, cor, identificador
        FROM tb03_categorias
        WHERE id_categoria = ? AND identificador = ?
    ");
    $stmt->execute([$id_categoria, $usuario_logado_id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($categoria) {
        echo json_encode([
            'success' => true,
            'categoria' => $categoria
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Categoria não encontrada ou você não tem permissão para acessá-la'
        ]);
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar categoria: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro no banco de dados'
    ]);
}
?>