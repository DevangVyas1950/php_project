<div class="sidebar">
  <div class="sidebar-logo"><i class="fas fa-heartbeat"></i> FitLife Admin</div>
  <div class="sidebar-section">Main</div>
  <nav>
    <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'class="active"':'' ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="manage-users.php" <?= basename($_SERVER['PHP_SELF'])=='manage-users.php'?'class="active"':'' ?>><i class="fas fa-users"></i> Users</a>
    <div class="sidebar-section">Content</div>
    <a href="manage-diet.php" <?= basename($_SERVER['PHP_SELF'])=='manage-diet.php'?'class="active"':'' ?>><i class="fas fa-utensils"></i> Diet Charts</a>
    <a href="manage-food.php" <?= basename($_SERVER['PHP_SELF'])=='manage-food.php'?'class="active"':'' ?>><i class="fas fa-apple-alt"></i> Foods</a>
    <a href="manage-exercise.php" <?= basename($_SERVER['PHP_SELF'])=='manage-exercise.php'?'class="active"':'' ?>><i class="fas fa-dumbbell"></i> Exercises</a>
  </nav>
  <div class="sidebar-footer"><a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>
