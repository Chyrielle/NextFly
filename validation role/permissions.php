<?php

require_once __DIR__ . '/roles.php';

$permissions = [

    ROLE_ADMIN => [
        'manage_users',
        'manage_roles',
        'manage_booking',
        'manage_payment',
        'view_reports'
    ],

    ROLE_CUSTOMER_SERVICE => [
        'manage_booking',
        'manage_payment'
    ],

    ROLE_VIEWER => [
        'view_booking'
    ]

];
