<?php
require_once 'config.php';

// Função para buscar tarefas por status (mantida a mesma)
function getTasksByStatus($status, $conn) {
    $sql = "SELECT t.id, t.title, t.status, t.start_date, t.end_date, r.id AS responsible_id, r.name AS responsible_name
            FROM tasks t
            LEFT JOIN responsibles r ON t.responsible = r.id
            WHERE t.status = ? ORDER BY t.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Função para buscar todos os responsáveis para o dropdown (mantida a mesma)
$allResponsibles = [];
$sqlResponsibles = "SELECT id, name FROM responsibles ORDER BY name ASC";
$resultResponsibles = $conn->query($sqlResponsibles);
if ($resultResponsibles) {
    while ($row = $resultResponsibles->fetch_assoc()) {
        $allResponsibles[] = $row;
    }
}

$a_fazer_tasks = getTasksByStatus('a_fazer', $conn);
$fazendo_tasks = getTasksByStatus('fazendo', $conn);
$feito_tasks = getTasksByStatus('feito', $conn);

// Data atual para comparação
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Lista de Tarefas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Minha Lista de Tarefas</h1>

        <form id="addTaskForm">
            <input type="text" id="taskTitle" placeholder="Adicionar nova tarefa..." required>
            <select id="taskResponsible">
                <option value="">Sem Responsável</option>
                <?php foreach ($allResponsibles as $res): ?>
                    <option value="<?php echo $res['id']; ?>"><?php echo htmlspecialchars($res['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" id="taskStartDate" placeholder="Data de Início">
            <input type="date" id="taskEndDate" placeholder="Data de Fim">
            <button type="submit">Adicionar</button>
        </form>

        <p class="manage-responsibles-link"><a href="responsibles.php">Gerenciar Responsáveis</a></p>

        <div class="board">
            <div class="column" id="a_fazer" ondrop="drop(event)" ondragover="allowDrop(event)">
                <h2>A Fazer</h2>
                <div class="task-list">
                    <?php foreach ($a_fazer_tasks as $task): ?>
                        <?php
                            $startDate = !empty($task['start_date']) ? date('d/m/Y', strtotime($task['start_date'])) : 'Não definida';
                            $endDate = !empty($task['end_date']) ? date('d/m/Y', strtotime($task['end_date'])) : 'Não definida';

                            // Lógica para colorir o card
                            $cardClass = '';
                            if (!empty($task['end_date'])) {
                                if ($task['end_date'] < $today && $task['status'] !== 'feito') {
                                    $cardClass = 'task-overdue'; // Vencida e não concluída
                                } elseif ($task['end_date'] === $today) {
                                    $cardClass = 'task-due-today'; // Vence hoje
                                }
                            }
                        ?>
                        <div class="task-card <?php echo $cardClass; ?>" draggable="true" ondragstart="drag(event)"
                             data-id="<?php echo $task['id']; ?>"
                             data-status="a_fazer"
                             data-title="<?php echo htmlspecialchars($task['title']); ?>"
                             data-responsible-id="<?php echo htmlspecialchars($task['responsible_id']); ?>"
                             data-responsible-name="<?php echo htmlspecialchars($task['responsible_name'] ?? 'Sem Responsável'); ?>"
                             data-start-date="<?php echo htmlspecialchars($task['start_date']); ?>"
                             data-end-date="<?php echo htmlspecialchars($task['end_date']); ?>">
                            <p class="card-title"><?php echo htmlspecialchars($task['title']); ?></p>
                            <p class="card-responsible">Responsável: <span><?php echo htmlspecialchars($task['responsible_name'] ?? 'Sem Responsável'); ?></span></p>
                            <p class="card-dates">Início: <span><?php echo $startDate; ?></span> | Fim: <span><?php echo $endDate; ?></span></p>
                            <div class="card-actions">
                                <div class="dropdown">
                                    <button class="dropbtn">Mover para</button>
                                    <div class="dropdown-content">
                                        <a href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'a_fazer')">A Fazer</a>
                                        <a href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'fazendo')">Fazendo</a>
                                        <a href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'feito')">Feito</a>
                                    </div>
                                </div>
                                <button class="edit-btn" onclick="openEditModal(<?php echo $task['id']; ?>,
                                    '<?php echo htmlspecialchars(addslashes($task['title'])); ?>',
                                    <?php echo json_encode($task['responsible_id']); ?>,
                                    '<?php echo htmlspecialchars($task['start_date']); ?>',
                                    '<?php echo htmlspecialchars($task['end_date']); ?>')">Editar</button>
                                <button class="delete-btn" onclick="deleteTask(<?php echo $task['id']; ?>)">Apagar</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="column" id="fazendo" ondrop="drop(event)" ondragover="allowDrop(event)">
                <h2>Fazendo</h2>
                <div class="task-list">
                    <?php foreach ($fazendo_tasks as $task): ?>
                        <?php
                            $startDate = !empty($task['start_date']) ? date('d/m/Y', strtotime($task['start_date'])) : 'Não definida';
                            $endDate = !empty($task['end_date']) ? date('d/m/Y', strtotime($task['end_date'])) : 'Não definida';

                            // Lógica para colorir o card
                            $cardClass = '';
                            if (!empty($task['end_date'])) {
                                if ($task['end_date'] < $today && $task['status'] !== 'feito') {
                                    $cardClass = 'task-overdue'; // Vencida e não concluída
                                } elseif ($task['end_date'] === $today) {
                                    $cardClass = 'task-due-today'; // Vence hoje
                                }
                            }
                        ?>
                        <div class="task-card <?php echo $cardClass; ?>" draggable="true" ondragstart="drag(event)"
                             data-id="<?php echo $task['id']; ?>"
                             data-status="fazendo"
                             data-title="<?php echo htmlspecialchars($task['title']); ?>"
                             data-responsible-id="<?php echo htmlspecialchars($task['responsible_id']); ?>"
                             data-responsible-name="<?php echo htmlspecialchars($task['responsible_name'] ?? 'Sem Responsável'); ?>"
                             data-start-date="<?php echo htmlspecialchars($task['start_date']); ?>"
                             data-end-date="<?php echo htmlspecialchars($task['end_date']); ?>">
                            <p class="card-title"><?php echo htmlspecialchars($task['title']); ?></p>
                            <p class="card-responsible">Responsável: <span><?php echo htmlspecialchars($task['responsible_name'] ?? 'Sem Responsável'); ?></span></p>
                            <p class="card-dates">Início: <span><?php echo $startDate; ?></span> | Fim: <span><?php echo $endDate; ?></span></p>
                            <div class="card-actions">
                                <div class="dropdown">
                                    <button class="dropbtn">Mover para</button>
                                    <div class="dropdown-content">
                                        <a href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'a_fazer')">A Fazer</a>
                                        <a href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'fazendo')">Fazendo</a>
                                        <a href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'feito')">Feito</a>
                                    </div>
                                </div>
                                <button class="edit-btn" onclick="openEditModal(<?php echo $task['id']; ?>,
                                    '<?php echo htmlspecialchars(addslashes($task['title'])); ?>',
                                    <?php echo json_encode($task['responsible_id']); ?>,
                                    '<?php echo htmlspecialchars($task['start_date']); ?>',
                                    '<?php echo htmlspecialchars($task['end_date']); ?>')">Editar</button>
                                <button class="delete-btn" onclick="deleteTask(<?php echo $task['id']; ?>)">Apagar</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="column" id="feito" ondrop="drop(event)" ondragover="allowDrop(event)">
                <h2>Feito</h2>
                <div class="task-list">
                    <?php foreach ($feito_tasks as $task): ?>
                        <?php
                            $startDate = !empty($task['start_date']) ? date('d/m/Y', strtotime($task['start_date'])) : 'Não definida';
                            $endDate = !empty($task['end_date']) ? date('d/m/Y', strtotime($task['end_date'])) : 'Não definida';

                            // Lógica para colorir o card (não aplica cores de vencimento se estiver em "feito")
                            $cardClass = '';
                        ?>
                        <div class="task-card <?php echo $cardClass; ?>" draggable="true" ondragstart="drag(event)"
                             data-id="<?php echo $task['id']; ?>"
                             data-status="feito"
                             data-title="<?php echo htmlspecialchars($task['title']); ?>"
                             data-responsible-id="<?php echo htmlspecialchars($task['responsible_id']); ?>"
                             data-responsible-name="<?php echo htmlspecialchars($task['responsible_name'] ?? 'Sem Responsável'); ?>"
                             data-start-date="<?php echo htmlspecialchars($task['start_date']); ?>"
                             data-end-date="<?php echo htmlspecialchars($task['end_date']); ?>">
                            <p class="card-title"><?php echo htmlspecialchars($task['title']); ?></p>
                            <p class="card-responsible">Responsável: <span><?php echo htmlspecialchars($task['responsible_name'] ?? 'Sem Responsável'); ?></span></p>
                            <p class="card-dates">Início: <span><?php echo $startDate; ?></span> | Fim: <span><?php echo $endDate; ?></span></p>
                            <div class="card-actions">
                                <div class="dropdown">
                                    <button class="dropbtn">Mover para</button>
                                    <div class="dropdown-content">
                                        <a href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'a_fazer')">A Fazer</a>
                                        <a href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'fazendo')">Fazendo</a>
                                        <a href="#" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'feito')">Feito</a>
                                    </div>
                                </div>
                                <button class="edit-btn" onclick="openEditModal(<?php echo $task['id']; ?>,
                                    '<?php echo htmlspecialchars(addslashes($task['title'])); ?>',
                                    <?php echo json_encode($task['responsible_id']); ?>,
                                    '<?php echo htmlspecialchars($task['start_date']); ?>',
                                    '<?php echo htmlspecialchars($task['end_date']); ?>')">Editar</button>
                                <button class="delete-btn" onclick="deleteTask(<?php echo $task['id']; ?>)">Apagar</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeEditModal()">&times;</span>
            <h2>Editar Tarefa</h2>
            <form id="editTaskForm">
                <input type="hidden" id="editTaskId">
                <label for="editTaskTitle">Título:</label>
                <input type="text" id="editTaskTitle" required>
                <label for="editTaskResponsible">Responsável:</label>
                <select id="editTaskResponsible">
                    <option value="">Sem Responsável</option>
                    <?php foreach ($allResponsibles as $res): ?>
                        <option value="<?php echo $res['id']; ?>"><?php echo htmlspecialchars($res['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="editTaskStartDate">Data de Início:</label>
                <input type="date" id="editTaskStartDate">
                <label for="editTaskEndDate">Data de Fim:</label>
                <input type="date" id="editTaskEndDate">
                <button type="submit">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script>
        const allResponsiblesData = <?php echo json_encode($allResponsibles); ?>;
        // Obtenha a data atual no formato YYYY-MM-DD
        const today = new Date().toISOString().slice(0, 10);
    </script>
    <script src="script.js"></script>
</body>
</html>