<?php
session_start();
session_unset();   // Svuota tutte le variabili di sessione
session_destroy(); // Distrugge la sessione sul server

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode(['messaggio' => 'Logout effettuato con successo']);
?>