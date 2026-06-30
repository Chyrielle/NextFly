<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

session_start();

function sendResponse($status, $data = null) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

$_SESSION = [];
session_destroy();

sendResponse(200, ["success" => true, "message" => "Logout berhasil"]);
?>
