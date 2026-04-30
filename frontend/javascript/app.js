// ==========================================
// 1. FUNZIONI GLOBALI (Sessione, Navbar)
// ==========================================

async function controllaSessione(richiedeLogin = false) {
    try {
        const risposta = await fetch('../api/me.php');
        const utente = await risposta.json();
        const menu = document.getElementById('menu-utente');

        if (utente.loggato) {
            if (menu) {
                menu.innerHTML = `
                    <span class="navbar-text text-white me-3">Ciao, <b>${utente.nome}</b>!</span>
                    <a href="ordini.html" class="btn btn-outline-info me-2">I miei Ordini</a>
                    <a href="carrello.html" class="btn btn-warning me-2">🛒 CARRELLO</a>
                    <button onclick="eseguiLogout()" class="btn btn-outline-danger">ESCI</button>
                `;
            }
        } else {
            // Se la pagina (es. carrello) richiede il login assoluto, lo cacciamo
            if (richiedeLogin) {
                window.location.href = 'login.html';
            } else if (menu) {
                // Altrimenti gli mostriamo i bottoni per accedere
                menu.innerHTML = `
                    <a href="login.html" class="btn btn-outline-light me-2">LOGIN</a>
                    <a href="registrazione.html" class="btn btn-outline-light me-2">REGISTRATI</a>
                    <a href="carrello.html" class="btn btn-warning">🛒 CARRELLO</a>
                `;
            }
        }
    } catch (errore) {
        console.log("Errore controllo sessione.");
    }
}

async function eseguiLogout() {
    await fetch('../api/logout.php');
    window.location.href = 'index.html';
}

async function aggiornaContatoreCarrello() {
    try {
        const risposta = await fetch('../api/carrello.php', { cache: 'no-store' });
        const dati = await risposta.json();
        const bottoniCarrello = document.querySelectorAll('.btn-warning'); 
        bottoniCarrello.forEach(btn => {
            btn.innerHTML = `🛒 CARRELLO (${dati.totale_articoli || 0})`;
        });
    } catch (errore) {
        console.error("Errore contatore carrello", errore);
    }
}

// Funzione furba: se 'isDettaglio' è true, aggiorna la pagina per scalare la giacenza.
// Altrimenti aggiorna solo il numerino nel menu.
async function aggiungiAlCarrello(idProdotto, isDettaglio = false) {
    try {
        const risposta = await fetch('../api/carrello.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_prodotto: idProdotto })
        });
        const risultato = await risposta.json();
        if (risposta.ok) {
            if (isDettaglio) {
                caricaDettaglio(); // Scala la giacenza in tempo reale nella pagina singola
            } else {
                aggiornaContatoreCarrello(); // Aggiorna il badge nella home
                alert("🎸 " + risultato.messaggio); 
            }
        } else {
            alert("Errore: " + risultato.errore);
        }
    } catch (errore) {
        console.error(errore);
    }
}


// ==========================================
// 2. FUNZIONI CATALOGO E PRODOTTO (index.html e prodotto.html)
// ==========================================

async function caricaCatalogo(idCategoria = null) {
    try {
        let urlApi = '../api/prodotti.php';
        if (idCategoria !== null) {
            urlApi += `?categoria=${idCategoria}`;
        }

        const risposta = await fetch(urlApi);
        const prodotti = await risposta.json();
        const contenitore = document.getElementById('catalogo');
        
        if (prodotti.length === 0) {
            contenitore.innerHTML = '<div class="col-12 text-center text-muted"><h5>Nessun prodotto trovato in questa categoria.</h5></div>';
            return;
        }

        contenitore.innerHTML = ''; 
        
        prodotti.forEach(prodotto => {
            contenitore.innerHTML += `
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <a href="prodotto.html?id=${prodotto.id_prodotto}">
                            <img src="${prodotto.immagine_url}" class="card-img-top" alt="${prodotto.nome}">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary mb-2 w-50">${prodotto.nome_categoria}</span>
                            <h5 class="card-title">
                                <a href="prodotto.html?id=${prodotto.id_prodotto}" class="text-decoration-none text-white">
                                    ${prodotto.nome}
                                </a>
                            </h5>
                            <p class="card-text small" style="color: #bbbbbb;">${prodotto.descrizione.substring(0, 60)}...</p>
                            <h4 class="mt-auto fw-bold" style="color: #d90429;">€ ${prodotto.prezzo}</h4>
                            <button class="btn btn-success mt-3" onclick="aggiungiAlCarrello(${prodotto.id_prodotto}, false)">
                                AGGIUNGI AL CARRELLO
                            </button>
                        </div>
                    </div>
                </div>`;
        });
    } catch (errore) {
        document.getElementById('catalogo').innerHTML = '<div class="alert alert-danger">Impossibile caricare il catalogo.</div>';
    }
}

