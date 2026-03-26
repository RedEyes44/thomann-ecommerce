<?php
// 1. Avviamo il motore delle sessioni (il "biglietto" per l'utente)
session_start(); 

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $dati_grezzi = file_get_contents("php://input");
    $dati = json_decode($dati_grezzi, true);

    // Controlliamo che l'utente non abbia lasciato i campi vuoti
    if (empty($dati['email']) || empty($dati['password'])) {
        http_response_code(400);
        // Niente accenti per non far esplodere JSON!
        echo json_encode(['errore' => 'Inserisci email e password.']);
        exit;
    }

    try {
        // 2. Cerchiamo l'utente nel database usando la sua email
        $sql = "SELECT * FROM utenti WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dati['email']]);
        
        $utente = $stmt->fetch(); // Estrae la riga dell'utente (se esiste)

        // 3. Il controllo magico di PHP
        // Se $utente esiste AND la password inserita corrisponde all'hash salvato nel DB
        if ($utente && password_verify($dati['password'], $utente['password'])) {
            
            // 4. LOGIN EFFETTUATO! Salviamo i dati dell'utente nella Sessione del server
            $_SESSION['id_utente'] = $utente['id_utente'];
            $_SESSION['nome'] = $utente['nome'];
            $_SESSION['email'] = $utente['email'];

            http_response_code(200);
            echo json_encode([
                'messaggio' => 'Login effettuato!',
                'nome' => $utente['nome'] // Lo rimandiamo al frontend per scrivergli "Ciao Nome"
            ]);
            exit;
            
        } else {
            // Se l'utente non esiste o la password è sbagliata (Diamo un errore generico per sicurezza)
            http_response_code(401); // 401 Unauthorized
            echo json_encode(['errore' => 'Email o password errati. Riprova.']);
            exit;
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore interno del server.']);
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito.']);
    exit;
}
?>
