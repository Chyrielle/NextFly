<?php


require_once "../../config/database.php";
require_once "sendmail.php";

$booking_code = $_POST['booking_code'];
$user_id = $_POST['user_id'];
$service_type = $_POST['service_type'];
$total = $_POST['total'];
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
