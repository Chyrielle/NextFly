<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['expire'])) {

    if (time() > $_SESSION['expire']) {

        $_SESSION = [];
        session_destroy();

        header("Location: login.html");
        exit();
    }

}
