<?php
// Impostazioni di connessione
$host = '127.0.0.1'; // Oppure 'localhost'
$db   = 'thomann';   // Il nome del tuo database
$user = 'root';      // Utente di default sui server locali
$pass = '';          // Lascia vuoto se usi XAMPP su Windows. Scrivi 'root' se usi MAMP su Mac.

// Configurazione del DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

// Opzioni di sicurezza e comodità per PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mostra gli errori SQL fermando lo script
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Restituisce i dati come array associativi
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Massima sicurezza contro SQL injection
];

try {
    // Creiamo l'oggetto PDO (la connessione vera e propria)
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Rimuovi il commento (//) dalla riga qui sotto solo se vuoi testare che funzioni!
    // echo "Boom! Connesso al database Thomann come un vero pro.";

} catch (\PDOException $e) {
    // Se qualcosa va storto (es. password sbagliata), blocca tutto e mostra l'errore
    http_response_code(500);
    echo json_encode(['errore' => 'Impossibile connettersi al database: ' . $e->getMessage()]);
    exit;
}
?>
