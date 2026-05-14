<?php
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dati = json_decode(file_get_contents("php://input"), true);

    if (empty($dati['nome']) || empty($dati['cognome']) || empty($dati['email']) || empty($dati['password'])) {
        http_response_code(400);
        echo json_encode(['errore' => 'Dati mancanti.']);
        exit;
    }

    $password_criptata = password_hash($dati['password'], PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO utenti (nome, cognome, email, password, data_registrazione) VALUES (?, ?, ?, ?, CURDATE())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dati['nome'], $dati['cognome'], $dati['email'], $password_criptata]);

        http_response_code(201);
        echo json_encode(['messaggio' => 'Registrazione completata con successo!']);

    } catch (PDOException $e) {
        if ($e->getCode() == 23000 || $e->getCode() == '23000') {
            http_response_code(409);
            echo json_encode(['errore' => 'Questa email risulta gia registrata. Usa il login.']);
        } else {
            http_response_code(500);
            echo json_encode(['errore' => 'Errore del server durante la registrazione.']);
        }
    }
}
?>