<?php
require_once '../includes/auth_check.php';
require_once '../includes/functions.php';
$user = get_user($_SESSION['user_id']);
$bmi_cat = bmi_category($user['bmi']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FitLife – Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/user.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <style>
    .profile-grid { display:grid; grid-template-columns: 1.1fr 0.9fr; gap:24px; }
    .profile-card { border-top:4px solid #e4381c; box-shadow:0 10px 24px rgba(26,122,74,0.10); }
    .profile-form .form-group { margin-bottom:14px; }
    .profile-form label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:#355; }
    .profile-form input, .profile-form select { width:100%; padding:10px 12px; border:1px solid #dfe8e2; border-radius:10px; }
    .btn-save-profile { background:#e4381c; color:#fff; border:none; padding:11px 14px; border-radius:10px; font-weight:600; }
    .btn-save-profile:hover { background:#b8290e; }
    .feedback { margin-top:10px; min-height:20px; font-size:13px; color:#e4381c; }
    @media (max-width: 900px) { .profile-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
<?php include 'topbar.php'; ?>
<div class="content">
  <div class="profile-grid">
    <div class="card profile-card">
      <div class="card-header"><h4><i class="fas fa-user"></i> My Profile</h4></div>
      <div class="form-section">
        <div style="text-align:center;margin-bottom:20px">
          <i class="fas fa-user-circle" style="font-size:64px;color:#e4381c"></i>
          <h3 style="margin:8px 0 4px"><?= htmlspecialchars($user['name']) ?></h3>
          <p style="color:#888;font-size:13px"><?= htmlspecialchars($user['email']) ?></p>
          <span class="bmi-badge badge-<?= $bmi_cat['color'] ?>" style="margin-top:8px;display:inline-block">
            BMI <?= $user['bmi'] ?> – <?= $bmi_cat['label'] ?>
          </span>
        </div>

        <form class="profile-form" id="profile-form">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="age">Age</label>
              <input type="number" id="age" name="age" value="<?= htmlspecialchars($user['age'] ?? '') ?>" min="1">
            </div>
            <div class="form-group">
              <label for="gender">Gender</label>
              <select id="gender" name="gender">
                <option value="male" <?= ($user['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                <option value="female" <?= ($user['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                <option value="other" <?= ($user['gender'] === 'other') ? 'selected' : '' ?>>Other</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="weight_kg">Weight (kg)</label>
              <input type="number" id="weight_kg" name="weight_kg" step="0.1" value="<?= htmlspecialchars($user['weight_kg'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="height_cm">Height (cm)</label>
              <input type="number" id="height_cm" name="height_cm" step="0.1" value="<?= htmlspecialchars($user['height_cm'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="goal">Goal</label>
            <select id="goal" name="goal">
              <option value="lose_weight" <?= ($user['goal'] === 'lose_weight') ? 'selected' : '' ?>>Lose Weight</option>
              <option value="gain_muscle" <?= ($user['goal'] === 'gain_muscle') ? 'selected' : '' ?>>Gain Muscle</option>
              <option value="stay_healthy" <?= ($user['goal'] === 'stay_healthy') ? 'selected' : '' ?>>Stay Healthy</option>
            </select>
          </div>
          <button type="submit" class="btn-save-profile"><i class="fas fa-save"></i> Save Profile</button>
          <div id="profile-feedback" class="feedback"></div>
        </form>
      </div>
    </div>

    <div class="card profile-card">
      <div class="card-header"><h4><i class="fas fa-weight"></i> Log Progress</h4></div>
      <div class="form-section">
        <div class="form-group"><label>Current Weight (kg)</label>
          <input type="number" id="p-weight" value="<?= $user['weight_kg'] ?>" step="0.1"></div>
        <div class="form-group"><label>Notes</label>
          <textarea id="p-notes" placeholder="How are you feeling today?"></textarea></div>
        <button class="btn-primary" onclick="logProgress()"><i class="fas fa-save"></i> Save Progress</button>
        <div id="p-feedback" style="margin-top:10px"></div>
      </div>
    </div>
  </div>

  <div class="card" style="margin-top:20px">
    <div class="card-header"><h4><i class="fas fa-chart-line"></i> Weight Progress</h4></div>
    <div style="padding:20px"><canvas id="progress-chart" height="80"></canvas></div>
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

$('#profile-form').on('submit', function(e) {
  e.preventDefault();
  $('#profile-feedback').text('Saving...');

  $.ajax({
    url: '../api/update_profile.php',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(res) {
      $('#profile-feedback').text(res.message || 'Profile updated.');
      if (res.status === 'success') {
        $('#p-weight').val($('#weight_kg').val());
      }
    },
    error: function() {
      $('#profile-feedback').text('Unable to update profile.');
    }
  });
});

function logProgress() {
  $.post('../api/progress.php', { action: 'log', weight_kg: $('#p-weight').val(), notes: $('#p-notes').val() }, function(res) {
    if (res.status === 'success') {
      $('#p-feedback').html('<span class="success-msg">Progress saved! New BMI: ' + res.bmi + '</span>');
      loadChart();
    }
  }, 'json');
}

function loadChart() {
  $.get('../api/progress.php', { action: 'history' }, function(data) {
    let labels = data.map(d => d.log_date);
    let weights = data.map(d => parseFloat(d.weight_kg));
    if (window.myChart) window.myChart.destroy();
    window.myChart = new Chart(document.getElementById('progress-chart'), {
      type: 'line',
      data: { labels, datasets: [{ label: 'Weight (kg)', data: weights, borderColor: '#e4381c', backgroundColor: 'rgba(26,122,74,.1)', tension: .3, fill: true }] },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: false } } }
    });
  }, 'json');
}

loadChart();
</script>
</body>
</html>
