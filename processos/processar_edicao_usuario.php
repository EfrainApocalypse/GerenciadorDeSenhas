<?php
session_start();
require '../propriedades/config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar a ação solicitada
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'update_info':
        updateUserInfo($pdo, $usuario_id);
        break;
    case 'change_password':
        changePassword($pdo, $usuario_id);
        break;
    case 'delete_account':
        deleteAccount($pdo, $usuario_id);
        break;
    default:
        $_SESSION['erro'] = 'Ação inválida.';
        header('Location: ../editar_usuario.php');
        exit;
}

// Função para atualizar informações do usuário
function updateUserInfo($pdo, $usuario_id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['erro'] = 'Método não permitido.';
        header('Location: ../editar_usuario.php');
        exit;
    }

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validações básicas
    if (empty($nome) || empty($email)) {
        $_SESSION['erro'] = 'Por favor, preencha todos os campos.';
        header('Location: ../editar_usuario.php');
        exit;
    }

    // Validação de nome
    if (strlen($nome) < 2 || strlen($nome) > 250) {
        $_SESSION['erro'] = 'Nome deve ter entre 2 e 250 caracteres.';
        header('Location: ../editar_usuario.php');
        exit;
    }

    // Validação de e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $_SESSION['erro'] = 'E-mail inválido ou muito longo.';
        header('Location: ../editar_usuario.php');
        exit;
    }

    try {
        // Verificar se o e-mail já existe para outro usuário
        $stmt_check = $pdo->prepare("SELECT identificador FROM tb01_usuarios WHERE email = ? AND identificador != ?");
        $stmt_check->execute([$email, $usuario_id]);
        
        if ($stmt_check->rowCount() > 0) {
            $_SESSION['erro'] = 'Este e-mail já está sendo usado por outro usuário.';
            header('Location: ../editar_usuario.php');
            exit;
        }

        // Atualizar dados do usuário
        $stmt = $pdo->prepare("UPDATE tb01_usuarios SET nome = ?, email = ?, data_atualizacao = NOW() WHERE identificador = ?");
        $result = $stmt->execute([$nome, $email, $usuario_id]);

        if ($result && $stmt->rowCount() > 0) {
            // Atualizar nome na sessão
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['sucesso'] = 'Informações atualizadas com sucesso!';
        } else {
            $_SESSION['erro'] = 'Nenhuma alteração foi realizada.';
        }

    } catch (PDOException $e) {
        error_log("Erro ao atualizar informações do usuário: " . $e->getMessage());
        $_SESSION['erro'] = 'Erro no banco de dados. Tente novamente.';
    }

    header('Location: ../editar_usuario.php');
    exit;
}

// Função para alterar senha
function changePassword($pdo, $usuario_id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['erro'] = 'Método não permitido.';
        header('Location: ../editar_usuario.php');
        exit;
    }

    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Validações básicas
    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        $_SESSION['erro'] = 'Por favor, preencha todos os campos de senha.';
        header('Location: ../editar_usuario.php');
        exit;
    }

    // Validação de nova senha
    if (strlen($nova_senha) < 6) {
        $_SESSION['erro'] = 'A nova senha deve ter pelo menos 6 caracteres.';
        header('Location: ../editar_usuario.php');
        exit;
    }

    // Verificar se senhas coincidem
    if ($nova_senha !== $confirmar_senha) {
        $_SESSION['erro'] = 'As senhas não coincidem.';
        header('Location: ../editar_usuario.php');
        exit;
    }

    // Verificar se nova senha é diferente da atual
    if ($senha_atual === $nova_senha) {
        $_SESSION['erro'] = 'A nova senha deve ser diferente da atual.';
        header('Location: ../editar_usuario.php');
        exit;
    }

    try {
        // Buscar senha atual do usuário
        $stmt = $pdo->prepare("SELECT palavra_passe FROM tb01_usuarios WHERE identificador = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            $_SESSION['erro'] = 'Usuário não encontrado.';
            header('Location: ../editar_usuario.php');
            exit;
        }

        // Verificar se a senha atual está correta
        if (!password_verify($senha_atual, $usuario['palavra_passe'])) {
            $_SESSION['erro'] = 'Senha atual incorreta.';
            header('Location: ../editar_usuario.php');
            exit;
        }

        // Hash da nova senha
        $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        // Atualizar senha no banco
        $stmt_update = $pdo->prepare("UPDATE tb01_usuarios SET palavra_passe = ?, data_atualizacao = NOW() WHERE identificador = ?");
        $result = $stmt_update->execute([$nova_senha_hash, $usuario_id]);

        if ($result) {
            $_SESSION['sucesso'] = 'Senha alterada com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao alterar senha. Tente novamente.';
        }

    } catch (PDOException $e) {
        error_log("Erro ao alterar senha: " . $e->getMessage());
        $_SESSION['erro'] = 'Erro no banco de dados. Tente novamente.';
    }

    header('Location: ../editar_usuario.php');
    exit;
}

// Função para excluir conta
function deleteAccount($pdo, $usuario_id) {
    try {
        // Iniciar transação
        $pdo->beginTransaction();

        // Deletar senhas do usuário
        $stmt_senhas = $pdo->prepare("DELETE FROM tb02_senhas WHERE identificador = ?");
        $stmt_senhas->execute([$usuario_id]);

        // Deletar categorias do usuário
        $stmt_categorias = $pdo->prepare("DELETE FROM tb03_categorias WHERE identificador = ?");
        $stmt_categorias->execute([$usuario_id]);

        // Deletar usuário
        $stmt_usuario = $pdo->prepare("DELETE FROM tb01_usuarios WHERE identificador = ?");
        $result = $stmt_usuario->execute([$usuario_id]);

        if ($result && $stmt_usuario->rowCount() > 0) {
            // Confirmar transação
            $pdo->commit();

            // Destruir sessão
            session_destroy();

            // Redirecionar para página de cadastro com mensagem
            session_start();
            $_SESSION['sucesso'] = 'Conta excluída com sucesso. Obrigado por ter usado nosso serviço.';
            header('Location: ../cadastro.php');
            exit;
        } else {
            // Reverter transação
            $pdo->rollBack();
            $_SESSION['erro'] = 'Erro ao excluir conta. Tente novamente.';
        }

    } catch (PDOException $e) {
        // Reverter transação em caso de erro
        $pdo->rollBack();
        error_log("Erro ao excluir conta: " . $e->getMessage());
        $_SESSION['erro'] = 'Erro no banco de dados. Tente novamente.';
    }

    header('Location: ../editar_usuario.php');
    exit;
}
?>