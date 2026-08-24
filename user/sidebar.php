<div class="sidebar" id="sidebar">
  <div class="sidebar-logo"><i class="fas fa-heartbeat"></i> FitLife</div>
  <nav>
    <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'class="active"':'' ?>><i class="fas fa-home"></i> Dashboard</a>
    <a href="diet-chart.php" <?= basename($_SERVER['PHP_SELF'])=='diet-chart.php'?'class="active"':'' ?>><i class="fas fa-utensils"></i> Diet Chart</a>
    <a href="food-log.php" <?= basename($_SERVER['PHP_SELF'])=='food-log.php'?'class="active"':'' ?>><i class="fas fa-apple-alt"></i> Food Log</a>
    <a href="exercise.php" <?= basename($_SERVER['PHP_SELF'])=='exercise.php'?'class="active"':'' ?>><i class="fas fa-dumbbell"></i> Exercise</a>
    <a href="daily-tasks.php" <?= basename($_SERVER['PHP_SELF'])=='daily-tasks.php'?'class="active"':'' ?>><i class="fas fa-tasks"></i> Daily Tasks</a>
    <a href="profile.php" <?= basename($_SERVER['PHP_SELF'])=='profile.php'?'class="active"':'' ?>><i class="fas fa-user"></i> Profile</a>
    <a href="weight-tracker.php" <?= basename($_SERVER['PHP_SELF'])=='weight-tracker.php'?'class="active"':'' ?>><i class="fas fa-weight"></i> Weight Tracker</a>
  </nav>
  <div class="sidebar-footer"><a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>
