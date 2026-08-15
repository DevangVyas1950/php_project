<?php
require_once '../includes/admin_check.php';
require_once '../includes/functions.php';
require_once '../config/db.php';

$total_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
$total_foods = $conn->query("SELECT COUNT(*) as c FROM foods")->fetch_assoc()['c'];
$total_ex    = $conn->query("SELECT COUNT(*) as c FROM exercises")->fetch_assoc()['c'];
$total_logs  = $conn->query("SELECT COUNT(*) as c FROM food_log WHERE logged_date=CURDATE()")->fetch_assoc()['c'];
$recent_users = $conn->query("SELECT name,email,goal,bmi,created_at FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>FitLife Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css"></head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <h2>Admin Dashboard</h2>
    <span class="admin-badge"><i class="fas fa-shield-alt"></i> Admin</span>
    <span style="font-size:13px;color:#888"><?= htmlspecialchars($_SESSION['name']) ?></span>
  </div>
  <div class="content">
    <div class="stats-grid">
      <div class="stat-card green"><div class="stat-icon"><i class="fas fa-users"></i></div>
        <div><div class="stat-num"><?= $total_users ?></div><div class="stat-label">Total Users</div></div></div>
      <div class="stat-card blue"><div class="stat-icon"><i class="fas fa-apple-alt"></i></div>
        <div><div class="stat-num"><?= $total_foods ?></div><div class="stat-label">Food Items</div></div></div>
      <div class="stat-card orange"><div class="stat-icon"><i class="fas fa-dumbbell"></i></div>
        <div><div class="stat-num"><?= $total_ex ?></div><div class="stat-label">Exercises</div></div></div>
      <div class="stat-card red"><div class="stat-icon"><i class="fas fa-fire"></i></div>
        <div><div class="stat-num"><?= $total_logs ?></div><div class="stat-label">Logs Today</div></div></div>
    </div>

    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-users"></i> Recent Users</h4>
        <a href="manage-users.php" style="font-size:13px;color:#e4381c;text-decoration:none">View all</a>
      </div>
      <table>
        <tr><th>Name</th><th>Email</th><th>Goal</th><th>BMI</th><th>Joined</th></tr>
        <?php foreach ($recent_users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge badge-info"><?= str_replace('_',' ',$u['goal']) ?></span></td>
          <td><?= $u['bmi'] ?></td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>function logout(){$.post('../api/auth.php',{action:'logout'},()=>window.location='../index.php','json')}</script>
</body></html>