async function caricaDettaglio() {
    const urlParams = new URLSearchParams(window.location.search);
    const idProdotto = urlParams.get('id');

    if (!idProdotto) {
        window.location.href = 'index.html';
        return;
    }

    try {
        const rispostaProdotto = await fetch(`../api/prodotto.php?id=${idProdotto}`);
        const prodotto = await rispostaProdotto.json();

        if (!rispostaProdotto.ok) throw new Error(prodotto.errore);

        const rispostaCarrello = await fetch('../api/carrello.php', { cache: 'no-store' });
        const datiCarrello = await rispostaCarrello.json();
        
        let quantitaInCarrello = 0;
        if (datiCarrello.carrello) {
            const itemTrovato = datiCarrello.carrello.find(i => i.id_prodotto == prodotto.id_prodotto);
            if (itemTrovato) quantitaInCarrello = itemTrovato.quantita;
        }

        const stockEffettivo = prodotto.giacenza - quantitaInCarrello;
        document.title = `${prodotto.nome} - TH-Guitars`;

        let boxDisponibilitaHTML = '';
        let bottoneCarrelloHTML = '';

        if (stockEffettivo > 0) {
            boxDisponibilitaHTML = `<strong>Disponibilità:</strong> ${stockEffettivo} pezzi in magazzino (Pronta consegna)`;
            // Passo "true" al bottone per fargli ricaricare la pagina live
            bottoneCarrelloHTML = `
                <button class="btn btn-success btn-lg w-100 py-3 mt-3 fw-bold" onclick="aggiungiAlCarrello(${prodotto.id_prodotto}, true)">
                    AGGIUNGI AL CARRELLO
                </button>`;
        } else {
            boxDisponibilitaHTML = `<strong class="text-danger">ESAURITO</strong> (Hai raggiunto il limite disponibile)`;
            bottoneCarrelloHTML = `
                <button class="btn btn-secondary btn-lg w-100 py-3 mt-3 fw-bold" disabled>
                    NON DISPONIBILE
                </button>`;
        }

        document.getElementById('dettaglio-prodotto').innerHTML = `
            <div class="col-md-6 text-center">
                <img src="${prodotto.immagine_url}" class="img-fluid rounded mb-3 w-100 shadow" style="height: 400px; object-fit: contain; background: radial-gradient(circle at 50% 100%, #6b0515 0%, #1c1c1c 55%, #0f0f0f 100%); padding: 20px; border: 1px solid #333; border-bottom: 3px solid #d90429;" alt="${prodotto.nome}">
            </div>
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html" class="text-secondary text-decoration-none">Catalogo</a></li>
                        <li class="breadcrumb-item active text-light">${prodotto.nome_categoria}</li>
                    </ol>
                </nav>
                <h1 class="display-5 fw-bold text-white text-uppercase">${prodotto.nome}</h1>
                <h2 class="my-4 fw-bold" style="color: #d90429;">€ ${prodotto.prezzo}</h2>
                <p class="lead mb-4" style="color: #bbbbbb;">${prodotto.descrizione}</p>
                
                <div class="alert border-secondary text-light" style="background-color: #222;">
                    ${boxDisponibilitaHTML}
                </div>

                ${bottoneCarrelloHTML}
            </div>
        `;
    } catch (errore) {
        document.getElementById('dettaglio-prodotto').innerHTML = `<div class="alert alert-danger bg-dark border-danger text-danger">Errore: ${errore.message}</div>`;
    }
}


// ==========================================
// 3. FUNZIONI CARRELLO E CHECKOUT (carrello.html)
// ==========================================

