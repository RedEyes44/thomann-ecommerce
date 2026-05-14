<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$dati = json_decode(file_get_contents("php://input"), true);

if (!$dati || !isset($dati['email']) || !isset($dati['password'])) {
    http_response_code(400);
    echo json_encode(['errore' => 'Inserisci email e password.']);
    exit;
}

try {
    // Cerchiamo l'utente nel DB
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE email = ?");
    $stmt->execute([$dati['email']]);
    $utente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifichiamo la password criptata
    if ($utente && password_verify($dati['password'], $utente['password'])) {
        
        // ECCO LA CORREZIONE: Salviamo l'ID e forziamo il salvataggio del RUOLO in memoria!
        $_SESSION['id_utente'] = $utente['id_utente']; 
        $_SESSION['ruolo'] = strtolower(trim($utente['ruolo'])); // Prende il tuo "admin" e lo blinda
        
        echo json_encode([
            'loggato' => true, 
            'messaggio' => 'Benvenuto ' . $utente['nome'],
            'ruolo' => $_SESSION['ruolo']
        ]);
        
    } else {
        http_response_code(401);
        echo json_encode(['errore' => 'Credenziali errate. Riprova.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['errore' => 'Errore di connessione al database.']);
}
?>