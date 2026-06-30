<?php

require_once __DIR__ . '/auth.php';

if (
    !isset($_SESSION['role'])
    ||
    !in_array($_SESSION['role'], ['admin', 'editor'])
) {

    header("Location: beranda.php");
    exit();

}
