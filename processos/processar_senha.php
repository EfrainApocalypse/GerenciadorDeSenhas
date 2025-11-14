<?php
// processar_senha.php
include '../requires/header.php';
include '../propriedades/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_senha = $_POST['id_senha'] ?? '';
    $nome = trim($_POST['nome']);
    $site_origem = trim($_POST['site_origem']);
    $usuario_origem = trim($_POST['usuario_origem']);
    $senha = trim($_POST['senha']);
    $id_categoria = $_POST['id_categoria'] ?: null;
    $identificador = $_SESSION['usuario_id'];
    
    // Validação básica
    if (empty($nome) || empty($site_origem) || empty($usuario_origem) || empty($senha)) {
        header('Location: ../index.php?erro=campos_obrigatorios');
        exit;
    }
    
    // Validações adicionais
    if (strlen($nome) > 150) {
        header('Location: ../index.php?erro=nome_muito_longo');
        exit;
    }
    
    if (strlen($site_origem) > 250) {
        header('Location: ../index.php?erro=site_muito_longo');
        exit;
    }
    
    if (strlen($usuario_origem) > 200) {
        header('Location: ../index.php?erro=usuario_muito_longo');
        exit;
    }
    
    try {
        if (empty($id_senha)) {
            // Inserir nova senha (CRIPTOGRAFADA)
            $stmt = $pdo->prepare("
                INSERT INTO tb02_senhas (nome, site_origem, usuario_origem, senha, id_categoria, identificador) 
                VALUES (?, ?, ?, AES_ENCRYPT(?, '$chave_segredo'), ?, ?)
            ");
            $result = $stmt->execute([$nome, $site_origem, $usuario_origem, $senha, $id_categoria, $identificador]);
            
            if ($result) {
                header('Location: ../index.php?sucesso=senha_cadastrada');
            } else {
                header('Location: ../index.php?erro=erro_cadastro');
            }
        } else {
            // Verificar se a senha pertence ao usuário antes de atualizar
            $stmt_check = $pdo->prepare("SELECT id_senha FROM tb02_senhas WHERE id_senha = ? AND identificador = ?");
            $stmt_check->execute([$id_senha, $identificador]);
            
            if ($stmt_check->rowCount() == 0) {
                header('Location: ../index.php?erro=senha_nao_encontrada');
                exit;
            }
            
            // Atualizar senha existente (CRIPTOGRAFADA)
            $stmt = $pdo->prepare("
                UPDATE tb02_senhas 
                SET nome = ?, site_origem = ?, usuario_origem = ?, senha = AES_ENCRYPT(?, '$chave_segredo'), id_categoria = ?, data_atualizacao = NOW()
                WHERE id_senha = ? AND identificador = ?
            ");
            $result = $stmt->execute([$nome, $site_origem, $usuario_origem, $senha, $id_categoria, $id_senha, $identificador]);
            
            if ($result) {
                header('Location: ../index.php?sucesso=senha_atualizada');
            } else {
                header('Location: ../index.php?erro=erro_atualizacao');
            }
        }
    } catch (PDOException $e) {
        // Log do erro para debug
        error_log("Erro no banco de dados: " . $e->getMessage());
        header('Location: ../index.php?erro=erro_banco ');
    }
} else {
    // Redirecionar se não for POST
    header('Location: ../index.php');
}
?>
