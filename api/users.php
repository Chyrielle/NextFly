<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

session_start();

require_once __DIR__ . '/../classes/infouser.php';

function sendResponse($status, $data = null) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

if (!isset($_SESSION['login'])) {
    sendResponse(401, ["success" => false, "message" => "Belum login"]);
}

if (time() > ($_SESSION['expire'] ?? 0)) {
    session_destroy();
    sendResponse(401, ["success" => false, "message" => "Sesi habis, silakan login ulang"]);
}

if ($_SESSION['role'] !== 'admin') {
    sendResponse(403, ["success" => false, "message" => "Akses ditolak. Hanya admin yang boleh."]);
}

$method    = $_SERVER["REQUEST_METHOD"];
$infouser  = new infouser();

if ($method === "GET") {
    $result = $infouser->readAll();
    $users  = [];
    while ($row = $result->fetch_assoc()) {
        unset($row['password']);
        $users[] = $row;
    }
    sendResponse(200, ["success" => true, "data" => $users]);

} elseif ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $role     = $data['role']     ?? '';

    if (empty($username) || empty($password) || empty($role)) {
        sendResponse(400, ["success" => false, "message" => "Semua field wajib diisi"]);
    }

    $infouser->insert($username, $password, $role);
    sendResponse(201, ["success" => true, "message" => "User berhasil ditambahkan"]);

} elseif ($method === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    $id   = $data['id']   ?? '';
    $role = $data['role'] ?? '';

    if (empty($id) || empty($role)) {
        sendResponse(400, ["success" => false, "message" => "ID dan role wajib diisi"]);
    }

    $infouser->update($id, $role);
    sendResponse(200, ["success" => true, "message" => "Role user ID $id berhasil diupdate"]);

} elseif ($method === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data['id'] ?? '';

    if (empty($id)) {
        sendResponse(400, ["success" => false, "message" => "ID wajib diisi"]);
    }

    $infouser->delete($id);
    sendResponse(200, ["success" => true, "message" => "User ID $id berhasil dihapus"]);

} else {
    sendResponse(405, ["success" => false, "message" => "Method tidak diizinkan"]);
}
?>
