<?php
// Diciamo al browser che risponderemo in JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST'); // Questa API accetta SOLO il metodo POST
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

// Controlliamo che il metodo sia effettivamente POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Leggiamo i dati JSON che il frontend ci ha inviato
    $dati_grezzi = file_get_contents("php://input");
    $dati = json_decode($dati_grezzi, true);

    // 2. Controlliamo che ci siano tutti i campi obbligatori
    if (!isset($dati['nome']) || !isset($dati['cognome']) || !isset($dati['email']) || !isset($dati['password'])) {
        http_response_code(400); // 400 Bad Request
        echo json_encode(['errore' => 'Mancano dei dati obbligatori nel form.']);
        exit;
    }

    // 3. Criptiamo la password (LA COSA PIÙ IMPORTANTE!)
    $password_criptata = password_hash($dati['password'], PASSWORD_DEFAULT);

    try {
        // 4. Prepariamo la query di inserimento (CURDATE() inserisce la data di oggi in automatico)
        $sql = "INSERT INTO utenti (nome, cognome, email, password, data_registrazione) 
                VALUES (?, ?, ?, ?, CURDATE())";
                
        $stmt = $pdo->prepare($sql);
        
        // 5. Eseguiamo la query passando i dati
        $stmt->execute([
            $dati['nome'], 
            $dati['cognome'], 
            $dati['email'], 
            $password_criptata
        ]);

        // 6. Se siamo arrivati fin qui, l'utente è salvato!
        http_response_code(201); // 201 Created
        echo json_encode(['messaggio' => 'Registrazione completata con successo!']);

    } catch (PDOException $e) {
        // 7. Gestione degli errori (es. Email già esistente)
        // L'errore 23000 in SQL significa "Violazione di un vincolo di unicità"
        if ($e->getCode() == 23000) {
            http_response_code(409); // 409 Conflict
            echo json_encode(['errore' => 'Questa email è già registrata. Usa il login.']);
        } else {
            http_response_code(500);
            echo json_encode(['errore' => 'Errore del server durante la registrazione.']);
        }
    }
} else {
    http_response_code(405); 
    echo json_encode(['errore' => 'Metodo non consentito. Usa POST.']);
}
?>