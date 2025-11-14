# Gerenciador de Senhas — Documentação do Projeto

Este documento descreve a arquitetura, objetivos, funcionamento e detalhes técnicos do Gerenciador de Senhas desenvolvido como projeto escolar. O sistema foi implementado em **HTML**, **CSS**, **JavaScript** e **PHP**, realizando operações de CRUD de credenciais e utilizando criptografia AES para proteção dos dados armazenados.

---

## 1. Objetivo do Projeto
O objetivo do sistema é permitir que usuários realizem cadastro, autenticação e gerenciamento de senhas associadas a diferentes serviços, garantindo sigilo por meio de criptografia simétrica no lado do servidor.

---

## 2. Estrutura do Projeto
A seguir, a organização do diretório conforme o código fornecido:
```
3o bim/
├── cadastro.php
├── db.php
├── editar_usuario.php
├── gerenciador_senhas.sql
├── index.php
├── login.php
├── logout.php
├── processos/
│   ├── buscar_categoria.php
│   ├── buscar_senha.php
│   ├── deletar_categoria.php
│   ├── deletar_senha.php
│   ├── processar_categoria.php
│   ├── processar_edicao_usuario.php
│   ├── processar_senha.php
│   ├── processa_cadastro.php
│   └── processa_login.php
├── propriedades/
│   └── config.php
└── requires/
    ├── footer.php
    └── header.php
```

### 2.1 Arquivos Principais
- **index.php** — Página principal após login; exibe e organiza as senhas.
- **login.php** e **processa_login.php** — Fluxo de autenticação.
- **cadastro.php** e **processa_cadastro.php** — Registro de novos usuários.
- **editar_usuario.php** e **processar_edicao_usuario.php** — Atualização de dados de usuário.
- **db.php** — Conexão com o banco de dados.
- **config.php** — Propriedades internas do sistema.
- **processar_senha.php**, **buscar_senha.php**, **deletar_senha.php** — CRUD de senhas.
- **gerenciador_senhas.sql** — Estrutura de tabelas.

### 2.2 Estrutura de Banco de Dados
O banco contém tabelas para:
- Usuários
- Categorias
- Senhas (com campos criptografados)

O arquivo `gerenciador_senhas.sql` inclui a criação das tabelas e campos necessários.

---

## 3. Funcionamento da Criptografia
A criptografia utilizada pelo sistema baseia-se no algoritmo **AES**, por meio da biblioteca nativa do PHP (OpenSSL). O fluxo adotado é o seguinte:

1. O servidor recebe a senha a ser armazenada.
2. Um IV (vetor de inicialização) é gerado para cada operação.
3. O conteúdo sensível é criptografado antes de ser salvo no banco.
4. O dado armazenado contém o IV concatenado ao ciphertext, normalmente codificado em base64.
5. Para leitura, o sistema reverte o processo, separando IV e conteúdo cifrado.

O projeto utiliza uma chave definida no servidor (armazenada no arquivo `config.php`).

---

## 4. Fluxos Principais

### 4.1 Autenticação
- O usuário realiza login por `login.php`.
- As credenciais são verificadas em `processa_login.php`.
- Sessões PHP são usadas para manter o estado do usuário autenticado.

### 4.2 Gerenciamento de Senhas
- Adição: `processar_senha.php`
- Edição: `processar_senha.php` com parâmetros modificados
- Exclusão: `deletar_senha.php`
- Listagem e busca: `buscar_senha.php`

### 4.3 Categorias
O sistema também permite criação, edição e exclusão de categorias.

---

## 5. Recursos de IA
Ferramentas de Inteligência Artificial foram utilizadas apenas como apoio no desenvolvimento, especialmente para:
- Geração e revisão de código
- Estruturação da interface
- Revisões e esclarecimentos técnicos

O funcionamento interno do sistema, incluindo criptografia e autenticação, foi implementado manualmente.

---

## 6. Melhorias Futuras
A seguir, sugestões de aprimoramentos viáveis com base na estrutura atual:

### 6.1 Segurança
- Implementação de derivação de chave individual por usuário (PBKDF2 ou Argon2).
- Remoção de chaves estáticas do servidor, substituindo por derivação com base na senha do usuário.
- Hashing forte e padronizado para senhas (ex.: `password_hash`).
- Uso obrigatório de HTTPS.
- Implementação de política de senhas fortes.

### 6.2 Infraestrutura
- Migração da lógica de criptografia para o lado do cliente por meio da Web Crypto API.
- Auditoria de entradas e validações de segurança para evitar SQL Injection e XSS.
- Padronização do projeto em MVC ou arquitetura modular.

### 6.3 Interface e Usabilidade
- Interface mais responsiva.
- Modo escuro.
- Sistema de pesquisa avançada.
- Exportação e importação de senhas (com criptografia).

### 6.4 Funcionalidades Extras
- Registro de logs de acesso.
- Autenticação em duas etapas.
- Suporte a múltiplos dispositivos.

---

## 7. Instalação
1. Configure um servidor PHP com suporte ao OpenSSL.
2. Importe o arquivo `gerenciador_senhas.sql` em seu banco de dados.
3. Ajuste credenciais e parâmetros no arquivo `propriedades/config.php`.
4. Coloque o diretório do projeto em um servidor web compatível e acesse pelo navegador.

---

## 8. Licença
O projeto é distribuído sob **licença livre**, permitindo uso, modificação e distribuição sem restrições.

---

## 9. Autor
Projeto desenvolvido para fins acadêmicos.