async function caricaDatiCarrello() {
    try {
        const risposta = await fetch('../api/carrello.php', { cache: 'no-store' });
        const dati = await risposta.json();
        const contenitore = document.getElementById('lista-carrello');
        
        if (dati.carrello.length === 0) {
            contenitore.innerHTML = `
                <div class="text-center py-5">
                    <h5 class="text-white">Il tuo carrello è vuoto.</h5>
                    <a href="index.html" class="btn btn-outline-light mt-3">TORNA AL CATALOGO</a>
                </div>
            `;
            document.getElementById('btn-checkout').disabled = true;
            document.getElementById('totale-articoli').innerText = "0";
            document.getElementById('totale-euro').innerText = "0.00";
            return;
        }

        contenitore.innerHTML = ''; 
        dati.carrello.forEach(item => {
            const prezzoTotaleRiga = (item.prezzo * item.quantita).toFixed(2);
            contenitore.innerHTML += `
                <div class="d-flex align-items-center border-bottom border-secondary pb-3 mb-3">
                    <img src="${item.immagine_url}" class="img-carrello rounded border border-dark me-3" alt="${item.nome}">
                    <div class="flex-grow-1">
                        <h5 class="mb-1 text-white">${item.nome}</h5>
                        <small style="color: #aaaaaa;">Prezzo singolo: € ${item.prezzo}</small>
                    </div>
                    <div class="text-center px-4">
                        <span class="d-block small" style="color: #aaaaaa;">Qtà</span>
                        <strong class="text-white">x${item.quantita}</strong>
                    </div>
                    <div class="text-end" style="width: 120px;">
                        <strong class="fs-5 d-block mb-2" style="color: #d90429;">€ ${prezzoTotaleRiga}</strong>
                        <button class="btn btn-sm btn-outline-danger" onclick="rimuoviDalCarrello(${item.id_prodotto})">
                            X Rimuovi
                        </button>
                    </div>
                </div>
            `;
        });

        document.getElementById('totale-articoli').innerText = dati.totale_articoli;
        document.getElementById('totale-euro').innerText = dati.totale_euro;
        document.getElementById('btn-checkout').disabled = false;

    } catch (errore) {
        document.getElementById('lista-carrello').innerHTML = `
            <div class="alert alert-danger bg-dark text-danger border-danger text-center">
                <strong>ERRORE!</strong> Impossibile caricare il carrello. Il server PHP ha restituito un errore.
            </div>`;
    }
}

async function rimuoviDalCarrello(idProdotto) {
    if(!confirm("Vuoi davvero rimuovere questo strumento dal carrello?")) return;
    try {
        const risposta = await fetch('../api/carrello.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_prodotto: idProdotto })
        });
        if (risposta.ok) {
            caricaDatiCarrello();
            aggiornaContatoreCarrello();
        } else {
            alert("Errore durante la rimozione.");
        }
    } catch (errore) { console.error(errore); }
}

async function eseguiCheckout() {
    const btn = document.getElementById('btn-checkout');
    btn.disabled = true;
    btn.innerHTML = 'ELABORAZIONE...';

    try {
        const risposta = await fetch('../api/checkout.php', { method: 'POST' });
        const risultato = await risposta.json();

        if (risposta.ok) {
            alert("🎉 " + risultato.messaggio + " (Ordine #" + risultato.id_ordine + ")");
            window.location.href = 'index.html';
        } else {
            alert("Errore: " + risultato.errore);
            btn.disabled = false;
            btn.innerHTML = 'PROCEDI AL PAGAMENTO';
        }
    } catch (errore) {
        alert("Errore di connessione.");
        btn.disabled = false;
        btn.innerHTML = 'PROCEDI AL PAGAMENTO';
    }
}

// ==========================================
// 4. FUNZIONI ORDINI (ordini.html)
// ==========================================

