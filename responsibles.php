<?php
require_once 'config.php';

$message = ''; // Para mensagens de sucesso ou erro

// Lógica para Adicionar Responsável
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_responsible') {
    $responsibleName = trim($_POST['responsible_name'] ?? '');

    if (!empty($responsibleName)) {
        // Verifica se o responsável já existe
        $checkSql = "SELECT id FROM responsibles WHERE name = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $responsibleName);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "<p class='error-message'>Erro: O responsável '{$responsibleName}' já existe!</p>";
        } else {
            $insertSql = "INSERT INTO responsibles (name) VALUES (?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("s", $responsibleName);

            if ($insertStmt->execute()) {
                $message = "<p class='success-message'>Responsável '{$responsibleName}' adicionado com sucesso!</p>";
            } else {
                $message = "<p class='error-message'>Erro ao adicionar responsável: " . $insertStmt->error . "</p>";
            }
            $insertStmt->close();
        }
        $checkStmt->close();
    } else {
        $message = "<p class='error-message'>O nome do responsável não pode ser vazio.</p>";
    }
}

// Lógica para Excluir Responsável
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_responsible') {
    $responsibleId = (int)($_POST['responsible_id'] ?? 0);

    if ($responsibleId > 0) {
        $deleteSql = "DELETE FROM responsibles WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $responsibleId);

        if ($deleteStmt->execute()) {
            $message = "<p class='success-message'>Responsável excluído com sucesso!</p>";
        } else {
            // Pode haver erro se o responsável ainda estiver associado a tarefas e a FK não for ON DELETE SET NULL
            $message = "<p class='error-message'>Erro ao excluir responsável: " . $deleteStmt->error . "</p>";
        }
        $deleteStmt->close();
    } else {
        $message = "<p class='error-message'>ID do responsável inválido.</p>";
    }
}

// Lógica para Listar Responsáveis
$responsibles = [];
$sql = "SELECT id, name FROM responsibles ORDER BY name ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $responsibles[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Responsáveis</title>
    <link rel="stylesheet" href="style.css"> <style>
        .responsible-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
        }
        .responsible-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .responsible-form {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        .responsible-form input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .responsible-form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .responsible-form button:hover {
            background-color: #0056b3;
        }
        .responsible-list {
            list-style: none;
            padding: 0;
        }
        .responsible-list li {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .responsible-list li span {
            font-weight: bold;
            color: #333;
        }
        .responsible-list button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .responsible-list button:hover {
            background-color: #c82333;
        }
        .message-area {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .success-message {
            color: #28a745;
        }
        .error-message {
            color: #dc3545;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="responsible-container">
        <h1>Gerenciar Responsáveis</h1>

        <div class="message-area">
            <?php echo $message; ?>
        </div>

        <form method="POST" class="responsible-form">
            <input type="hidden" name="action" value="add_responsible">
            <input type="text" name="responsible_name" placeholder="Nome do novo responsável" required>
            <button type="submit">Adicionar</button>
        </form>

        <h2>Responsáveis Cadastrados</h2>
        <?php if (empty($responsibles)): ?>
            <p>Nenhum responsável cadastrado ainda.</p>
        <?php else: ?>
            <ul class="responsible-list">
                <?php foreach ($responsibles as $responsible): ?>
                    <li>
                        <span><?php echo htmlspecialchars($responsible['name']); ?></span>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este responsável?');">
                            <input type="hidden" name="action" value="delete_responsible">
                            <input type="hidden" name="responsible_id" value="<?php echo $responsible['id']; ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <a href="index.php" class="back-link">&larr; Voltar para a Lista de Tarefas</a>
    </div>
</body>
</html>