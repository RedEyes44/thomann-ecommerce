<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito.']);
    exit;
}

if (!isset($_SESSION['id_utente'])) {
    http_response_code(401);
    echo json_encode(['errore' => 'Devi essere loggato per completare l\'ordine.']);
    exit;
}

if (empty($_SESSION['carrello'])) {
    http_response_code(400);
    echo json_encode(['errore' => 'Il carrello è vuoto.']);
    exit;
}

// Leggiamo l'indirizzo in arrivo dal Javascript
$dati = json_decode(file_get_contents("php://input"), true);
if (empty($dati['indirizzo'])) {
    http_response_code(400);
    echo json_encode(['errore' => 'Indirizzo di spedizione mancante.']);
    exit;
}

$indirizzo = trim($dati['indirizzo']);
$id_utente = $_SESSION['id_utente'];
$totale_euro = 0;

// Ricalcoliamo il totale lato server per sicurezza (mai fidarsi del frontend!)
foreach ($_SESSION['carrello'] as $item) {
    $totale_euro += ($item['prezzo'] * $item['quantita']);
}

try {
    $pdo->beginTransaction();

    // 1. Inseriamo la testata dell'ordine CON l'indirizzo
    $stmt = $pdo->prepare("INSERT INTO ordini (id_utente, indirizzo_spedizione, totale_euro) VALUES (?, ?, ?)");
    $stmt->execute([$id_utente, $indirizzo, $totale_euro]);
    $id_ordine = $pdo->lastInsertId();

    // 2. Salviamo i dettagli e SCALIAMO LA GIACENZA dal magazzino!
    $stmt_dettagli = $pdo->prepare("INSERT INTO dettagli_ordine (id_ordine, id_prodotto, quantita, prezzo_acquisto) VALUES (?, ?, ?, ?)");
    $stmt_giacenza = $pdo->prepare("UPDATE prodotti SET giacenza = giacenza - ? WHERE id_prodotto = ?");

    foreach ($_SESSION['carrello'] as $item) {
        $stmt_dettagli->execute([$id_ordine, $item['id_prodotto'], $item['quantita'], $item['prezzo']]);
        // Togliamo fisicamente i prodotti comprati dal DB
        $stmt_giacenza->execute([$item['quantita'], $item['id_prodotto']]);
    }

    $pdo->commit();
    $_SESSION['carrello'] = []; // Svuotiamo il carrello

    http_response_code(200);
    echo json_encode(['messaggio' => 'Ordine completato con successo!', 'id_ordine' => $id_ordine]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['errore' => 'Errore durante l\'elaborazione dell\'ordine.']);
}
?>