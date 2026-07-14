<?php

require_once __DIR__ . '/../../vendor/autoload.php';


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

    $labelLayanan = [
    'Hotel'   => 'Akomodasi Hotel',
    'Bus'     => 'Perjalanan Bus',
    'Kereta'  => 'Perjalanan Kereta',
    'Pesawat' => 'Penerbangan',
];
$service_type_label = $labelLayanan[$service_type] ?? $service_type;
    
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

       $mail->Body = "<h2>Transaksi Berhasil</h2>"
    . "<hr>"
    . "<p><b>Kode Booking :</b> {$booking_code}</p>"
    . "<p><b>Kode Transaksi :</b> {$transaction_code}</p>"
    . "<p><b>Layanan :</b> {$service_type_label}</p>"
    . "<p><b>Total :</b> Rp " . number_format($total,0,",",".") . "</p>"
    . "<p><b>Metode Pembayaran :</b> {$payment_method}</p>"
    . "<p><b>Status :</b> {$status}</p>"
    . "<p>Terima kasih telah menggunakan <b>NextFly</b>.</p>";
        
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
