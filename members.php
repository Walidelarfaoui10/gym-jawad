<?php
require 'config.php';
require_login();
$agency_id = $_SESSION['agency_id'];

// handle search & filters via GET
$q = $_GET['q'] ?? '';
$status = $_GET['status'] ?? '';

$sql = 'SELECT * FROM members WHERE agency_id = :aid';
$params = [':aid'=>$agency_id];
if ($q) {
    $sql .= ' AND (full_name LIKE :q OR phone LIKE :q)';
    $params[':q'] = "%$q%";
}
$sql .= ' ORDER BY registration_date DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = date('Y-m-d');
$soon = date('Y-m-d', strtotime('+3 days'));

function member_status($m) {
    $today = date('Y-m-d');
    $soon = date('Y-m-d', strtotime('+3 days'));
    $last = $m['last_payment_date'] ?: $m['registration_date'];
    $next = date('Y-m-d', strtotime($last . ' + ' . intval($m['membership_duration_months']) . ' months'));
    if ($next < $today) return 'overdue';
    if ($next <= $soon) return 'due';
    return 'paid';
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Members</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body class="p-4">
<div class="container">
  <div class="d-flex justify-content-between mb-3">
    <h3>Members</h3>
    <div><a href="dashboard.php" class="btn btn-secondary">Dashboard</a> <a href="members.php?action=add" class="btn btn-primary">Add Member</a></div>
  </div>

  <form class="row g-2 mb-3">
    <div class="col-auto"><input name="q" value="<?=htmlspecialchars($q)?>" placeholder="Search name or phone" class="form-control"></div>
    <div class="col-auto">
      <select name="status" class="form-select">
        <option value="">All</option>
        <option value="paid" <?= $status==='paid'?'selected':''?>>Paid</option>
        <option value="due" <?= $status==='due'?'selected':''?>>Due Soon</option>
        <option value="overdue" <?= $status==='overdue'?'selected':''?>>Overdue</option>
      </select>
    </div>
    <div class="col-auto"><button class="btn btn-outline-primary">Filter</button></div>
    <div class="col-auto ms-auto"><a href="export.php" class="btn btn-outline-success">Export CSV</a></div>
  </form>

<?php if(isset($_GET['action']) && $_GET['action']=='add' || isset($_GET['action']) && $_GET['action']=='edit'): 
  $member = false;
  if ($_GET['action']=='edit') {
    $stm = $pdo->prepare('SELECT * FROM members WHERE id=? AND agency_id=?');
    $stm->execute([$_GET['id'],$agency_id]);
    $member = $stm->fetch(PDO::FETCH_ASSOC);
  }
?>
  <form method="post" action="save_member.php" class="card p-3 mb-3">
    <input type="hidden" name="id" value="<?= $member['id'] ?? '' ?>">
    <div class="row">
      <div class="col-md-6 mb-3"><label>Full Name</label><input name="full_name" class="form-control" required value="<?=htmlspecialchars($member['full_name'] ?? '')?>"></div>
      <div class="col-md-6 mb-3"><label>Phone</label><input name="phone" class="form-control" value="<?=htmlspecialchars($member['phone'] ?? '')?>"></div>
      <div class="col-md-4 mb-3"><label>Registration Date</label><input type="date" name="registration_date" class="form-control" value="<?=htmlspecialchars($member['registration_date'] ?? date('Y-m-d'))?>"></div>
      <div class="col-md-4 mb-3"><label>Last Payment Date</label><input type="date" name="last_payment_date" class="form-control" value="<?=htmlspecialchars($member['last_payment_date'] ?? date('Y-m-d'))?>"></div>
      <div class="col-md-4 mb-3"><label>Membership Type</label>
        <select name="membership_type" class="form-select">
          <option value="Monthly" <?= (isset($member['membership_type']) && $member['membership_type']=='Monthly')?'selected':'' ?>>Monthly</option>
          <option value="3 Months" <?= (isset($member['membership_type']) && $member['membership_type']=='3 Months')?'selected':'' ?>>3 Months</option>
          <option value="Year" <?= (isset($member['membership_type']) && $member['membership_type']=='Year')?'selected':'' ?>>Year</option>
        </select>
      </div>
      <div class="col-md-4 mb-3"><label>Duration (months)</label><input type="number" name="membership_duration_months" class="form-control" value="<?=htmlspecialchars($member['membership_duration_months'] ?? 1)?>"></div>
      <div class="col-md-4 mb-3"><label>Membership Price</label><input type="number" step="0.01" name="membership_price" class="form-control" value="<?=htmlspecialchars($member['membership_price'] ?? 0)?>"></div>
      <div class="col-12 mb-3"><label>Notes</label><textarea name="notes" class="form-control"><?=htmlspecialchars($member['notes'] ?? '')?></textarea></div>
    </div>
    <div class="d-flex">
      <button class="btn btn-primary me-2">Save</button>
      <a href="members.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
<?php else: ?>

<table class="table">
<thead><tr><th>Name</th><th>Phone</th><th>Next Payment</th><th>Price</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($members as $m):
  $last = $m['last_payment_date'] ?: $m['registration_date'];
  $next = date('Y-m-d', strtotime($last . ' + ' . intval($m['membership_duration_months']) . ' months'));
  $st = member_status($m);
  $status_html = ($st=='overdue')?'<span class="badge bg-danger">Unpaid</span>':(($st=='due')?'<span class="badge bg-warning">Due Soon</span>':'<span class="badge bg-success">Paid</span>');
?>
<tr>
  <td><?=htmlspecialchars($m['full_name'])?> <?= $status_html ?></td>
  <td><?=htmlspecialchars($m['phone'])?></td>
  <td><?= $next ?></td>
  <td><?= number_format($m['membership_price'],2) ?> MAD</td>
  <td>
    <a class="btn btn-sm btn-primary" href="members.php?action=edit&id=<?= $m['id'] ?>">Edit</a>
    <a class="btn btn-sm btn-danger" href="delete_member.php?id=<?= $m['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
    <a class="btn btn-sm btn-success" href="mark_paid.php?id=<?= $m['id'] ?>">Mark Paid</a>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php endif; ?>

</div>
</body></html>