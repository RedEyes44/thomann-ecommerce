<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['id_utente'])) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non loggato']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_ordine, data_ordine AS data, totale_euro AS totale, stato 
                           FROM ordini 
                           WHERE id_utente = ? 
                           ORDER BY data_ordine DESC");
    $stmt->execute([$_SESSION['id_utente']]);
    $ordini = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt_dettagli = $pdo->prepare("SELECT p.nome, p.immagine_url AS immagine, d.quantita, d.prezzo_acquisto AS prezzo 
                                    FROM dettagli_ordine d 
                                    JOIN prodotti p ON d.id_prodotto = p.id_prodotto 
                                    WHERE d.id_ordine = ?");

    foreach ($ordini as &$ordine) {
        $stmt_dettagli->execute([$ordine['id_ordine']]);
        $ordine['prodotti'] = $stmt_dettagli->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($ordini, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['errore' => 'Errore SQL: ' . $e->getMessage()]);
}
?>