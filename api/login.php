<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start();

require_once __DIR__ . '/../config/database.php';

function sendResponse($status, $data = null) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "POST") {
    sendResponse(405, ["success" => false, "message" => "Method tidak diizinkan"]);
}

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    sendResponse(400, ["success" => false, "message" => "Username dan password wajib diisi"]);
}

$db = new Database();
$conn = $db->conn;

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = MD5(?)");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
$_SESSION['login'] = true;
$_SESSION['user']  = $user['username'];
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']  = $user['role'];
$_SESSION['expire'] = time() + 3600;
    
    sendResponse(200, [
        "success"  => true,
        "message"  => "Login berhasil",
        "username" => $user['username'],
        "role"     => $user['role']
    ]);
} else {
    sendResponse(401, ["success" => false, "message" => "Username atau password salah"]);
}
?>
