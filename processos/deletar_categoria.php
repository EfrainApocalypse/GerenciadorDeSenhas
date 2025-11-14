<?php
// deletar_categoria.php
include '../propriedades/config.php';
require '../requires/header.php';

header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

$usuario_logado_id = $_SESSION['usuario_id'];

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

if (!isset($input['id_categoria']) || empty($input['id_categoria'])) {
    echo json_encode(['success' => false, 'message' => 'ID da categoria não fornecido']);
    exit;
}

$id_categoria = $input['id_categoria'];

// Validar se o ID é numérico
if (!is_numeric($id_categoria)) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    // Primeiro, verificar se a categoria existe e pertence ao usuário
    $stmt_check = $pdo->prepare("
        SELECT id_categoria, tipo FROM tb03_categorias 
        WHERE id_categoria = ? AND identificador = ?
    ");
    $stmt_check->execute([$id_categoria, $usuario_logado_id]);
    $categoria_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$categoria_existente) {
        echo json_encode([
            'success' => false, 
            'message' => 'Categoria não encontrada ou você não tem permissão para excluí-la'
        ]);
        exit;
    }
    
    // Verificar quantas senhas usam esta categoria
    $stmt_count = $pdo->prepare("
        SELECT COUNT(*) as total FROM tb02_senhas 
        WHERE id_categoria = ? AND identificador = ?
    ");
    $stmt_count->execute([$id_categoria, $usuario_logado_id]);
    $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $senhas_afetadas = $count_result['total'];
    
    // Iniciar transação para garantir consistência
    $pdo->beginTransaction();
    
    try {
        // Primeiro, remover a categoria das senhas (definir como NULL)
        if ($senhas_afetadas > 0) {
            $stmt_update_senhas = $pdo->prepare("
                UPDATE tb02_senhas 
                SET id_categoria = NULL 
                WHERE id_categoria = ? AND identificador = ?
            ");
            $stmt_update_senhas->execute([$id_categoria, $usuario_logado_id]);
        }
        
        // Depois, excluir a categoria
        $stmt_delete = $pdo->prepare("
            DELETE FROM tb03_categorias 
            WHERE id_categoria = ? AND identificador = ?
        ");
        $result = $stmt_delete->execute([$id_categoria, $usuario_logado_id]);
        
        if ($result && $stmt_delete->rowCount() > 0) {
            // Confirmar transação
            $pdo->commit();
            
            $message = 'Categoria "' . $categoria_existente['tipo'] . '" excluída com sucesso';
            if ($senhas_afetadas > 0) {
                $message .= '. ' . $senhas_afetadas . ' senha(s) foram movidas para "Sem Categoria"';
            }
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'senhas_afetadas' => $senhas_afetadas
            ]);
        } else {
            // Reverter transação
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao excluir a categoria'
            ]);
        }
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    // Log do erro para debug
    error_log("Erro ao deletar categoria: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro no banco de dados: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Log do erro para debug
    error_log("Erro geral ao deletar categoria: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}
?>