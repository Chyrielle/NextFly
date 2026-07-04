<?php

session_start();

if (
    !isset($_SESSION['login'])
    ||
    !in_array(
        $_SESSION['role'] ?? '',
        ['admin', 'customer_service']
    )
) {
    header("location: ../../login.html");
    exit();
}

require_once "../../config/database.php";

$db = new Database();
$conn = $db->conn;

$result = $conn->query(
    "SELECT *
     FROM reports
     WHERE status = 'answered'
     ORDER BY created_at DESC"
);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Report</title>
</head>
<body>

<h1>Riwayat Report</h1>

<a href="../admin/Admin.php">
    Kembali ke Admin
</a>

<hr>

<?php if ($result && $result->num_rows > 0): ?>

    <?php while ($report = $result->fetch_assoc()): ?>

        <div style="border:1px solid #ccc;padding:15px;margin-bottom:15px;">

            <h3>
                <?= htmlspecialchars($report['subject']) ?>
            </h3>

            <p>
                <b>Pesan User:</b><br>
                <?= nl2br(htmlspecialchars($report['message'])) ?>
            </p>

            <hr>

            <p>
                <b>Jawaban Customer Service:</b><br>
                <?= nl2br(htmlspecialchars($report['answer'])) ?>
            </p>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <p>Belum ada report yang dijawab.</p>

<?php endif; ?>

</body>
</html>
