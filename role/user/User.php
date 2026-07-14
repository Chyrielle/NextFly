<?php
session_start();
session_regenerate_id(true);
if(!isset($_SESSION['expire'])){
    $_SESSION['expire'] = time() + 30;
}
if(time() > $_SESSION['expire']){
    $_SESSION = [];
    session_destroy();
    header("location: ../../login.html");
    exit();
}
$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION['login']) || !in_array($role, ['admin', 'editor', 'viewer'])) {
    header("location: ../../login.html");
    exit();
}
require '../../vendor/autoload.php';
use GuzzleHttp\Client;
$users = [];
try {
    $client   = new Client();
    $response = $client->get('http://localhost/your-project/api/users.php', [
        'headers' => [
            'Cookie' => session_name() . '=' . session_id()
        ]
    ]);
    $data  = json_decode($response->getBody(), true);
    $users = $data['success'] ? $data['data'] : [];
} catch (Exception $e) {
    $users = [];
}

$namaUser = $_SESSION['nama'] ?? 'User';

require_once '../../config/database.php';
$db   = new Database();
$conn = $db->conn;

$transaksi = [];
$user_id   = $_SESSION['user_id'] ?? 0;

$stmt = mysqli_prepare($conn, "SELECT service_type, booking_code, total, status, created_at
                                FROM transactions
                                WHERE user_id = ?
                                ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $transaksi[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Nextfly</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Poppins', sans-serif; color: #1b3b3b; background: #f4f1ea; }
  a { text-decoration: none; color: inherit; }

  nav {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 40px; background: #0e3b38; color: #fff;
  }
  nav .logo { font-size: 20px; font-weight: 700; }
  nav .menu { display: flex; align-items: center; gap: 24px; font-size: 14px; }
  nav .menu a:hover { color: #e8664b; }
  nav .user {
    display: flex; align-items: center; gap: 10px;
  }
  nav .avatar {
    width: 34px; height: 34px; border-radius: 50%; background: #e8664b;
    display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;
  }

  main { padding: 32px 40px; max-width: 1100px; margin: 0 auto; }

  .greeting h1 { font-size: 26px; }
  .greeting p { margin-top: 6px; color: #556; font-size: 14px; }

  .shortcuts {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin: 28px 0 36px;
  }
  .shortcuts a {
    background: #fff; border-radius: 14px; padding: 22px 12px; text-align: center;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06); transition: transform .15s;
  }
  .shortcuts a:hover { transform: translateY(-3px); }
  .shortcuts .icon { font-size: 26px; margin-bottom: 8px; }
  .shortcuts .label { font-size: 13.5px; font-weight: 500; }

  section { margin-bottom: 36px; }
  section h2 { font-size: 18px; margin-bottom: 14px; }

  .trip-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
  .trip-card {
    background: #fff; border-radius: 14px; padding: 18px; box-shadow: 0 4px 14px rgba(0,0,0,0.06);
  }
  .trip-card .tag {
    display: inline-block; background: #e8f5f3; color: #0e3b38; font-size: 11px;
    padding: 3px 10px; border-radius: 10px; margin-bottom: 10px; font-weight: 600;
  }
  .trip-card h3 { font-size: 15.5px; margin-bottom: 4px; }
  .trip-card p { font-size: 13px; color: #667; }
  .trip-card .date { margin-top: 10px; font-size: 12.5px; color: #e8664b; font-weight: 600; }

  table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 14px; overflow: hidden; }
  th, td { text-align: left; padding: 14px 16px; font-size: 13.5px; }
  thead { background: #0e3b38; color: #fff; }
  tbody tr:nth-child(even) { background: #faf8f3; }
  .status { padding: 4px 10px; border-radius: 10px; font-size: 11.5px; font-weight: 600; }
  .status.success { background: #e3f6e8; color: #1e7d3a; }
  .status.pending { background: #fff3e0; color: #b8720c; }

  .role-switch { margin-top: 30px; display: flex; gap: 12px; }
  .role-switch button {
    background: #0e3b38; color: #fff; border: none; padding: 10px 18px;
    border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer;
  }

  @media (max-width: 700px) {
    nav .menu { display: none; }
    .shortcuts { grid-template-columns: repeat(2, 1fr); }
    main { padding: 24px 18px; }
    table, thead, tbody, th, td, tr { display: block; }
    thead { display: none; }
    tbody tr { margin-bottom: 12px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    td { border-bottom: 1px solid #eee; }
    td::before { content: attr(data-label); font-weight: 600; display: block; font-size: 11px; color: #999; }
  }
</style>
</head>
<body>

<nav>
  <div class="logo">Nextfly</div>
  <div class="menu">
    <a href="User.php">Dashboard</a>
    <a href="booking.php">Booking</a>
    <a href="payment.php">Pembayaran</a>
    <a href="report.php">Bantuan</a>
    <a href="#" onclick="logoutUser(); return false;">Keluar</a>
  </div>
  <div class="user">
    <div class="avatar"><?php echo strtoupper(substr($namaUser, 0, 1)); ?></div>
  </div>
</nav>

<main>
  <div class="greeting">
    <h1>Halo, <?php echo htmlspecialchars($namaUser); ?> 👋</h1>
    <p>Mau pesan tiket apa hari ini?</p>
  </div>

  <div class="shortcuts">
    <a href="booking.php">
      <div class="icon">🏨</div>
      <div class="label">Hotel</div>
    </a>
    <a href="booking.php">
      <div class="icon">✈️</div>
      <div class="label">Pesawat</div>
    </a>
    <a href="booking.php">
      <div class="icon">🚆</div>
      <div class="label">Kereta</div>
    </a>
    <a href="booking.php">
      <div class="icon">🚌</div>
      <div class="label">Bus</div>
    </a>
  </div>

  <section>
    <h2>Pesanan Mendatang</h2>
    <div class="trip-cards">
      <div class="trip-card">
        <span class="tag">Hotel</span>
        <h3>Grand Ocean Hotel</h3>
        <p>Nusa Penida, Bali</p>
        <div class="date">12 - 14 Juli 2026</div>
      </div>
      <div class="trip-card">
        <span class="tag">Pesawat</span>
        <h3>Jakarta → Denpasar</h3>
        <p>Nextfly Air, Kelas Ekonomi</p>
        <div class="date">20 Juli 2026</div>
      </div>
    </div>
  </section>

  <section>
    <h2>Riwayat Transaksi</h2>
    <table>
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Jenis</th>
          <th>Deskripsi</th>
          <th>Total</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($transaksi)): ?>
        <tr>
          <td colspan="5" style="text-align:center; color:#888;">Belum ada transaksi.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($transaksi as $t): ?>
        <?php
          $statusRaw   = strtolower($t['status']);
          $statusClass = ($statusRaw === 'success' || $statusRaw === 'berhasil') ? 'success' : 'pending';
          $statusLabel = ($statusClass === 'success') ? 'Berhasil' : 'Menunggu';
        ?>
        <tr>
          <td data-label="Tanggal"><?php echo date('d M Y', strtotime($t['created_at'])); ?></td>
          <td data-label="Jenis"><?php echo htmlspecialchars($t['service_type']); ?></td>
          <td data-label="Deskripsi"><?php echo htmlspecialchars($t['booking_code']); ?></td>
          <td data-label="Total">Rp <?php echo number_format($t['total'], 0, ',', '.'); ?></td>
          <td data-label="Status"><span class="status <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </section>

  <?php if ($role === 'admin'): ?>
  <section>
    <h2>Akses Lain</h2>
    <div class="role-switch">
      <form action="../admin/Admin.php" method="GET">
        <button type="submit">Buka Dashboard Admin</button>
      </form>
      <form action="../customer-service/CS.php" method="GET">
        <button type="submit">Buka Dashboard Customer Service</button>
      </form>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($role === 'customer_service'): ?>
  <section>
    <h2>Akses Lain</h2>
    <div class="role-switch">
      <form action="../customer-service/CS.php" method="GET">
        <button type="submit">Buka Dashboard Customer Service</button>
      </form>
    </div>
  </section>
  <?php endif; ?>
</main>

<script>
  function logoutUser() {
    fetch("../../api/logout.php", { method: "POST" })
      .then(res => res.json())
      .then(data => {
        window.location.href = "../../login.html";
      })
      .catch(() => {
        window.location.href = "../../login.html";
      });
  }
</script>
</body>
</html>
