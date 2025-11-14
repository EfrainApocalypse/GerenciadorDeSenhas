<?php
// header.php
// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se as variáveis de sessão estão definidas
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nome'])) {
    // Redireciona para a tela de login
    header('Location: ./login.php?erro=usuario_nao_logado');
    exit;
}
?>
