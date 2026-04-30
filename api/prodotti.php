<?php
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Query base: estraiamo tutto
        $sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo, p.giacenza, p.immagine_url, c.nome AS nome_categoria 
                FROM prodotti p 
                LEFT JOIN categorie c ON p.categoria = c.id_categoria 
                WHERE 1=1"; // Il WHERE 1=1 è un trucco furbo per poter concatenare facilmente gli AND
        
        $parametri = [];

        // 1. FILTRO CATEGORIA: Se l'utente clicca un bottone
        if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
            $sql .= " AND p.categoria = ?";
            $parametri[] = $_GET['categoria'];
        }

        // 2. FILTRO RICERCA: Se l'utente digita qualcosa nella barra
        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
            // Cerchiamo la parola sia nel nome che nella descrizione del prodotto
            $sql .= " AND (p.nome LIKE ? OR p.descrizione LIKE ?)";
            $termine_ricerca = '%' . trim($_GET['search']) . '%'; // Aggiungiamo i jolly % per trovare parole all'interno
            $parametri[] = $termine_ricerca;
            $parametri[] = $termine_ricerca;
        }

        // Prepariamo ed eseguiamo la query con i parametri dinamici
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametri);
        
        http_response_code(200);
        echo json_encode($stmt->fetchAll());

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore nel recupero dei prodotti']);
    }
} else {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito.']);
}
?>