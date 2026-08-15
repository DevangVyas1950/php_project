<?php
require_once '../includes/admin_check.php';
require_once '../includes/functions.php';
$exercises = get_all_exercises();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Manage Exercises – FitLife Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css"></head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
<div class="topbar"><h2>Manage Exercises</h2><span class="admin-badge">Admin</span></div>
<div class="content">
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><h4><i class="fas fa-plus"></i> Add Exercise</h4></div>
  <div class="form-section">
    <div id="ex-alert" class="alert"></div>
    <div class="form-row">
      <div class="form-group"><label>Name</label><input type="text" id="e-name" placeholder="Push-Ups"></div>
      <div class="form-group"><label>Category</label>
        <select id="e-cat"><option value="cardio">Cardio</option><option value="strength">Strength</option><option value="flexibility">Flexibility</option><option value="yoga">Yoga</option></select>
      </div>
    </div>
    <div class="form-row" style="grid-template-columns:1fr 1fr 1fr">
      <div class="form-group"><label>Duration (min)</label><input type="number" id="e-dur"></div>
      <div class="form-group"><label>Calories Burned</label><input type="number" id="e-cal"></div>
      <div class="form-group"><label>Difficulty</label>
        <select id="e-diff"><option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option></select>
      </div>
    </div>
    <div class="form-group"><label>Instructions</label><textarea id="e-inst" placeholder="Step by step instructions..."></textarea></div>
    <button class="btn-sm btn-primary" onclick="addExercise()"><i class="fas fa-plus"></i> Add Exercise</button>
  </div>
</div>
<div class="card">
  <div class="card-header"><h4><i class="fas fa-dumbbell"></i> Exercise Library (<?= count($exercises) ?> exercises)</h4></div>
  <table>
    <tr><th>Name</th><th>Category</th><th>Duration</th><th>Calories</th><th>Difficulty</th></tr>
    <?php foreach ($exercises as $ex): ?>
    <tr>
      <td><?= htmlspecialchars($ex['name']) ?></td>
      <td><span class="badge badge-info"><?= $ex['category'] ?></span></td>
      <td><?= $ex['duration_min'] ?> min</td>
      <td><?= $ex['calories_burned'] ?> kcal</td>
      <td><span class="badge badge-<?= $ex['difficulty']==='beginner'?'success':($ex['difficulty']==='intermediate'?'warning':'danger') ?>"><?= ucfirst($ex['difficulty']) ?></span></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
</div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
function logout(){$.post('../api/auth.php',{action:'logout'},()=>window.location='../index.php','json')}
function addExercise(){
  $.post('../api/admin.php',{action:'add_exercise',name:$('#e-name').val(),category:$('#e-cat').val(),duration_min:$('#e-dur').val(),calories_burned:$('#e-cal').val(),difficulty:$('#e-diff').val(),instructions:$('#e-inst').val()},function(res){
    let al=$('#ex-alert');
    if(res.status==='success'){al.attr('class','alert success').text('Exercise added!').show();location.reload();}
    else al.attr('class','alert error').text(res.message).show();
  },'json');
}
</script>
</body></html>
