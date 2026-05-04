<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// 1. SICUREZZA: Solo loggati
if (!isset($_SESSION['id_utente'])) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autorizzato']);
    exit;
}

// 2. SICUREZZA: Solo Admin
$stmtRole = $pdo->prepare("SELECT ruolo FROM utenti WHERE id_utente = ?");
$stmtRole->execute([$_SESSION['id_utente']]);
$userRole = $stmtRole->fetchColumn();

if ($userRole !== 'admin') {
    http_response_code(403);
    echo json_encode(['errore' => 'Accesso negato. Area riservata agli amministratori.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// 2. GET: LETTURA DI TUTTI GLI ORDINI
// ==========================================
if ($method === 'GET') {
    try {
        // TORNATI ALLA RAGIONE: Usiamo data_ordine!
        $sql = "SELECT o.id_ordine, o.data_ordine, o.totale_euro, o.stato, o.indirizzo_spedizione, 
                       u.nome, u.cognome, u.email 
                FROM ordini o 
                JOIN utenti u ON o.id_utente = u.id_utente 
                ORDER BY o.data_ordine DESC";
                
        $stmt = $pdo->query($sql);
        $ordini = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($ordini, JSON_UNESCAPED_UNICODE);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore nel recupero degli ordini: ' . $e->getMessage()]);
        exit;
    }
}
// ==========================================
// POST: AGGIORNAMENTO STATO
// ==========================================
elseif ($method === 'POST') {
    $dati = json_decode(file_get_contents("php://input"), true);
    
    if (isset($dati['id_ordine']) && isset($dati['nuovo_stato'])) {
        try {
            $stmt = $pdo->prepare("UPDATE ordini SET stato = ? WHERE id_ordine = ?");
            $stmt->execute([$dati['nuovo_stato'], $dati['id_ordine']]);
            echo json_encode(['messaggio' => 'Stato aggiornato con successo!']);
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['errore' => 'Impossibile aggiornare lo stato']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['errore' => 'Dati mancanti per l\'aggiornamento']);
        exit;
    }
}
?>