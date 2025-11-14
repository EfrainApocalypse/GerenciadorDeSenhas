<?php
session_start();
include './propriedades/config.php';
require './requires/header.php';

// Buscar dados do usuário atual
$stmt = $pdo->prepare("SELECT nome, email FROM tb01_usuarios WHERE identificador = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: login.php');
    exit;
}

// Verificar mensagens de feedback
$mensagem = null;
if (isset($_GET['sucesso'])) {
    $mensagem = mostrarMensagem($_GET['sucesso']);
} elseif (isset($_GET['erro'])) {
    $mensagem = mostrarMensagem($_GET['erro']);
} elseif (isset($_SESSION['erro'])) {
    $mensagem = ['tipo' => 'error', 'texto' => $_SESSION['erro']];
    unset($_SESSION['erro']);
} elseif (isset($_SESSION['sucesso'])) {
    $mensagem = ['tipo' => 'success', 'texto' => $_SESSION['sucesso']];
    unset($_SESSION['sucesso']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Gerenciador de Senhas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            text-align: center;
        }

        .header .logo {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.3);
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .back-button {
            position: absolute;
            top: 30px;
            left: 30px;
            background: rgba(255, 255, 255, 0.9);
            color: #2c3e50;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section {
            margin-bottom: 40px;
            padding: 30px;
            border: 2px solid #ecf0f1;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .form-section:hover {
            border-color: #3498db;
            box-shadow: 0 5px 20px rgba(52, 152, 219, 0.1);
        }

        .section-title {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .section-title i {
            color: #3498db;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ecf0f1;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 20px rgba(52, 152, 219, 0.2);
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
        }

        .form-group input::placeholder {
            color: #bdc3c7;
        }

        .password-input-group {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #7f8c8d;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #2c3e50;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 15px;
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
        }

        .password-match {
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            margin-top: 20px;
        }

        .danger-zone {
            border-color: #e74c3c;
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.02), rgba(192, 57, 43, 0.02));
        }

        .danger-zone .section-title {
            color: #c0392b;
            border-bottom-color: rgba(231, 76, 60, 0.2);
        }

        .danger-zone .section-title i {
            color: #e74c3c;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .content {
                padding: 25px;
            }

            .form-section {
                padding: 20px;
            }

            .back-button {
                position: static;
                margin-bottom: 20px;
                width: fit-content;
            }

            .form-actions {
                flex-direction: column;
                gap: 15px;
            }

            .btn-danger {
                margin-left: 0;
                width: 100%;
            }
        }

        /* Loading animation */
        .btn-loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s ease infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Voltar ao Dashboard
        </a>

        <div class="header">
            <div class="logo">
                <i class="fas fa-user-edit"></i>
            </div>
            <h1>Editar Perfil</h1>
            <p>Gerencie suas informações pessoais</p>
        </div>

        <div class="content">
            <?php if ($mensagem): ?>
                <div class="alert alert-<?= $mensagem['tipo'] == 'success' ? 'success' : 'error' ?>">
                    <i class="fas fa-<?= $mensagem['tipo'] == 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                    <?= $mensagem['texto'] ?>
                </div>
            <?php endif; ?>

            <!-- Seção de Informações Pessoais -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Informações Pessoais
                </h2>
                
                <form id="infoForm" method="POST" action="./processos/processar_edicao_usuario.php">
                    <input type="hidden" name="action" value="update_info">
                    
                    <div class="form-group">
                        <label for="nome">
                            <i class="fas fa-user"></i>
                            Nome Completo
                        </label>
                        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required maxlength="250">
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            E-mail
                        </label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required maxlength="150">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            Salvar Informações
                        </button>
                    </div>
                </form>
            </div>

            <!-- Seção de Alteração de Senha -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-lock"></i>
                    Alterar Senha
                </h2>
                
                <form id="passwordForm" method="POST" action="./processos/processar_edicao_usuario.php">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="senha_atual">
                            <i class="fas fa-key"></i>
                            Senha Atual
                        </label>
                        <div class="password-input-group">
                            <input type="password" id="senha_atual" name="senha_atual" placeholder="Digite sua senha atual" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('senha_atual')"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nova_senha">
                            <i class="fas fa-lock"></i>
                            Nova Senha
                        </label>
                        <div class="password-input-group">
                            <input type="password" id="nova_senha" name="nova_senha" placeholder="Digite a nova senha" required minlength="6">
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('nova_senha')"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">
                            <i class="fas fa-check"></i>
                            Confirmar Nova Senha
                        </label>
                        <div class="password-input-group">
                            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a nova senha" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmar_senha')"></i>
                        </div>
                        <span id="passwordMatch" class="password-match"></span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-key"></i>
                            Alterar Senha
                        </button>
                    </div>
                </form>
            </div>

            <!-- Zona de Perigo -->
            <div class="form-section danger-zone">
                <h2 class="section-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Zona de Perigo
                </h2>
                
                <p style="color: #7f8c8d; margin-bottom: 20px;">
                    <i class="fas fa-info-circle"></i>
                    Ações irreversíveis. Tenha certeza antes de prosseguir.
                </p>

                <div class="form-actions">
                    <button type="button" class="btn-danger" onclick="confirmDeleteAccount()">
                        <i class="fas fa-user-times"></i>
                        Excluir Conta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Função para alternar visibilidade da senha
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Verificar se senhas coincidem
        function checkPasswordMatch() {
            const novaSenha = document.getElementById('nova_senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;
            const matchIndicator = document.getElementById('passwordMatch');

            if (confirmarSenha === '') {
                matchIndicator.textContent = '';
                return true;
            }

            if (novaSenha === confirmarSenha) {
                matchIndicator.textContent = '✓ Senhas coincidem';
                matchIndicator.style.color = '#27ae60';
                return true;
            } else {
                matchIndicator.textContent = '✗ Senhas não coincidem';
                matchIndicator.style.color = '#e74c3c';
                return false;
            }
        }

        // Event listeners para verificação de senha
        document.getElementById('nova_senha').addEventListener('input', checkPasswordMatch);
        document.getElementById('confirmar_senha').addEventListener('input', checkPasswordMatch);

        // Validação do formulário de informações
        document.getElementById('infoForm').addEventListener('submit', function(e) {
            const nome = document.getElementById('nome').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!nome || !email) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return false;
            }

            // Adicionar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.classList.add('btn-loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        });

        // Validação do formulário de senha
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const senhaAtual = document.getElementById('senha_atual').value;
            const novaSenha = document.getElementById('nova_senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;

            if (!senhaAtual || !novaSenha || !confirmarSenha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos de senha.');
                return false;
            }

            if (novaSenha.length < 6) {
                e.preventDefault();
                alert('A nova senha deve ter pelo menos 6 caracteres.');
                return false;
            }

            if (novaSenha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não coincidem.');
                return false;
            }

            if (senhaAtual === novaSenha) {
                e.preventDefault();
                alert('A nova senha deve ser diferente da atual.');
                return false;
            }

            // Adicionar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.classList.add('btn-loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Alterando...';
        });

        // Confirmação de exclusão de conta
        function confirmDeleteAccount() {
            const confirmation = confirm(
                'ATENÇÃO: Esta ação excluirá permanentemente sua conta e TODAS as suas senhas.\n\n' +
                'Esta ação NÃO PODE SER DESFEITA!\n\n' +
                'Tem certeza que deseja continuar?'
            );
            
            if (confirmation) {
                const finalConfirmation = prompt(
                    'Para confirmar a exclusão da conta, digite "EXCLUIR" (em maiúsculas):'
                );
                
                if (finalConfirmation === 'EXCLUIR') {
                    window.location.href = './processos/processar_edicao_usuario.php?action=delete_account';
                } else {
                    alert('Exclusão cancelada. Texto de confirmação incorreto.');
                }
            }
        }

        // Auto-hide de mensagens após 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>