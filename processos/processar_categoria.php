<?php
// processar_categoria.php
include '../propriedades/config.php';
require '../requires/header.php';

// Debug - verificar se chegou aqui
error_log("processar_categoria.php iniciado");

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    error_log("Usuário não logado - redirecionando");
    header('Location: ../index.php?erro=usuario_nao_logado');
    exit;
}

$usuario_logado_id = $_SESSION['usuario_id'];

// Verificar método de requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Método inválido: " . $_SERVER['REQUEST_METHOD']);
    header('Location: ../index.php?erro=metodo_invalido');
    exit;
}

// Debug - mostrar dados recebidos
error_log("Dados POST recebidos: " . print_r($_POST, true));

// Capturar dados do formulário
$id_categoria = !empty($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : null;
$tipo = trim($_POST['tipo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$cor = $_POST['cor'] ?? '3498db';
$identificador = $usuario_logado_id;

// Debug dos dados processados
error_log("Dados processados - ID: $id_categoria, Tipo: $tipo, Cor: $cor, User: $identificador");

// Validações básicas
if (empty($tipo)) {
    error_log("Nome da categoria vazio");
    header('Location: ../index.php?erro=nome_categoria_obrigatorio');
    exit;
}

if (strlen($tipo) > 50) {
    error_log("Nome da categoria muito longo: " . strlen($tipo));
    header('Location: ../index.php?erro=nome_categoria_muito_longo');
    exit;
}

if (strlen($descricao) > 255) {
    error_log("Descrição muito longa: " . strlen($descricao));
    header('Location: ../index.php?erro=descricao_muito_longa');
    exit;
}

// Validar cor (deve ter 6 caracteres hexadecimais)
if (!preg_match('/^[a-fA-F0-9]{6}$/', $cor)) {
    error_log("Cor inválida: $cor - usando padrão");
    $cor = '3498db'; // Cor padrão se inválida
}

try {
    if ($id_categoria && $id_categoria > 0) {
        // ATUALIZAR categoria existente
        error_log("Tentando atualizar categoria ID: $id_categoria");
        
        // Verificar se a categoria pertence ao usuário
        $stmt_check = $pdo->prepare("
            SELECT id_categoria FROM tb03_categorias 
            WHERE id_categoria = ? AND identificador = ?
        ");
        $stmt_check->execute([$id_categoria, $identificador]);
        
        if (!$stmt_check->fetch()) {
            error_log("Categoria não encontrada ou sem permissão");
            header('Location: ../index.php?erro=categoria_nao_encontrada');
            exit;
        }
        
        // Verificar se já existe outra categoria com o mesmo nome
        $stmt_duplicate = $pdo->prepare("
            SELECT id_categoria FROM tb03_categorias 
            WHERE tipo = ? AND identificador = ? AND id_categoria != ?
        ");
        $stmt_duplicate->execute([$tipo, $identificador, $id_categoria]);
        
        if ($stmt_duplicate->fetch()) {
            error_log("Categoria com nome duplicado encontrada");
            header('Location: ../index.php?erro=categoria_ja_existe');
            exit;
        }
        
        // Atualizar categoria
        $stmt = $pdo->prepare("
            UPDATE tb03_categorias 
            SET tipo = ?, descricao = ?, cor = ?
            WHERE id_categoria = ? AND identificador = ?
        ");
        
        $result = $stmt->execute([$tipo, $descricao, $cor, $id_categoria, $identificador]);
        error_log("Resultado update: " . ($result ? "true" : "false"));
        
        if ($result && $stmt->rowCount() > 0) {
            error_log("Categoria atualizada com sucesso");
            header('Location: ../index.php?sucesso=categoria_atualizada');
        } else {
            error_log("Erro ao atualizar - rowCount: " . $stmt->rowCount());
            header('Location: ../index.php?erro=erro_ao_atualizar_categoria');
        }
        
    } else {
        // CRIAR nova categoria
        error_log("Tentando criar nova categoria");
        
        // Verificar se já existe categoria com o mesmo nome
        $stmt_duplicate = $pdo->prepare("
            SELECT id_categoria FROM tb03_categorias 
            WHERE tipo = ? AND identificador = ?
        ");
        $stmt_duplicate->execute([$tipo, $identificador]);
        
        if ($stmt_duplicate->fetch()) {
            error_log("Categoria com nome duplicado encontrada");
            header('Location: ../index.php?erro=categoria_ja_existe');
            exit;
        }
        
        // Inserir nova categoria
        $stmt = $pdo->prepare("
            INSERT INTO tb03_categorias (tipo, descricao, cor, identificador)
            VALUES (?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([$tipo, $descricao, $cor, $identificador]);
        error_log("Resultado insert: " . ($result ? "true" : "false"));
        
        if ($result) {
            $new_id = $pdo->lastInsertId();
            error_log("Categoria criada com sucesso - ID: $new_id");
            header('Location: ../index.php?sucesso=categoria_criada');
        } else {
            error_log("Erro ao criar categoria");
            header('Location: ../index.php?erro=erro_ao_criar_categoria');
        }
    }
    
} catch (PDOException $e) {
    // Log do erro para debug
    error_log("Erro PDO ao processar categoria: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    header('Location: ../index.php?erro=erro_banco_dados');
    exit;
} catch (Exception $e) {
    error_log("Erro geral ao processar categoria: " . $e->getMessage());
    header('Location: ../index.php?erro=erro_interno');
    exit;
}
?>