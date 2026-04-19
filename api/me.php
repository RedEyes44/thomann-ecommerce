<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['id_utente'])) {
    http_response_code(200);
    echo json_encode(['loggato' => true, 'nome' => $_SESSION['nome'], 'email' => $_SESSION['email']]);
} else {
    http_response_code(401);
    echo json_encode(['loggato' => false]);
}
?>