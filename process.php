<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $responsibleId = (int)($_POST['responsible_id'] ?? 0);
        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL; // Pega e verifica data
        $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;     // Pega e verifica data

        $responsibleIdForDb = ($responsibleId === 0) ? NULL : $responsibleId;

        if (!empty($title)) {
            $sql = "INSERT INTO tasks (title, responsible, start_date, end_date, status) VALUES (?, ?, ?, ?, 'a_fazer')";
            $stmt = $conn->prepare($sql);

            // Determina os tipos para bind_param
            $types = "s"; // title (string)
            if ($responsibleIdForDb === NULL) {
                $types .= "s"; // responsible (NULL as string)
            } else {
                $types .= "i"; // responsible (int)
            }
            $types .= "ss"; // start_date, end_date (strings)

            // Cria um array de referências para bind_param
            $params = [$title, $responsibleIdForDb, $startDate, $endDate];
            $bind_params = array_merge([$types], $params);

            // Usar call_user_func_array para bind_param flexível
            call_user_func_array([$stmt, 'bind_param'], refValues($bind_params));

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Tarefa adicionada com sucesso!';
                $response['id'] = $conn->insert_id;
            } else {
                $response['message'] = 'Erro ao adicionar tarefa: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'O título da tarefa não pode ser vazio.';
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($id > 0 && in_array($status, ['a_fazer', 'fazendo', 'feito'])) {
            $sql = "UPDATE tasks SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Status da tarefa atualizado com sucesso!';
            } else {
                $response['message'] = 'Erro ao atualizar status: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Dados inválidos para atualização.';
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $responsibleId = (int)($_POST['responsible_id'] ?? 0);
        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL; // Pega e verifica data
        $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;     // Pega e verifica data

        $responsibleIdForDb = ($responsibleId === 0) ? NULL : $responsibleId;

        if ($id > 0 && !empty($title)) {
            $sql = "UPDATE tasks SET title = ?, responsible = ?, start_date = ?, end_date = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);

            // Determina os tipos para bind_param
            $types = "s"; // title (string)
            if ($responsibleIdForDb === NULL) {
                $types .= "s"; // responsible (NULL as string)
            } else {
                $types .= "i"; // responsible (int)
            }
            $types .= "ssi"; // start_date, end_date (strings), id (int)

            // Cria um array de referências para bind_param
            $params = [$title, $responsibleIdForDb, $startDate, $endDate, $id];
            $bind_params = array_merge([$types], $params);

            // Usar call_user_func_array para bind_param flexível
            call_user_func_array([$stmt, 'bind_param'], refValues($bind_params));

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Tarefa editada com sucesso!';
            } else {
                $response['message'] = 'Erro ao editar tarefa: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'ID da tarefa ou título inválido para edição.';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $sql = "DELETE FROM tasks WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Tarefa apagada com sucesso!';
            } else {
                $response['message'] = 'Erro ao apagar tarefa: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'ID da tarefa inválido para apagar.';
        }
    } else {
        $response['message'] = 'Ação inválida.';
    }
} else {
    $response['message'] = 'Método de requisição inválido.';
}

$conn->close();

// Função auxiliar para passar parâmetros por referência para bind_param
function refValues($arr) {
    if (strnatcmp(phpversion(), '5.3') >= 0) // PHP 5.3+
    {
        $refs = array();
        foreach ($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}

echo json_encode($response);
?>