<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['ruolo'] !== 'admin') {
    http_response_code(403); echo json_encode(['errore' => 'Accesso negato.']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM categorie ORDER BY nome ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} elseif ($method === 'POST') {
    $dati = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO categorie (nome) VALUES (?)");
    $stmt->execute([$dati['nome']]);
    echo json_encode(['messaggio' => 'Categoria aggiunta con successo']);
} elseif ($method === 'DELETE') {
    $dati = json_decode(file_get_contents("php://input"), true);
    // Controllo di sicurezza per evitare di rompere i prodotti
    $check = $pdo->prepare("SELECT COUNT(*) FROM prodotti WHERE categoria = ?");
    $check->execute([$dati['id_categoria']]);
    if($check->fetchColumn() > 0) {
        http_response_code(400); echo json_encode(['errore' => 'Impossibile eliminare: ci sono prodotti in questa categoria.']); exit;
    }
    $stmt = $pdo->prepare("DELETE FROM categorie WHERE id_categoria = ?");
    $stmt->execute([$dati['id_categoria']]);
    echo json_encode(['messaggio' => 'Categoria eliminata']);
}
?>