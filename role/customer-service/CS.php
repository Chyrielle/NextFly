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
if (!isset($_SESSION['login']) || !in_array($role, ['admin', 'customer_service'])) {
    header("location: ../../login.html");
    exit();
}
echo "hello editor";
?>
<?php if ($_SESSION['role'] === 'admin'): ?>
<form action="../admin/Admin.php" method="GET"><button type="submit">Admin</button></form>
<form action="../user/User.php" method="GET"><button type="submit">User</button></form>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'editor'): ?>
<form action="../user/User.php" method="GET"><button type="submit">User</button></form>
<?php endif; ?>

