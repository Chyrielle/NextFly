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
$role     = $data['role']     ?? 'viewer';

if (empty($username) || empty($password) || empty($email)) {
    echo json_encode(["success" => false, "message" => "Semua field wajib diisi"]);
    exit();
}

// Simpan ke database
$db   = new Database();
$conn = $db->conn;

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, MD5(?), ?)");
$stmt->bind_param("sss", $username, $password, $role);
$stmt->execute();

// Kirim email konfirmasi via Mailtrap
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'df1e4ee6d06bb4';
    $mail->Password   = 'd9c73d89b893e9';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('no-reply@test.com', 'Web App');
    $mail->addAddress($email, $username);

    $mail->Subject = 'Konfirmasi Pendaftaran';
    $mail->Body    = "Halo $username, pendaftaran kamu berhasil! Role kamu: $role.";

    $mail->send();

    echo json_encode(["success" => true, "message" => "Registrasi berhasil! Email konfirmasi telah dikirim."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Registrasi berhasil tapi email gagal dikirim: " . $mail->ErrorInfo]);
}
?>