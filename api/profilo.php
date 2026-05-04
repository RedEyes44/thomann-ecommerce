<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Sicurezza: blocca se non sei loggato
if (!isset($_SESSION['id_utente'])) {
    http_response_code(401); echo json_encode(['errore' => 'Non loggato']); exit;
}

$id_utente = $_SESSION['id_utente'];
$method = $_SERVER['REQUEST_METHOD'];

// GET: Leggi i dati del profilo
if ($method === 'GET') {
    $stmt = $pdo->prepare("SELECT nome, cognome, email, indirizzo_spedizione FROM utenti WHERE id_utente = ?");
    $stmt->execute([$id_utente]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
} 
// POST: Aggiorna indirizzo e/o password
elseif ($method === 'POST') {
    $dati = json_decode(file_get_contents("php://input"), true);
    
    $indirizzo = $dati['indirizzo'] ?? null;
    $nuova_password = $dati['password'] ?? null;

    try {
        if (!empty($nuova_password)) {
            // Se ha scritto una nuova password, aggiorniamo tutto (hastandola!)
            $hash = password_hash($nuova_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utenti SET indirizzo_spedizione = ?, password = ? WHERE id_utente = ?");
            $stmt->execute([$indirizzo, $hash, $id_utente]);
        } else {
            // Se la password è vuota, aggiorniamo solo l'indirizzo
            $stmt = $pdo->prepare("UPDATE utenti SET indirizzo_spedizione = ? WHERE id_utente = ?");
            $stmt->execute([$indirizzo, $id_utente]);
        }
        echo json_encode(['messaggio' => 'Profilo aggiornato con successo!']);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['errore' => 'Errore nel salvataggio.']);
    }
}
?>