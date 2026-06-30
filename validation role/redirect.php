<?php

require_once __DIR__ . '/roles.php';

function getRedirectPage($role)
{
    switch ($role) {

        case ROLE_ADMIN:
            return 'admin.php';

        case ROLE_EDITOR:
            return 'editor.php';

        case ROLE_VIEWER:
            return 'beranda.php';

        default:
            return 'login.html';
    }
}
