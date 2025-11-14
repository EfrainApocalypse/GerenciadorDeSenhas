<?php
// config.php - Configurações do banco de dados
$host = "localhost";
$user = "efrain";     // ajuste se necessário
$pass = "1234";         // ajuste se necessário
$db   = "gerenciador_senhas";
$chave_segredo = "chave-secreta"; // Chave para criptografia AES

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para mostrar mensagens de feedback
function mostrarMensagem($codigo) {
    $mensagens = [
        // Mensagens de sucesso - Senhas
        'senha_cadastrada' => [
            'tipo' => 'success',
            'texto' => 'Senha cadastrada com sucesso!'
        ],
        'senha_atualizada' => [
            'tipo' => 'success',
            'texto' => 'Senha atualizada com sucesso!'
        ],
        'senha_excluida' => [
            'tipo' => 'success',
            'texto' => 'Senha excluída com sucesso!'
        ],
        
        // Mensagens de sucesso - Categorias
        'categoria_criada' => [
            'tipo' => 'success',
            'texto' => 'Categoria criada com sucesso!'
        ],
        'categoria_atualizada' => [
            'tipo' => 'success',
            'texto' => 'Categoria atualizada com sucesso!'
        ],
        'categoria_excluida' => [
            'tipo' => 'success',
            'texto' => 'Categoria excluída com sucesso!'
        ],
        
        // Mensagens de sucesso - Autenticação
        'cadastro_sucesso' => [
            'tipo' => 'success',
            'texto' => 'Cadastro realizado com sucesso! Faça login para acessar.'
        ],
        'login_sucesso' => [
            'tipo' => 'success',
            'texto' => 'Login realizado com sucesso!'
        ],
        'logout_sucesso' => [
            'tipo' => 'success',
            'texto' => 'Logout realizado com sucesso!'
        ],
        
        // Mensagens de erro - Autenticação
        'usuario_nao_logado' => [
            'tipo' => 'error',
            'texto' => 'Você precisa estar logado para acessar esta página.'
        ],
        'sessao_expirada' => [
            'tipo' => 'error',
            'texto' => 'Sua sessão expirou. Faça login novamente.'
        ],
        'acesso_negado' => [
            'tipo' => 'error',
            'texto' => 'Acesso negado. Você não tem permissão para esta ação.'
        ],
        'login_invalido' => [
            'tipo' => 'error',
            'texto' => 'E-mail ou senha incorretos.'
        ],
        'conta_desativada' => [
            'tipo' => 'error',
            'texto' => 'Conta desativada. Entre em contato com o administrador.'
        ],
        
        // Mensagens de erro - Cadastro
        'email_ja_existe' => [
            'tipo' => 'error',
            'texto' => 'E-mail já cadastrado. Use outro e-mail ou faça login.'
        ],
        'email_invalido' => [
            'tipo' => 'error',
            'texto' => 'E-mail inválido.'
        ],
        'senha_fraca' => [
            'tipo' => 'error',
            'texto' => 'A senha deve conter: pelo menos 8 caracteres, uma letra maiúscula, uma minúscula, um número e um caractere especial.'
        ],
        'senhas_nao_coincidem' => [
            'tipo' => 'error',
            'texto' => 'As senhas não coincidem.'
        ],
        'nome_muito_curto' => [
            'tipo' => 'error',
            'texto' => 'Nome deve ter pelo menos 2 caracteres.'
        ],
        'nome_muito_longo' => [
            'tipo' => 'error',
            'texto' => 'Nome deve ter no máximo 250 caracteres.'
        ],
        'email_muito_longo' => [
            'tipo' => 'error',
            'texto' => 'E-mail deve ter no máximo 150 caracteres.'
        ],
        'campos_obrigatorios_cadastro' => [
            'tipo' => 'error',
            'texto' => 'Por favor, preencha todos os campos.'
        ],
        'erro_cadastro' => [
            'tipo' => 'error',
            'texto' => 'Erro ao realizar cadastro. Tente novamente.'
        ],
        
        // Mensagens de erro - Métodos
        'metodo_invalido' => [
            'tipo' => 'error',
            'texto' => 'Método de requisição inválido.'
        ],
        
        // Mensagens de erro - Senhas
        'senha_nao_encontrada' => [
            'tipo' => 'error',
            'texto' => 'Senha não encontrada ou você não tem permissão para acessá-la.'
        ],
        'erro_ao_salvar_senha' => [
            'tipo' => 'error',
            'texto' => 'Erro ao salvar a senha. Tente novamente.'
        ],
        'erro_ao_atualizar_senha' => [
            'tipo' => 'error',
            'texto' => 'Erro ao atualizar a senha. Tente novamente.'
        ],
        'erro_ao_excluir_senha' => [
            'tipo' => 'error',
            'texto' => 'Erro ao excluir a senha. Tente novamente.'
        ],
        'campos_obrigatorios_senha' => [
            'tipo' => 'error',
            'texto' => 'Preencha todos os campos obrigatórios da senha.'
        ],
        'nome_senha_muito_longo' => [
            'tipo' => 'error',
            'texto' => 'Nome da senha deve ter no máximo 150 caracteres.'
        ],
        'site_muito_longo' => [
            'tipo' => 'error',
            'texto' => 'Site/origem deve ter no máximo 250 caracteres.'
        ],
        'usuario_muito_longo' => [
            'tipo' => 'error',
            'texto' => 'Usuário/email deve ter no máximo 200 caracteres.'
        ],
        
        // Mensagens de erro - Categorias
        'categoria_nao_encontrada' => [
            'tipo' => 'error',
            'texto' => 'Categoria não encontrada ou você não tem permissão para acessá-la.'
        ],
        'categoria_ja_existe' => [
            'tipo' => 'error',
            'texto' => 'Já existe uma categoria com este nome.'
        ],
        'nome_categoria_obrigatorio' => [
            'tipo' => 'error',
            'texto' => 'O nome da categoria é obrigatório.'
        ],
        'nome_categoria_muito_longo' => [
            'tipo' => 'error',
            'texto' => 'O nome da categoria deve ter no máximo 50 caracteres.'
        ],
        'descricao_muito_longa' => [
            'tipo' => 'error',
            'texto' => 'A descrição deve ter no máximo 255 caracteres.'
        ],
        'erro_ao_criar_categoria' => [
            'tipo' => 'error',
            'texto' => 'Erro ao criar a categoria. Tente novamente.'
        ],
        'erro_ao_atualizar_categoria' => [
            'tipo' => 'error',
            'texto' => 'Erro ao atualizar a categoria. Tente novamente.'
        ],
        'erro_ao_excluir_categoria' => [
            'tipo' => 'error',
            'texto' => 'Erro ao excluir a categoria. Tente novamente.'
        ],
        'categoria_possui_senhas' => [
            'tipo' => 'error',
            'texto' => 'Esta categoria possui senhas associadas. Mova-as antes de excluir.'
        ],
        
        // Mensagens de erro - Sistema
        'erro_banco_dados' => [
            'tipo' => 'error',
            'texto' => 'Erro no banco de dados. Tente novamente mais tarde.'
        ],
        'dados_invalidos' => [
            'tipo' => 'error',
            'texto' => 'Dados inválidos enviados.'
        ],
        'operacao_nao_permitida' => [
            'tipo' => 'error',
            'texto' => 'Operação não permitida.'
        ],
        'erro_interno' => [
            'tipo' => 'error',
            'texto' => 'Erro interno do servidor. Contate o administrador.'
        ],
        'campos_obrigatorios' => [
            'tipo' => 'error',
            'texto' => 'Por favor, preencha todos os campos obrigatórios.'
        ]
    ];
    
    return $mensagens[$codigo] ?? [
        'tipo' => 'error',
        'texto' => 'Mensagem não encontrada.'
    ];
}
?>