<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Sicurezza: Solo utenti loggati
if (!isset($_SESSION['id_utente'])) {
    http_response_code(401);
    echo json_encode(['errore' => 'Devi essere loggato per vedere i tuoi ordini.']);
    exit;
}

$id_utente = $_SESSION['id_utente'];

try {
    // Recuperiamo gli ordini dell'utente
    // Usiamo una JOIN per avere i dettagli e il nome del prodotto in un'unica passata
    $sql = "SELECT o.id_ordine, o.data_ordine, o.totale_euro, 
                   d.quantita, d.prezzo_acquisto, p.nome AS nome_prodotto, p.immagine_url
            FROM ordini o
            JOIN dettagli_ordine d ON o.id_ordine = d.id_ordine
            JOIN prodotti p ON d.id_prodotto = p.id_prodotto
            WHERE o.id_utente = ?
            ORDER BY o.data_ordine DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utente]);
    $risultati = $stmt->fetchAll();

    // Raggruppiamo i dati per ordine (perché la query restituisce una riga per ogni prodotto)
    $ordini = [];
    foreach ($risultati as $riga) {
        $id_o = $riga['id_ordine'];
        if (!isset($ordini[$id_o])) {
            $ordini[$id_o] = [
                'id_ordine' => $id_o,
                'data' => $riga['data_ordine'],
                'totale' => $riga['totale_euro'],
                'prodotti' => []
            ];
        }
        $ordini[$id_o]['prodotti'][] = [
            'nome' => $riga['nome_prodotto'],
            'quantita' => $riga['quantita'],
            'prezzo' => $riga['prezzo_acquisto'],
            'immagine' => $riga['immagine_url']
        ];
    }

    // Trasformiamo l'array associativo in una lista semplice per il frontend
    echo json_encode(array_values($ordini));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['errore' => 'Errore nel recupero degli ordini.']);
}
?>