<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';
$user_id = (int)$_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$bmi_cat = bmi_category($user['bmi']);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Progress - FitLife</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700,800,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/activitar-dashboard-theme.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:'Segoe UI',sans-serif;background:#f0f4f8}
.navbar{background:#e4381c;color:#fff;padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:56px}
.navbar .brand{font-size:20px;font-weight:700}.navbar .nav-links{display:flex;gap:4px}.navbar .nav-links a{color:rgba(255,255,255,.85);text-decoration:none;padding:8px 14px;border-radius:6px;font-size:14px}
.navbar .nav-links a:hover,.navbar .nav-links a.active{background:rgba(255,255,255,.2)}.navbar .user-info{font-size:14px;display:flex;align-items:center;gap:10px}
.navbar .user-info a{color:rgba(255,255,255,.8);text-decoration:none}.container{max-width:960px;margin:24px auto;padding:0 16px}
.page-title{font-size:22px;font-weight:700;color:#e4381c;margin-bottom:20px}.card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:20px}
.card-title{font-size:16px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px;border-bottom:2px solid #f0f4f8;padding-bottom:12px}
.card-title i{color:#e4381c}.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px}
.stat-box{background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,.06);text-align:center}
.stat-box .val{font-size:28px;font-weight:700;color:#e4381c}.stat-box .lbl{font-size:13px;color:#888;margin-top:4px}
.bmi-success{color:#e4381c}.bmi-warning{color:#e67e22}.bmi-danger{color:#e74c3c}.bmi-info{color:#2196f3}
.log-form{display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end}
.log-form input,.log-form textarea{padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;outline:none;width:100%}
.log-form input:focus{border-color:#e4381c}.btn{padding:10px 20px;background:#e4381c;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer}
.btn:hover{background:#c73216}.result-box{background:#e8f8ee;border:1px solid #b8e8cc;border-radius:8px;padding:12px;margin-top:12px;display:none;font-size:14px;color:#e4381c}
</style></head>
<body>
<nav class="navbar">
    <div class="brand"><i class="fas fa-heartbeat"></i> FitLife</div>
    <div class="nav-links">
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="diet-chart.php"><i class="fas fa-utensils"></i> Diet</a>
        <a href="exercise.php"><i class="fas fa-dumbbell"></i> Exercise</a>
        <a href="food-log.php"><i class="fas fa-clipboard-list"></i> Food Log</a>
        <a href="daily-tasks.php"><i class="fas fa-tasks"></i> Tasks</a>
        <a href="progress.php" class="active"><i class="fas fa-chart-line"></i> Progress</a>
    </div>
    <div class="user-info"><i class="fas fa-user-circle" style="font-size:20px"></i><?= htmlspecialchars($user['name']) ?><a href="../api/auth.php?action=logout"><i class="fas fa-sign-out-alt"></i></a></div>
</nav>
<div class="container">
    <div class="page-title"><i class="fas fa-chart-line"></i> My Progress</div>
    <div class="stat-row">
        <div class="stat-box"><div class="val"><?= $user['weight_kg'] ?>kg</div><div class="lbl">Current Weight</div></div>
        <div class="stat-box"><div class="val bmi-<?= $bmi_cat['class'] ?>"><?= $user['bmi'] ?></div><div class="lbl">BMI · <?= $bmi_cat['label'] ?></div></div>
        <div class="stat-box"><div class="val"><?= $user['height_cm'] ?>cm</div><div class="lbl">Height</div></div>
    </div>
    <div class="card">
        <div class="card-title"><i class="fas fa-weight"></i> Log Today's Weight</div>
        <div class="log-form">
            <div><label style="font-size:13px;font-weight:600;color:#444;display:block;margin-bottom:6px">Weight (kg)</label>
                <input type="number" id="log-weight" step="0.1" placeholder="e.g. 72.5" value="<?= $user['weight_kg'] ?>"></div>
            <div><label style="font-size:13px;font-weight:600;color:#444;display:block;margin-bottom:6px">Notes (optional)</label>
                <input type="text" id="log-notes" placeholder="How are you feeling today?"></div>
            <button class="btn" onclick="logProgress()">Save</button>
        </div>
        <div id="log-result" class="result-box"></div>
    </div>
    <div class="card">
        <div class="card-title"><i class="fas fa-chart-line"></i> Weight History</div>
        <canvas id="weight-chart" height="80"></canvas>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let chart;
function loadHistory() {
    $.get('../api/progress.php', { action:'history' }, function(res) {
        const r = JSON.parse(res);
        const labels = r.history.map(h => h.log_date);
        const weights = r.history.map(h => h.weight_kg);
        if (chart) chart.destroy();
        chart = new Chart(document.getElementById('weight-chart'), {
            type:'line',
            data:{ labels, datasets:[{ label:'Weight (kg)', data:weights, borderColor:'#e4381c', backgroundColor:'rgba(26,138,90,.1)', tension:.3, fill:true, pointBackgroundColor:'#e4381c' }] },
            options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:false, title:{ display:true, text:'kg' } } } }
        });
    });
}
function logProgress() {
    const weight = $('#log-weight').val();
    const notes = $('#log-notes').val();
    if (!weight) { alert('Enter weight'); return; }
    $.post('../api/progress.php', { action:'log', weight, notes }, function(res) {
        const r = JSON.parse(res);
        if (r.status==='success') {
            $('#log-result').html(`✅ Saved! BMI: <strong>${r.bmi}</strong> (${r.bmi_category})`).show();
            loadHistory();
            setTimeout(() => $('#log-result').hide(), 4000);
        }
    });
}
loadHistory();
</script>
</body></html>
