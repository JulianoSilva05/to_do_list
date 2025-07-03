// Funções para Drag and Drop (mantidas as mesmas)
function allowDrop(event) {
    event.preventDefault();
    const column = event.target.closest('.column');
    if (column) {
        column.classList.add('drag-over');
    }
}

function drag(event) {
    event.dataTransfer.setData("text", event.target.dataset.id);
}

function drop(event) {
    event.preventDefault();
    const taskId = event.dataTransfer.getData("text");
    const targetColumn = event.target.closest('.column');
    const newStatus = targetColumn.id;

    if (targetColumn) {
        targetColumn.classList.remove('drag-over');
        const draggedElement = document.querySelector(`.task-card[data-id="${taskId}"]`);
        if (draggedElement) {
            targetColumn.querySelector('.task-list').appendChild(draggedElement);
            draggedElement.dataset.status = newStatus;
            
            // Remove classes de cor ao mover para "feito"
            if (newStatus === 'feito') {
                draggedElement.classList.remove('task-overdue', 'task-due-today');
            } else { // Reaplica a lógica de cor se mover para "a_fazer" ou "fazendo"
                applyTaskCardColor(draggedElement, draggedElement.dataset.endDate, newStatus);
            }
            
            updateTaskStatus(taskId, newStatus);
        }
    }
}

document.querySelectorAll('.column').forEach(column => {
    column.addEventListener('dragleave', (event) => {
        event.target.closest('.column').classList.remove('drag-over');
    });
    column.addEventListener('dragenter', (event) => {
        event.target.closest('.column').classList.add('drag-over');
    });
});

// Adicionar Nova Tarefa via AJAX
document.getElementById('addTaskForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const taskTitle = document.getElementById('taskTitle').value;
    const taskResponsibleId = document.getElementById('taskResponsible').value;
    const taskStartDate = document.getElementById('taskStartDate').value;
    const taskEndDate = document.getElementById('taskEndDate').value;

    fetch('process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&title=${encodeURIComponent(taskTitle)}&responsible_id=${encodeURIComponent(taskResponsibleId)}&start_date=${encodeURIComponent(taskStartDate)}&end_date=${encodeURIComponent(taskEndDate)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const aFazerColumn = document.getElementById('a_fazer').querySelector('.task-list');
            const responsibleName = taskResponsibleId ? (allResponsiblesData.find(r => r.id == taskResponsibleId)?.name || 'Sem Responsável') : 'Sem Responsável';
            
            const newCard = createTaskCardElement(data.id, taskTitle, taskResponsibleId, responsibleName, taskStartDate, taskEndDate, 'a_fazer');
            aFazerColumn.prepend(newCard);
            
            // Aplica a cor ao novo card
            applyTaskCardColor(newCard, taskEndDate, 'a_fazer');

            document.getElementById('taskTitle').value = '';
            document.getElementById('taskResponsible').value = '';
            document.getElementById('taskStartDate').value = '';
            document.getElementById('taskEndDate').value = '';
        } else {
            alert('Erro ao adicionar tarefa: ' + data.message);
        }
    })
    .catch(error => console.error('Erro:', error));
});

// Função auxiliar para criar o elemento do card (atualizada para incluir datas e cores)
function createTaskCardElement(id, title, responsibleId, responsibleName, startDate, endDate, status) {
    const newCard = document.createElement('div');
    newCard.className = 'task-card'; // Classe base
    newCard.draggable = true;
    newCard.setAttribute('ondragstart', 'drag(event)');
    newCard.dataset.id = id;
    newCard.dataset.status = status;
    newCard.dataset.title = title;
    newCard.dataset.responsibleId = responsibleId;
    newCard.dataset.responsibleName = responsibleName;
    newCard.dataset.startDate = startDate;
    newCard.dataset.endDate = endDate;

    const displayStartDate = startDate ? formatDateForDisplay(startDate) : 'Não definida';
    const displayEndDate = endDate ? formatDateForDisplay(endDate) : 'Não definida';

    newCard.innerHTML = `
        <p class="card-title">${escapeHTML(title)}</p>
        <p class="card-responsible">Responsável: <span>${escapeHTML(responsibleName)}</span></p>
        <p class="card-dates">Início: <span>${displayStartDate}</span> | Fim: <span>${displayEndDate}</span></p>
        <div class="card-actions">
            <div class="dropdown">
                <button class="dropbtn">Mover para</button>
                <div class="dropdown-content">
                    <a href="#" onclick="updateTaskStatusAndColor(${id}, 'a_fazer')">A Fazer</a>
                    <a href="#" onclick="updateTaskStatusAndColor(${id}, 'fazendo')">Fazendo</a>
                    <a href="#" onclick="updateTaskStatusAndColor(${id}, 'feito')">Feito</a>
                </div>
            </div>
            <button class="edit-btn" onclick="openEditModal(${id},
                '${escapeHTML(title)}',
                ${responsibleId === '' ? 'null' : responsibleId},
                '${escapeHTML(startDate)}',
                '${escapeHTML(endDate)}')">Editar</button>
            <button class="delete-btn" onclick="deleteTask(${id})">Apagar</button>
        </div>
    `;
    return newCard;
}

// Função para formatar a data para exibição (DD/MM/YYYY)
function formatDateForDisplay(dateString) {
    if (!dateString) return '';
    const [year, month, day] = dateString.split('-');
    return `${day}/${month}/${year}`;
}

