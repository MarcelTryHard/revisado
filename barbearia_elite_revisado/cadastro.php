<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: app.php");
    exit();
}

$erro_cadastro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';
    
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar)) {
        $erro_cadastro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro_cadastro = "Email inválido.";
    } elseif ($senha !== $confirmar) {
        $erro_cadastro = "As senhas não coincidem.";
    } elseif (strlen($senha) < 6) {
        $erro_cadastro = "A senha deve ter pelo <?php
// 1. Inicialização e Segurança
session_start();
require_once 'config.php';

// Redirecionamento se já estiver logado
if (!empty($_SESSION['usuario_id'])) {
    header("Location: app.php");
    exit();
}

// Geração de Token CSRF para o formulário
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erro_cadastro = '';
$sucesso_cadastro = '';

// 2. Processamento do Formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Validação do Token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $erro_cadastro = "Sessão expirada ou requisição inválida. Tente novamente.";
    } else {
        // Sanitização e Captura de Dados
        $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $senha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';
        
        // Validações
        if (empty($nome) || empty($email) || empty($senha) || empty($confirmar)) {
            $erro_cadastro = "Todos os campos são obrigatórios.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro_cadastro = "Por favor, insira um endereço de e-mail válido.";
        } elseif ($senha !== $confirmar) {
            $erro_cadastro = "As senhas digitadas não coincidem.";
        } elseif (strlen($senha) < 6) {
            $erro_cadastro = "A senha deve ter pelo menos 6 caracteres.";
        } else {
            try {
                // Verifica se o e-mail já existe
                $sql_check = "SELECT id FROM usuarios WHERE email = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("s", $email);
                $stmt_check->execute();
                $result = $stmt_check->get_result();
                
                if ($result->num_rows > 0) {
                    $erro_cadastro = "Este e-mail já está em uso. Tente recuperar sua senha ou use outro e-mail.";
                } else {
                    // Hash seguro da senha
                    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                    
                    // Inserção no banco de dados
                    $sql_insert = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bind_param("sss", $nome, $email, $senhaHash);
                    
                    if ($stmt_insert->execute()) {
                        // Regenera o token CSRF após uso bem-sucedido e redireciona
                        unset($_SESSION['csrf_token']);
                        header("Location: login.php?cadastro=sucesso");
                        exit();
                    } else {
                        $erro_cadastro = "Ocorreu um erro ao criar sua conta. Tente novamente.";
                    }
                    $stmt_insert->close();
                }
                $stmt_check->close();
            } catch (Exception $e) {
                // Captura falhas de conexão ou sintaxe SQL
                $erro_cadastro = "Erro interno no servidor. Tente novamente mais tarde.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Barbearia Elite</title>
    <style>
        :root {
            --primary: #d4af37;
            --primary-hover: #b5952f;
            --bg-color: #f4f7f6;
            --text-color: #333;
            --danger: #ff4757;
            --danger-bg: #ffebee;
            --card-bg: #ffffff;
            --input-border: #dfe6e9;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .header-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header-container h1 { margin: 0; color: #2c3e50; font-size: 28px; }
        .header-container p { color: #7f8c8d; margin-top: 5px; }

        .auth-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            border-top: 5px solid var(--primary);
        }

        .auth-card h2 {
            margin-top: 0;
            margin-bottom: 25px;
            text-align: center;
            color: #2c3e50;
            font-size: 22px;
        }

        .alert-error {
            background-color: var(--danger-bg);
            color: var(--danger);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #ffcccb;
            display: none; /* Controlado via JS e PHP */
        }

        .form-group { margin-bottom: 20px; }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
            outline: none;
        }

        .form-control:focus { border-color: var(--primary); }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
        }

        .btn-submit:hover { background-color: var(--primary-hover); }
        .btn-submit:active { transform: scale(0.98); }
        .btn-submit:disabled { background-color: #bdc3c7; cursor: not-allowed; }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
        }

        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="header-container">
    <h1>💈 Barbearia Elite</h1>
    <p>Agende seus cortes com facilidade</p>
</div>

<main class="auth-card">
    <form id="formCadastro" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h2>Criar Nova Conta</h2>
        
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <?php if (!empty($erro_cadastro)): ?>
            <div class="alert-error" style="display: block;">
                ⚠️ <?php echo htmlspecialchars($erro_cadastro); ?>
            </div>
        <?php endif; ?>

        <div id="jsErrorAlert" class="alert-error"></div>

        <div class="form-group">
            <label for="nome">Nome Completo</label>
            <input type="text" id="nome" name="nome" class="form-control" placeholder="Ex: João da Silva" required 
                   value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required 
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" class="form-control" placeholder="Mínimo de 6 caracteres" required>
        </div>

        <div class="form-group">
            <label for="confirmar_senha">Confirmar Senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" placeholder="Repita sua senha" required>
        </div>

        <button type="submit" id="btnSubmit" class="btn-submit">Cadastrar</button>

        <div class="auth-footer">
            Já tem uma conta? <a href="login.php">Fazer Login</a>
        </div>
    </form>
</main>

<script>
document.getElementById('formCadastro').addEventListener('submit', function(e) {
    const senha = document.getElementById('senha').value;
    const confirmar = document.getElementById('confirmar_senha').value;
    const btnSubmit = document.getElementById('btnSubmit');
    const jsErrorAlert = document.getElementById('jsErrorAlert');
    
    // Resetando erros prévios do JS
    jsErrorAlert.style.display = 'none';
    jsErrorAlert.innerText = '';

    // Validação de Tamanho
    if (senha.length < 6) {
        e.preventDefault();
        mostrarErro('A senha deve ter pelo menos 6 caracteres.');
        return;
    }

    // Validação de Coincidência
    if (senha !== confirmar) {
        e.preventDefault();
        mostrarErro('As senhas digitadas não coincidem.');
        return;
    }
    
    // Prevenção de duplo clique (Double Submit)
    btnSubmit.disabled = true;
    btnSubmit.innerText = 'Cadastrando...';
});

function mostrarErro(mensagem) {
    const jsErrorAlert = document.getElementById('jsErrorAlert');
    jsErrorAlert.innerText = `⚠️ ${mensagem}`;
    jsErrorAlert.style.display = 'block';
    
    // Opcional: Oculta o alerta gerado pelo PHP se o JS encontrar um erro antes de enviar
    const phpAlert = document.querySelector('.alert-error:not(#jsErrorAlert)');
    if(phpAlert) phpAlert.style.display = 'none';
}
</script>

</body>
</html>
