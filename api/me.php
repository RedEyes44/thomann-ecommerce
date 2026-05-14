<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (isset($_SESSION['id_utente'])) {
    try {
        // Peschiamo i dati aggiornati dal DB per sapere il ruolo esatto
        $stmt = $pdo->prepare("SELECT nome, ruolo FROM utenti WHERE id_utente = ?");
        $stmt->execute([$_SESSION['id_utente']]);
        $utente = $stmt->fetch();

        echo json_encode([
            'loggato' => true,
            'id_utente' => $_SESSION['id_utente'],
            'nome' => $utente['nome'],
            'ruolo' => $utente['ruolo'] // ECCO IL DATO CHIAVE!
        ]);
    } catch (PDOException $e) {
        echo json_encode(['loggato' => false]);
    }
} else {
    echo json_encode(['loggato' => false]);
}
?>