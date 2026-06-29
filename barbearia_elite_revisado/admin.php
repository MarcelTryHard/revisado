<?php
// 1. Inicialização e Segurança
session_start();
require_once 'config.php';

// Redirecionamento se não logado
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

// Geração de Token CSRF para evitar ataques de falsificação de requisição
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. API / Backend (Ações AJAX)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['acao'])) {
    header('Content-Type: application/json');
    
    // Validação do Token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado: Token de segurança inválido.']);
        exit();
    }

    $acao = $_POST['acao'];
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    if ($id === 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido.']);
        exit();
    }

    try {
        if ($acao === 'deletar') {
            $stmt = $conn->prepare("DELETE FROM agendamentos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $sucesso = $stmt->execute();
            $stmt->close();
            
            echo json_encode(['success' => $sucesso, 'message' => $sucesso ? 'Registro removido!' : 'Erro ao remover.']);
            exit();
        }
        
        if ($acao === 'atualizar_status') {
            $status_permitidos = ['agendado', 'concluido', 'cancelado'];
            $status = in_array($_POST['status'], $status_permitidos) ? $_POST['status'] : 'agendado';
            
            $stmt = $conn->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            $sucesso = $stmt->execute();
            $stmt->close();
            
            echo json_encode(['success' => $sucesso, 'message' => $sucesso ? 'Status atualizado!' : 'Erro ao atualizar.']);
            exit();
        }
    } catch (Exception $e) {
        // Em um ambiente real de produção, logue o erro em um arquivo e exiba uma mensagem genérica
        echo json_encode(['success' => false, 'message' => 'Erro interno no servidor.']);
        exit();
    }
}

