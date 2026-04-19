<?php
session_start(); 
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dati = json_decode(file_get_contents("php://input"), true);

    if (empty($dati['email']) || empty($dati['password'])) {
        http_response_code(400);
        echo json_encode(['errore' => 'Inserisci email e password.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM utenti WHERE email = ?");
        $stmt->execute([$dati['email']]);
        $utente = $stmt->fetch();

        if ($utente && password_verify($dati['password'], $utente['password'])) {
            $_SESSION['id_utente'] = $utente['id_utente'];
            $_SESSION['nome'] = $utente['nome'];
            $_SESSION['email'] = $utente['email'];

            http_response_code(200);
            echo json_encode(['messaggio' => 'Login effettuato!', 'nome' => $utente['nome']]);
        } else {
            http_response_code(401);
            echo json_encode(['errore' => 'Email o password errati.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore interno del server.']);
    }
}
?>