// NOVO: Função para aplicar classes de cor ao card
function applyTaskCardColor(cardElement, endDate, status) {
    cardElement.classList.remove('task-overdue', 'task-due-today'); // Remove classes existentes

    if (endDate && status !== 'feito') { // Só aplica se tiver data final e não estiver em "feito"
        if (endDate < today) {
            cardElement.classList.add('task-overdue');
        } else if (endDate === today) {
            cardElement.classList.add('task-due-today');
        }
    }
}

// NOVO: Função que chama updateTaskStatus e depois atualiza a cor
function updateTaskStatusAndColor(taskId, newStatus) {
    const currentCard = document.querySelector(`.task-card[data-id="${taskId}"]`);
    if (currentCard) {
        // Remove as classes de cor antes de mover, a lógica applyTaskCardColor vai tratar
        currentCard.classList.remove('task-overdue', 'task-due-today');
    }

    fetch('process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&id=${taskId}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (currentCard) {
                const targetColumn = document.getElementById(newStatus).querySelector('.task-list');
                targetColumn.appendChild(currentCard);
                currentCard.dataset.status = newStatus;
                
                // Aplica a cor após a movimentação
                applyTaskCardColor(currentCard, currentCard.dataset.endDate, newStatus);
            }
            console.log('Status atualizado com sucesso!');
        } else {
            alert('Erro ao atualizar status: ' + data.message);
        }
    })
    .catch(error => console.error('Erro:', error));
}


// Modal de Edição (mantido o mesmo, mas a lógica de cor será no submit)
const editModal = document.getElementById('editModal');
const editTaskIdInput = document.getElementById('editTaskId');
const editTaskTitleInput = document.getElementById('editTaskTitle');
const editTaskResponsibleSelect = document.getElementById('editTaskResponsible');
const editTaskStartDateInput = document.getElementById('editTaskStartDate');
const editTaskEndDateInput = document.getElementById('editTaskEndDate');
const editTaskForm = document.getElementById('editTaskForm');

function openEditModal(id, title, responsibleId, startDate, endDate) {
    editTaskIdInput.value = id;
    editTaskTitleInput.value = title;
    editTaskResponsibleSelect.value = responsibleId || '';
    editTaskStartDateInput.value = startDate;
    editTaskEndDateInput.value = endDate;
    editModal.style.display = 'flex';
}

function closeEditModal() {
    editModal.style.display = 'none';
}

window.addEventListener('click', function(event) {
    if (event.target == editModal) {
        closeEditModal();
    }
});

// Enviar Formulário de Edição via AJAX (atualizado para cores)
editTaskForm.addEventListener('submit', function(event) {
    event.preventDefault();
    const taskId = editTaskIdInput.value;
    const newTitle = editTaskTitleInput.value;
    const newResponsibleId = editTaskResponsibleSelect.value;
    const newStartDate = editTaskStartDateInput.value;
    const newEndDate = editTaskEndDateInput.value;

    fetch('process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=edit&id=${taskId}&title=${encodeURIComponent(newTitle)}&responsible_id=${encodeURIComponent(newResponsibleId)}&start_date=${encodeURIComponent(newStartDate)}&end_date=${encodeURIComponent(newEndDate)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cardToUpdate = document.querySelector(`.task-card[data-id="${taskId}"]`);
            if (cardToUpdate) {
                cardToUpdate.querySelector('.card-title').textContent = newTitle;

                const newResponsibleName = newResponsibleId ? (allResponsiblesData.find(r => r.id == newResponsibleId)?.name || 'Sem Responsável') : 'Sem Responsável';
                cardToUpdate.querySelector('.card-responsible span').textContent = newResponsibleName;

                const displayNewStartDate = newStartDate ? formatDateForDisplay(newStartDate) : 'Não definida';
                const displayNewEndDate = newEndDate ? formatDateForDisplay(newEndDate) : 'Não definida';
                cardToUpdate.querySelector('.card-dates span:first-child').textContent = displayNewStartDate;
                cardToUpdate.querySelector('.card-dates span:last-child').textContent = displayNewEndDate;

                // Atualiza os data-attributes
                cardToUpdate.dataset.title = newTitle;
                cardToUpdate.dataset.responsibleId = newResponsibleId;
                cardToUpdate.dataset.responsibleName = newResponsibleName;
                cardToUpdate.dataset.startDate = newStartDate;
                cardToUpdate.dataset.endDate = newEndDate;

                // Reaplica a cor com base nas novas datas e status atual do card
                applyTaskCardColor(cardToUpdate, newEndDate, cardToUpdate.dataset.status);

                // Atualiza o onclick do botão de editar
                const editButton = cardToUpdate.querySelector('.edit-btn');
                if(editButton) {
                     editButton.setAttribute('onclick', `openEditModal(${taskId},
                         '${escapeHTML(newTitle)}',
                         ${newResponsibleId === '' ? 'null' : newResponsibleId},
                         '${escapeHTML(newStartDate)}',
                         '${escapeHTML(newEndDate)}')`);
                }
            }
            closeEditModal();
        } else {
            alert('Erro ao editar tarefa: ' + data.message);
        }
    })
    .catch(error => console.error('Erro:', error));
});


// Apagar Tarefa via AJAX (mantida a mesma)
function deleteTask(taskId) {
    if (confirm('Tem certeza que deseja apagar esta tarefa?')) {
        fetch('process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&id=${taskId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cardToRemove = document.querySelector(`.task-card[data-id="${taskId}"]`);
                if (cardToRemove) {
                    cardToRemove.remove();
                }
            } else {
                alert('Erro ao apagar tarefa: ' + data.message);
            }
        })
        .catch(error => console.error('Erro:', error));
    }
}

// Função para escapar HTML para prevenir XSS (mantida a mesma)
function escapeHTML(str) {
    if (typeof str !== 'string' || str === null) return str;
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}