<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once 'db.php';

// Se l'utente non ha ancora un carrello nella sua sessione, lo creiamo vuoto
if (!isset($_SESSION['carrello'])) {
    $_SESSION['carrello'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// SEZIONE POST: AGGIUNGIAMO AL CARRELLO
// ==========================================
if ($method === 'POST') {
    $dati = json_decode(file_get_contents("php://input"), true);
    
    // Controlliamo che il frontend ci abbia mandato l'ID del prodotto
    if (!isset($dati['id_prodotto'])) {
        http_response_code(400);
        echo json_encode(['errore' => 'ID prodotto mancante.']);
        exit;
    }

    $id_prodotto = $dati['id_prodotto'];

    try {
        // Peschiamo il prodotto dal DB per assicurarci che esista e prendere il prezzo VERO
        $stmt = $pdo->prepare("SELECT id_prodotto, nome, prezzo, immagine_url FROM prodotti WHERE id_prodotto = ?");
        $stmt->execute([$id_prodotto]);
        $prodotto = $stmt->fetch();

        if ($prodotto) {
            // Controlliamo se c'è già nel carrello per aumentare solo la quantità
            $trovato = false;
            foreach ($_SESSION['carrello'] as &$item) {
                if ($item['id_prodotto'] == $id_prodotto) {
                    $item['quantita'] += 1;
                    $trovato = true;
                    break;
                }
            }

            // Se non c'è, lo aggiungiamo come nuovo elemento
            if (!$trovato) {
                $_SESSION['carrello'][] = [
                    'id_prodotto' => $prodotto['id_prodotto'],
                    'nome' => $prodotto['nome'],
                    'prezzo' => $prodotto['prezzo'],
                    'immagine_url' => $prodotto['immagine_url'],
                    'quantita' => 1
                ];
            }

            http_response_code(200);
            echo json_encode([
                'messaggio' => 'Aggiunto al carrello!', 
                'totale_articoli' => array_sum(array_column($_SESSION['carrello'], 'quantita'))
            ]);
            exit;
        } else {
            http_response_code(404);
            echo json_encode(['errore' => 'Prodotto non trovato.']);
            exit;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del server.']);
        exit;
    }
} 
// ==========================================
// SEZIONE GET: VEDIAMO COSA C'È NEL CARRELLO
// ==========================================
elseif ($method === 'GET') {
    $totale_euro = 0;
    $totale_articoli = 0;

    // Calcoliamo il totale dei soldi e dei pezzi
    foreach ($_SESSION['carrello'] as $item) {
        $totale_euro += ($item['prezzo'] * $item['quantita']);
        $totale_articoli += $item['quantita'];
    }

    echo json_encode([
        'carrello' => $_SESSION['carrello'],
        'totale_euro' => number_format($totale_euro, 2, '.', ''), // Formattiamo a 2 decimali
        'totale_articoli' => $totale_articoli
    ]);
    exit;
} else {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito.']);
    exit;
}
?>

