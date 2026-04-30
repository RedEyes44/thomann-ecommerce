<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

// 1. Controllo Sicurezza: Devi essere loggato
if (!isset($_SESSION['id_utente'])) {
    http_response_code(401);
    echo json_encode(['errore' => 'Devi effettuare il login per completare l\'acquisto.']);
    exit;
}

// 2. Controllo Sicurezza: Il carrello non deve essere vuoto
if (!isset($_SESSION['carrello']) || count($_SESSION['carrello']) === 0) {
    http_response_code(400);
    echo json_encode(['errore' => 'Il carrello è vuoto.']);
    exit;
}

$id_utente = $_SESSION['id_utente'];
$carrello = $_SESSION['carrello'];

// Calcoliamo il totale VERO (ci fidiamo solo del backend)
$totale_euro = 0;
foreach ($carrello as $item) {
    $totale_euro += ($item['prezzo'] * $item['quantita']);
}

try {
    // 3. Iniziamo una TRANSAZIONE SQL (Se qualcosa va storto, annulla tutto)
    $pdo->beginTransaction();

    // 4. Creiamo l'ordine principale nella tabella 'ordini'
    $sql_ordine = "INSERT INTO ordini (id_utente, totale_euro) VALUES (?, ?)";
    $stmt_ordine = $pdo->prepare($sql_ordine);
    $stmt_ordine->execute([$id_utente, $totale_euro]);
    
    $id_ordine_appena_creato = $pdo->lastInsertId();

    // Prepariamo le due query che ci serviranno per ogni prodotto
    $sql_dettaglio = "INSERT INTO dettagli_ordine (id_ordine, id_prodotto, quantita, prezzo_acquisto) VALUES (?, ?, ?, ?)";
    $stmt_dettaglio = $pdo->prepare($sql_dettaglio);

    // ECCO LA MAGIA: La query per togliere i pezzi dal magazzino
    $sql_magazzino = "UPDATE prodotti SET giacenza = giacenza - ? WHERE id_prodotto = ?";
    $stmt_magazzino = $pdo->prepare($sql_magazzino);

    // 5. Cicliamo il carrello
    foreach ($carrello as $item) {
        // A. Salviamo lo scontrino
        $stmt_dettaglio->execute([
            $id_ordine_appena_creato,
            $item['id_prodotto'],
            $item['quantita'],
            $item['prezzo']
        ]);
        
        // B. SCALIAMO LA GIACENZA VERA DAL DATABASE!
        $stmt_magazzino->execute([
            $item['quantita'],
            $item['id_prodotto']
        ]);
    }

    // 6. Confermiamo la transazione! Salviamo tutto definitivamente.
    $pdo->commit();

    // 7. Svuotiamo il carrello dalla sessione dell'utente
    $_SESSION['carrello'] = [];

    // 8. Diamo il via libera al Frontend
    http_response_code(200);
    echo json_encode(['messaggio' => 'Ordine completato con successo!', 'id_ordine' => $id_ordine_appena_creato]);

} catch (PDOException $e) {
    // Se è esploso qualcosa, annulliamo il salvataggio a metà
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['errore' => 'Errore fatale durante la creazione dell\'ordine.']);
}
?>