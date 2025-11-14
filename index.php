<?php
// index.php - Página principal
include './propriedades/config.php';
require './requires/header.php';
// Verificar mensagens de feedback
$mensagem = null;
if (isset($_GET['sucesso'])) {
    $mensagem = mostrarMensagem($_GET['sucesso']);
} elseif (isset($_GET['erro'])) {
    $mensagem = mostrarMensagem($_GET['erro']);
}

// Buscar senhas do usuário logado
$stmt = $pdo->prepare("
    SELECT 
        s.id_senha,
        s.nome,
        s.site_origem,
        s.usuario_origem,
        CAST(AES_DECRYPT(s.senha, 'chave-secreta') AS CHAR) AS senha,
        s.id_categoria,
        s.data_criacao,
        s.data_atualizacao,
        c.tipo as categoria_nome,
        c.cor as categoria_cor
    FROM tb02_senhas s
    LEFT JOIN tb03_categorias c ON s.id_categoria = c.id_categoria
    WHERE s.identificador = ?
    ORDER BY s.data_criacao DESC
");

$stmt->execute([$_SESSION['usuario_id']]);
$senhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias para o dropdown e sidebar
$stmt_cat = $pdo->prepare("
    SELECT * FROM tb03_categorias 
    WHERE identificador = ? 
    ORDER BY tipo
");
$stmt_cat->execute([$_SESSION['usuario_id']]);
$categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

// Contar senhas por categoria
$categoria_counts = [];
$categoria_counts['todas'] = count($senhas);
$categoria_counts['sem_categoria'] = 0;

foreach ($senhas as $senha) {
    if ($senha['id_categoria']) {
        $categoria_counts[$senha['id_categoria']] = ($categoria_counts[$senha['id_categoria']] ?? 0) + 1;
    } else {
        $categoria_counts['sem_categoria']++;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Senhas</title>
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
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 300px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 0 15px 15px 0;
            padding: 20px;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .sidebar h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-list {
            list-style: none;
        }

        .category-item {
            margin-bottom: 8px;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .category-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            text-decoration: none;
            color: #2c3e50;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .category-link:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .category-link.active {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
        }

        .category-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .category-count {
            background: rgba(0, 0, 0, 0.1);
            color: currentColor;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .category-actions {
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .category-item:hover .category-actions {
            opacity: 1;
        }

        .btn-category-action {
            background: none;
            border: none;
            color: currentColor;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            font-size: 12px;
            transition: background 0.3s ease;
        }

        .btn-category-action:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .add-category-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-align: center;
        }

        .header p {
            color: #7f8c8d;
            text-align: center;
            font-size: 1.1rem;
        }

        .content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(243, 156, 18, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1002;
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(46, 204, 113, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
            margin-left: 5px;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(231, 76, 60, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 5px;
            transition: all 0.3s ease;
        }

        .btn-info:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.4);
        }

        .btn-yellow {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-yellow:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
        }

        .add-password-section {
            margin-bottom: 30px;
            text-align: center;
        }

        .category-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .category-title {
            color: #2c3e50;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .passwords-list {
            margin-top: 20px;
        }

        .password-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 5px solid #3498db;
            transition: all 0.3s ease;
        }

        .password-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .password-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .password-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .password-details {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .password-site {
            font-weight: 500;
            color: #34495e;
        }

        .category-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 10px;
            color: white;
        }

        .password-display {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 8px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin-top: 10px;
            border: 2px solid #34495e;
            word-break: break-all;
            position: relative;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            overflow-y: auto;
            padding: 20px 0;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 0 auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: calc(100vh - 40px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
            position: relative;
            z-index: 1001;
            overflow-y: auto;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover {
            color: #e74c3c;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.2);
        }

        .password-input-group {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #7f8c8d;
            font-size: 18px;
        }

        .toggle-password:hover {
            color: #2c3e50;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #ecf0f1;
            position: sticky;
            bottom: 0;
            background-color: #fefefe;
            z-index: 1002;
        }

        .form-actions button {
            min-width: 120px;
            pointer-events: auto;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #bdc3c7;
        }

        .color-picker-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 8px;
            margin-top: 10px;
        }

        .color-option {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 3px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .color-option:hover {
            transform: scale(1.1);
            border-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .color-option.selected {
            border-color: #2c3e50;
            transform: scale(1.2);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-radius: 0;
                padding: 15px;
            }

            .main-content {
                padding: 15px;
            }

            .password-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .modal-content {
                width: 95%;
                padding: 20px;
                max-height: calc(100vh - 20px);
            }
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .password-item {
            transition: all 0.3s ease;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
            animation: fadeIn 0.5s ease-in;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #bdc3c7;
        }

        .empty-state h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        /* Seção de usuário na sidebar */
        .user-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
        }

        .user-section button {
            width: 100%;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .user-section .btn-info {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .user-section .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .user-section .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            margin-bottom: 0;
        }

        .user-section .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        /* Animação de loading para botões da sidebar */
        .user-section .btn-loading {
            pointer-events: none;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .user-section {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar de Categorias -->
        <div class="sidebar">
            <h3><i class="fas fa-folder"></i> Categorias</h3>
            
            <ul class="category-list">
                <!-- Todas as senhas -->
                <li class="category-item">
                    <div class="category-link active" data-category="todas" onclick="filterByCategory('todas')">
                        <div class="category-info">
                            <i class="fas fa-th-list"></i>
                            <span>Todas as Senhas</span>
                        </div>
                        <span class="category-count"><?= $categoria_counts['todas'] ?></span>
                    </div>
                </li>

                <!-- Sem categoria -->
                <li class="category-item">
                    <div class="category-link" data-category="sem_categoria" onclick="filterByCategory('sem_categoria')">
                        <div class="category-info">
                            <i class="fas fa-question-circle"></i>
                            <span>Sem Categoria</span>
                        </div>
                        <span class="category-count"><?= $categoria_counts['sem_categoria'] ?></span>
                    </div>
                </li>

                <!-- Categorias do usuário -->
                <?php foreach ($categorias as $categoria): ?>
                <li class="category-item">
                    <div class="category-link" data-category="<?= $categoria['id_categoria'] ?>" onclick="filterByCategory('<?= $categoria['id_categoria'] ?>')">
                        <div class="category-info">
                            <div class="category-color" style="background-color: #<?= $categoria['cor'] ?>"></div>
                            <span><?= htmlspecialchars($categoria['tipo']) ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="category-count"><?= $categoria_counts[$categoria['id_categoria']] ?? 0 ?></span>
                            <div class="category-actions">
                                <button class="btn-category-action" onclick="editCategory(<?= $categoria['id_categoria'] ?>, '<?= htmlspecialchars($categoria['tipo']) ?>', '<?= $categoria['cor'] ?>')" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-category-action" onclick="deleteCategory(<?= $categoria['id_categoria'] ?>, '<?= htmlspecialchars($categoria['tipo']) ?>')" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="add-category-section">
                <button class="btn-yellow" onclick="openCategoryModal()" style="width: 100%; margin-bottom: 15px;">
                    <i class="fas fa-plus"></i> Nova Categoria
                </button>
            </div>

            <!-- Nova seção de usuário -->
            <div class="user-section" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #ecf0f1;">
                <button class="btn-info" onclick="window.location.href='editar_usuario.php'" style="width: 100%; margin-bottom: 10px;">
                    <i class="fas fa-user-edit"></i> Editar Perfil
                </button>
                <button class="btn-danger" onclick="confirmLogout()" style="width: 100%;">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </button>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-shield-alt"></i> Gerenciador de Senhas</h1>
                <p>Mantenha suas senhas seguras e organizadas</p>
            </div>

            <div class="content">
                <?php if ($mensagem): ?>
                    <div class="alert alert-<?= $mensagem['tipo'] == 'success' ? 'success' : 'error' ?>">
                        <i class="fas fa-<?= $mensagem['tipo'] == 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                        <?= $mensagem['texto'] ?>
                    </div>
                <?php endif; ?>

                <!-- Seção para adicionar nova senha -->
                <div class="add-password-section">
                    <button class="btn-primary" onclick="openPasswordModal()">
                        <i class="fas fa-plus"></i> Cadastrar Nova Senha
                    </button>
                </div>

                <!-- Header da categoria atual -->
                <div class="category-header">
                    <h2 class="category-title" id="currentCategoryTitle">
                        <i class="fas fa-th-list"></i> 
                        <span>Todas as Senhas</span>
                        <span class="category-count" id="currentCategoryCount"><?= $categoria_counts['todas'] ?></span>
                    </h2>
                </div>

                <!-- Lista de senhas -->
                <div class="passwords-list" id="passwordsList">
                    <?php if (empty($senhas)): ?>
                        <!-- Estado vazio -->
                        <div class="empty-state">
                            <i class="fas fa-key"></i>
                            <h3>Nenhuma senha cadastrada</h3>
                            <p>Clique em "Cadastrar Nova Senha" para adicionar sua primeira senha.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($senhas as $senha): ?>
                            <div class="password-item" data-password-id="<?= $senha['id_senha'] ?>" data-category="<?= $senha['id_categoria'] ?: 'sem_categoria' ?>" style="border-left-color: <?= $senha['categoria_cor'] ? '#' . $senha['categoria_cor'] : '#95a5a6' ?>;">
                                <div class="password-header">
                                    <div>
                                        <div class="password-name">
                                            <?= htmlspecialchars($senha['nome']) ?>
                                            <?php if ($senha['categoria_nome']): ?>
                                                <span class="category-badge" style="background-color: #<?= $senha['categoria_cor'] ?: '3498db' ?>;">
                                                    <?= htmlspecialchars($senha['categoria_nome']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="category-badge" style="background-color: #95a5a6;">
                                                    Sem Categoria
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="password-details">
                                            <span class="password-site">
                                                <i class="fas fa-globe"></i> <?= htmlspecialchars($senha['site_origem']) ?>
                                            </span>
                                            <span style="margin-left: 15px;">
                                                <i class="fas fa-user"></i> <?= htmlspecialchars($senha['usuario_origem']) ?>
                                            </span>
                                            <span style="margin-left: 15px; font-size: 0.8rem;">
                                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($senha['data_criacao'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button class="btn-info btn-show-password" onclick="showPassword(<?= $senha['id_senha'] ?>, '<?= htmlspecialchars($senha['senha'], ENT_QUOTES) ?>')">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                        <button class="btn-secondary" onclick="editPassword(<?= $senha['id_senha'] ?>)">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <button class="btn-danger" onclick="deletePassword(<?= $senha['id_senha'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cadastro/edição de senha -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePasswordModal()">&times;</span>
            <h2 id="modalTitle"><i class="fas fa-key"></i> Cadastrar Nova Senha</h2>
            
            <form id="passwordForm" method="POST" action="./processos/processar_senha.php">
                <input type="hidden" id="passwordId" name="id_senha" value="">
                <input type="hidden" name="identificador" value="<?= $usuario_logado_id ?>">
                
                <div class="form-group">
                    <label for="nome"><i class="fas fa-tag"></i> Nome da Senha</label>
                    <input type="text" id="nome" name="nome" required placeholder="Ex: Gmail Pessoal, Facebook, etc.">
                </div>

                <div class="form-group">
                    <label for="site_origem"><i class="fas fa-globe"></i> Site/Origem</label>
                    <input type="text" id="site_origem" name="site_origem" required placeholder="Ex: gmail.com, facebook.com">
                </div>

                <div class="form-group">
                    <label for="usuario_origem"><i class="fas fa-user"></i> Usuário/Email</label>
                    <input type="text" id="usuario_origem" name="usuario_origem" required placeholder="Seu usuário ou email">
                </div>

                <div class="form-group">
                    <label for="senha"><i class="fas fa-lock"></i> Senha</label>
                    <div class="password-input-group">
                        <input type="password" id="senha" name="senha" required placeholder="Digite a senha">
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('senha')"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_categoria"><i class="fas fa-folder"></i> Categoria</label>
                    <select id="id_categoria" name="id_categoria">
                        <option value="">Sem categoria</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['id_categoria'] ?>">
                                <?= htmlspecialchars($categoria['tipo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closePasswordModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-success">
                        <i class="fas fa-save"></i> Salvar Senha
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para cadastro/edição de categoria -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCategoryModal()">&times;</span>
            <h2 id="categoryModalTitle"><i class="fas fa-folder"></i> Nova Categoria</h2>
            
            <form id="categoryForm" method="POST" action="./processos/processar_categoria.php">
                <input type="hidden" id="categoryId" name="id_categoria" value="">
                <input type="hidden" name="identificador" value="<?= $usuario_logado_id ?>">
                
                <div class="form-group">
                    <label for="categoria_nome"><i class="fas fa-tag"></i> Nome da Categoria</label>
                    <input type="text" id="categoria_nome" name="tipo" required placeholder="Ex: Trabalho, Pessoal, Estudos">
                </div>

                <div class="form-group">
                    <label for="categoria_descricao"><i class="fas fa-align-left"></i> Descrição (opcional)</label>
                    <textarea id="categoria_descricao" name="descricao" placeholder="Descrição da categoria" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-palette"></i> Cor da Categoria</label>
                    <input type="hidden" id="categoria_cor" name="cor" value="3498db">
                    <div class="color-picker-grid">
                        <div class="color-option selected" style="background-color: #3498db" data-color="3498db"></div>
                        <div class="color-option" style="background-color: #e74c3c" data-color="e74c3c"></div>
                        <div class="color-option" style="background-color: #2ecc71" data-color="2ecc71"></div>
                        <div class="color-option" style="background-color: #f39c12" data-color="f39c12"></div>
                        <div class="color-option" style="background-color: #9b59b6" data-color="9b59b6"></div>
                        <div class="color-option" style="background-color: #1abc9c" data-color="1abc9c"></div>
                        <div class="color-option" style="background-color: #34495e" data-color="34495e"></div>
                        <div class="color-option" style="background-color: #95a5a6" data-color="95a5a6"></div>
                        <div class="color-option" style="background-color: #e67e22" data-color="e67e22"></div>
                        <div class="color-option" style="background-color: #16a085" data-color="16a085"></div>
                        <div class="color-option" style="background-color: #27ae60" data-color="27ae60"></div>
                        <div class="color-option" style="background-color: #2980b9" data-color="2980b9"></div>
                        <div class="color-option" style="background-color: #8e44ad" data-color="8e44ad"></div>
                        <div class="color-option" style="background-color: #c0392b" data-color="c0392b"></div>
                        <div class="color-option" style="background-color: #d35400" data-color="d35400"></div>
                        <div class="color-option" style="background-color: #7f8c8d" data-color="7f8c8d"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeCategoryModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-success">
                        <i class="fas fa-save"></i> Salvar Categoria
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Função para abrir o modal de cadastro
        function openPasswordModal(isEdit = false, passwordData = null) {
            const modal = document.getElementById('passwordModal');
            const modalTitle = document.getElementById('modalTitle');
            const form = document.getElementById('passwordForm');
            
            if (isEdit && passwordData) {
                modalTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Senha';
                // Preencher campos com dados existentes
                document.getElementById('passwordId').value = passwordData.id_senha;
                document.getElementById('nome').value = passwordData.nome;
                document.getElementById('site_origem').value = passwordData.site_origem;
                document.getElementById('usuario_origem').value = passwordData.usuario_origem;
                document.getElementById('senha').value = passwordData.senha;
                document.getElementById('id_categoria').value = passwordData.id_categoria || '';
            } else {
                modalTitle.innerHTML = '<i class="fas fa-plus"></i> Cadastrar Nova Senha';
                form.reset();
                document.getElementById('passwordId').value = '';
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Função para fechar o modal
        function closePasswordModal() {
            const modal = document.getElementById('passwordModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Função para editar senha
        function editPassword(id) {
            // Fazer requisição AJAX para buscar dados da senha
            fetch(`./processos/buscar_senha.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        openPasswordModal(true, data.senha);
                    } else {
                        alert('Erro ao carregar dados da senha.');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar dados da senha.');
                });
        }
        
        // Objeto para armazenar os timers ativos
        const activeTimers = {};

        // Função para mostrar/ocultar senha
        function showPassword(id, senha) {
            const passwordItem = document.querySelector(`[data-password-id="${id}"]`);
            const showButton = passwordItem.querySelector('.btn-show-password');
            
            // Verificar se já existe uma senha sendo exibida
            const existingDisplay = passwordItem.querySelector('.password-display');
            
            if (existingDisplay) {
                // Se já está mostrando, ocultar a senha
                hidePassword(id);
                return;
            }
            
            // Criar elemento para mostrar a senha
            const passwordDisplay = document.createElement('div');
            passwordDisplay.className = 'password-display';
            passwordDisplay.innerHTML = `
                <i class="fas fa-key"></i> 
                <strong>Senha:</strong> ${senha}
                <span style="float: right; font-size: 12px; opacity: 0.7;">
                    <i class="fas fa-clock"></i> Oculta em <span id="countdown-${id}">10</span>s
                </span>
            `;
            
            // Adicionar após os detalhes da senha
            const passwordDetails = passwordItem.querySelector('.password-details');
            passwordDetails.parentNode.insertBefore(passwordDisplay, passwordDetails.nextSibling);
            
            // Atualizar botão para modo "ocultar"
            showButton.innerHTML = '<i class="fas fa-eye-slash"></i> Ocultar';
            showButton.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
            
            // Iniciar countdown
            let countdown = 10;
            const countdownElement = document.getElementById(`countdown-${id}`);
            
            const timer = setInterval(() => {
                countdown--;
                if (countdownElement) {
                    countdownElement.textContent = countdown;
                }
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    delete activeTimers[id];
                    hidePassword(id);
                }
            }, 1000);
            
            // Armazenar o timer para poder cancelá-lo depois
            activeTimers[id] = timer;
        }

        // Função para ocultar senha
        function hidePassword(id) {
            const passwordItem = document.querySelector(`[data-password-id="${id}"]`);
            const showButton = passwordItem.querySelector('.btn-show-password');
            const passwordDisplay = passwordItem.querySelector('.password-display');
            
            if (!passwordDisplay) return;
            
            // Cancelar timer se existir
            if (activeTimers[id]) {
                clearInterval(activeTimers[id]);
                delete activeTimers[id];
            }
            
            // Remover display da senha com animação
            passwordDisplay.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            passwordDisplay.style.opacity = '0';
            passwordDisplay.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                if (passwordDisplay.parentNode) {
                    passwordDisplay.parentNode.removeChild(passwordDisplay);
                }
            }, 300);
            
            // Restaurar botão para modo "mostrar"
            showButton.innerHTML = '<i class="fas fa-eye"></i> Ver';
            showButton.style.background = 'linear-gradient(135deg, #3498db, #2980b9)';
        }

        // Função para deletar senha
        function deletePassword(id) {
            if (confirm('Tem certeza que deseja excluir esta senha? Esta ação não pode ser desfeita.')) {
                fetch('./processos/deletar_senha.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({id_senha: id})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro ao excluir senha: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao excluir senha.');
                });
            }
        }

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

        // Fechar modal ao clicar fora dele (mas não nos botões)
        window.onclick = function(event) {
            const modal = document.getElementById('passwordModal');
            const modalContent = document.querySelector('.modal-content');
            
            if (event.target === modal && !modalContent.contains(event.target)) {
                closePasswordModal();
            }
        }

        // Fechar modal com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePasswordModal();
            }
        });

        // Validação do formulário
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const nome = document.getElementById('nome').value.trim();
            const site = document.getElementById('site_origem').value.trim();
            const usuario = document.getElementById('usuario_origem').value.trim();
            const senha = document.getElementById('senha').value.trim();

            if (!nome || !site || !usuario || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
                return false;
            }
        });
        // Auto-hide de mensagens após 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
        // JavaScript corrigido para debug das categorias
        // === FUNÇÃO DE LOGOUT ===
        
        function confirmLogout() {
            const confirmation = confirm('Tem certeza que deseja sair?');
            if (confirmation) {
                // Mostrar loading
                const logoutButton = document.querySelector('.btn-danger');
                if (logoutButton) {
                    logoutButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saindo...';
                    logoutButton.style.pointerEvents = 'none';
                }
                
                // Redirecionar para logout
                window.location.href = 'logout.php';
            }
        }

        // === FIM FUNÇÃO DE LOGOUT ===
        // Funções do modal de categoria (corrigidas)
        function openCategoryModal(isEdit = false, categoryData = null) {
            console.log('openCategoryModal chamada:', { isEdit, categoryData });
            
            const modal = document.getElementById('categoryModal');
            const modalTitle = document.getElementById('categoryModalTitle');
            const form = document.getElementById('categoryForm');
            
            if (!modal) {
                console.error('Modal de categoria não encontrado!');
                return;
            }
            
            if (isEdit && categoryData) {
                console.log('Modo edição ativado');
                modalTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Categoria';
                document.getElementById('categoryId').value = categoryData.id;
                document.getElementById('categoria_nome').value = categoryData.nome;
                document.getElementById('categoria_descricao').value = categoryData.descricao || '';
                document.getElementById('categoria_cor').value = categoryData.cor;
                
                // Atualizar seleção de cor
                document.querySelectorAll('.color-option').forEach(option => {
                    option.classList.remove('selected');
                    if (option.dataset.color === categoryData.cor) {
                        option.classList.add('selected');
                    }
                });
            } else {
                console.log('Modo criação ativado');
                modalTitle.innerHTML = '<i class="fas fa-plus"></i> Nova Categoria';
                form.reset();
                document.getElementById('categoryId').value = '';
                document.getElementById('categoria_cor').value = '3498db';
                
                // Reset da seleção de cor
                document.querySelectorAll('.color-option').forEach(option => {
                    option.classList.remove('selected');
                });
                const defaultOption = document.querySelector('.color-option[data-color="3498db"]');
                if (defaultOption) {
                    defaultOption.classList.add('selected');
                }
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            console.log('Modal aberto');
        }

        function closeCategoryModal() {
            console.log('closeCategoryModal chamada');
            const modal = document.getElementById('categoryModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                console.log('Modal fechado');
            }
        }

        function editCategory(id, nome, cor) {
            console.log('editCategory chamada:', { id, nome, cor });
            
            // Fazer requisição para buscar dados completos da categoria
            fetch(`./processos/buscar_categoria.php?id=${id}`)
                .then(response => {
                    console.log('Resposta buscar categoria:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Dados da categoria:', data);
                    if (data.success) {
                        openCategoryModal(true, {
                            id: data.categoria.id_categoria,
                            nome: data.categoria.tipo,
                            cor: data.categoria.cor,
                            descricao: data.categoria.descricao
                        });
                    } else {
                        // Fallback - usar dados básicos se a busca falhar
                        openCategoryModal(true, {
                            id: id,
                            nome: nome,
                            cor: cor,
                            descricao: ''
                        });
                    }
                })
                .catch(error => {
                    console.warn('Erro ao buscar categoria, usando dados básicos:', error);
                    // Fallback - usar dados básicos
                    openCategoryModal(true, {
                        id: id,
                        nome: nome,
                        cor: cor,
                        descricao: ''
                    });
                });
        }

        function deleteCategory(id, nome) {
            console.log('deleteCategory chamada:', { id, nome });
            
            if (confirm(`Tem certeza que deseja excluir a categoria "${nome}"?\n\nAs senhas desta categoria ficarão sem categoria.`)) {
                console.log('Exclusão confirmada, enviando requisição...');
                
                fetch('./processos/deletar_categoria.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id_categoria: parseInt(id) })
                })
                .then(response => {
                    console.log('Resposta delete:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Resultado delete:', data);
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Erro ao excluir categoria: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição de exclusão:', error);
                    alert('Erro ao excluir categoria. Verifique o console para detalhes.');
                });
            }
        }

        // Event listeners para seleção de cor (corrigido)
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregado, configurando event listeners...');
            
            // Configurar seletores de cor
            const colorOptions = document.querySelectorAll('.color-option');
            console.log('Opções de cor encontradas:', colorOptions.length);
            
            colorOptions.forEach((option, index) => {
                option.addEventListener('click', function() {
                    console.log(`Cor selecionada: ${this.dataset.color}`);
                    
                    // Remove seleção anterior
                    colorOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Adiciona seleção atual
                    this.classList.add('selected');
                    
                    // Atualiza valor do input hidden
                    const corInput = document.getElementById('categoria_cor');
                    if (corInput) {
                        corInput.value = this.dataset.color;
                        console.log('Input cor atualizado para:', this.dataset.color);
                    } else {
                        console.error('Input categoria_cor não encontrado!');
                    }
                });
            });
            
            // Validação do formulário de categoria
            const categoryForm = document.getElementById('categoryForm');
            if (categoryForm) {
                categoryForm.addEventListener('submit', function(e) {
                    console.log('Formulário de categoria submetido');
                    
                    const nome = document.getElementById('categoria_nome').value.trim();
                    const cor = document.getElementById('categoria_cor').value;
                    
                    console.log('Dados do formulário:', { nome, cor });
                    
                    if (!nome) {
                        e.preventDefault();
                        alert('Por favor, insira o nome da categoria.');
                        console.log('Validação falhou: nome vazio');
                        return false;
                    }
                    
                    if (!cor || !cor.match(/^[a-fA-F0-9]{6}$/)) {
                        console.warn('Cor inválida, usando padrão');
                        document.getElementById('categoria_cor').value = '3498db';
                    }
                    
                    console.log('Formulário validado, enviando...');
                });
            } else {
                console.error('Formulário de categoria não encontrado!');
            }
            
            // Debug: verificar se todos os elementos necessários existem
            const elementos = {
                'categoryModal': document.getElementById('categoryModal'),
                'categoryForm': document.getElementById('categoryForm'),
                'categoria_nome': document.getElementById('categoria_nome'),
                'categoria_cor': document.getElementById('categoria_cor'),
                'categoryId': document.getElementById('categoryId')
            };
            
            console.log('Verificação de elementos:', elementos);
            
            Object.entries(elementos).forEach(([nome, elemento]) => {
                if (!elemento) {
                    console.error(`Elemento ${nome} não encontrado!`);
                }
            });
        });

        // Event listeners globais (atualizados)
        window.onclick = function(event) {
            const passwordModal = document.getElementById('passwordModal');
            const categoryModal = document.getElementById('categoryModal');
            
            if (event.target === passwordModal) {
                closePasswordModal();
            }
            if (event.target === categoryModal) {
                closeCategoryModal();
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePasswordModal();
                closeCategoryModal();
            }
        });

        // Função de teste para verificar se tudo funciona
        function testarCategorias() {
            console.log('=== TESTE DE CATEGORIAS ===');
            console.log('1. Testando abertura de modal...');
            openCategoryModal();
            
            setTimeout(() => {
                console.log('2. Testando fechamento de modal...');
                closeCategoryModal();
            }, 2000);
        }
        // === FILTRO DE CATEGORIAS ===
        
        // Função para filtrar senhas por categoria
        function filterByCategory(categoriaId) {
            console.log('Filtrando por categoria:', categoriaId);
            
            // Atualizar estado ativo da sidebar
            updateSidebarActiveState(categoriaId);
            
            // Buscar todos os itens de senha
            const passwordItems = document.querySelectorAll('.password-item');
            const categoryTitle = document.getElementById('currentCategoryTitle');
            const categoryCount = document.getElementById('currentCategoryCount');
            
            let visibleCount = 0;
            let categoryName = '';
            let categoryIcon = '';
            
            // Definir nome e ícone da categoria
            switch (categoriaId) {
                case 'todas':
                    categoryName = 'Todas as Senhas';
                    categoryIcon = 'fas fa-th-list';
                    break;
                case 'sem_categoria':
                    categoryName = 'Sem Categoria';
                    categoryIcon = 'fas fa-question-circle';
                    break;
                default:
                    // Buscar nome da categoria específica
                    const categoryLink = document.querySelector(`[data-category="${categoriaId}"]`);
                    if (categoryLink) {
                        const categoryText = categoryLink.querySelector('.category-info span');
                        categoryName = categoryText ? categoryText.textContent : `Categoria ${categoriaId}`;
                        categoryIcon = 'fas fa-folder';
                    } else {
                        categoryName = `Categoria ${categoriaId}`;
                        categoryIcon = 'fas fa-folder';
                    }
                    break;
            }
            
            // Filtrar e mostrar/ocultar itens
            passwordItems.forEach(item => {
                const itemCategory = item.getAttribute('data-category');
                let shouldShow = false;
                
                if (categoriaId === 'todas') {
                    shouldShow = true;
                } else if (categoriaId === 'sem_categoria') {
                    shouldShow = (itemCategory === 'sem_categoria' || itemCategory === '' || itemCategory === null);
                } else {
                    shouldShow = (itemCategory === categoriaId);
                }
                
                if (shouldShow) {
                    item.style.display = 'block';
                    item.style.animation = 'fadeIn 0.3s ease-in';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Atualizar título e contador
            if (categoryTitle) {
                categoryTitle.innerHTML = `
                    <i class="${categoryIcon}"></i> 
                    <span>${categoryName}</span>
                    <span class="category-count" id="currentCategoryCount">${visibleCount}</span>
                `;
            }
            
            // Verificar se não há senhas para mostrar
            checkEmptyState(visibleCount, categoryName);
        }

        // Função para atualizar estado ativo da sidebar
        function updateSidebarActiveState(activeCategoryId) {
            // Remover classe active de todos os links
            const allCategoryLinks = document.querySelectorAll('.category-link');
            allCategoryLinks.forEach(link => {
                link.classList.remove('active');
            });
            
            // Adicionar classe active ao link clicado
            const activeLink = document.querySelector(`[data-category="${activeCategoryId}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }

        // Função para verificar estado vazio
        function checkEmptyState(visibleCount, categoryName) {
            const passwordsList = document.getElementById('passwordsList');
            
            // Remover estado vazio existente
            const existingEmptyState = passwordsList.querySelector('.empty-state');
            if (existingEmptyState) {
                existingEmptyState.remove();
            }
            
            // Se não há senhas visíveis, mostrar estado vazio
            if (visibleCount === 0) {
                const emptyStateHtml = `
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>Nenhuma senha encontrada</h3>
                        <p>Não há senhas na categoria "${categoryName}".</p>
                    </div>
                `;
                passwordsList.insertAdjacentHTML('beforeend', emptyStateHtml);
            }
        }

        // === FIM FILTRO DE CATEGORIAS ===

        // Para chamar no console: testarCategorias()
        window.testarCategorias = testarCategorias;
    </script>
</body>
</html>