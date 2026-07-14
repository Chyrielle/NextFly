<?php
session_start();
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'] ?? '', ['admin', 'editor', 'viewer'])) {
    header("location: ../../login.html");
    exit();
}
require_once "../../config/database.php";
$db   = new Database();
$conn = $db->conn;

$user_id  = $_SESSION['user_id'] ?? 0;
$namaUser = $_SESSION['nama'] ?? ($_SESSION['user'] ?? 'User');

$successMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($subject) && !empty($message)) {
        $stmt = $conn->prepare(
            "INSERT INTO reports (user_id, subject, message) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iss", $user_id, $subject, $message);
        $stmt->execute();
        $successMsg = "Keluhan berhasil dikirim. Tim kami akan segera merespons.";
    }
}

$stmt = $conn->prepare(
    "SELECT subject, message, answer, status, created_at
     FROM reports
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$riwayat = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kirim Keluhan - Nextfly</title>
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

  main { padding: 32px 40px; max-width: 800px; margin: 0 auto; }
  h1 { font-size: 24px; margin-bottom: 4px; }
  main > p.sub { color: #556; font-size: 14px; margin-bottom: 24px; }

  .box {
    background: #fff; border-radius: 14px; padding: 22px 24px; margin-bottom: 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
  }
  .box h2 { font-size: 16px; margin-bottom: 14px; }

  input[type="text"], textarea {
    width: 100%; border: 1px solid #ddd; border-radius: 10px; padding: 12px 14px;
    font-family: 'Poppins'; font-size: 13.5px; margin-bottom: 12px; resize: vertical;
  }
  button {
    background: #e8664b; color: #fff; border: none; padding: 12px 26px;
    border-radius: 20px; font-size: 13.5px; font-weight: 600; cursor: pointer;
  }
  button:hover { background: #d1553d; }

  .alert-success {
    background: #e3f6e8; color: #1e7d3a; padding: 12px 16px; border-radius: 10px;
    font-size: 13.5px; margin-bottom: 18px;
  }

  .report-card { margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px solid #f0eee6; }
  .report-card:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
  .report-card h3 { font-size: 14.5px; margin-bottom: 4px; }
  .report-card .date { font-size: 11.5px; color: #999; margin-bottom: 8px; }
  .report-card .message { font-size: 13.5px; color: #445; margin-bottom: 8px; white-space: pre-line; }
  .badge { padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; }
  .badge.pending { background: #fff3e0; color: #b8720c; }
  .badge.answered { background: #e3f6e8; color: #1e7d3a; }
  .answer-box {
    background: #f4f1ea; border-radius: 10px; padding: 10px 14px; margin-top: 8px;
    font-size: 13px; color: #334;
  }
  .empty-state { text-align: center; color: #889; font-size: 14px; padding: 20px 0; }

  @media (max-width: 700px) {
    nav .menu { display: none; }
    main { padding: 24px 18px; }
  }
</style>
</head>
<body>

<nav>
  <div class="logo">Nextfly</div>
  <div class="menu">
    <a href="User.php">Dashboard</a>
    <a href="booking.php">Booking</a>
    <a href="report.php">Bantuan</a>
    <a href="#" onclick="logoutUser(); return false;">Keluar</a>
  </div>
</nav>

<main>
  <h1>Kirim Keluhan</h1>
  <p class="sub">Ada masalah dengan pesanan atau layanan? Kirimkan keluhanmu di sini.</p>

  <?php if ($successMsg): ?>
    <div class="alert-success"><?= htmlspecialchars($successMsg) ?></div>
  <?php endif; ?>

  <div class="box">
    <h2>Form Keluhan</h2>
    <form method="POST">
      <input type="text" name="subject" placeholder="Subjek keluhan" required>
      <textarea name="message" rows="5" placeholder="Jelaskan keluhanmu secara detail..." required></textarea>
      <button type="submit">Kirim Keluhan</button>
    </form>
  </div>

  <div class="box">
    <h2>Riwayat Keluhan</h2>
    <?php if ($riwayat->num_rows > 0): ?>
      <?php while ($r = $riwayat->fetch_assoc()): ?>
        <div class="report-card">
          <h3><?= htmlspecialchars($r['subject']) ?></h3>
          <div class="date"><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></div>
          <div class="message"><?= nl2br(htmlspecialchars($r['message'])) ?></div>
          <?php if ($r['status'] === 'answered'): ?>
            <span class="badge answered">Sudah Dijawab</span>
            <div class="answer-box"><b>Jawaban CS:</b><br><?= nl2br(htmlspecialchars($r['answer'])) ?></div>
          <?php else: ?>
            <span class="badge pending">Menunggu</span>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">Belum ada keluhan yang dikirim.</div>
    <?php endif; ?>
  </div>
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
