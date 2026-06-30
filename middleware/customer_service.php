<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../validation role/roles.php';

if (
    !isset($_SESSION['role'])
    ||
    !in_array(
        $_SESSION['role'],
        [ROLE_ADMIN, ROLE_CUSTOMER_SERVICE]
    )
) {

    header("Location: beranda.php");
    exit();

}
