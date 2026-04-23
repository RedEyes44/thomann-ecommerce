<?php
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Query base
        $sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo, p.giacenza, p.immagine_url, c.nome AS nome_categoria 
                FROM prodotti p 
                LEFT JOIN categorie c ON p.categoria = c.id_categoria";
        
        $parametri = [];

        // Se il frontend ci passa una categoria specifica, filtriamo!
        if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
            $sql .= " WHERE p.categoria = ?"; // Aggiungiamo il filtro
            $parametri[] = $_GET['categoria'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametri); // Passiamo i parametri (se ci sono)
        
        http_response_code(200);
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore nel recupero dei prodotti']);
    }
}
?>