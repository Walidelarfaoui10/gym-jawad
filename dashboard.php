<?php
require 'config.php';
require_login();

$agency_id = $_SESSION['agency_id'];

$today = date('Y-m-d');
$soon = date('Y-m-d', strtotime('+3 days'));

// totals
$total_members = $pdo->prepare('SELECT COUNT(*) FROM members WHERE agency_id = ?');
$total_members->execute([$agency_id]);
$total_members = $total_members->fetchColumn();

// fetch members to compute statuses
$stm = $pdo->prepare('SELECT * FROM members WHERE agency_id = ? ORDER BY registration_date DESC');
$stm->execute([$agency_id]);
$members = $stm->fetchAll(PDO::FETCH_ASSOC);

$overdue_count = 0;
$due_soon_list = [];
foreach ($members as $m) {
    $last = $m['last_payment_date'] ?: $m['registration_date'];
    $next = date('Y-m-d', strtotime($last . ' + ' . intval($m['membership_duration_months']) . ' months'));
    if ($next < $today) $overdue_count++;
    elseif ($next <= $soon) $due_soon_list[] = ['m'=>$m,'next'=>$next];
}

// monthly income
$month_start = date('Y-m-01');
$month_income_q = $pdo->prepare('SELECT COALESCE(SUM(amount),0) as s FROM payments WHERE agency_id = ? AND date(paid_at) BETWEEN ? AND ?');
$month_income_q->execute([$agency_id, $month_start, $today]);
$month_income = $month_income_q->fetchColumn();

?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Dashboard</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body class="p-4">
<div class="container-fluid">
  <div class="d-flex justify-content-between mb-3">
    <h3>Dashboard — <?=htmlspecialchars($_SESSION['agency_name'])?></h3>
    <div><a href="members.php" class="btn btn-success">Manage Members</a> <a href="logout.php" class="btn btn-outline-secondary">Logout</a></div>
  </div>

  <div class="row mb-3">
    <div class="col-md-3"><div class="card p-3">Total Members<br><h4><?= $total_members ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3">Overdue Members<br><h4><?= $overdue_count ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3">This Month Income<br><h4><?= number_format($month_income,2) ?> MAD</h4></div></div>
  </div>

  <h5>Due Soon (next 3 days)</h5>
  <table class="table">
    <thead><tr><th>Name</th><th>Phone</th><th>Next Payment</th><th>Price</th></tr></thead>
    <tbody>
    <?php foreach($due_soon_list as $it): $m=$it['m']; $next=$it['next']; ?>
      <tr><td><?=htmlspecialchars($m['full_name'])?></td><td><?=htmlspecialchars($m['phone'])?></td><td><?= $next ?></td><td><?= number_format($m['membership_price'],2) ?> MAD</td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>

</div>
</body>
</html>