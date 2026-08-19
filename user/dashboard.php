<?php
require_once '../includes/auth_check.php';
require_once '../includes/functions.php';

$user     = get_user($_SESSION['user_id']);
$tasks    = get_today_tasks($_SESSION['user_id']);
$calories = get_today_calories($_SESSION['user_id']);
$chart    = get_user_diet_chart($_SESSION['user_id']);
$bmi_cat  = bmi_category($user['bmi']);
$goal_cal = ($user['goal'] === 'lose_weight') ? CALORIES_LOSE : (($user['goal'] === 'gain_muscle') ? CALORIES_GAIN : CALORIES_MAINTAIN);
$cal_pct  = min(100, round(($calories / $goal_cal) * 100));
$completed = count(array_filter($tasks, fn($t) => $t['is_completed']));
$task_pct  = count($tasks) > 0 ? round(($completed / count($tasks)) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FitLife – Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/user.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css">
<style>
  .weight-card { border-top: 4px solid #e4381c; }
  .btn-weight { background:#e4381c; color:#fff; border:none; padding:10px 14px; border-radius:10px; }
  .btn-weight:hover { background:#b8290e; }
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-logo"><i class="fas fa-heartbeat"></i> FitLife</div>
  <nav>
    <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
    <a href="diet-chart.php"><i class="fas fa-utensils"></i> Diet Chart</a>
    <a href="food-log.php"><i class="fas fa-apple-alt"></i> Food Log</a>
    <a href="exercise.php"><i class="fas fa-dumbbell"></i> Exercise</a>
    <a href="daily-tasks.php"><i class="fas fa-tasks"></i> Daily Tasks</a>
    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
  </nav>
  <div class="sidebar-footer">
    <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <button class="menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    <h2>Dashboard</h2>
    <div class="user-info">
      <i class="fas fa-user-circle"></i>
      <span><?= htmlspecialchars($user['name']) ?></span>
    </div>
  </div>

  <div class="content">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
      <div>
        <h3>Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>! 👋</h3>
        <p>Goal: <strong><?= str_replace('_', ' ', ucwords($user['goal'])) ?></strong> &nbsp;|&nbsp; Today: <?= date('l, d M Y') ?></p>
      </div>
      <div class="bmi-badge badge-<?= $bmi_cat['color'] ?>">
        BMI <?= $user['bmi'] ?> &bull; <?= $bmi_cat['label'] ?>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-fire"></i></div>
        <div class="stat-info">
          <span class="stat-num"><?= round($calories) ?></span>
          <span class="stat-label">Calories Today</span>
          <div class="progress-bar"><div class="progress-fill" style="width:<?= $cal_pct ?>%"></div></div>
          <span class="stat-sub"><?= $cal_pct ?>% of <?= $goal_cal ?> kcal goal</span>
        </div>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-tasks"></i></div>
        <div class="stat-info">
          <span class="stat-num"><?= $completed ?>/<?= count($tasks) ?></span>
          <span class="stat-label">Tasks Done</span>
          <div class="progress-bar"><div class="progress-fill" style="width:<?= $task_pct ?>%"></div></div>
          <span class="stat-sub"><?= $task_pct ?>% complete</span>
        </div>
      </div>
      <div class="stat-card orange weight-card">
        <div class="stat-icon"><i class="fas fa-weight"></i></div>
        <div class="stat-info">
          <span class="stat-num"><?= $user['weight_kg'] ?> kg</span>
          <span class="stat-label">Current Weight</span>
          <span class="stat-sub">Height: <?= $user['height_cm'] ?> cm</span>
          <button class="btn-weight mt-2" type="button" data-bs-toggle="modal" data-bs-target="#weightModal"><i class="fas fa-plus"></i> Log Weight</button>
        </div>
      </div>
      <div class="stat-card purple">
        <div class="stat-icon"><i class="fas fa-utensils"></i></div>
        <div class="stat-info">
          <span class="stat-num"><?= $chart ? $chart['total_calories'] : '—' ?></span>
          <span class="stat-label">Diet Plan kcal</span>
          <span class="stat-sub"><?= $chart ? htmlspecialchars($chart['title']) : 'No plan assigned' ?></span>
        </div>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h4><i class="fas fa-chart-line"></i> Weight Progress</h4>
      </div>
      <div class="card-body">
        <canvas id="weightChart" height="110"></canvas>
      </div>
    </div>

    <!-- Tasks & Quick Log -->
    <div class="grid2">
      <!-- Today's Tasks -->
      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-check-circle"></i> Today's Tasks</h4>
          <a href="daily-tasks.php">View all</a>
        </div>
        <div id="task-list">
          <?php foreach ($tasks as $task): ?>
          <div class="task-item <?= $task['is_completed'] ? 'done' : '' ?>" id="task-<?= $task['id'] ?>">
            <input type="checkbox" <?= $task['is_completed'] ? 'checked' : '' ?>
              onchange="toggleTask(<?= $task['id'] ?>, this.checked)">
            <span class="task-title"><?= htmlspecialchars($task['task_title']) ?></span>
            <span class="task-badge type-<?= $task['task_type'] ?>"><?= $task['task_type'] ?></span>
          </div>
          <?php endforeach; ?>
          <?php if (empty($tasks)): ?>
          <p class="empty-state"><i class="fas fa-check-double"></i> No tasks for today!</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Quick Calorie Log -->
      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-plus-circle"></i> Quick Log Food</h4>
          <a href="food-log.php">Full log</a>
        </div>
        <div class="quick-log">
          <input type="text" id="food-search" placeholder="Search food..." oninput="searchFood(this.value)">
          <div id="food-results" class="food-results"></div>
          <div id="selected-food" style="display:none">
            <p id="sel-food-name" class="sel-food-label"></p>
            <div class="qty-row">
              <input type="number" id="food-qty" value="100" min="1" placeholder="Grams">
              <button onclick="logFood()"><i class="fas fa-check"></i> Log</button>
            </div>
          </div>
          <div id="log-feedback" class="log-feedback"></div>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<div class="modal fade" id="weightModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-weight me-2"></i>Log Today's Weight</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Weight (kg)</label>
          <input type="number" id="weight-input" class="form-control" step="0.1" min="1" value="<?= $user['weight_kg'] ?>">
        </div>
        <div class="form-group mt-3">
          <label>Date</label>
          <input type="date" id="weight-date" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div id="weight-feedback" class="mt-3 text-success"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="saveWeight()">Save Weight</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/js/ajax-calls.js"></script>
<script>
let selectedFoodId = null;

function searchFood(q) {
  if (q.length < 2) { $('#food-results').html(''); return; }
  $.get('../api/food.php', { action: 'search', query: q }, function(foods) {
    let html = '';
    foods.forEach(f => {
      html += `<div class="food-result-item" onclick="selectFood(${f.id},'${f.name}',${f.calories_per_100g})">
        <span>${f.name}</span><small>${f.calories_per_100g} kcal/100g</small></div>`;
    });
    $('#food-results').html(html || '<p class="empty-state">No results</p>');
  }, 'json');
}

function selectFood(id, name, cal) {
  selectedFoodId = id;
  $('#sel-food-name').text(name + ' (' + cal + ' kcal/100g)');
  $('#selected-food').show();
  $('#food-results').html('');
  $('#food-search').val(name);
}

function logFood() {
  if (!selectedFoodId) return;
  $.post('../api/food.php', {
    action: 'log', food_id: selectedFoodId, quantity_g: $('#food-qty').val()
  }, function(res) {
    if (res.status === 'success') {
      $('#log-feedback').html(`<span class="success-msg">+${res.calories_added} kcal logged! Total: ${res.total_calories} kcal</span>`);
      $('#selected-food').hide();
      $('#food-search').val('');
      selectedFoodId = null;
    }
  }, 'json');
}

function toggleTask(id, checked) {
  $.post('../api/tasks.php', { action: 'toggle', task_id: id, status: checked ? 1 : 0 }, function() {
    $(`#task-${id}`).toggleClass('done', checked);
  }, 'json');
}

function logout() {
  $.post('../api/auth.php', { action: 'logout' }, function() {
    window.location = '../index.php';
  }, 'json');
}

function toggleSidebar() {
  $('#sidebar').toggleClass('open');
}

function saveWeight() {
  const weight = $('#weight-input').val();
  const date = $('#weight-date').val();
  if (!weight) {
    $('#weight-feedback').text('Please enter your weight.');
    return;
  }

  $('#weight-feedback').text('Saving...');
  $.post('../api/weight.php', { action: 'update_weight', weight_kg: weight, recorded_date: date }, function(res) {
    $('#weight-feedback').text(res.message || 'Weight saved.');
    if (res.status === 'success') {
      setTimeout(() => {
        $('#weightModal').modal('hide');
        loadWeightChart();
        location.reload();
      }, 600);
    }
  }, 'json');
}

function loadWeightChart() {
  $.get('../api/weight.php', { action: 'get_history' }, function(data) {
    const labels = data.map(d => d.recorded_date);
    const weights = data.map(d => parseFloat(d.weight_kg));

    if (window.weightChartInstance) {
      window.weightChartInstance.destroy();
    }

    window.weightChartInstance = new Chart(document.getElementById('weightChart'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Weight (kg)',
          data: weights,
          borderColor: '#e4381c',
          backgroundColor: 'rgba(26, 122, 74, 0.15)',
          tension: 0.3,
          fill: true,
          pointRadius: 4,
          pointHoverRadius: 5
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: false } }
      }
    });
  }, 'json');
}

loadWeightChart();
</script>
</body>
</html>
