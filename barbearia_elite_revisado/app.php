<?php
// 1. Inicialização e Segurança
session_start();
require_once 'config.php';

// Redirecionamento de segurança
if (empty($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Token CSRF para requisições AJAX
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Dicionário de Serviços (Centralizado para evitar repetição no código)
$servicos_disponiveis = [
    'corte'       => '💇 Corte de cabelo - R$ 40,00',
    'barba'       => '🧔 Barba - R$ 30,00',
    'corte_barba' => '💈 Corte + Barba - R$ 60,00',
    'combo'       => '⭐ Combo Premium - R$ 80,00'
];

// 2. API / Backend (Ações AJAX)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['acao'])) {
    header('Content-Type: application/json');
    
    // Validação do Token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Sessão expirada. Recarregue a página.']);
        exit();
    }
    
    $acao = $_POST['acao'];
    $usuario_id = filter_var($_SESSION['usuario_id'], FILTER_VALIDATE_INT);
    
    try {
        if ($acao === 'agendar') {
            // Sanitização de Entradas
            $cliente_nome = trim(filter_input(INPUT_POST, 'cliente_nome', FILTER_SANITIZE_SPECIAL_CHARS));
            $cliente_email = trim(filter_input(INPUT_POST, 'cliente_email', FILTER_SANITIZE_EMAIL));
            $cliente_telefone = trim(filter_input(INPUT_POST, 'cliente_telefone', FILTER_SANITIZE_SPECIAL_CHARS));
            $servico = array_key_exists($_POST['servico'], $servicos_disponiveis) ? $_POST['servico'] : '';
            $data = trim($_POST['data'] ?? '');
            $horario = trim($_POST['horario'] ?? '');
            
            if (empty($cliente_nome) || empty($cliente_email) || empty($cliente_telefone) || empty($servico) || empty($data) || empty($horario)) {
                echo json_encode(['success' => false, 'message' => 'Por favor, preencha todos os campos corretamente!']);
                exit();
            }
            
            $sql = "INSERT INTO agendamentos (usuario_id, cliente_nome, cliente_email, cliente_telefone, servico, data, horario) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssss", $usuario_id, $cliente_nome, $cliente_email, $cliente_telefone, $servico, $data, $horario);
            
            if ($stmt->execute()) {
                $novo_id = $stmt->insert_id;
                
                // Retornamos os dados formatados para o JS montar o card na tela sem recarregar
                $data_formatada = date('d/m/Y', strtotime($data));
                $servico_nome = $servicos_disponiveis[$servico];
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Agendamento realizado com sucesso!',
                    'agendamento' => [
                        'id' => $novo_id,
                        'cliente_nome' => $cliente_nome,
                        'cliente_telefone' => $cliente_telefone,
                        'servico_nome' => $servico_nome,
                        'data_formatada' => $data_formatada,
                        'horario' => $horario
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao agendar. Tente novamente.']);
            }
            $stmt->close();
            exit();
        }
        
        if ($acao === 'cancelar') {
            $agendamento_id = filter_input(INPUT_POST, 'agendamento_id', FILTER_VALIDATE_INT) ?: 0;
            
            $sql = "UPDATE agendamentos SET status = 'cancelado' WHERE id = ? AND usuario_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $agendamento_id, $usuario_id);
            
            $sucesso = $stmt->execute() && $stmt->affected_rows > 0;
            
            echo json_encode([
                'success' => $sucesso, 
                'message' => $sucesso ? 'Agendamento cancelado!' : 'Erro ao cancelar ou registro não encontrado.'
            ]);
            $stmt->close();
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
        exit();
    }
}

// 3. Consultas para a View (Frontend)
$agendamentos = [];
try {
    $sql = "SELECT * FROM agendamentos WHERE usuario_id = ? AND status = 'agendado' ORDER BY data ASC, horario ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $agendamentos[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // Tratamento silencioso
}
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Profissional';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbearia Elite - Meus Agendamentos</title>
    <style>
        :root {
            --primary: #d4af37;
            --primary-hover: #b5952f;
            --bg-color: #f4f7f6;
            --text-color: #333;
            --danger: #ff4757;
            --card-bg: #ffffff;
            --input-border: #dfe6e9;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        /* Cabeçalho */
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
        }
        header h1 { margin: 0; font-size: 24px; color: var(--primary); }
        header p { margin: 5px 0 0; font-size: 14px; opacity: 0.8; }
        
        .btn-logout {
            position: absolute; top: 20px; right: 20px;
            color: #fff; text-decoration: none; font-size: 14px;
            border: 1px solid rgba(255,255,255,0.3); padding: 6px 12px; border-radius: 4px; transition: 0.3s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.1); border-color: white; }

        /* Layout Principal */
        .container {
            max-width: 1000px; margin: 30px auto; padding: 0 20px;
            display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px;
        }
        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
            .btn-logout { position: static; display: inline-block; margin-top: 15px; }
        }

        /* Cartões */
        .card {
            background: var(--card-bg); padding: 25px; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 4px solid var(--primary);
        }
        .card h2 { margin-top: 0; color: #2c3e50; font-size: 20px; margin-bottom: 20px; }

        /* Formulário */
        .form-control {
            width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid var(--input-border);
            border-radius: 8px; font-size: 14px; box-sizing: border-box; outline: none; transition: 0.3s;
        }
        .form-control:focus { border-color: var(--primary); }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

        .btn-submit {
            width: 100%; padding: 14px; background-color: var(--primary); color: #fff;
            border: none; border-radius: 8px; font-size: 16px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .btn-submit:hover { background-color: var(--primary-hover); }
        .btn-submit:disabled { background-color: #bdc3c7; cursor: not-allowed; }

        /* Lista de Agendamentos */
        ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 15px; }
        li {
            background: #fdfdfd; border: 1px solid #f1f2f6; border-left: 4px solid var(--primary);
            padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;
            transition: all 0.3s ease;
        }
        .info-cliente strong { display: block; font-size: 16px; color: #2c3e50; margin-bottom: 5px; }
        .info-detalhes { font-size: 13px; color: #7f8c8d; line-height: 1.6; }
        .data-badge {
            background: #e3f2fd; color: #1976d2; padding: 4px 8px;
            border-radius: 4px; font-weight: bold; font-size: 12px; display: inline-block; margin-top: 5px;
        }

        .btn-cancelar {
            background: white; color: var(--danger); border: 1px solid var(--danger);
            padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px;
            font-weight: 600; transition: 0.2s;
        }
        .btn-cancelar:hover { background: var(--danger); color: white; }
        
        .empty-state { text-align: center; color: #95a5a6; padding: 30px 0; font-style: italic; }

        /* Toast Notifications */
        #toast {
            visibility: hidden; min-width: 250px; background-color: #2ed573; color: #fff;
            text-align: center; border-radius: 8px; padding: 16px; position: fixed;
            z-index: 1000; left: 50%; bottom: 30px; transform: translateX(-50%);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-weight: bold; font-size: 14px;
            opacity: 0; transition: opacity 0.3s, bottom 0.3s;
        }
        #toast.show { visibility: visible; opacity: 1; bottom: 50px; }
        #toast.error { background-color: var(--danger); }
    </style>
</head>
<body>

<header>
    <h1>💈 Barbearia Elite</h1>
    <p>Bem-vindo, <?php echo htmlspecialchars($nome_usuario); ?>!</p>
    <a href="logout.php" class="btn-logout">Sair do sistema</a>
</header>

<main class="container">
    <div class="card">
        <form id="formAgendamento">
            <h2>Novo Agendamento</h2>
            
            <input type="text" id="cliente_nome" class="form-control" placeholder="Nome do cliente" required>
            <input type="email" id="cliente_email" class="form-control" placeholder="Email do cliente" required>
            <input type="tel" id="cliente_telefone" class="form-control" placeholder="Telefone (WhatsApp)" required>
            
            <select id="servico" class="form-control" required>
                <option value="">Selecione o serviço</option>
                <?php foreach($servicos_disponiveis as $chave => $valor): ?>
                    <option value="<?php echo $chave; ?>"><?php echo $valor; ?></option>
                <?php endforeach; ?>
            </select>
            
            <div class="grid-2">
                <input type="date" id="data" class="form-control" required>
                <input type="time" id="horario" class="form-control" required>
            </div>
            
            <button type="submit" id="btnSubmit" class="btn-submit">Agendar Cliente</button>
        </form>
    </div>
    
    <div class="card">
        <h2>📅 Próximos Agendamentos</h2>
        <ul id="listaAgendamentos">
            <?php if (empty($agendamentos)): ?>
                <li id="emptyState" class="empty-state" style="border: none; background: transparent; justify-content: center;">
                    Nenhum agendamento pendente no momento.
                </li>
            <?php else: ?>
                <?php foreach ($agendamentos as $agenda): ?>
                    <li id="agenda-<?php echo $agenda['id']; ?>">
                        <div class="info-cliente">
                            <strong><?php echo htmlspecialchars($agenda['cliente_nome']); ?></strong>
                            <div class="info-detalhes">
                                📞 <?php echo htmlspecialchars($agenda['cliente_telefone']); ?><br>
                                ✂️ <?php echo htmlspecialchars($servicos_disponiveis[$agenda['servico']] ?? $agenda['servico']); ?>
                            </div>
                            <div class="data-badge">
                                📅 <?php echo date('d/m/Y', strtotime($agenda['data'])); ?> às <?php echo htmlspecialchars($agenda['horario']); ?>
                            </div>
                        </div>
                        <button class="btn-cancelar" onclick="cancelarAgendamento(<?php echo $agenda['id']; ?>)">Cancelar</button>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</main>

<div id="toast"></div>

<script>
// Variáveis Globais
const form = document.getElementById('formAgendamento');
const toast = document.getElementById('toast');
const btnSubmit = document.getElementById('btnSubmit');
const lista = document.getElementById('listaAgendamentos');
const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>";

// Configurar data mínima (amanhã) de forma confiável
const hoje = new Date();
const amanha = new Date(hoje);
amanha.setDate(hoje.getDate() + 1);
document.getElementById('data').min = amanha.toISOString().split('T')[0];

// Envio do Formulário (AJAX)
form.addEventListener('submit', async function(e) {
    e.preventDefault();
    btnSubmit.disabled = true;
    btnSubmit.innerText = 'Agendando...';
    
    const formData = new FormData();
    formData.append('acao', 'agendar');
    formData.append('csrf_token', csrfToken);
    formData.append('cliente_nome', document.getElementById('cliente_nome').value);
    formData.append('cliente_email', document.getElementById('cliente_email').value);
    formData.append('cliente_telefone', document.getElementById('cliente_telefone').value);
    formData.append('servico', document.getElementById('servico').value);
    formData.append('data', document.getElementById('data').value);
    formData.append('horario', document.getElementById('horario').value);
    
    try {
        const response = await fetch(window.location.href, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            adicionarCardNaTela(data.agendamento);
            form.reset(); // Limpa o formulário
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Erro de conexão com o servidor.', 'error');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerText = 'Agendar Cliente';
    }
});

// Cancela e remove do DOM (AJAX)
async function cancelarAgendamento(id) {
    if (!confirm('Tem certeza que deseja cancelar este agendamento?')) return;
    
    const formData = new FormData();
    formData.append('acao', 'cancelar');
    formData.append('agendamento_id', id);
    formData.append('csrf_token', csrfToken);
    
    try {
        const response = await fetch(window.location.href, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            
            // Efeito visual antes de remover
            const item = document.getElementById(`agenda-${id}`);
            item.style.opacity = '0';
            item.style.transform = 'scale(0.95)';
            setTimeout(() => {
                item.remove();
                verificarListaVazia();
            }, 300);
            
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Erro de conexão ao cancelar.', 'error');
    }
}

// Funções de Interface
function adicionarCardNaTela(agendamento) {
    // Remove o aviso de lista vazia, se existir
    const emptyState = document.getElementById('emptyState');
    if (emptyState) emptyState.remove();

    // Cria o novo elemento HTML
    const li = document.createElement('li');
    li.id = `agenda-${agendamento.id}`;
    li.innerHTML = `
        <div class="info-cliente">
            <strong>${agendamento.cliente_nome}</strong>
            <div class="info-detalhes">
                📞 ${agendamento.cliente_telefone}<br>
                ✂️ ${agendamento.servico_nome}
            </div>
            <div class="data-badge">
                📅 ${agendamento.data_formatada} às ${agendamento.horario}
            </div>
        </div>
        <button class="btn-cancelar" onclick="cancelarAgendamento(${agendamento.id})">Cancelar</button>
    `;
    
    // Adiciona ao topo da lista com animação
    li.style.opacity = '0';
    lista.prepend(li);
    setTimeout(() => li.style.opacity = '1', 50);
}

function verificarListaVazia() {
    if (lista.children.length === 0) {
        lista.innerHTML = `<li id="emptyState" class="empty-state" style="border: none; background: transparent; justify-content: center;">Nenhum agendamento pendente no momento.</li>`;
    }
}

function showToast(message, type = 'success') {
    toast.textContent = message;
    toast.className = 'show';
    if (type === 'error') toast.classList.add('error');
    
    setTimeout(() => {
        toast.className = toast.className.replace('show', '');
    }, 3000);
}
</script>

</body>
</html>