<?php
require_once '../includes/admin_check.php';
require_once '../includes/functions.php';
$foods = get_all_foods();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Manage Foods – FitLife Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css"></head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
<div class="topbar"><h2>Manage Foods</h2><span class="admin-badge">Admin</span></div>
<div class="content">
<div class="card" style="margin-bottom:20px">
  <div class="card-header"><h4><i class="fas fa-plus"></i> Add Food Item</h4></div>
  <div class="form-section">
    <div id="food-alert" class="alert"></div>
    <div class="form-row">
      <div class="form-group"><label>Name</label><input type="text" id="f-name" placeholder="Grilled Chicken"></div>
      <div class="form-group"><label>Category</label>
        <select id="f-cat"><option value="breakfast">Breakfast</option><option value="lunch">Lunch</option><option value="dinner">Dinner</option><option value="snack">Snack</option></select>
      </div>
    </div>
    <div class="form-row" style="grid-template-columns:repeat(4,1fr)">
      <div class="form-group"><label>Calories/100g</label><input type="number" id="f-cal" step="0.1"></div>
      <div class="form-group"><label>Protein (g)</label><input type="number" id="f-prot" step="0.1"></div>
      <div class="form-group"><label>Carbs (g)</label><input type="number" id="f-carbs" step="0.1"></div>
      <div class="form-group"><label>Fat (g)</label><input type="number" id="f-fat" step="0.1"></div>
    </div>
    <button class="btn-sm btn-primary" onclick="addFood()"><i class="fas fa-plus"></i> Add Food</button>
  </div>
</div>
<div class="card">
  <div class="card-header"><h4><i class="fas fa-apple-alt"></i> Food Database (<?= count($foods) ?> items)</h4></div>
  <table>
    <tr><th>Name</th><th>Category</th><th>Calories/100g</th><th>Protein</th><th>Carbs</th><th>Fat</th></tr>
    <?php foreach ($foods as $f): ?>
    <tr>
      <td><?= htmlspecialchars($f['name']) ?></td>
      <td><span class="badge badge-gray"><?= $f['category'] ?></span></td>
      <td><?= $f['calories_per_100g'] ?> kcal</td>
      <td><?= $f['protein_g'] ?>g</td>
      <td><?= $f['carbs_g'] ?>g</td>
      <td><?= $f['fat_g'] ?>g</td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
</div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
function logout(){$.post('../api/auth.php',{action:'logout'},()=>window.location='../index.php','json')}
function addFood(){
  $.post('../api/admin.php',{action:'add_food',name:$('#f-name').val(),category:$('#f-cat').val(),calories_per_100g:$('#f-cal').val(),protein_g:$('#f-prot').val(),carbs_g:$('#f-carbs').val(),fat_g:$('#f-fat').val()},function(res){
    let al=$('#food-alert');
    if(res.status==='success'){al.attr('class','alert success').text('Food added!').show();location.reload();}
    else al.attr('class','alert error').text(res.message).show();
  },'json');
}
</script>
</body></html>
