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
if (
    !isset($_SESSION['login'])
    ||
    !in_array($role, ['admin', 'customer_service'])
) {
    header("location: ../../login.html");
    exit();
}

// Nama CS yang login, dipakai untuk sapaan di dashboard
$namaCS = $_SESSION['nama'] ?? 'Customer Service';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard CS - Nextfly</title>
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
  nav .avatar {
    width: 34px; height: 34px; border-radius: 50%; background: #e8664b;
    display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;
  }

  main { padding: 32px 40px; max-width: 1100px; margin: 0 auto; }
  h1 { font-size: 24px; margin-bottom: 4px; }
  main > p.sub { color: #556; font-size: 14px; margin-bottom: 24px; }

  .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 32px; }
  .stat-card {
    background: #fff; border-radius: 14px; padding: 20px 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.06);
  }
  .stat-card .label { font-size: 13px; color: #778; }
  .stat-card .value { font-size: 26px; font-weight: 700; margin-top: 6px; color: #0e3b38; }
  .stat-card.alert .value { color: #e8664b; }

  section { margin-bottom: 32px; }
  section h2 { font-size: 17px; margin-bottom: 14px; }

  table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
  th, td { text-align: left; padding: 14px 16px; font-size: 13.5px; }
  thead { background: #0e3b38; color: #fff; }
  tbody tr:nth-child(even) { background: #faf8f3; }

  .badge { padding: 4px 10px; border-radius: 10px; font-size: 11.5px; font-weight: 600; }
  .badge.open { background: #fff3e0; color: #b8720c; }
  .badge.progress { background: #e8f0ff; color: #2255c7; }
  .badge.done { background: #e3f6e8; color: #1e7d3a; }

  .reply-btn {
    background: #e8664b; color: #fff; border: none; padding: 7px 16px;
    border-radius: 20px; font-size: 12.5px; font-weight: 600; cursor: pointer;
  }

  .quick-actions { display: flex; gap: 12px; flex-wrap: wrap; }
  .quick-actions button {
    background: #0e3b38; color: #fff; border: none; padding: 10px 20px;
    border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer;
  }
  .quick-actions button:hover { background: #145a54; }

  @media (max-width: 800px) {
    nav .menu { display: none; }
    .stats { grid-template-columns: 1fr; }
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
  <div class="logo">Nextfly · CS</div>
  <div class="menu">
    <a href="CS.php">Dashboard</a>
    <a href="reports.php">Laporan</a>
    <a href="history.php">Riwayat</a>
    <a href="#" onclick="logoutUser(); return false;">Keluar</a>
  </div>
  <div class="avatar"><?php echo strtoupper(substr($namaCS, 0, 1)); ?></div>
</nav>

<main>
  <h1>Halo, <?php echo htmlspecialchars($namaCS); ?> 👋</h1>
  <p class="sub">Pantau dan tanggapi keluhan pengguna Nextfly.</p>

  <div class="stats">
    <div class="stat-card alert">
      <div class="label">Tiket Baru Masuk</div>
      <div class="value">8</div>
    </div>
    <div class="stat-card">
      <div class="label">Sedang Ditangani</div>
      <div class="value">5</div>
    </div>
    <div class="stat-card">
      <div class="label">Selesai Hari Ini</div>
      <div class="value">12</div>
    </div>
  </div>

  <section>
    <h2>Keluhan & Pertanyaan Terbaru</h2>
    <table>
      <thead>
        <tr>
          <th>Pengguna</th>
          <th>Topik</th>
          <th>Waktu</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td data-label="Pengguna">Rangga W.</td>
          <td data-label="Topik">Pembayaran gagal, saldo terpotong</td>
          <td data-label="Waktu">09:12</td>
          <td data-label="Status"><span class="badge open">Baru</span></td>
          <td data-label="Aksi"><button class="reply-btn">Tanggapi</button></td>
        </tr>
        <tr>
          <td data-label="Pengguna">Melati S.</td>
          <td data-label="Topik">Ubah tanggal check-in hotel</td>
          <td data-label="Waktu">08:47</td>
          <td data-label="Status"><span class="badge progress">Diproses</span></td>
          <td data-label="Aksi"><button class="reply-btn">Tanggapi</button></td>
        </tr>
        <tr>
          <td data-label="Pengguna">Fajar R.</td>
          <td data-label="Topik">Tiket kereta belum masuk email</td>
          <td data-label="Waktu">08:20</td>
          <td data-label="Status"><span class="badge open">Baru</span></td>
          <td data-label="Aksi"><button class="reply-btn">Tanggapi</button></td>
        </tr>
        <tr>
          <td data-label="Pengguna">Dinda A.</td>
          <td data-label="Topik">Refund pesanan bus dibatalkan</td>
          <td data-label="Waktu">Kemarin</td>
          <td data-label="Status"><span class="badge done">Selesai</span></td>
          <td data-label="Aksi"><button class="reply-btn">Lihat</button></td>
        </tr>
      </tbody>
    </table>
  </section>

  <section>
    <h2>Menu Cepat</h2>
    <div class="quick-actions">
      <form action="reports.php" method="GET">
        <button type="submit">Kelola Report</button>
      </form>
      <form action="history.php" method="GET">
        <button type="submit">Riwayat Report</button>
      </form>
    </div>
  </section>

  <?php if ($role === 'admin'): ?>
  <section>
    <h2>Akses Lain (Admin)</h2>
    <div class="quick-actions">
      <form action="../admin/admin.php" method="GET">
        <button type="submit">Buka Dashboard Admin</button>
      </form>
      <form action="../user/user.php" method="GET">
        <button type="submit">Buka Dashboard User</button>
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
