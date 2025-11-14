<?php
// logout.php
// Inicia a sessão (se ainda não iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Se as sessões usam cookie, remove o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finalmente destrói a sessão
session_destroy();

// Redireciona para a página de login
header('Location: login.php');
exit;
