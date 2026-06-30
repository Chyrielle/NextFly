<?php

require_once __DIR__ . '/permissions.php';

function hasPermission($role, $permission)
{
    global $permissions;

    if (!isset($permissions[$role])) {
        return false;
    }

    return in_array(
        $permission,
        $permissions[$role]
    );
}
