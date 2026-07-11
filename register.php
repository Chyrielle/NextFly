<?php
session_start();

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/config/database.php';

header("Content-Type: application/json");

$data     = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$email    = $data['email']    ?? '';
$role     = $data['role']     ?? 'user';

if (empty($username) || empty($password) || empty($email)) {
    echo json_encode(["success" => false, "message" => "Semua field wajib diisi"]);
    exit();
}

$db   = new Database();
$conn = $db->conn;

$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, MD5(?), ?)");
$stmt->bind_param("ssss", $username, $email, $password, $role);
$stmt->execute();

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'felloniasmith@gmail.com';     
    $mail->Password   = 'yqhnsvvpgzrtaywm';       
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('felloniasmith@gmail.com', 'Registration Confirmation');
    $mail->addAddress($email, $username);

    $mail->isHTML(true);
    $mail->Subject = 'Konfirmasi Pendaftaran';
    $mail->Body    = "Halo <b>$username</b>,<br><br>Pendaftaran kamu berhasil! Role kamu: <b>$role</b>.";
    $mail->AltBody = "Halo $username, pendaftaran kamu berhasil! Role kamu: $role.";

    $mail->send();

    echo json_encode(["success" => true, "message" => "Registrasi berhasil! Email konfirmasi telah dikirim."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Registrasi berhasil tapi email gagal dikirim: " . $mail->ErrorInfo]);
}
?>
