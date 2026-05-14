<?php
header('Content-Type: application/json');
require_once 'db.php';

// Controlliamo se è stato passato un ID nell'URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Facciamo una JOIN per avere anche il nome della categoria
        $sql = "SELECT p.*, c.nome AS nome_categoria 
                FROM prodotti p 
                JOIN categorie c ON p.categoria = c.id_categoria 
                WHERE p.id_prodotto = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $prodotto = $stmt->fetch();

        if ($prodotto) {
            echo json_encode($prodotto);
        } else {
            http_response_code(404);
            echo json_encode(['errore' => 'Prodotto non trovato']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del server']);
    }
} else {
    http_response_code(400);
    echo json_encode(['errore' => 'ID mancante']);
}
?>