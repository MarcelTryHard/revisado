<?php
session_start();
require_once 'config.php';

// Se já estiver logado, redireciona baseado no tipo
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_tipo'] == 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: app.php");
    }
    exit();
}

$erro_login = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $mensagem = "Preencha todos os campos!";
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => $mensagem]);
            exit();
        }
        $erro_login = $mensagem;
    } else {
        // Busca o usuário na tabela unificada
        $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            if (password_verify($senha, $usuario['senha'])) {
                // Armazena dados do usuário na sessão
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_tipo'] = $usuario['tipo'];
                
                // Define o redirecionamento baseado no tipo
                if ($usuario['tipo'] == 'admin') {
                    $redirect_page = "admin.php";
                } else {
                    $redirect_page = "app.php";
                }
                
                if ($is_ajax) {
                    echo json_encode(['success' => true, 'message' => 'Login realizado com sucesso!', 'redirect' => $redirect_page]);
                    exit();
                }
                
                header("Location: " . $redirect_page);
                exit();
            }
        }
        
        $mensagem = "Email ou senha inválidos!";
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => $mensagem]);
            exit();
        }
        $erro_login = $mensagem;
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barbearia Elite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <h1>💈 Barbearia Elite</h1>
    <p>Acesse sua conta</p>
</header>

<main>
    <form id="formLogin" method="POST">
        <h2>Login</h2>
        
        <?php if ($erro_login): ?>
            <div style="background: #e74c3c; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 14px;">
                <?php echo htmlspecialchars($erro_login); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] == 'sucesso'): ?>
            <div style="background: #2ecc71; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 14px;">
                Cadastro realizado com sucesso! Faça seu login.
            </div>
        <?php endif; ?>

        <input type="email" id="email" name="email" placeholder="Email" required autocomplete="email">
        <input type="password" id="senha" name="senha" placeholder="Senha" required autocomplete="current-password">

        <button type="submit" id="submitBtn">Entrar</button>

        <p>Não tem conta? <a href="cadastro.php">Cadastrar</a></p>
    </form>
</main>

<div id="toast"></div>

<script>
const form = document.getElementById('formLogin');
const submitBtn = document.getElementById('submitBtn');
const toast = document.getElementById('toast');

form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    const senha = document.getElementById('senha').value;
    
    if (!email || !senha) {
        showToast('Preencha todos os campos!', 'error');
        return;
    }
    
    setButtonLoading(true);
    
    try {
        const formData = new FormData();
        formData.append('email', email);
        formData.append('senha', senha);
        
        const response = await fetch(window.location.href, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showToast(data.message, 'error');
            setButtonLoading(false);
            document.getElementById('senha').value = '';
        }
    } catch (error) {
        showToast('Erro ao processar login.', 'error');
        setButtonLoading(false);
    }
});

function showToast(message, type = 'success') {
    toast.textContent = message;
    toast.className = 'show';
    if (type === 'error') {
        toast.classList.add('error');
    }
    
    setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.remove('error');
    }, 3000);
}

function setButtonLoading(loading) {
    if (loading) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading-spinner"></span> Entrando...';
    } else {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Entrar';
    }
}
</script>

<style>
.loading-spinner {
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 2px solid #fff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 0.6s linear infinite;
    margin-right: 5px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

#toast {
    visibility: hidden;
    min-width: 250px;
    background-color: #2ecc71;
    color: #fff;
    text-align: center;
    border-radius: 8px;
    padding: 12px;
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s, visibility 0.3s;
}

#toast.show {
    visibility: visible;
    opacity: 1;
}

#toast.error {
    background-color: #e74c3c;
}
</style>

</body>
</html>