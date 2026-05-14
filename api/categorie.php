<?php
// File: api/categorie.php
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Pesca tutte le categorie in ordine alfabetico
    $stmt = $pdo->query("SELECT id_categoria, nome FROM categorie ORDER BY nome ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['errore' => 'Errore di connessione al DB']);
}
?>