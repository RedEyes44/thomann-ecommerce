<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['carrello'])) {
    $_SESSION['carrello'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $dati = json_decode(file_get_contents("php://input"), true);
    if (!isset($dati['id_prodotto'])) {
        http_response_code(400);
        echo json_encode(['errore' => 'ID mancante.']);
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
            echo json_encode(['messaggio' => 'Aggiunto al carrello!', 'totale_articoli' => array_sum(array_column($_SESSION['carrello'], 'quantita'))]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore server.']);
    }
} elseif ($method === 'GET') {
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
}
?>