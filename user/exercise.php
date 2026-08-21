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
  <title>FitLife – Exercise</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/user.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css">
  <style>
    .exercise-shell { display:grid; gap:20px; }
    .summary-card, .library-card { border-top:4px solid #e4381c; box-shadow:0 10px 24px rgba(26,122,74,0.10); }
    .filter-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
    .filter-tab { border:1px solid #dfe8e2; background:#fff; color:#355; border-radius:999px; padding:7px 12px; font-size:13px; font-weight:600; }
    .filter-tab.active { background:#e4381c; color:#fff; border-color:#e4381c; }
    .exercise-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:16px; }
    .exercise-card { border:1px solid #e8efe9; border-radius:16px; overflow:hidden; background:#fcfffd; transition:transform .2s ease, box-shadow .2s ease; }
    .exercise-card:hover { transform:translateY(-2px); box-shadow:0 10px 20px rgba(0,0,0,.06); }
    .exercise-media { height:140px; background:linear-gradient(135deg, #edf8f2, #dff2e5); display:flex; align-items:center; justify-content:center; color:#e4381c; font-size:36px; }
    .exercise-media img { width:100%; height:100%; object-fit:cover; }
    .exercise-body { padding:14px; }
    .exercise-title { font-weight:700; color:#233; margin-bottom:6px; }
    .exercise-badge { display:inline-block; padding:4px 9px; border-radius:999px; font-size:12px; background:#eef7f2; color:#e4381c; text-transform:capitalize; }
    .exercise-meta { font-size:13px; color:#789; margin-top:8px; }
    .btn-log { margin-top:12px; background:#e4381c; color:#fff; border:none; padding:8px 12px; border-radius:10px; font-size:13px; font-weight:600; }
    .btn-log:hover { background:#b8290e; }
    .summary-progress { height:10px; border-radius:999px; background:#eaf5ee; overflow:hidden; }
    .summary-progress > div { height:100%; background:linear-gradient(90deg, #e4381c, #e4381c); }
    .summary-row { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:10px; }
    .summary-pill { background:#f3fbf6; color:#e4381c; border:1px solid #dcefe2; padding:6px 10px; border-radius:999px; font-size:12px; }
    @media (max-width: 992px) { .exercise-grid { grid-template-columns:1fr 1fr; } }
    @media (max-width: 650px) { .exercise-grid { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
<?php include 'topbar.php'; ?>
<div class="content exercise-shell">
  <div class="card summary-card">
    <div class="card-header">
      <h4><i class="fas fa-fire"></i> Today's Summary</h4>
    </div>
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div class="display-6 fw-bold" id="today-total">0</div>
          <div class="text-muted">Calories burned today</div>
        </div>
        <div class="summary-pill" id="today-goal">Goal: 0 kcal</div>
      </div>
      <div class="summary-progress mt-3">
        <div id="summary-progress-fill" style="width:0%"></div>
      </div>
      <div class="summary-row" id="summary-categories"></div>
    </div>
  </div>

  <div class="card library-card">
    <div class="card-header">
      <div>
        <h4><i class="fas fa-dumbbell"></i> Exercise Library</h4>
        <p class="mb-0 text-muted" style="font-size:13px;">Pick an exercise, set your duration, and log it instantly.</p>
      </div>
    </div>
    <div class="card-body">
      <div class="filter-tabs">
        <button class="filter-tab active" data-category="all">All</button>
        <button class="filter-tab" data-category="cardio">Cardio</button>
        <button class="filter-tab" data-category="yoga">Yoga</button>
        <button class="filter-tab" data-category="strength">Strength</button>
        <button class="filter-tab" data-category="flexibility">Flexibility</button>
      </div>
      <div id="exercise-grid" class="exercise-grid"></div>
    </div>
  </div>
</div>
</div>

<div class="modal fade" id="logExerciseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-running me-2"></i>Log Exercise</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Exercise</label>
          <select id="exercise-select" class="form-select"></select>
        </div>
        <div class="mb-3">
          <label class="form-label">Duration (minutes)</label>
          <input type="number" id="duration-mins" class="form-control" min="1" value="20">
        </div>
        <div class="alert alert-light border" id="calorie-preview">Estimated calories: 0 kcal</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="save-log-btn">Log Workout</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function logout() {
  $.post('../api/auth.php', { action: 'logout' }, function() {
    window.location = '../index.php';
  }, 'json');
}

function toggleSidebar() {
  $('#sidebar').toggleClass('open');
}

let currentExercises = [];
let activeCategory = 'all';
let selectedExerciseId = null;
let logModal = new bootstrap.Modal(document.getElementById('logExerciseModal'));

function renderExercises(items) {
  currentExercises = items;
  const grid = $('#exercise-grid');
  if (!items.length) {
    grid.html('<div class="text-muted">No exercises found for this category.</div>');
    return;
  }

  grid.html(items.map(item => {
    const media = item.media_url ? `<img src="${item.media_url}" alt="${item.title}">` : `<i class="fas fa-running"></i>`;
    return `
      <div class="exercise-card">
        <div class="exercise-media">${media}</div>
        <div class="exercise-body">
          <div class="exercise-title">${item.title}</div>
          <span class="exercise-badge">${item.category}</span>
          <div class="exercise-meta">${item.instructions ? item.instructions.substring(0, 90) + '...' : 'No instructions provided.'}</div>
          <div class="exercise-meta"><strong>${item.calories_per_min}</strong> kcal/min</div>
          <button class="btn-log w-100" onclick="openLogModal(${item.id})"><i class="fas fa-plus"></i> Log Exercise</button>
        </div>
      </div>`;
  }).join(''));
}

function loadExercises(category) {
  activeCategory = category;
  $('.filter-tab').removeClass('active');
  $('.filter-tab[data-category="' + category + '"]').addClass('active');

  $.get('../api/exercise.php', { action: 'library', category: category }, function(data) {
    renderExercises(data);
    populateExerciseSelect(data);
  }, 'json');
}

function populateExerciseSelect(items) {
  const select = $('#exercise-select');
  select.empty();
  items.forEach(item => {
    select.append(`<option value="${item.id}" ${selectedExerciseId === item.id ? 'selected' : ''}>${item.title}</option>`);
  });
  if (!items.length) {
    select.append('<option value="">No exercises available</option>');
  }
}

function openLogModal(exerciseId) {
  selectedExerciseId = exerciseId;
  const selected = currentExercises.find(item => item.id === exerciseId) || currentExercises[0];
  if (selected) {
    $('#exercise-select').val(selected.id);
    $('#duration-mins').val(20);
    updateCaloriePreview();
    logModal.show();
  }
}

function updateCaloriePreview() {
  const exerciseId = parseInt($('#exercise-select').val(), 10);
  const duration = parseInt($('#duration-mins').val(), 10) || 0;
  const exercise = currentExercises.find(item => item.id === exerciseId);
  if (!exercise || duration <= 0) {
    $('#calorie-preview').text('Estimated calories: 0 kcal');
    return;
  }

  const weight = <?= (float) ($user['weight_kg'] ?? 70) ?>;
  const burned = Math.round(duration * exercise.calories_per_min * (weight / 70));
  $('#calorie-preview').text(`Estimated calories: ${burned} kcal`);
}

$('#exercise-select').on('change', updateCaloriePreview);
$('#duration-mins').on('input', updateCaloriePreview);

$('#save-log-btn').on('click', function() {
  const exerciseId = $('#exercise-select').val();
  const duration = $('#duration-mins').val();
  if (!exerciseId || !duration) {
    $('#calorie-preview').text('Please select an exercise and enter a duration.');
    return;
  }

  $.post('../api/exercise.php', {
    action: 'log',
    exercise_id: exerciseId,
    duration_mins: duration
  }, function(res) {
    if (res.status === 'success') {
      $('#calorie-preview').text(`Logged ${res.calories_burned} kcal for today.`);
      loadTodaySummary();
      logModal.hide();
    } else {
      $('#calorie-preview').text(res.message || 'Unable to log workout.');
    }
  }, 'json');
});

function loadTodaySummary() {
  $.get('../api/exercise.php', { action: 'today_totals' }, function(data) {
    $('#today-total').text(data.total_calories || 0);
    $('#today-goal').text('Goal: ' + (data.daily_goal || 600) + ' kcal');
    const pct = Math.min(100, Math.round(((data.total_calories || 0) / (data.daily_goal || 600)) * 100));
    $('#summary-progress-fill').css('width', pct + '%');

    let html = '';
    data.by_category.forEach(item => {
      html += `<div class="summary-pill">${item.category}: ${item.calories} kcal</div>`;
    });
    $('#summary-categories').html(html);
  }, 'json');
}

$('.filter-tab').on('click', function() {
  loadExercises($(this).data('category'));
});

loadExercises('all');
loadTodaySummary();
</script>
</body>
</html>
