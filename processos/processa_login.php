<?php
session_start();
require '../propriedades/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Validação básica
    if (empty($email) || empty($senha)) {
        $_SESSION['erro'] = "Por favor, preencha todos os campos.";
        header("Location: ../login.php");
        exit;
    }

    // Validação de e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erro'] = "E-mail inválido.";
        header("Location: ../login.php");
        exit;
    }

    try {
        // Buscar usuário pelo e-mail
        $sql = "SELECT identificador, nome, palavra_passe, situacao FROM tb01_usuarios WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar se o usuário existe e a senha está correta
        if ($usuario && password_verify($senha, $usuario['palavra_passe'])) {
            // Verificar se a conta está ativa
            if ($usuario['situacao'] != 1) {
                $_SESSION['erro'] = "Conta desativada. Entre em contato com o administrador.";
                header("Location: ../login.php");
                exit;
            }

            // Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['identificador'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            
            // Atualizar último acesso (opcional)
            $update_sql = "UPDATE tb01_usuarios SET data_atualizacao = NOW() WHERE identificador = :id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':id', $usuario['identificador']);
            $update_stmt->execute();

            // Redirecionar para página principal
            header("Location: ../index.php");
            exit;
        } else {
            $_SESSION['erro'] = "E-mail ou senha incorretos.";
            header("Location: ../login.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro no login: " . $e->getMessage());
        $_SESSION['erro'] = "Erro interno. Tente novamente mais tarde.";
        header("Location: ../login.php");
        exit;
    }
} else {
    // Método não permitido
    header("Location: ../login.php");
    exit;
}
?>