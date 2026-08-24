<?php
require_once '../includes/auth_check.php';
require_once '../includes/functions.php';

$user = get_user($_SESSION['user_id']);
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLife – Weight Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/user.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css">
    <style>
        .tracker-card { border-top: 4px solid #e4381c; box-shadow: 0 8px 20px rgba(26, 122, 74, 0.12); }
        .summary-pill { border: 1px solid #e7f4ee; border-radius: 12px; background: #f8fcfa; }
        .badge-positive { background-color: #eaf7ee; color: #e4381c; }
        .badge-negative { background-color: #fdeceb; color: #c0392b; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
<?php include 'topbar.php'; ?>
<div class="content">
    <div class="card p-4 mb-4 tracker-card">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h3 class="mb-1" style="color:#e4381c;"><i class="fas fa-weight me-2"></i>Weight History Tracking</h3>
                <p class="mb-0 text-muted">Log your daily weight, review progress, and keep your goal in view.</p>
            </div>
            <div class="text-lg-end">
                <div class="h4 mb-0" id="summary-current">—</div>
                <small class="text-muted">Current weight</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="summary-pill p-3 h-100">
                <div class="small text-uppercase text-muted">Current Weight</div>
                <div class="h5 mt-2 mb-0" id="summary-current-weight">—</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-pill p-3 h-100">
                <div class="small text-uppercase text-muted">Starting Weight</div>
                <div class="h5 mt-2 mb-0" id="summary-start-weight">—</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-pill p-3 h-100">
                <div class="small text-uppercase text-muted">Change</div>
                <div class="h5 mt-2 mb-0" id="summary-change">—</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card tracker-card h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Log / Update Weight</h5>
                </div>
                <div class="card-body">
                    <form id="weight-form">
                        <div class="mb-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" id="weight_kg" class="form-control" step="0.1" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Recorded date</label>
                            <input type="date" id="recorded_date" class="form-control" value="<?= $today ?>" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save me-2"></i>Save Weight
                        </button>
                    </form>
                    <div id="form-feedback" class="mt-3"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card tracker-card h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Progress Chart</h5>
                </div>
                <div class="card-body">
                    <canvas id="weightChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
const defaultWeight = <?= json_encode($user['weight_kg'] ?? null) ?>;

function showFeedback(message, type = 'success') {
    const color = type === 'success' ? '#e4381c' : '#dc3545';
    $('#form-feedback').html(`<div class="alert py-2 mb-0" style="color:${color}; border-color:${color};">${message}</div>`);
}

function updateSummary(values) {
    const current = values.length ? values[values.length - 1] : (defaultWeight !== null ? parseFloat(defaultWeight) : null);
    const start = values.length ? values[0] : current;

    if (current === null || start === null) {
        $('#summary-current').text('—');
        $('#summary-current-weight').text('—');
        $('#summary-start-weight').text('—');
        $('#summary-change').text('—');
        return;
    }

    const change = current - start;
    const changeText = `${change >= 0 ? '+' : ''}${change.toFixed(1)} kg`;
    const badgeClass = change >= 0 ? 'badge-positive' : 'badge-negative';

    $('#summary-current').text(`${current.toFixed(1)} kg`);
    $('#summary-current-weight').text(`${current.toFixed(1)} kg`);
    $('#summary-start-weight').text(`${start.toFixed(1)} kg`);
    $('#summary-change').html(`<span class="badge ${badgeClass} px-3 py-2">${change >= 0 ? '<i class="fas fa-arrow-up me-1"></i>' : '<i class="fas fa-arrow-down me-1"></i>'}${changeText}</span>`);
}

function loadWeightChart() {
    $.getJSON('../api/weight.php', { action: 'get_history', limit: 30 }, function (res) {
        const labels = res.labels || [];
        const values = res.values || [];

        updateSummary(values);

        if (window.weightChartInstance) {
            window.weightChartInstance.destroy();
        }

        const ctx = document.getElementById('weightChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(26, 122, 74, 0.2)');
        gradient.addColorStop(1, 'rgba(26, 122, 74, 0.02)');

        window.weightChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Weight (kg)',
                    data: values,
                    borderColor: '#e4381c',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#e4381c'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.parsed.y.toFixed(1)} kg on ${context.label}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function (value) {
                                return `${value} kg`;
                            }
                        }
                    }
                }
            }
        });
    });
}

$('#weight-form').on('submit', function (e) {
    e.preventDefault();

    $.post('../api/weight.php', {
        action: 'add_weight',
        weight_kg: $('#weight_kg').val(),
        recorded_date: $('#recorded_date').val()
    }, function (res) {
        if (res.status === 'success') {
            showFeedback(res.message || 'Weight updated!');
            $('#weight-form')[0].reset();
            $('#recorded_date').val('<?= $today ?>');
            loadWeightChart();
        } else {
            showFeedback(res.message || 'Unable to save weight.', 'error');
        }
    }, 'json');
});

loadWeightChart();
</script>
</body>
</html>
