<?php
session_start();

// Mostriamo gli errori per debug, così non c'è mai più il caricamento infinito silenzioso
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
// Aggiunto il DELETE tra i metodi consentiti!
header('Access-Control-Allow-Methods: GET, POST, DELETE'); 

require_once 'db.php';

// Se l'utente non ha ancora un carrello, lo creiamo vuoto
if (!isset($_SESSION['carrello'])) {
    $_SESSION['carrello'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// SEZIONE POST: AGGIUNGIAMO AL CARRELLO
// ==========================================
if ($method === 'POST') {
    $dati = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($dati['id_prodotto'])) {
        http_response_code(400);
        echo json_encode(['errore' => 'ID prodotto mancante.']);
        exit;
    }

    $id_prodotto = $dati['id_prodotto'];

    try {
        $stmt = $pdo->prepare("SELECT id_prodotto, nome, prezzo, immagine_url FROM prodotti WHERE id_prodotto = ?");
        $stmt->execute([$id_prodotto]);
        $prodotto = $stmt->fetch();

        if ($prodotto) {
            $trovato = false;
            foreach ($_SESSION['carrello'] as &$item) {
                if ($item['id_prodotto'] == $id_prodotto) {
                    $item['quantita'] += 1;
                    $trovato = true;
                    break;
                }
            }

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

    foreach ($_SESSION['carrello'] as $item) {
        $totale_euro += ($item['prezzo'] * $item['quantita']);
        $totale_articoli += $item['quantita'];
    }

    echo json_encode([
        'carrello' => $_SESSION['carrello'],
        'totale_euro' => number_format($totale_euro, 2, '.', ''),
        'totale_articoli' => $totale_articoli
    ]);
    exit;
} 
// ==========================================
// SEZIONE DELETE: RIMUOVIAMO DAL CARRELLO
// ==========================================
elseif ($method === 'DELETE') {
    $dati = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($dati['id_prodotto'])) {
        http_response_code(400);
        echo json_encode(['errore' => 'ID mancante.']);
        exit;
    }

    $id_prodotto = $dati['id_prodotto'];

    foreach ($_SESSION['carrello'] as $key => $item) {
        if ($item['id_prodotto'] == $id_prodotto) {
            unset($_SESSION['carrello'][$key]);
            // Ri-ordiniamo gli indici (importantissimo)
            $_SESSION['carrello'] = array_values($_SESSION['carrello']); 
            break;
        }
    }

    http_response_code(200);
    echo json_encode(['messaggio' => 'Prodotto rimosso']);
    exit;

} else {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito.']);
    exit;
}
?>