<?php
require_once '../includes/auth_check.php';
require_once '../includes/functions.php';

$user = get_user($_SESSION['user_id']);
$goal_cal = match ($user['goal'] ?? 'stay_healthy') {
    'lose_weight' => 1800,
    'gain_muscle' => 2600,
    default => 2200,
};
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLife – Food Log</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/user.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
<?php include 'topbar.php'; ?>
<div class="content">
    <div class="card p-4 mb-4" style="border-top: 4px solid #e4381c; box-shadow: 0 8px 20px rgba(26, 122, 74, 0.12);">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h3 class="mb-1" style="color:#e4381c;"><i class="fas fa-apple-alt me-2"></i>Food Log</h3>
                <p class="mb-0 text-muted">Track your meals, calories, and macros for today.</p>
            </div>
            <div class="text-lg-end">
                <div class="fw-bold" id="progress-text">0 / <?= $goal_cal ?> kcal</div>
                <small class="text-muted">Today's calorie goal</small>
            </div>
        </div>
        <div class="progress mt-3" style="height: 12px;">
            <div id="calorie-progress" class="progress-bar" style="width: 0%; background-color: #e4381c;"></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Log a Meal</h5>
                </div>
                <div class="card-body">
                    <form id="food-log-form">
                        <div class="mb-3">
                            <label class="form-label">Food title</label>
                            <input type="text" id="food_name" class="form-control" placeholder="e.g. Chicken Salad" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meal type</label>
                            <select id="meal_type" class="form-select">
                                <option value="Breakfast">Breakfast</option>
                                <option value="Lunch">Lunch</option>
                                <option value="Dinner">Dinner</option>
                                <option value="Snack">Snack</option>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Calories</label>
                                <input type="number" id="calories" class="form-control" min="1" step="0.1" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Date</label>
                                <input type="date" id="logged_date" class="form-control" value="<?= $today ?>">
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-4">
                                <label class="form-label">Protein</label>
                                <input type="number" id="protein_g" class="form-control" min="0" step="0.1" value="0">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Carbs</label>
                                <input type="number" id="carbs_g" class="form-control" min="0" step="0.1" value="0">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Fat</label>
                                <input type="number" id="fat_g" class="form-control" min="0" step="0.1" value="0">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mt-3">
                            <i class="fas fa-save me-2"></i>Save Meal
                        </button>
                    </form>
                    <div id="form-feedback" class="mt-3"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Today's Meals</h5>
                    <span class="badge" style="background-color:#e4381c;"><?= $today ?></span>
                </div>
                <div class="card-body" id="today-list"></div>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
const GOAL_CAL = <?= $goal_cal ?>;

function showFeedback(message, type = 'success') {
    const color = type === 'success' ? '#e4381c' : '#dc3545';
    $('#form-feedback').html(`<div class="alert py-2 mb-0" style="color:${color}; border-color:${color};">${message}</div>`);
}

function deleteLog(id) {
    if (!confirm('Remove this meal entry?')) {
        return;
    }

    $.post('../api/food.php', { action: 'delete_log', id: id }, function (res) {
        if (res.status === 'success') {
            loadTodayLogs();
        }
    }, 'json');
}

function loadTodayLogs() {
    const selectedDate = $('#logged_date').val() || '<?= $today ?>';

    $.get('../api/food.php', { action: 'list_today', logged_date: selectedDate }, function (res) {
        const items = res.items || [];
        const totals = res.totals || { calories: 0, protein: 0, carbs: 0, fat: 0 };
        const mealOrder = ['Breakfast', 'Lunch', 'Dinner', 'Snack'];
        let html = '';

        if (items.length === 0) {
            html = '<div class="text-center py-4 text-muted"><i class="fas fa-apple-alt fa-2x mb-3"></i><p class="mb-0">No meals logged for this date yet.</p></div>';
        } else {
            mealOrder.forEach((mealType) => {
                const grouped = items.filter((item) => item.meal_type === mealType);
                if (grouped.length === 0) {
                    return;
                }

                html += `<div class="border rounded p-3 mb-3">`;
                html += `<h6 class="fw-bold text-uppercase mb-3" style="color:#e4381c;">${mealType}</h6>`;
                grouped.forEach((item) => {
                    html += `<div class="d-flex justify-content-between align-items-start border-bottom py-2">`;
                    html += `<div><div class="fw-semibold">${item.food_name}</div><small class="text-muted">${parseFloat(item.calories).toFixed(1)} kcal • P ${parseFloat(item.protein_g).toFixed(1)}g • C ${parseFloat(item.carbs_g).toFixed(1)}g • F ${parseFloat(item.fat_g).toFixed(1)}g</small></div>`;
                    html += `<div class="text-end"><button class="btn btn-sm btn-outline-danger" onclick="deleteLog(${item.id})"><i class="fas fa-trash"></i></button></div>`;
                    html += `</div>`;
                });
                html += `</div>`;
            });
        }

        $('#today-list').html(html);
        $('#progress-text').text(`${Math.round(totals.calories)} / ${GOAL_CAL} kcal`);
        const percentage = Math.min(100, Math.round((totals.calories / GOAL_CAL) * 100));
        $('#calorie-progress').css('width', percentage + '%');
    }, 'json');
}

$('#food-log-form').on('submit', function (e) {
    e.preventDefault();

    $.post('../api/food.php', {
        action: 'add_log',
        food_name: $('#food_name').val(),
        meal_type: $('#meal_type').val(),
        calories: $('#calories').val(),
        protein_g: $('#protein_g').val(),
        carbs_g: $('#carbs_g').val(),
        fat_g: $('#fat_g').val(),
        logged_date: $('#logged_date').val()
    }, function (res) {
        if (res.status === 'success') {
            showFeedback(res.message || 'Meal logged successfully.');
            $('#food-log-form')[0].reset();
            $('#logged_date').val('<?= $today ?>');
            $('#protein_g').val(0);
            $('#carbs_g').val(0);
            $('#fat_g').val(0);
            loadTodayLogs();
        } else {
            showFeedback(res.message || 'Unable to save meal.', 'error');
        }
    }, 'json');
});

loadTodayLogs();
</script>
</body>
</html>
