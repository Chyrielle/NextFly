<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../../config/database.php";
require_once "sendmail.php";

$db   = new Database();
$conn = $db->conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $booking_code   = $_POST['booking_code'];
    $user_id        = $_POST['user_id'];
    $service_type   = $_POST['service_type'];
    $total          = $_POST['total'];
    $payment_method = $_POST['payment_method'];

    if(
        empty($booking_code) ||
        empty($user_id) ||
        empty($service_type) ||
        empty($total) ||
        empty($payment_method)
    )
    {
        die("Semua data wajib diisi.");
    }

    $transaction_code = "TRX" . rand(100000,999999);
    $status = "Pending";

    $sql = "INSERT INTO transactions
    (
    booking_code,
    transaction_code,
    user_id,
    service_type,
    total,
    payment_method,
    status
    )
    VALUES
    (
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?
    )";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "ssisdss",
        $booking_code,
        $transaction_code,
        $user_id,
        $service_type,
        $total,
        $payment_method,
        $status
    );

    if (mysqli_stmt_execute($stmt))
    {
        $query = mysqli_query($conn, "SELECT email FROM users WHERE id='$user_id'");
        if(mysqli_num_rows($query) == 0){
            die("User tidak ditemukan.");
        }
        $user = mysqli_fetch_assoc($query);
        $email = $user['email'];

        if (sendTransactionEmail(
            $email,
            $booking_code,
            $transaction_code,
            $service_type,
            $total,
            $payment_method,
            $status
        ))
        {
            header("Location: history.php");
            exit;
        }
        else
        {
            echo "Email gagal dikirim.";
        }
    }
    else
    {
        echo "Gagal menyimpan transaksi.";
    }

    exit; 
}

$booking_code = $_GET['booking_code'] ?? ('BKG' . rand(100000, 999999));
$service_type = $_GET['service_type'] ?? 'Hotel';
$nama_layanan = $_GET['nama_layanan'] ?? 'Grand Ocean Hotel';
$lokasi       = $_GET['lokasi'] ?? 'Nusa Penida, Bali';
$total        = $_GET['total'] ?? 1462000;
$user_id      = $_SESSION['user_id'] ?? '';
?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pembayaran - Nextfly</title>
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

  main { padding: 32px 40px; max-width: 1000px; margin: 0 auto; }
  h1 { font-size: 24px; margin-bottom: 4px; }
  main > p.sub { color: #556; font-size: 14px; margin-bottom: 24px; }

  .layout { display: grid; grid-template-columns: 1.1fr 1fr; gap: 24px; align-items: start; }

  .box {
    background: #fff; border-radius: 16px; padding: 22px 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.06);
  }
  .box h2 { font-size: 16px; margin-bottom: 16px; }

  .item-detail { display: flex; gap: 14px; align-items: center; margin-bottom: 16px; }
  .item-detail .icon {
    width: 46px; height: 46px; border-radius: 12px; background: #e8f5f3;
    display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;
  }
  .item-detail h3 { font-size: 15px; margin-bottom: 3px; }
  .item-detail p { font-size: 13px; color: #667; }

  .row { display: flex; justify-content: space-between; font-size: 13.5px; padding: 8px 0; color: #445; }
  .row.total { border-top: 1px dashed #ddd; margin-top: 8px; padding-top: 14px; font-weight: 700; font-size: 15px; color: #0e3b38; }

  .method {
    display: flex; align-items: center; gap: 12px; border: 1.5px solid #eee; border-radius: 12px;
    padding: 14px 16px; margin-bottom: 12px; cursor: pointer; font-size: 14px;
  }
  .method input { accent-color: #e8664b; width: 16px; height: 16px; }
  .method .icon { font-size: 18px; }
  .method.selected { border-color: #e8664b; background: #fff6f4; }

  .pay-btn {
    width: 100%; background: #e8664b; color: #fff; border: none; padding: 14px;
    border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 8px;
  }
  .pay-btn:hover { background: #d1553d; }

  .note { font-size: 12px; color: #889; margin-top: 10px; text-align: center; }

  @media (max-width: 800px) {
    nav .menu { display: none; }
    main { padding: 24px 18px; }
    .layout { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<nav>
  <div class="logo">Nextfly</div>
  <div class="menu">
    <a href="user.php">Dashboard</a>
    <a href="booking.php">Booking</a>
    <a href="payment.php">Pembayaran</a>
    <a href="../../logout.php">Keluar</a>
  </div>
  <div class="avatar">U</div>
</nav>

<main>
  <h1>Pembayaran</h1>
  <p class="sub">Periksa kembali pesananmu sebelum melanjutkan pembayaran.</p>

  <form class="layout" method="POST" action="payment.php">
    <div class="box">
      <h2>Ringkasan Pesanan</h2>
      <div class="item-detail">
        <div class="icon">🏨</div>
        <div>
          <h3><?php echo htmlspecialchars($nama_layanan); ?></h3>
          <p><?php echo htmlspecialchars($lokasi); ?></p>
        </div>
      </div>
      <div class="row"><span>Kode Booking</span><span><?php echo htmlspecialchars($booking_code); ?></span></div>
      <div class="row"><span>Jenis Layanan</span><span><?php echo htmlspecialchars($service_type); ?></span></div>
      <div class="row total"><span>Total Pembayaran</span><span>Rp <?php echo number_format((float)$total, 0, ',', '.'); ?></span></div>
    </div>

    <div class="box">
      <h2>Metode Pembayaran</h2>

      <label class="method selected">
        <input type="radio" name="payment_method" value="Transfer Bank" checked>
        <span class="icon">🏦</span>
        <span>Transfer Bank</span>
      </label>
      <label class="method">
        <input type="radio" name="payment_method" value="E-Wallet">
        <span class="icon">📱</span>
        <span>E-Wallet (OVO / GoPay / DANA)</span>
      </label>
      <label class="method">
        <input type="radio" name="payment_method" value="Kartu Kredit">
        <span class="icon">💳</span>
        <span>Kartu Kredit / Debit</span>
      </label>

      <input type="hidden" name="booking_code" value="<?php echo htmlspecialchars($booking_code); ?>">
      <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
      <input type="hidden" name="service_type" value="<?php echo htmlspecialchars($service_type); ?>">
      <input type="hidden" name="total" value="<?php echo htmlspecialchars($total); ?>">

      <button type="submit" class="pay-btn">Bayar Sekarang</button>
      <p class="note">Pembayaran diproses secara aman oleh Nextfly.</p>
    </div>
  </form>
</main>

<script>
  document.querySelectorAll('.method').forEach(m => {
    m.addEventListener('click', () => {
      document.querySelectorAll('.method').forEach(x => x.classList.remove('selected'));
      m.classList.add('selected');
      m.querySelector('input').checked = true;
    });
  });
</script>

</body>
</html>
