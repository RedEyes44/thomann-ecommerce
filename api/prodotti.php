<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    // La base della query. WHERE 1=1 è un trucco per poter aggiungere "AND..." senza errori
    $sql = "SELECT p.*, c.nome AS nome_categoria 
            FROM prodotti p 
            LEFT JOIN categorie c ON p.categoria = c.id_categoria 
            WHERE 1=1";
            
    $parametri = [];

    // 1. FILTRO CATEGORIA
    if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
        $sql .= " AND p.categoria = ?";
        $parametri[] = $_GET['categoria'];
    }

    // 2. FILTRO RICERCA
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $sql .= " AND (p.nome LIKE ? OR p.descrizione LIKE ?)";
        $termine_ricerca = '%' . trim($_GET['search']) . '%';
        $parametri[] = $termine_ricerca;
        $parametri[] = $termine_ricerca;
    }

    // 3. FILTRO ORDINAMENTO
    $ordinamento = " ORDER BY p.id_prodotto DESC"; // Default: Ultimi arrivi

    if (isset($_GET['sort'])) {
        if ($_GET['sort'] === 'prezzo_asc') {
            $ordinamento = " ORDER BY p.prezzo ASC";
        } elseif ($_GET['sort'] === 'prezzo_desc') {
            $ordinamento = " ORDER BY p.prezzo DESC";
        } elseif ($_GET['sort'] === 'alfabetico') {
            $ordinamento = " ORDER BY p.nome ASC";
        }
    }

    $sql .= $ordinamento;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametri);
    $prodotti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($prodotti, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['errore' => 'Errore SQL: ' . $e->getMessage()]);
}
?>