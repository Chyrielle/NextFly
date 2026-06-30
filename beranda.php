<?php
session_start();
session_regenerate_id(true);

if(!isset($_SESSION['expire'])){
    $_SESSION['expire'] = time() + 30;
}

if(time() > $_SESSION['expire']){
    $_SESSION = [];
    session_destroy();
    header("location: login.html");
    exit();
}
$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION['login']) || !in_array($role, ['admin', 'editor', 'viewer'])) {
    header("location: login.html");
    exit();
}

require 'vendor/autoload.php';
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
<h2>Daftar User</h2>
<table border="1" cellpadding="6">
    <thead>
        <tr><th>ID</th><th>Username</th><th>Role</th></tr>
    </thead>
    <tbody>
        <?php if (empty($users)): ?>
            <tr><td colspan="3">Tidak ada data</td></tr>
        <?php else: ?>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php if ($_SESSION['role'] === 'admin'): ?>
<form action="admin.php" method="GET"><button type="submit">Admin</button></form>
<form action="editor.php" method="GET"><button type="submit">Editor</button></form>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'editor'): ?>
<form action="editor.php" method="GET"><button type="submit">Editor</button></form>
<?php endif; ?>
