<?php
require_once '../includes/admin_check.php';
require_once '../includes/functions.php';
$charts = get_all_diet_charts();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Manage Diet Charts – FitLife Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css"></head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
<div class="topbar"><h2>Manage Diet Charts</h2><span class="admin-badge">Admin</span></div>
<div class="content">
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><h4><i class="fas fa-plus"></i> Create Diet Chart</h4></div>
  <div class="form-section">
    <div id="diet-alert" class="alert"></div>
    <div class="form-row">
      <div class="form-group"><label>Title</label><input type="text" id="d-title" placeholder="Weight Loss Plan – 1500 kcal"></div>
      <div class="form-group"><label>Goal</label>
        <select id="d-goal"><option value="lose_weight">Lose Weight</option><option value="gain_muscle">Gain Muscle</option><option value="stay_healthy">Stay Healthy</option></select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Total Calories</label><input type="number" id="d-cal" placeholder="1500"></div>
      <div class="form-group"><label>Description</label><input type="text" id="d-desc" placeholder="Brief description..."></div>
    </div>
    <button class="btn-sm btn-primary" onclick="addChart()"><i class="fas fa-plus"></i> Create Chart</button>
  </div>
</div>
<div class="card">
  <div class="card-header"><h4><i class="fas fa-clipboard-list"></i> Diet Charts</h4></div>
  <table>
    <tr><th>Title</th><th>Goal</th><th>Calories</th><th>Created By</th></tr>
    <?php foreach ($charts as $c): ?>
    <tr>
      <td><?= htmlspecialchars($c['title']) ?></td>
      <td><span class="badge badge-info"><?= str_replace('_',' ',$c['goal']) ?></span></td>
      <td><?= $c['total_calories'] ?> kcal</td>
      <td><?= htmlspecialchars($c['created_by_name'] ?? 'Admin') ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
</div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
function logout(){$.post('../api/auth.php',{action:'logout'},()=>window.location='../index.php','json')}
function addChart(){
  $.post('../api/admin.php',{action:'add_diet_chart',title:$('#d-title').val(),goal:$('#d-goal').val(),total_calories:$('#d-cal').val(),description:$('#d-desc').val()},function(res){
    let al=$('#diet-alert');
    if(res.status==='success'){al.attr('class','alert success').text('Diet chart created!').show();location.reload();}
    else al.attr('class','alert error').text(res.message).show();
  },'json');
}
</script>
</body></html>
