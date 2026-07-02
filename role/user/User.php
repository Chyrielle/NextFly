<?php
session_start();
session_regenerate_id(true);

if(!isset($_SESSION['expire'])){
    $_SESSION['expire'] = time() + 30;
}

if(time() > $_SESSION['expire']){
    $_SESSION = [];
    session_destroy();
    header("location: ../../login.html");
    exit();
}
$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION['login']) || !in_array($role, ['admin', 'customer_service', 'user'])) {
    header("location: ../../login.html");
    exit();
}

require '../../vendor/autoload.php';
use GuzzleHttp\Client;

$users = [];
try {
    $client   = new Client();
    $response = $client->get('http://localhost/your-project/api/users.php', [
        'headers' => [
            'Cookie' => session_name() . '=' . session_id()
        ]
    ]);
    $data  = json_decode($response->getBody(), true);
    $users = $data['success'] ? $data['data'] : [];
} catch (Exception $e) {
    $users = [];
}
echo "hello pengunjung";
?>
<?php if ($_SESSION['role'] === 'admin'): ?>
<form action="../admin/Admin.php" method="GET"><button type="submit">Admin</button></form>
<form action="../customer-service/CS.php" method="GET"><button type="submit">Customer_Service</button></form>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'customer_service'): ?>
<form action="../customer-service/CS.php" method="GET"><button type="submit">Customer_Service</button></form>
<?php endif; ?>