async function caricaOrdini() {
    try {
        const risp = await fetch('../api/ordini.php');
        const ordini = await risp.json();
        const contenitore = document.getElementById('lista-ordini');

        if (ordini.length === 0) {
            contenitore.innerHTML = '<div class="alert alert-info bg-dark border-secondary text-white">Non hai ancora effettuato ordini. Che aspetti a comprare una chitarra?</div>';
            return;
        }

        contenitore.innerHTML = '';
        ordini.forEach(ordine => {
            let prodottiHTML = '';
            ordine.prodotti.forEach(p => {
                prodottiHTML += `
                    <div class="d-flex align-items-center mb-2">
                        <img src="${p.immagine}" width="40" class="me-2 rounded border border-dark">
                        <!-- FIX: Aggiunto text-white per i nomi e rosso per i prezzi -->
                        <span class="text-white">${p.nome} (x${p.quantita}) - <b style="color: #d90429;">€${p.prezzo}</b></span>
                    </div>`;
            });

            contenitore.innerHTML += `
                <!-- FIX: Sfondo scuro per tutta la card -->
                <div class="card mb-4 shadow-sm" style="background-color: #1c1c1c; border-color: #333;">
                    
                    <!-- FIX: Header della card grigio scuro, non più bianco! -->
                    <div class="card-header d-flex justify-content-between text-white" style="background-color: #222; border-bottom: 1px solid #333;">
                        <span class="fw-bold">Ordine #${ordine.id_ordine}</span>
                        <span style="color: #aaaaaa;">${new Date(ordine.data).toLocaleDateString('it-IT')}</span>
                    </div>
                    
                    <div class="card-body">
                        ${prodottiHTML}
                        <hr class="border-secondary">
                        <div class="text-end">
                            <!-- FIX: Totale in rosso acceso -->
                            <h5 class="fw-bold" style="color: #d90429;">Totale: €${ordine.totale}</h5>
                        </div>
                    </div>
                </div>`;
        });
    } catch (e) {
        document.getElementById('lista-ordini').innerHTML = '<div class="alert alert-danger bg-dark text-danger border-danger">Errore nel caricamento ordini.</div>';
    }
}


// ==========================================
// 5. INIZIALIZZATORE (Il cervello del sito)
// ==========================================
// Questa parte parte in automatico appena la pagina si carica.
// Guarda gli ID nell'HTML per capire in che pagina si trova l'utente.

document.addEventListener('DOMContentLoaded', () => {

    // 1. Pagine protette (se non sei loggato ti butto fuori)
    if (document.getElementById('lista-carrello') || document.getElementById('lista-ordini')) {
        controllaSessione(true).then(() => {
            aggiornaContatoreCarrello();
            if (document.getElementById('lista-carrello')) caricaDatiCarrello();
            if (document.getElementById('lista-ordini')) caricaOrdini();
        });
    } 
    // 2. Pagine pubbliche (puoi vederle anche senza login)
    else if (document.getElementById('catalogo') || document.getElementById('dettaglio-prodotto')) {
        controllaSessione(false).then(() => {
            aggiornaContatoreCarrello();
            if (document.getElementById('catalogo')) caricaCatalogo();
            if (document.getElementById('dettaglio-prodotto')) caricaDettaglio();
        });
    }

    // 3. Pagina di Login
    const formLogin = document.getElementById('formLogin');
    if (formLogin) {
        formLogin.addEventListener('submit', async function(event) {
            event.preventDefault();
            const dati = {
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            };
            const box = document.getElementById('messaggio-alert');
            
            try {
                const risposta = await fetch('../api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dati)
                });
                const risultato = await risposta.json();

                if (risposta.ok) {
                    box.innerHTML = `<div class="alert alert-success bg-dark border-success text-success">${risultato.messaggio}</div>`;
                    setTimeout(() => { window.location.href = 'index.html'; }, 1000);
                } else {
                    box.innerHTML = `<div class="alert alert-danger bg-dark border-danger text-danger">${risultato.errore}</div>`;
                }
            } catch (err) {
                box.innerHTML = `<div class="alert alert-danger">Errore di rete.</div>`;
            }
        });
    }

    // 4. Pagina di Registrazione
    const formReg = document.getElementById('formRegistrazione');
    if (formReg) {
        formReg.addEventListener('submit', async function(event) {
            event.preventDefault();
            const dati = {
                nome: document.getElementById('nome').value,
                cognome: document.getElementById('cognome').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            };
            const box = document.getElementById('messaggio-alert');
            
            try {
                const risposta = await fetch('../api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dati)
                });
                const risultato = await risposta.json();

                if (risposta.ok) {
                    box.innerHTML = `<div class="alert alert-success bg-dark border-success text-success">${risultato.messaggio} Ti stiamo portando al Login...</div>`;
                    setTimeout(() => { window.location.href = 'login.html'; }, 1500);
                } else {
                    box.innerHTML = `<div class="alert alert-danger bg-dark border-danger text-danger">${risultato.errore}</div>`;
                }
            } catch (err) {
                box.innerHTML = `<div class="alert alert-danger">Errore di rete.</div>`;
            }
        });
    }
});