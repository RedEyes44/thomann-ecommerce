<?php
session_start(); // Apriamo la memoria

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Controlliamo se esiste un id_utente salvato nella sessione
if (isset($_SESSION['id_utente'])) {
    // L'utente è loggato! Gli mandiamo i suoi dati
    http_response_code(200);
    echo json_encode([
        'loggato' => true,
        'nome' => $_SESSION['nome'],
        'email' => $_SESSION['email']
    ]);
} else {
    // Nessuno è loggato
    http_response_code(401);
    echo json_encode(['loggato' => false]);
}
?>