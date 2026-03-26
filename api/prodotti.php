<?php
// 1. Diciamo al browser che stiamo parlando in JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Evita blocchi quando testi dal tuo PC

// 2. Chiamiamo il file che ci connette al database
// Usiamo require_once così se db.php manca o ha errori, lo script si ferma subito
require_once 'db.php';

// 3. Controlliamo che il metodo sia GET (stiamo solo leggendo dati)
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        // 4. Scriviamo la query SQL
        // Usiamo una JOIN per prendere anche il nome della categoria, è molto più comodo per il frontend!
        $sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo, p.giacenza, p.immagine_url, c.nome AS nome_categoria 
                FROM prodotti p 
                LEFT JOIN categorie c ON p.categoria = c.id_categoria";
                
        // 5. Prepariamo ed eseguiamo la query usando PDO (che abbiamo creato in db.php)
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        // 6. Trasformiamo i risultati in un array di PHP
        $prodotti = $stmt->fetchAll();

        // 7. Rispondiamo con un bel JSON codice 200 (OK)
        http_response_code(200);
        echo json_encode($prodotti);
        
    } catch (PDOException $e) {
        // Se la query fallisce per qualche motivo
        http_response_code(500);
        echo json_encode(['errore' => 'Errore nel recupero dei prodotti']);
    }
} else {
    // Se provi a fare un POST o un DELETE su questo file, ti rimbalza
    http_response_code(405); 
    echo json_encode(['errore' => 'Metodo non consentito. Usa GET.']);
}
?>
