<?php
session_start();
require '../propriedades/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Validações básicas
    if (empty($nome) || empty($email) || empty($senha)) {
        $_SESSION['erro'] = "Por favor, preencha todos os campos.";
        header("Location: ../cadastro.php");
        exit;
    }

    // Validação de nome (mínimo 2 caracteres)
    if (strlen($nome) < 2) {
        $_SESSION['erro'] = "Nome deve ter pelo menos 2 caracteres.";
        header("Location: ../cadastro.php");
        exit;
    }

    // Validação de nome (máximo 250 caracteres)
    if (strlen($nome) > 250) {
        $_SESSION['erro'] = "Nome deve ter no máximo 250 caracteres.";
        header("Location: ../cadastro.php");
        exit;
    }

    // Validação de e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erro'] = "E-mail inválido.";
        header("Location: ../cadastro.php");
        exit;
    }

    // Validação de e-mail (máximo 150 caracteres)
    if (strlen($email) > 150) {
        $_SESSION['erro'] = "E-mail deve ter no máximo 150 caracteres.";
        header("Location: ../cadastro.php");
        exit;
    }

    // Validação de senha (mínimo 6 caracteres)
    if (strlen($senha) < 6) {
        $_SESSION['erro'] = "A senha deve ter pelo menos 6 caracteres.";
        header("Location: ../cadastro.php");
        exit;
    }

    try {
        // Verificar se já existe usuário com o mesmo e-mail
        $sql_check = "SELECT identificador FROM tb01_usuarios WHERE email = :email LIMIT 1";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':email', $email);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            $_SESSION['erro'] = "E-mail já cadastrado. Use outro e-mail ou faça login.";
            header("Location: ../cadastro.php");
            exit;
        }

        // Hash da senha
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        // Inserir o usuário
        $sql_insert = "INSERT INTO tb01_usuarios (nome, email, palavra_passe, situacao) 
                       VALUES (:nome, :email, :senha, 1)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->bindParam(':nome', $nome);
        $stmt_insert->bindParam(':email', $email);
        $stmt_insert->bindParam(':senha', $hash);

        if ($stmt_insert->execute()) {
            $novo_usuario_id = $pdo->lastInsertId();

            $_SESSION['sucesso'] = "Cadastro realizado com sucesso! Faça login para acessar.";
            header("Location: ../login.php");
            exit;
        } else {
            $_SESSION['erro'] = "Erro ao realizar cadastro. Tente novamente.";
            header("Location: ../cadastro.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro no cadastro: " . $e->getMessage());
        
        // Verificar se é erro de chave duplicada
        if ($e->getCode() == 23000) {
            $_SESSION['erro'] = "E-mail já cadastrado. Use outro e-mail.";
        } else {
            $_SESSION['erro'] = "Erro interno. Tente novamente mais tarde.";
        }
        
        header("Location: ../cadastro.php");
        exit;
    }
} else {
    // Método não permitido
    header("Location: ../cadastro.php");
    exit;
}
?>