<?php
require_once '../includes/auth_check.php';
require_once '../includes/functions.php';

$user = get_user($_SESSION['user_id']);
$chart = get_user_diet_chart($_SESSION['user_id']);
$meals = $chart ? get_chart_meals($chart['id']) : [];
$grouped = [];
foreach ($meals as $m) {
    $grouped[$m['meal_time']][] = $m;
}

$recommendation = get_diet_recommendation($user);
$goalDisplay = str_replace('_', ' ', ucwords($user['goal'] ?? 'stay_healthy'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLife – Diet Chart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/user.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css">
    <style>
        .accent-card {
            border: 1px solid #e4381c;
            border-top: 4px solid #e4381c;
            box-shadow: 0 8px 20px rgba(26, 122, 74, 0.12);
        }
        .accent-text { color: #e4381c; }
        .accent-bg { background-color: #e4381c; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
<?php include 'topbar.php'; ?>
<div class="content">
    <div class="card p-4 mb-4 accent-card">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h3 class="mb-1 accent-text"><i class="fas fa-utensils me-2"></i>Your Personalized Diet Plan</h3>
                <p class="mb-0 text-muted">Hello <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>, your plan is tailored for <?= htmlspecialchars($goalDisplay) ?> with your age, body stats, and activity profile in mind.</p>
            </div>
            <div class="text-lg-end">
                <div class="h3 mb-0 accent-text"><?= (int) $recommendation['target_calories'] ?> kcal</div>
                <small class="text-muted">Target calories per day</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-3 accent-card">
                <div class="small text-uppercase text-muted">Profile</div>
                <div class="h5 mt-2 mb-0 accent-text"><?= htmlspecialchars($user['age'] ?? '—') ?> yrs</div>
                <small class="text-muted">Age bracket: <?= htmlspecialchars(str_replace('_', ' ', $recommendation['age_bracket'])) ?></small>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-3 accent-card">
                <div class="small text-uppercase text-muted">Goal</div>
                <div class="h5 mt-2 mb-0 accent-text"><?= htmlspecialchars($recommendation['goal_label']) ?></div>
                <small class="text-muted"><?= htmlspecialchars($recommendation['focus']) ?></small>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-3 accent-card">
                <div class="small text-uppercase text-muted">Target Macros</div>
                <div class="h5 mt-2 mb-0 accent-text">Protein <?= (int) $recommendation['target_macros']['protein_g'] ?>g</div>
                <small class="text-muted">Carbs <?= (int) $recommendation['target_macros']['carbs_g'] ?>g • Fat <?= (int) $recommendation['target_macros']['fat_g'] ?>g</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 p-3 accent-card">
                <div class="small text-uppercase text-muted">Body Stats</div>
                <div class="h5 mt-2 mb-0 accent-text"><?= htmlspecialchars($user['weight_kg'] ?? '—') ?> kg</div>
                <small class="text-muted"><?= htmlspecialchars($user['height_cm'] ?? '—') ?> cm • <?= htmlspecialchars($user['gender'] ?? 'other') ?></small>
            </div>
        </div>
    </div>

    <div class="card mb-4 accent-card">
        <div class="card-header accent-bg text-white">
            <h4 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>4-Meal Daily Breakdown</h4>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($recommendation['meal_plan'] as $meal): ?>
                    <div class="col-md-6 col-xl-3">
                        <div class="card h-100 border-0" style="background:#f7fbf8">
                            <div class="card-body">
                                <div class="fw-bold accent-text mb-2"><?= htmlspecialchars($meal['meal']) ?></div>
                                <h6 class="mb-2"><?= htmlspecialchars($meal['title']) ?></h6>
                                <p class="mb-0 text-muted small"><?= htmlspecialchars($meal['details']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card accent-card">
        <div class="card-header accent-bg text-white">
            <h4 class="mb-0"><i class="fas fa-utensils me-2"></i>Assigned Diet Chart</h4>
        </div>
        <div class="card-body">
            <?php if (!$chart): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-utensils fa-3x mb-3"></i>
                    <h5>No diet plan assigned yet</h5>
                    <p class="mb-0">Please ask your admin to assign a diet plan to your account.</p>
                </div>
            <?php else: ?>
                <div class="welcome-banner mb-3" style="margin-bottom:20px">
                    <div>
                        <h3><?= htmlspecialchars($chart['title']) ?></h3>
                        <p><?= htmlspecialchars($chart['description']) ?></p>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:28px;font-weight:700"><?= $chart['total_calories'] ?></div>
                        <div style="font-size:12px;opacity:.8">kcal per day</div>
                    </div>
                </div>

                <div class="meal-section">
                    <?php foreach (['breakfast','lunch','dinner','snack'] as $mt): ?>
                        <?php if (!empty($grouped[$mt])): ?>
                            <div class="meal-time-label"><i class="fas fa-clock"></i> <?= ucfirst($mt) ?></div>
                            <?php foreach ($grouped[$mt] as $meal): ?>
                                <div class="meal-row">
                                    <div>
                                        <div class="meal-name"><?= htmlspecialchars($meal['name']) ?> (<?= $meal['quantity_g'] ?>g)</div>
                                        <div class="macro-pills">
                                            <span class="macro">P: <?= round($meal['protein_g'] * $meal['quantity_g'] / 100, 1) ?>g</span>
                                            <span class="macro">C: <?= round($meal['carbs_g'] * $meal['quantity_g'] / 100, 1) ?>g</span>
                                            <span class="macro">F: <?= round($meal['fat_g'] * $meal['quantity_g'] / 100, 1) ?>g</span>
                                        </div>
                                    </div>
                                    <div class="meal-cals"><?= $meal['total_calories'] ?> kcal</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
function logout() {
  $.post('../api/auth.php', { action: 'logout' }, function() {
    window.location = '../index.php';
  }, 'json');
}
function toggleSidebar() {
  $('#sidebar').toggleClass('open');
}
</script>
</body>
</html>
