<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Sicurezza: Solo Admin
if (!isset($_SESSION['id_utente'])) {
    http_response_code(401); echo json_encode(['errore' => 'Non autorizzato']); exit;
}
$stmtRole = $pdo->prepare("SELECT ruolo FROM utenti WHERE id_utente = ?");
$stmtRole->execute([$_SESSION['id_utente']]);
if ($stmtRole->fetchColumn() !== 'admin') {
    http_response_code(403); echo json_encode(['errore' => 'Accesso negato.']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// 1. GET: Leggi tutti i prodotti (per la tabella admin)
if ($method === 'GET') {
    try {
        $sql = "SELECT p.*, c.nome AS nome_categoria 
                FROM prodotti p 
                LEFT JOIN categorie c ON p.categoria = c.id_categoria 
                ORDER BY p.id_prodotto DESC";
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['errore' => 'Errore DB']);
    }
} 
// 2. POST: Aggiungi o Modifica un prodotto
elseif ($method === 'POST') {
    $dati = json_decode(file_get_contents("php://input"), true);
    
    // Controlliamo i campi obbligatori
    if(empty($dati['nome']) || empty($dati['prezzo']) || empty($dati['categoria']) || !isset($dati['giacenza'])) {
        http_response_code(400); echo json_encode(['errore' => 'Compila tutti i campi obbligatori.']); exit;
    }

    try {
        if (!empty($dati['id_prodotto'])) {
            // Se c'è un ID, è una MODIFICA (UPDATE)
            $stmt = $pdo->prepare("UPDATE prodotti SET nome=?, descrizione=?, prezzo=?, giacenza=?, categoria=?, immagine_url=? WHERE id_prodotto=?");
            $stmt->execute([$dati['nome'], $dati['descrizione'], $dati['prezzo'], $dati['giacenza'], $dati['categoria'], $dati['immagine_url'], $dati['id_prodotto']]);
            echo json_encode(['messaggio' => 'Prodotto modificato con successo!']);
        } else {
            // Se non c'è ID, è un NUOVO PRODOTTO (INSERT)
            $stmt = $pdo->prepare("INSERT INTO prodotti (nome, descrizione, prezzo, giacenza, categoria, immagine_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$dati['nome'], $dati['descrizione'], $dati['prezzo'], $dati['giacenza'], $dati['categoria'], $dati['immagine_url']]);
            echo json_encode(['messaggio' => 'Nuovo prodotto aggiunto!']);
        }
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['errore' => 'Errore durante il salvataggio.']);
    }
} 
// 3. DELETE: Elimina un prodotto
elseif ($method === 'DELETE') {
    $dati = json_decode(file_get_contents("php://input"), true);
    try {
        $stmt = $pdo->prepare("DELETE FROM prodotti WHERE id_prodotto=?");
        $stmt->execute([$dati['id_prodotto']]);
        echo json_encode(['messaggio' => 'Prodotto eliminato!']);
    } catch (PDOException $e) {
        // Se il prodotto è in un ordine, MySQL bloccherà la cancellazione per sicurezza (Foreign Key).
        http_response_code(400); 
        echo json_encode(['errore' => 'Non puoi eliminare un prodotto che è già stato acquistato in un ordine. Piuttosto, metti la giacenza a 0!']);
    }
}
?>