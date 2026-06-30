<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../validation role/roles.php';

if (
    !isset($_SESSION['role'])
    ||
    $_SESSION['role'] !== ROLE_ADMIN
) {

    header("Location: beranda.php");
    exit();

}
