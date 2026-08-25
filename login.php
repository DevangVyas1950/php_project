<?php taal se taal mila ho ho taal se taal mila
require_once 'config/constants.php';
if (is_logged_in()) {
    if (is_admin()) redirect('admin/dashboard.php');
    else redirect('user/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FitLife – Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Nunito Sans', sans-serif; background: linear-gradient(135deg, #e4381c 0%, #8f2b0d 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
.card { background:#fff; border-radius:16px; padding:40px; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.logo { text-align:center; margin-bottom:28px; }
.logo i { font-size:48px; color:#e4381c; }
.logo h1 { font-family:'Oswald', sans-serif; font-size:28px; color:#e4381c; font-weight:700; margin-top:8px; letter-spacing:1px; text-transform:uppercase; }
.logo p { color:#666; font-size:14px; }
.tabs { display:flex; border-bottom:2px solid #eee; margin-bottom:24px; }
.tab { flex:1; padding:10px; text-align:center; cursor:pointer; font-weight:600; color:#999; border-bottom:2px solid transparent; margin-bottom:-2px; transition:.2s; font-family:'Oswald', sans-serif; letter-spacing:.5px; text-transform:uppercase; font-size:13px; }
.tab.active { color:#e4381c; border-bottom-color:#e4381c; }
.form-group { margin-bottom:16px; }
label { display:block; font-size:13px; font-weight:600; color:#333; margin-bottom:6px; }
input, select { width:100%; padding:10px 14px; border:1.5px solid #ddd; border-radius:8px; font-size:14px; transition:.2s; }
input:focus, select:focus { outline:none; border-color:#e4381c; box-shadow:0 0 0 3px rgba(228,56,28,.12); }
.row2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.btn { width:100%; padding:12px; background:linear-gradient(to right,#e4381c,#e16521); color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; margin-top:8px; transition:.2s; font-family:'Oswald', sans-serif; letter-spacing:1px; text-transform:uppercase; box-shadow:0 4px 14px rgba(228,56,28,.28); }
.btn:hover { background:linear-gradient(to right,#e16521,#e4381c); box-shadow:0 6px 20px rgba(228,56,28,.4); transform:translateY(-1px); }
.alert { padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:16px; display:none; }
.alert.error { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.alert.success { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
#register-form { display:none; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <i class="fas fa-heartbeat"></i>
    <h1>FitLife</h1>
    <p>Your personal health & fitness companion</p>
  </div>
  <div class="tabs">
    <div class="tab active" onclick="showTab('login')">Login</div>
    <div class="tab" onclick="showTab('register')">Register</div>
  </div>
  <div id="alert" class="alert"></div>

  <!-- LOGIN FORM -->
  <div id="login-form">
    <div class="form-group">
      <label>Email</label>
      <input type="email" id="l-email" placeholder="you@example.com">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" id="l-password" placeholder="Your password">
    </div>
    <button class="btn" onclick="doLogin()"><i class="fas fa-sign-in-alt"></i> Login</button>
  </div>

  <!-- REGISTER FORM -->
  <div id="register-form">
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" id="r-name" placeholder="Rahul Mehta">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" id="r-email" placeholder="you@example.com">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" id="r-password" placeholder="Min 6 characters">
    </div>
    <div class="row2">
      <div class="form-group">
        <label>Age</label>
        <input type="number" id="r-age" placeholder="25" min="10" max="100">
      </div>
      <div class="form-group">
        <label>Gender</label>
        <select id="r-gender">
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>
      </div>
    </div>
    <div class="row2">
      <div class="form-group">
        <label>Weight (kg)</label>
        <input type="number" id="r-weight" placeholder="70" step="0.1">
      </div>
      <div class="form-group">
        <label>Height (cm)</label>
        <input type="number" id="r-height" placeholder="170">
      </div>
    </div>
    <div class="form-group">
      <label>Your Goal</label>
      <select id="r-goal">
        <option value="lose_weight">Lose Weight</option>
        <option value="gain_muscle">Gain Muscle</option>
        <option value="stay_healthy">Stay Healthy</option>
      </select>
    </div>
    <button class="btn" onclick="doRegister()"><i class="fas fa-user-plus"></i> Register</button>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
function showTab(tab) {
  $('.tab').removeClass('active');
  $(`.tab:contains(${tab === 'login' ? 'Login' : 'Register'})`).addClass('active');
  $('#login-form, #register-form').hide();
  $(`#${tab}-form`).show();
  $('#alert').hide();
}

function showAlert(msg, type) {
  $('#alert').attr('class', `alert ${type}`).text(msg).show();
}

function doLogin() {
  $.post('api/auth.php', {
    action: 'login',
    email: $('#l-email').val(),
    password: $('#l-password').val()
  }, function(res) {
    if (res.status === 'success') {
      showAlert('Logging you in...', 'success');
      setTimeout(() => window.location = res.role === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php', 800);
    } else showAlert(res.message, 'error');
  }, 'json');
}

function doRegister() {
  $.post('api/auth.php', {
    action: 'register',
    name: $('#r-name').val(),
    email: $('#r-email').val(),
    password: $('#r-password').val(),
    age: $('#r-age').val(),
    gender: $('#r-gender').val(),
    weight_kg: $('#r-weight').val(),
    height_cm: $('#r-height').val(),
    goal: $('#r-goal').val()
  }, function(res) {
    if (res.status === 'success') {
      showAlert('Registered! Please login.', 'success');
      setTimeout(() => showTab('login'), 1200);
    } else showAlert(res.message, 'error');
  }, 'json');
}

$(document).keypress(function(e) {
  if (e.which == 13) {
    if ($('#login-form').is(':visible')) doLogin();
    else doRegister();
  }
});
</script>
</body>
</html>
