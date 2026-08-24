<?php $user = $user ?? get_user($_SESSION['user_id']); ?>
<div class="topbar">
  <button class="menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
  <h2><?= ucfirst(str_replace(['.php','-'], ['', ' '], basename($_SERVER['PHP_SELF']))) ?></h2>
  <div class="user-info"><i class="fas fa-user-circle"></i><span><?= htmlspecialchars($user['name']) ?></span></div>
</div>
