<?php
session_start();
if (
    !isset($_SESSION['login'])
    ||
    !in_array(
        $_SESSION['role'] ?? '',
        ['admin', 'customer_service']
    )
) {
    header("location: ../../login.html");
    exit();
}
require_once "../../config/database.php";
$db = new Database();
$conn = $db->conn;
$result = $conn->query(
    "SELECT *
     FROM reports
     WHERE status = 'answered'
     ORDER BY created_at DESC"
);

$namaUser = $_SESSION['nama'] ?? ($_SESSION['user'] ?? 'Pengguna');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Report - Nextfly</title>
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

  main { padding: 32px 40px; max-width: 900px; margin: 0 auto; }
  .back-link { display: inline-block; font-size: 13.5px; color: #0e3b38; font-weight: 600; margin-bottom: 18px; }
  .back-link:hover { color: #e8664b; }
  h1 { font-size: 24px; margin-bottom: 4px; }
  main > p.sub { color: #556; font-size: 14px; margin-bottom: 24px; }

  .report-card {
    background: #fff; border-radius: 14px; padding: 22px 24px; margin-bottom: 18px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
  }
  .report-card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
  .report-card h3 { font-size: 16px; }
  .badge.done { background: #e3f6e8; color: #1e7d3a; padding: 4px 10px; border-radius: 10px; font-size: 11.5px; font-weight: 600; white-space: nowrap; }

  .block-label { font-size: 11.5px; font-weight: 600; color: #889; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px; }
  .report-card .message { font-size: 13.5px; color: #445; line-height: 1.6; white-space: pre-line; margin-bottom: 16px; }
  .report-card .answer-box {
    background: #f4f1ea; border-radius: 10px; padding: 14px 16px; font-size: 13.5px; color: #1b3b3b; line-height: 1.6; white-space: pre-line;
  }

  .empty-state {
    background: #fff; border-radius: 14px; padding: 40px; text-align: center; color: #889; font-size: 14px;
  }

  @media (max-width: 700px) {
    nav .menu { display: none; }
    main { padding: 24px 18px; }
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
  <div class="avatar"><?= strtoupper(substr($namaUser, 0, 1)) ?></div>
</nav>

<main>
  <?php if ($_SESSION['role'] === 'admin'): ?>
    <a class="back-link" href="../admin/admin.php">← Kembali ke Dashboard Admin</a>
  <?php else: ?>
    <a class="back-link" href="CS.php">← Kembali ke Dashboard Customer Service</a>
  <?php endif; ?>

  <h1>Riwayat Report</h1>
  <p class="sub">Laporan pengguna yang sudah dijawab.</p>

  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($report = $result->fetch_assoc()): ?>
      <div class="report-card">
        <div class="top">
          <h3><?= htmlspecialchars($report['subject']) ?></h3>
          <span class="badge done">Selesai</span>
        </div>

        <div class="block-label">Pesan User</div>
        <p class="message"><?= nl2br(htmlspecialchars($report['message'])) ?></p>

        <div class="block-label">Jawaban Customer Service</div>
        <div class="answer-box"><?= nl2br(htmlspecialchars($report['answer'])) ?></div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="empty-state">Belum ada report yang dijawab.</div>
  <?php endif; ?>
</main>

<script>
  function logoutUser() {
    fetch("../../api/logout.php", { method: "POST" })
      .then(res => res.json())
      .then(() => { window.location.href = "../../login.html"; })
      .catch(() => { window.location.href = "../../login.html"; });
  }
</script>
</body>
</html>
