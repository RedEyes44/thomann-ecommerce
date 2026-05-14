<?php
// BOMBA ANTI-CACHE: Costringe il browser a scaricare i dati freschi
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

ini_set('display_errors', 0);
session_start();
require_once 'db.php';

// Forza il Database a mostrare eventuali errori nascosti
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_SESSION['id_utente']) || !isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['errore' => 'Accesso negato. Riavvia la sessione.']);
    exit;
}

try {
    $s1 = $pdo->query("SELECT COUNT(*) AS totale_ordini, COALESCE(SUM(totale_euro), 0) AS incasso_totale FROM ordini")->fetch(PDO::FETCH_ASSOC);
    $s2 = $pdo->query("SELECT id_prodotto, nome, giacenza FROM prodotti WHERE giacenza <= 3 ORDER BY giacenza ASC")->fetchAll(PDO::FETCH_ASSOC);
    $stats_vendite = $pdo->query("SELECT stato, COUNT(*) as numero_ordini, COALESCE(SUM(totale_euro), 0) as incasso FROM ordini GROUP BY stato")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'totale_ordini' => (int)$s1['totale_ordini'], 
        'incasso_totale' => (float)$s1['incasso_totale'], 
        'allerte_scorte' => $s2,
        'vendite' => $stats_vendite,
        'status' => 'Connessione Perfetta'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['errore' => "ERRORE SQL INVISIBILE: " . $e->getMessage()]);
}
?>