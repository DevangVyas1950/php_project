<?php
require_once '../includes/admin_check.php';
require_once '../includes/functions.php';
require_once '../config/db.php';
$users = get_all_users();
$charts = get_all_diet_charts();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Manage Users – FitLife Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css"></head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
<div class="topbar"><h2>Manage Users</h2><span class="admin-badge">Admin</span></div>
<div class="content">
<div class="card">
  <div class="card-header"><h4><i class="fas fa-users"></i> All Users</h4></div>
  <table>
    <tr><th>Name</th><th>Email</th><th>Goal</th><th>Weight</th><th>BMI</th><th>Joined</th><th>Assign Plan</th></tr>
    <?php foreach ($users as $u): if($u['role']==='admin')continue; ?>
    <tr>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><span class="badge badge-info"><?= str_replace('_',' ',$u['goal']) ?></span></td>
      <td><?= $u['weight_kg'] ?> kg</td>
      <td><?= $u['bmi'] ?></td>
      <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
      <td>
        <select onchange="assignDiet(<?= $u['id'] ?>,this.value)" style="padding:4px;border:1px solid #ddd;border-radius:5px;font-size:12px">
          <option value="">Assign plan…</option>
          <?php foreach($charts as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
</div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
function logout(){$.post('../api/auth.php',{action:'logout'},()=>window.location='../index.php','json')}
function assignDiet(uid,cid){
  if(!cid)return;
  $.post('../api/admin.php',{action:'assign_diet',user_id:uid,chart_id:cid},function(res){
    alert(res.status==='success'?'Diet plan assigned!':'Error: '+res.message);
  },'json');
}
</script>
</body></html>
