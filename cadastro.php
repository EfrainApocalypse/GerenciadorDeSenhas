<?php 
session_start();
include './propriedades/config.php';

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
    <title>Cadastro - Gerenciador de Senhas</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 20px 0;
        }

        /* Animação de fundo */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="a" cx=".5" cy=".5" r=".5"><stop offset="0%" stop-color="%23ffffff" stop-opacity=".1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="20" cy="20" r="10" fill="url(%23a)"><animate attributeName="cx" values="20;80;20" dur="8s" repeatCount="indefinite"/></circle><circle cx="80" cy="40" r="15" fill="url(%23a)"><animate attributeName="cy" values="40;80;40" dur="12s" repeatCount="indefinite"/></circle><circle cx="40" cy="80" r="8" fill="url(%23a)"><animate attributeName="cx" values="40;60;40" dur="10s" repeatCount="indefinite"/></circle></svg>') no-repeat center center;
            background-size: cover;
            opacity: 0.3;
            pointer-events: none;
        }

        .cadastro-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .cadastro-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .cadastro-header .logo {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.3);
        }

        .cadastro-header h2 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .cadastro-header p {
            color: #7f8c8d;
            font-size: 1rem;
            margin-bottom: 20px;
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

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.9rem;
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
            border-color: #27ae60;
            box-shadow: 0 0 20px rgba(46, 204, 113, 0.2);
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
        }

        .form-group input::placeholder {
            color: #bdc3c7;
            font-weight: 400;
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

        .btn-cadastro {
            width: 100%;
            padding: 16px 20px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-cadastro::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-cadastro:hover::before {
            left: 100%;
        }

        .btn-cadastro:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
        }

        .btn-cadastro:active {
            transform: translateY(-1px);
        }

        .form-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #ecf0f1;
        }

        .form-footer a {
            color: #27ae60;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .form-footer a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 50%;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .form-footer a:hover::after {
            width: 100%;
        }

        .form-footer a:hover {
            color: #2ecc71;
            transform: translateY(-1px);
        }

        /* Responsividade */
        @media (max-width: 480px) {
            .cadastro-container {
                padding: 30px 20px;
                margin: 20px;
                border-radius: 15px;
            }

            .cadastro-header .logo {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .cadastro-header h2 {
                font-size: 1.5rem;
            }

            .form-group input {
                padding: 12px 16px;
                font-size: 16px;
            }

            .btn-cadastro {
                padding: 14px 20px;
                font-size: 16px;
            }
        }

        /* Loading animation */
        .btn-cadastro.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-cadastro.loading::after {
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
    <div class="cadastro-container">
        <div class="cadastro-header">
            <div class="logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Cadastrar</h2>
            <p>Crie sua conta no gerenciador de senhas</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $mensagem['tipo'] == 'success' ? 'success' : 'error' ?>">
                <i class="fas fa-<?= $mensagem['tipo'] == 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                <?= $mensagem['texto'] ?>
            </div>
        <?php endif; ?>

        <form action="./processos/processa_cadastro.php" method="POST" id="cadastroForm">
            <div class="form-group">
                <label for="nome">
                    <i class="fas fa-user"></i>
                    Nome Completo
                </label>
                <input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" required>
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    E-mail
                </label>
                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
            </div>

            <div class="form-group">
                <label for="senha">
                    <i class="fas fa-lock"></i>
                    Senha
                </label>
                <div class="password-input-group">
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required minlength="6">
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('senha')"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmarSenha">
                    <i class="fas fa-lock"></i>
                    Confirmar Senha
                </label>
                <div class="password-input-group">
                    <input type="password" id="confirmarSenha" name="confirmarSenha" placeholder="Confirme sua senha" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmarSenha')"></i>
                </div>
                <span id="passwordMatch" style="font-size: 0.85rem; margin-top: 5px; display: block;"></span>
            </div>

            <button type="submit" class="btn-cadastro">
                <i class="fas fa-user-plus"></i>
                Cadastrar
            </button>
        </form>

        <div class="form-footer">
            <p>Já tem conta? <a href="login.php">Faça login aqui</a></p>
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
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmarSenha').value;
            const matchIndicator = document.getElementById('passwordMatch');

            if (confirmarSenha === '') {
                matchIndicator.textContent = '';
                return true;
            }

            if (senha === confirmarSenha) {
                matchIndicator.textContent = '✓ Senhas coincidem';
                matchIndicator.style.color = '#27ae60';
                return true;
            } else {
                matchIndicator.textContent = '✗ Senhas não coincidem';
                matchIndicator.style.color = '#e74c3c';
                return false;
            }
        }

        // Event listeners
        document.getElementById('senha').addEventListener('input', function() {
            checkPasswordMatch();
        });

        document.getElementById('confirmarSenha').addEventListener('input', function() {
            checkPasswordMatch();
        });

        // Animação de loading no formulário
        document.getElementById('cadastroForm').addEventListener('submit', function(e) {
            const nome = document.getElementById('nome').value.trim();
            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmarSenha').value;

            // Validações básicas
            if (!nome || !email || !senha || !confirmarSenha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return false;
            }

            if (senha.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres.');
                return false;
            }

            if (senha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não coincidem.');
                return false;
            }

            // Adicionar loading
            const submitBtn = this.querySelector('.btn-cadastro');
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cadastrando...';
        });

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

        // Validação de e-mail em tempo real
        document.getElementById('email').addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.style.borderColor = '#e74c3c';
                this.focus();
            } else {
                this.style.borderColor = '#ecf0f1';
            }
        });
    </script>
</body>
</html>