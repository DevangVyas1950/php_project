<?php
// includes/header.php - User panel header (html5up.net Phantom theme style)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'FitLife' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="is-preload">
<div id="wrapper">
  <header id="header">
    <div class="inner">
      <a href="<?= BASE_URL ?>user/dashboard.php" class="logo">
        <span class="symbol"><i class="fa fa-heartbeat"></i></span>
        <span class="title">FitLife</span>
      </a>
      <nav><ul><li><a href="#menu">Menu</a></li></ul></nav>
    </div>
  </header>
  <nav id="menu">
    <h2>Menu</h2>
    <ul>
      <li><a href="<?= BASE_URL ?>user/dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
      <li><a href="<?= BASE_URL ?>user/diet-chart.php"><i class="fa fa-utensils"></i> Diet Chart</a></li>
      <li><a href="<?= BASE_URL ?>user/exercise.php"><i class="fa fa-dumbbell"></i> Exercises</a></li>
      <li><a href="<?= BASE_URL ?>user/food-log.php"><i class="fa fa-apple-alt"></i> Food Log</a></li>
      <li><a href="<?= BASE_URL ?>user/daily-tasks.php"><i class="fa fa-tasks"></i> Daily Tasks</a></li>
      <li><a href="<?= BASE_URL ?>user/profile.php"><i class="fa fa-user"></i> Profile</a></li>
      <li><a href="<?= BASE_URL ?>api/auth.php?action=logout"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </nav>
  <div id="main">
