<?php
session_start();
session_regenerate_id(true);

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: ../../login.html");
    exit();
}
if (time() > ($_SESSION['expire'] ?? 0)) {
    session_destroy();
    header("location: ../../login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - Nextfly</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<style>
  body{font-family:'Poppins',sans-serif; background:#f7f3ec; color:#26332f; margin:0;}
  header{display:flex; justify-content:space-between; align-items:center; background:#fff; padding:14px 24px; border-bottom:1px solid #e4e0d6;}
  .wrap{max-width:1000px; margin:auto; padding:24px;}
  .card{background:#fff; border:1px solid #e4e0d6; border-radius:10px; padding:18px; margin-bottom:20px;}
  .grid{display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;}
  .grid .card{margin:0; text-align:center;}
  .grid .val{font-size:1.3rem; font-weight:700; color:#0d7d73;}
  table{width:100%; border-collapse:collapse; font-size:0.9rem;}
  th,td{padding:8px; border-bottom:1px solid #e4e0d6; text-align:left;}
  input,select,button{font-family:inherit; padding:8px; border-radius:6px; border:1px solid #e4e0d6;}
  button{background:#0d7d73; color:#fff; border:none; cursor:pointer;}
  .btn-danger{background:#e0524d;}
  .btn-warning{background:#e0a72e;}
  nav button{margin-right:8px; margin-bottom:8px;}
  @media(max-width:700px){ .grid{grid-template-columns:repeat(2,1fr);} }
</style>
</head>
<body>

<header>
  <strong>Nextfly Admin</strong>
  <span>Halo, <?= htmlspecialchars($_SESSION['user']) ?> — <button class="btn-danger" onclick="doLogout()">Keluar</button></span>
</header>

<div class="wrap">

<?php
require_once "../../config/database.php";
$db = new Database();
$conn = $db->conn;

$totalUser = 0; $totalCS = 0; $totalTransaksi = 0; $totalPendapatan = 0;

$r = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='customer_service'");
if($r) $totalCS = $r->fetch_assoc()['total'];

$r = $conn->query("SELECT COUNT(*) as total FROM users");
if($r) $totalUser = $r->fetch_assoc()['total'];

$r = $conn->query("SELECT COUNT(*) as total FROM transactions");
if($r) $totalTransaksi = $r->fetch_assoc()['total'];

$r = $conn->query("SELECT SUM(total) as pendapatan FROM transactions");
if($r) $totalPendapatan = $r->fetch_assoc()['pendapatan'] ?? 0;
?>

<div class="grid">
  <div class="card"><div>Total User</div><div class="val"><?= $totalUser ?></div></div>
  <div class="card"><div>Total CS</div><div class="val"><?= $totalCS ?></div></div>
  <div class="card"><div>Transaksi</div><div class="val"><?= $totalTransaksi ?></div></div>
  <div class="card"><div>Pendapatan</div><div class="val">Rp <?= number_format($totalPendapatan,0,",",".") ?></div></div>
</div>

<div class="card">
  <nav>
    <button onclick="location.href='../user/User.php'">User</button>
    <button onclick="location.href='../customer-service/CS.php'">Customer_Service</button>
    <button onclick="location.href='../customer-service/reports.php'">Data Report</button>
    <button onclick="location.href='../customer-service/history.php'">Riwayat Report</button>
  </nav>
</div>

<div class="card">
  <h3>Tambah User</h3>
  <p id="pesan"></p>
  <input id="new_username" placeholder="Username">
  <input id="new_password" type="password" placeholder="Password">
  <select id="new_role">
    <option value="admin">Admin</option>
    <option value="customer_service">Customer_Service</option>
    <option value="user">User</option>
  </select>
  <button onclick="tambahUser()">+ Simpan</button>
</div>

<div class="card">
  <h3>Daftar User</h3>
  <table>
    <tr><th>Username</th><th>Role</th><th>Aksi</th></tr>
    <tbody id="bodyUser"><tr><td colspan="3">Memuat data...</td></tr></tbody>
  </table>
</div>

<div class="card">
  <h3>Monitoring Transaksi</h3>
  <table>
    <tr><th>Booking</th><th>Transaksi</th><th>Layanan</th><th>Total</th><th>Status</th></tr>
    <?php
    $result = $conn->query("SELECT * FROM transactions ORDER BY created_at DESC");
    if($result && $result->num_rows > 0){
        while($trx = $result->fetch_assoc()){
            echo "<tr><td>{$trx['booking_code']}</td><td>{$trx['transaction_code']}</td><td>{$trx['service_type']}</td><td>Rp ".number_format($trx['total'],0,',','.')."</td><td>{$trx['status']}</td></tr>";
        }
    } else {
        echo '<tr><td colspan="5">Belum ada transaksi.</td></tr>';
    }
    ?>
  </table>
</div>

</div>

<script>
async function loadUsers() {
  const res = await fetch('../../api/users.php');
  const json = await res.json();
  const tbody = document.getElementById('bodyUser');
  if (!json.success) { tbody.innerHTML = `<tr><td colspan="3">${json.message}</td></tr>`; return; }
  tbody.innerHTML = json.data.map(u => `
    <tr id="row-${u.id}">
      <td>${u.username}</td>
      <td>
        <select id="role-${u.id}">
          <option value="admin" ${u.role==='admin'?'selected':''}>Admin</option>
          <option value="customer_service" ${u.role==='customer_service'?'selected':''}>Customer_Service</option>
          <option value="user" ${u.role==='user'?'selected':''}>User</option>
        </select>
      </td>
      <td>
        <button class="btn-warning" onclick="updateRole(${u.id})">Ganti Role</button>
        <button class="btn-danger" onclick="hapusUser(${u.id})">Hapus</button>
      </td>
    </tr>`).join('');
}

async function tambahUser() {
  const username = document.getElementById('new_username').value;
  const password = document.getElementById('new_password').value;
  const role = document.getElementById('new_role').value;
  const res = await fetch('../../api/users.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ username, password, role })
  });
  const json = await res.json();
  document.getElementById('pesan').textContent = json.message;
  if (json.success) {
    document.getElementById('new_username').value = '';
    document.getElementById('new_password').value = '';
    loadUsers();
  }
}

async function updateRole(id) {
  const role = document.getElementById(`role-${id}`).value;
  const res = await fetch('../../api/users.php', {
    method:'PUT', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ id, role })
  });
  const json = await res.json();
  document.getElementById('pesan').textContent = json.message;
  loadUsers();
}

async function hapusUser(id) {
  if (!confirm(`Yakin hapus user ID ${id}?`)) return;
  const res = await fetch('../../api/users.php', {
    method:'DELETE', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ id })
  });
  const json = await res.json();
  document.getElementById('pesan').textContent = json.message;
  loadUsers();
}

async function doLogout() {
  await fetch('../../api/logout.php', { method:'POST' });
  window.location.href = '../../login.html';
}

loadUsers();
</script>
</body>
</html>
