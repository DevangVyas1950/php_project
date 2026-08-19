<?php
require_once '../includes/auth_check.php';
require_once '../includes/functions.php';
$user = get_user($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FitLife – Daily Tasks</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/user.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css">
  <style>
    .tasks-shell { display:grid; grid-template-columns: 1.25fr 0.75fr; gap:24px; }
    .task-card, .add-task-card { border-top: 4px solid #e4381c; box-shadow: 0 10px 24px rgba(26, 122, 74, 0.1); }
    .task-progress-badge { background:#eaf7f0; color:#e4381c; padding:7px 12px; border-radius:999px; font-weight:600; }
    .task-list { display:flex; flex-direction:column; gap:12px; }
    .task-item { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border:1px solid #e8efe9; border-radius:12px; background:#fcfffd; }
    .task-item.done { background:#f3fbf6; border-color:#cfe8d9; }
    .task-left { display:flex; align-items:center; gap:12px; flex:1; }
    .task-left input[type="checkbox"] { width:18px; height:18px; accent-color:#e4381c; }
    .task-title { font-weight:600; color:#233; }
    .task-item.done .task-title { text-decoration:line-through; color:#7a8b84; }
    .task-meta { display:flex; flex-direction:column; align-items:flex-end; gap:6px; }
    .task-badge { padding:4px 10px; border-radius:999px; font-size:12px; text-transform:capitalize; background:#eef7f2; color:#e4381c; }
    .task-due { font-size:12px; color:#789; }
    .empty-state { text-align:center; padding:28px 12px; border:1px dashed #cfe8d9; border-radius:14px; background:#f8fdf9; }
    .empty-illustration { font-size:48px; margin-bottom:10px; color:#e4381c; }
    .task-form .form-group { margin-bottom:14px; }
    .task-form label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:#355; }
    .task-form input, .task-form select { width:100%; padding:10px 12px; border:1px solid #dfe8e2; border-radius:10px; }
    .btn-add-task { width:100%; background:#e4381c; color:#fff; border:none; padding:11px 14px; border-radius:10px; font-weight:600; }
    .btn-add-task:hover { background:#b8290e; }
    .form-feedback { margin-top:10px; font-size:13px; color:#e4381c; min-height:18px; }
    @media (max-width: 900px) { .tasks-shell { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
<?php include 'topbar.php'; ?>
<div class="content">
  <div class="tasks-shell">
    <div class="card task-card">
      <div class="card-header">
        <div>
          <h4><i class="fas fa-tasks"></i> Today's Tasks</h4>
          <p style="margin:4px 0 0;color:#789;font-size:13px;">Track your priorities for today.</p>
        </div>
        <span id="task-progress" class="task-progress-badge">0/0 done</span>
      </div>
      <div id="task-list" class="task-list"></div>
    </div>

    <div class="card add-task-card">
      <div class="card-header">
        <h4><i class="fas fa-plus"></i> + Add Task</h4>
      </div>
      <form class="task-form" onsubmit="event.preventDefault(); addTask();">
        <div class="form-group">
          <label for="task-title">Task Title</label>
          <input type="text" id="task-title" placeholder="e.g. Drink 3L water">
        </div>
        <div class="form-group">
          <label for="task-type">Type</label>
          <select id="task-type">
            <option value="diet">Diet</option>
            <option value="workout">Workout</option>
            <option value="water">Water</option>
            <option value="general">General</option>
          </select>
        </div>
        <div class="form-group">
          <label for="task-date">Due Date</label>
          <input type="date" id="task-date">
        </div>
        <button type="submit" class="btn-add-task"><i class="fas fa-plus"></i> Add Task</button>
        <div id="task-feedback" class="form-feedback"></div>
      </form>
    </div>
  </div>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
function logout() {
  $.post('../api/auth.php', { action: 'logout' }, function() {
    window.location = '../index.php';
  }, 'json');
}

function toggleSidebar() {
  $('#sidebar').toggleClass('open');
}

function formatType(type) {
  const map = { diet: 'Diet', workout: 'Workout', water: 'Water', general: 'General', other: 'Other' };
  return map[type] || 'Other';
}

function loadTasks() {
  $.get('../api/tasks.php', { action: 'today' }, function(tasks) {
    const done = tasks.filter(t => t.is_completed == 1).length;
    $('#task-progress').text(done + '/' + tasks.length + ' done');

    let html = '';
    if (!tasks.length) {
      html = `<div class="empty-state">
        <div class="empty-illustration"><i class="fas fa-check-circle"></i></div>
        <h5>No tasks for today</h5>
        <p class="mb-0" style="color:#789;">Add a task to build your routine for the day.</p>
      </div>`;
    } else {
      tasks.forEach(task => {
        html += `<div class="task-item ${task.is_completed == 1 ? 'done' : ''}" id="task-${task.id}">
          <div class="task-left">
            <input type="checkbox" ${task.is_completed == 1 ? 'checked' : ''} onchange="toggleTask(${task.id}, this.checked)">
            <div>
              <div class="task-title">${task.task_title}</div>
              <div class="task-due">Due ${task.due_date || 'Today'}</div>
            </div>
          </div>
          <div class="task-meta">
            <span class="task-badge">${formatType(task.task_type)}</span>
          </div>
        </div>`;
      });
    }

    $('#task-list').html(html);
  }, 'json');
}

function toggleTask(id, checked) {
  $.post('../api/tasks.php', { action: 'toggle', task_id: id, status: checked ? 1 : 0 }, function() {
    $(`#task-${id}`).toggleClass('done', checked);
    loadTasks();
  }, 'json');
}

function addTask() {
  const title = $('#task-title').val().trim();
  if (!title) {
    $('#task-feedback').text('Please enter a task title.');
    return;
  }

  $('#task-feedback').text('Adding task...');
  $.post('../api/tasks.php', {
    action: 'add',
    task_title: title,
    task_type: $('#task-type').val(),
    due_date: $('#task-date').val()
  }, function(res) {
    if (res.status === 'success') {
      $('#task-title').val('');
      $('#task-feedback').text('Task added successfully.');
      loadTasks();
    } else {
      $('#task-feedback').text(res.message || 'Unable to add task.');
    }
  }, 'json');
}

$('#task-date').val(new Date().toISOString().slice(0, 10));
loadTasks();
</script>
</body>
</html>
