<?php
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo, p.giacenza, p.immagine_url, c.nome AS nome_categoria 
                FROM prodotti p LEFT JOIN categorie c ON p.categoria = c.id_categoria";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        http_response_code(200);
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore nel recupero dei prodotti']);
    }
}
?>