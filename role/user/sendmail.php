<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendTransactionEmail(
    $email,
    $booking_code,
    $transaction_code,
    $service_type,
    $total,
    $payment_method,
    $status
)
{
    $mail = new PHPMailer(true);

    try {

        

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

       
        $mail->Username = 'felloniasmith@gmail.com';

        $mail->Password = 'yqhnsvvpgzrtaywm';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        

        $mail->setFrom(
            'felloniasmith@gmail.com',
            'NextFly'
        );

       

        $mail->addAddress($email);

       

        $mail->isHTML(true);

        $mail->Subject = 'Konfirmasi Transaksi NextFly';

        $mail->Body = "
        <h2>Transaksi Berhasil</h2>

        <hr>

        <p><b>Kode Booking :</b> {$booking_code}</p>

        <p><b>Kode Transaksi :</b> {$transaction_code}</p>

        <p><b>Layanan :</b> {$service_type}</p>

        <p><b>Total :</b> Rp " . number_format($total,0,",",".") . "</p>

        <p><b>Metode Pembayaran :</b> {$payment_method}</p>

        <p><b>Status :</b> {$status}</p>

        <br>

        Terima kasih telah menggunakan <b>NextFly</b>.
        ";

        $mail->send();

        return true;

    } catch (Exception $e) {

        echo "<pre>";
        echo "PHPMailer Error:\n";
        echo $mail->ErrorInfo;
        echo "</pre>";

        return false;

    }

}
