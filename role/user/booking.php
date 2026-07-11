<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");

function sendResponse($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION['login']) || $role !== 'user') {
    sendResponse(401, [
        "success" => false,
        "message" => "Sesi login sudah habis. Silakan login ulang.",
        "redirect" => "../../login.html"
    ]);
}

$input = json_decode(file_get_contents("php://input"), true);

$service_type = trim($input['service_type'] ?? '');
$nama_layanan = trim($input['nama_layanan'] ?? '');
$lokasi       = trim($input['lokasi'] ?? '');
$total        = $input['total'] ?? '';

if (empty($service_type) || empty($nama_layanan) || empty($lokasi) || empty($total)) {
    sendResponse(400, [
        "success" => false,
        "message" => "Data booking tidak lengkap."
    ]);
}

$booking_code = 'BKG' . strtoupper(substr(uniqid(), -8));

sendResponse(200, [
    "success"       => true,
    "booking_code"  => $booking_code,
    "service_type"  => $service_type,
    "nama_layanan"  => $nama_layanan,
    "lokasi"        => $lokasi,
    "total"         => $total
]);