// 3. Consultas para a View (Frontend)
try {
    // Usando null coalescing para garantir valores padrão em caso de erro na query
    $metrica_total = $conn->query("SELECT COUNT(*) as total FROM agendamentos")->fetch_assoc()['total'] ?? 0;
    $metrica_pendentes = $conn->query("SELECT COUNT(*) as total FROM agendamentos WHERE status = 'agendado'")->fetch_assoc()['total'] ?? 0;

    $sql = "SELECT a.*, u.nome as barbeiro 
            FROM agendamentos a 
            LEFT JOIN usuarios u ON a.usuario_id = u.id 
            ORDER BY a.data DESC, a.horario DESC";
            
    $agendamentos = $conn->query($sql)->fetch_all(MYSQLI_ASSOC) ?? [];
} catch (Exception $e) {
    $erro_banco = "Não foi possível carregar os dados no momento.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Barbearia Elite</title>
    <style>
        :root {
            --primary: #d4af37; /* Dourado da barbearia */
            --bg-color: #f4f7f6;
            --text-color: #333;
            --danger: #ff4757;
            --success: #2ed573;
            --warning: #ffa502;
            --card-bg: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .admin-container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
        
        .header-title { display: flex; align-items: center; gap: 10px; margin-bottom: 25px; color: #2c3e50; }

        /* Dashboard Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { 
            background: var(--card-bg); padding: 25px; border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; 
            border-bottom: 5px solid var(--primary); transition: transform 0.2s ease;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card h3 { margin: 0; font-size: 16px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card p { margin: 10px 0 0; font-size: 32px; font-weight: bold; color: #2c3e50; }

        /* Table & Controls */
        .admin-table-container { background: var(--card-bg); padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .table-header-controls { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px; gap: 15px; }
        .table-header-controls h2 { margin: 0; font-size: 20px; color: #2c3e50; }
        
        .search-bar { 
            padding: 10px 15px; border: 1px solid #dfe6e9; border-radius: 8px; 
            width: 100%; max-width: 350px; font-size: 14px; outline: none; transition: border-color 0.2s;
        }
        .search-bar:focus { border-color: var(--primary); }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { text-align: left; padding: 15px 12px; border-bottom: 1px solid #f1f2f6; font-size: 14px; }
        th { background-color: #f8f9fa; font-weight: 600; color: #2c3e50; }
        tr:hover { background-color: #fdfdfd; }

        /* Badges & Buttons */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-agendado { background: #e3f2fd; color: #1976d2; }
        .badge-cancelado { background: #ffebee; color: #c62828; }
        .badge-concluido { background: #e8f5e9; color: #2e7d32; }

        .btn { 
            padding: 6px 12px; border-radius: 6px; cursor: pointer; border: none; 
            color: white; font-size: 12px; font-weight: 500; margin-right: 4px; 
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn:hover { opacity: 0.9; }
        .btn:active { transform: scale(0.95); }
        .btn-delete { background: var(--danger); }
        .btn-success { background: var(--success); }
        .btn-warning { background: var(--warning); }
        
        /* Utility */
        .empty-state { text-align: center; padding: 30px; color: #7f8c8d; font-style: italic; }
    </style>
</head>
<body>

<div class="admin-container">
    <h1 class="header-title">💈 Painel Administrativo</h1>
    
    <?php if(isset($erro_banco)): ?>
        <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            ⚠️ <?php echo $erro_banco; ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total de Agendamentos</h3>
            <p><?php echo htmlspecialchars($metrica_total); ?></p>
        </div>
        <div class="stat-card">
            <h3>Pendentes</h3>
            <p><?php echo htmlspecialchars($metrica_pendentes); ?></p>
        </div>
    </div>

    <div class="admin-table-container">
        <div class="table-header-controls">
            <h2>📋 Lista de Agendamentos</h2>
            <input type="text" id="searchInput" class="search-bar" placeholder="🔍 Buscar cliente ou serviço..." aria-label="Buscar agendamentos">
        </div>
        
        <div class="table-responsive">
            <table id="tabelaAgendamentos">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agendamentos)): ?>
                        <tr><td colspan="5" class="empty-state">Nenhum agendamento encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($agendamentos as $row): ?>
                        <tr id="row-<?php echo $row['id']; ?>">
                            <td><?php echo date('d/m/Y', strtotime($row['data'])) . " - " . htmlspecialchars($row['horario']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['cliente_nome']); ?></strong></td>
                            <td><?php echo htmlspecialchars(ucfirst($row['servico'])); ?></td>
                            <td class="status-cell">
                                <span class="badge badge-<?php echo htmlspecialchars($row['status']); ?>">
                                    <?php echo htmlspecialchars(strtoupper($row['status'])); ?>
                                </span>
                            </td>
                            <td class="actions-cell">
                                <?php if($row['status'] === 'agendado'): ?>
                                    <button class="btn btn-success action-btn" onclick="atualizarStatus(<?php echo $row['id']; ?>, 'concluido')">✓ Concluir</button>
                                    <button class="btn btn-warning action-btn" onclick="atualizarStatus(<?php echo $row['id']; ?>, 'cancelado')">✕ Cancelar</button>
                                <?php endif; ?>
                                <button class="btn btn-delete" onclick="deletarRegistro(<?php echo $row['id']; ?>)">Excluir</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Token global para as requisições AJAX
const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>";

/**
 * Atualiza o status via AJAX e modifica o DOM sem recarregar a página
 */
async function atualizarStatus(id, status) {
    if (!confirm(`Deseja marcar este agendamento como ${status.toUpperCase()}?`)) return;

    const formData = new FormData();
    formData.append('acao', 'atualizar_status');
    formData.append('id', id);
    formData.append('status', status);
    formData.append('csrf_token', csrfToken);
    
    try {
        const res = await fetch(window.location.href, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            // Atualiza o DOM dinamicamente
            const row = document.getElementById(`row-${id}`);
            
            // Atualiza o Badge
            const statusCell = row.querySelector('.status-cell');
            statusCell.innerHTML = `<span class="badge badge-${status}">${status.toUpperCase()}</span>`;
            
            // Remove os botões de concluir/cancelar, deixando apenas o excluir
            const actionBtns = row.querySelectorAll('.action-btn');
            actionBtns.forEach(btn => btn.remove());
            
        } else {
            alert(data.message || 'Falha ao atualizar o status.');
        }
    } catch (error) {
        console.error("Erro:", error);
        alert('Ocorreu um erro na requisição ao servidor.');
    }
}

/**
 * Deleta o registro via AJAX e remove a linha do DOM
 */
async function deletarRegistro(id) {
    if (!confirm('Esta ação é irreversível. Deseja excluir permanentemente?')) return;
    
    const formData = new FormData();
    formData.append('acao', 'deletar');
    formData.append('id', id);
    formData.append('csrf_token', csrfToken);
    
    try {
        const res = await fetch(window.location.href, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            // Animação simples de fade out antes de remover
            const row = document.getElementById(`row-${id}`);
            row.style.transition = "opacity 0.3s ease";
            row.style.opacity = "0";
            setTimeout(() => row.remove(), 300);
        } else {
            alert(data.message || 'Falha ao deletar o registro.');
        }
    } catch (error) {
        console.error("Erro:", error);
        alert('Ocorreu um erro na requisição ao servidor.');
    }
}

/**
 * Filtro de tabela no Frontend otimizado
 */
document.getElementById('searchInput').addEventListener('input', function() {
    const termo = this.value.toLowerCase();
    const linhas = document.querySelectorAll('#tabelaAgendamentos tbody tr');
    
    linhas.forEach(linha => {
        // Ignora a linha de estado vazio (empty-state) se houver
        if (linha.querySelector('.empty-state')) return;
        
        const textoLinha = linha.innerText.toLowerCase();
        linha.style.display = textoLinha.includes(termo) ? "" : "none";
    });
});
</script>

</body>
</html>