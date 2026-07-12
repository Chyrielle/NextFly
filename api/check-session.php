<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");

session_start();

$isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;
$role       = $_SESSION['role'] ?? '';

$dashboardUrl = 'role/user/User.php';
if ($role === 'admin') {
    $dashboardUrl = 'role/admin/Admin.php';
} elseif ($role === 'customer_service') {
    $dashboardUrl = 'role/customer-service/CS.php';
}

echo json_encode([
    "loggedIn" => $isLoggedIn,
    "role"     => $role,
    "dashboard" => $dashboardUrl
]);
exit();
?>
