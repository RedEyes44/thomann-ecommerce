<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['id_utente'])) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autorizzato']);
    exit;
}

$stmtRole = $pdo->prepare("SELECT ruolo FROM utenti WHERE id_utente = ?");
$stmtRole->execute([$_SESSION['id_utente']]);
$userRole = $stmtRole->fetchColumn();

if ($userRole !== 'admin') {
    http_response_code(403);
    echo json_encode(['errore' => 'Accesso negato. Area riservata agli amministratori.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
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
        echo json_encode(['errore' => 'Errore SQL: ' . $e->getMessage()]);
        exit;
    }
} elseif ($method === 'POST') {
    $dati = json_decode(file_get_contents("php://input"), true);
    if (isset($dati['id_ordine']) && isset($dati['nuovo_stato'])) {
        try {
            $stmt = $pdo->prepare("UPDATE ordini SET stato = ? WHERE id_ordine = ?");
            $stmt->execute([$dati['nuovo_stato'], $dati['id_ordine']]);
            http_response_code(200);
            echo json_encode(['messaggio' => 'Stato dell\'ordine aggiornato con successo!']);
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
} else {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non supportato']);
}
?>