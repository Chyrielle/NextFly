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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $report_id = $_POST['report_id'] ?? '';
    $answer    = $_POST['answer'] ?? '';

    if (!empty($report_id) && !empty($answer)) {

        $stmt = $conn->prepare(
            "UPDATE reports
             SET answer = ?, status = 'answered'
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $answer,
            $report_id
        );

        $stmt->execute();
    }
}

$result = $conn->query(
    "SELECT *
     FROM reports
     WHERE status = 'pending'
     ORDER BY created_at DESC"
);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Report</title>
</head>
<body>

<h1>Kelola Report</h1>

<a href="CS.php">
    Kembali
</a>

<hr>

<?php if ($result && $result->num_rows > 0): ?>

    <?php while ($report = $result->fetch_assoc()): ?>

        <div style="border:1px solid #ccc;padding:15px;margin-bottom:15px;">

            <h3>
                <?= htmlspecialchars($report['subject']) ?>
            </h3>

            <p>
                <?= nl2br(htmlspecialchars($report['message'])) ?>
            </p>

            <form method="POST">

                <input
                    type="hidden"
                    name="report_id"
                    value="<?= $report['id'] ?>"
                >

                <textarea
                    name="answer"
                    rows="4"
                    cols="50"
                    placeholder="Tulis jawaban..."
                    required
                ></textarea>

                <br><br>

                <button type="submit">
                    Kirim Jawaban
                </button>

            </form>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <p>Tidak ada report pending.</p>

<?php endif; ?>

</body>
</html>
