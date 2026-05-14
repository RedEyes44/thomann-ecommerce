// ==========================================
// 1. FUNZIONI GLOBALI (Sessione, Navbar)
// ==========================================

async function controllaSessione(richiedeLogin = false) {
    try {
        const risposta = await fetch('../api/me.php');
        const utente = await risposta.json();
        const menu = document.getElementById('menu-utente');

        console.log(utente);

        if (utente.loggato) {
            // Se è admin, prepariamo il bottone speciale
            let adminButton = '';
            if (utente.ruolo == 'admin') {
                adminButton = `<a href="admin_ordini.html" class="btn btn-danger me-3 fw-bold">⚙️ ADMIN</a>`;
            }

            if (menu) {
                // INTEGRATO QUI IL BOTTONE ADMIN E CHIUSO CORRETTAMENTE IL TAG <b> e <h6>
                menu.innerHTML = `
                    ${adminButton}
                    <h6 class="navbar-text text-white me-3 text-decoration-none" style="cursor:pointer; transition: 0.2s;">
                        👤 Ciao, <b>${utente.nome}</b>
                    </h6>
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
// FUNZIONI CATALOGO E PRODOTTO 
// ==========================================

// Memoria globale: ricorda in che categoria siamo mentre ordiniamo o cerchiamo
let categoriaAttiva = null; 

async function caricaCatalogo(idCategoria = undefined, testoRicerca = null) {
    try {
        // 1. GESTIONE STATO CATEGORIA
        if (idCategoria !== undefined) {
            categoriaAttiva = idCategoria;
        }

        // 2. LETTURA INPUT E TENDINA
        const inputRicerca = document.getElementById('barra-ricerca');
        const selectOrdine = document.getElementById('filtro-ordine');
        
        const testoReale = testoRicerca !== null ? testoRicerca : (inputRicerca ? inputRicerca.value : '');
        const ordineReale = selectOrdine ? selectOrdine.value : 'nuovi';

        // 3. COSTRUZIONE URL DINAMICO
        let urlApi = '../api/prodotti.php?';
        if (categoriaAttiva !== null) urlApi += `categoria=${categoriaAttiva}&`;
        if (testoReale.trim() !== '') urlApi += `search=${encodeURIComponent(testoReale)}&`;
        urlApi += `sort=${ordineReale}`;

        // 4. CHIAMATA E RENDER
        const risposta = await fetch(urlApi);
        const prodotti = await risposta.json();
        const contenitore = document.getElementById('catalogo');
        
        if (prodotti.length === 0) {
            contenitore.innerHTML = '<div class="col-12 text-center text-light mt-5"><h4>🎸 Nessuno strumento trovato con questi filtri.</h4></div>';
            return;
        }

        contenitore.innerHTML = ''; 
        prodotti.forEach(prodotto => {
            contenitore.innerHTML += `
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm border-secondary" style="background-color: #1c1c1c;">
                        <a href="prodotto.html?id=${prodotto.id_prodotto}">
                            <img src="${prodotto.immagine_url}" class="card-img-top" style="background: radial-gradient(circle at 50% 100%, #6b0515 0%, #1c1c1c 55%, #0f0f0f 100%); border-bottom: 2px solid #d90429;" alt="${prodotto.nome}">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary mb-2 w-50">${prodotto.nome_categoria}</span>
                            <h5 class="card-title">
                                <a href="prodotto.html?id=${prodotto.id_prodotto}" class="text-decoration-none text-white text-uppercase fw-bold">
                                    ${prodotto.nome}
                                </a>
                            </h5>
                            <h4 class="mt-auto fw-bold" style="color: #d90429;">€ ${prodotto.prezzo}</h4>
                            <button class="btn btn-success mt-3 fw-bold" onclick="aggiungiAlCarrello(${prodotto.id_prodotto}, false)">
                                AGGIUNGI AL CARRELLO
                            </button>
                        </div>
                    </div>
                </div>`;
        });
    } catch (errore) {
        document.getElementById('catalogo').innerHTML = '<div class="alert alert-danger bg-dark text-danger border-danger">Impossibile caricare il catalogo.</div>';
    }
}

function eseguiRicerca() {
    caricaCatalogo(undefined);
}

// ==========================================
// GESTIONE BARRA DI RICERCA E AUTOCOMPLETE
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const inputRicerca = document.getElementById('barra-ricerca');
    const boxSuggerimenti = document.getElementById('suggerimenti-ricerca');

    if (inputRicerca && boxSuggerimenti) {
        inputRicerca.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                boxSuggerimenti.classList.add('d-none');
                eseguiRicerca();
            }
        });

        inputRicerca.addEventListener('input', async function () {
            const testo = this.value.trim();
            if (testo.length < 2) {
                boxSuggerimenti.classList.add('d-none');
                return;
            }
            try {
                const risposta = await fetch(`../api/prodotti.php?search=${encodeURIComponent(testo)}`);
                const prodotti = await risposta.json();
                boxSuggerimenti.innerHTML = '';
                if (prodotti.length > 0) {
                    prodotti.forEach(prodotto => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action d-flex align-items-center';
                        li.innerHTML = `
                            <img src="${prodotto.immagine_url}" style="width: 40px; height: 40px; object-fit: contain; margin-right: 15px; background-color: #fff; border-radius: 4px; padding: 2px;">
                            <span>${prodotto.nome}</span>
                        `;
                        li.addEventListener('click', () => {
                            inputRicerca.value = prodotto.nome;
                            boxSuggerimenti.classList.add('d-none');
                            eseguiRicerca();
                        });
                        boxSuggerimenti.appendChild(li);
                    });
                    boxSuggerimenti.classList.remove('d-none');
                } else {
                    boxSuggerimenti.innerHTML = '<li class="list-group-item text-muted">Nessuno strumento trovato...</li>';
                    boxSuggerimenti.classList.remove('d-none');
                }
            } catch (e) {
                console.error("Errore durante l'autocomplete", e);
            }
        });

        document.addEventListener('click', function(e) {
            if (!inputRicerca.contains(e.target) && !boxSuggerimenti.contains(e.target)) {
                boxSuggerimenti.classList.add('d-none');
            }
        });
    }
});

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

        try {
            const rispProf = await fetch('../api/profilo.php');
            const datiProf = await rispProf.json();
            if(datiProf.indirizzo_spedizione) {
                document.getElementById('indirizzo-spedizione').value = datiProf.indirizzo_spedizione;
            }
        } catch(e) {}

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

function eseguiCheckout(e) {
    if(e) e.preventDefault();
    const indirizzo = document.getElementById('indirizzo-spedizione').value.trim();
    if (!indirizzo) { alert("⚠️ Inserisci un indirizzo per procedere!"); return; }

    const metodo = document.getElementById('metodo-pagamento').value;
    if (metodo === 'postepay' || metodo === 'carta') {
        // Previene la creazione di modali infinite se clicchi più volte
        const modalEl = document.getElementById('modalPostepay');
        let modal = bootstrap.Modal.getInstance(modalEl);
        if (!modal) {
            modal = new bootstrap.Modal(modalEl);
        }
        modal.show();
    } else {
        processaOrdineBackend(indirizzo);
    }
}

function elaboraPagamentoFittizio() {
    document.getElementById('btn-paga-sandbox').classList.add('d-none');
    document.getElementById('pp-spinner').classList.remove('d-none');

    setTimeout(() => {
        const modalEl = document.getElementById('modalPostepay');
        
        // RIPRISTINO LA MODALE: Nascondo la rotellina e rimetto il tasto (risolve il loop di caricamento)
        document.getElementById('btn-paga-sandbox').classList.remove('d-none');
        document.getElementById('pp-spinner').classList.add('d-none');
        
        bootstrap.Modal.getInstance(modalEl).hide();
        processaOrdineBackend(document.getElementById('indirizzo-spedizione').value.trim());
    }, 2500);
}

async function processaOrdineBackend(indirizzo) {
    const btn = document.getElementById('btn-checkout');
    btn.disabled = true; btn.innerHTML = 'ELABORAZIONE...';
    try {
        // IL FIX DELL'INDIRIZZO: Ora invia { indirizzo: indirizzo } come vuole il tuo file PHP
        const risposta = await fetch('../api/checkout.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ indirizzo: indirizzo }) 
        });
        const risultato = await risposta.json();
        
        if (risposta.ok) { 
            alert("🎉 " + risultato.messaggio + " (Transazione Approvata)"); 
            window.location.href = 'ordini.html'; 
        } else { 
            alert("Errore: " + risultato.errore); 
            // Ripristino il bottone originale in caso di errore
            btn.disabled = false; 
            btn.innerHTML = 'PROCEDI AL PAGAMENTO'; 
        }
    } catch (errore) { 
        alert("Errore di rete."); 
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
        
        if (ordini.errore) throw new Error(ordini.errore);

        const contenitore = document.getElementById('lista-ordini');

        if (ordini.length === 0) {
            contenitore.innerHTML = '<div class="alert alert-info bg-dark border-secondary text-white">Non hai ancora effettuato ordini. Che aspetti a comprare una chitarra?</div>';
            return;
        }

        contenitore.innerHTML = '';
        ordini.forEach(ordine => {
            let prodottiHTML = '';
            if(ordine.prodotti) {
                ordine.prodotti.forEach(p => {
                    prodottiHTML += `
                        <div class="d-flex align-items-center mb-2">
                            <img src="${p.immagine}" width="40" class="me-2 rounded border border-dark">
                            <span class="text-white">${p.nome} (x${p.quantita}) - <b style="color: #d90429;">€${p.prezzo}</b></span>
                        </div>`;
                });
            }

            contenitore.innerHTML += `
                <div class="card mb-4 shadow-sm" style="background-color: #1c1c1c; border-color: #333;">
                    <div class="card-header d-flex justify-content-between text-white" style="background-color: #222; border-bottom: 1px solid #333;">
                        <span class="fw-bold">Ordine #${ordine.id_ordine} - Stato: <span class="text-warning">${ordine.stato.toUpperCase()}</span></span>
                        <span style="color: #aaaaaa;">${new Date(ordine.data).toLocaleDateString('it-IT')}</span>
                    </div>
                    <div class="card-body">
                        ${prodottiHTML}
                        <hr class="border-secondary">
                        <div class="text-end">
                            <h5 class="fw-bold" style="color: #d90429;">Totale: €${ordine.totale}</h5>
                        </div>
                    </div>
                </div>`;
        });
    } catch (e) {
        document.getElementById('lista-ordini').innerHTML = `<div class="alert alert-danger bg-dark text-danger border-danger">Errore fatale: ${e.message}</div>`;
    }
}

// ==========================================
// 5. FUNZIONI PANNELLO ADMIN
// ==========================================
async function caricaTuttiOrdini() {
    try {
        const risposta = await fetch('../api/admin_ordini.php');
        
        if (risposta.status === 401 || risposta.status === 403) {
            alert("⚠️ Area riservata agli Amministratori.");
            window.location.href = 'index.html';
            return;
        }

        const textResponse = await risposta.text();
        let ordini = [];
        try {
            ordini = JSON.parse(textResponse);
        } catch (parseError) {
            throw new Error("Il server non ha restituito JSON valido. Controlla il DB.");
        }

        if (ordini.errore) throw new Error(ordini.errore);

        const contenitore = document.getElementById('tabella-ordini');

        if (ordini.length === 0) {
            contenitore.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nessun ordine presente nel database.</td></tr>';
            return;
        }

        contenitore.innerHTML = '';
        ordini.forEach(ordine => {
            const dataFormattata = new Date(ordine.data_ordine).toLocaleString('it-IT');
            let coloreStato = 'bg-secondary';
            if(ordine.stato === 'confermato') coloreStato = 'bg-primary';
            if(ordine.stato === 'spedito') coloreStato = 'bg-warning text-dark';
            if(ordine.stato === 'consegnato') coloreStato = 'bg-success';

            contenitore.innerHTML += `
                <tr>
                    <td class="fw-bold">#${ordine.id_ordine}</td>
                    <td><small style="color: #aaaaaa;">${dataFormattata}</small></td>
                    <td>
                        <div class="fw-bold">${ordine.nome} ${ordine.cognome || ''}</div>
                        <small style="color: #aaaaaa;">${ordine.email}</small>
                    </td>
                    <td><small>${ordine.indirizzo_spedizione || 'Non specificato'}</small></td>
                    <td class="fw-bold" style="color: #d90429;">€${ordine.totale_euro}</td>
                    <td>
                        <select class="form-select form-select-sm bg-dark text-white border-secondary select-stato" id="stato-${ordine.id_ordine}">
                            <option value="in attesa" ${ordine.stato === 'in attesa' ? 'selected' : ''}>In attesa</option>
                            <option value="confermato" ${ordine.stato === 'confermato' ? 'selected' : ''}>Confermato</option>
                            <option value="spedito" ${ordine.stato === 'spedito' ? 'selected' : ''}>Spedito</option>
                            <option value="consegnato" ${ordine.stato === 'consegnato' ? 'selected' : ''}>Consegnato</option>
                        </select>
                        <span class="badge ${coloreStato} mt-1 w-100">Attuale: ${ordine.stato.toUpperCase()}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger w-100 fw-bold" onclick="aggiornaStato(${ordine.id_ordine})">AGGIORNA</button>
                    </td>
                </tr>
            `;
        });
    } catch (errore) {
        document.getElementById('tabella-ordini').innerHTML = `<tr><td colspan="7" class="text-center text-danger fw-bold">Errore di connessione: ${errore.message}</td></tr>`;
    }
}

async function aggiornaStato(idOrdine) {
    const nuovoStato = document.getElementById(`stato-${idOrdine}`).value;
    try {
        const risposta = await fetch('../api/admin_ordini.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_ordine: idOrdine, nuovo_stato: nuovoStato })
        });
        const risultato = await risposta.json();
        if (risposta.ok) {
            alert("✅ " + risultato.messaggio);
            caricaTuttiOrdini();
        } else {
            alert("Errore: " + risultato.errore);
        }
    } catch (e) {
        alert("Errore di rete durante l'aggiornamento.");
    }
}

async function caricaAdminProdotti() {
    try {
        const risposta = await fetch('../api/admin_prodotti.php');
        if (risposta.status === 401 || risposta.status === 403) {
            window.location.href = 'index.html'; return;
        }

        const prodotti = await risposta.json();
        const contenitore = document.getElementById('tabella-prodotti');
        contenitore.innerHTML = '';

        prodotti.forEach(p => {
            const giacenzaStyle = p.giacenza <= 0 ? 'color: #d90429; font-weight: bold;' : '';
            const jsonProdotto = JSON.stringify(p).replace(/'/g, "&apos;").replace(/"/g, "&quot;");

            contenitore.innerHTML += `
                <tr>
                    <td><img src="${p.immagine_url}" width="40" class="rounded bg-white p-1"></td>
                    <td class="fw-bold">${p.nome}</td>
                    <td><span class="badge bg-secondary">${p.nome_categoria}</span></td>
                    <td>€ ${p.prezzo}</td>
                    <td style="${giacenzaStyle}">${p.giacenza} pz.</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-info me-1" onclick="apriFormProdotto(${jsonProdotto})">✏️</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminaProdotto(${p.id_prodotto})">🗑️</button>
                    </td>
                </tr>
            `;
        });
    } catch (e) {
        document.getElementById('tabella-prodotti').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Errore di connessione al server.</td></tr>';
    }
}

function apriFormProdotto(prodotto = null) {
    document.getElementById('box-form-prodotto').classList.remove('d-none');
    const form = document.getElementById('formProdotto');
    
    if (prodotto) {
        document.getElementById('titolo-form').innerText = "Modifica Prodotto";
        document.getElementById('id_prodotto').value = prodotto.id_prodotto;
        document.getElementById('nome_prodotto').value = prodotto.nome;
        document.getElementById('categoria_prodotto').value = prodotto.categoria;
        document.getElementById('prezzo_prodotto').value = prodotto.prezzo;
        document.getElementById('giacenza_prodotto').value = prodotto.giacenza;
        document.getElementById('immagine_prodotto').value = prodotto.immagine_url;
        document.getElementById('descrizione_prodotto').value = prodotto.descrizione;
    } else {
        document.getElementById('titolo-form').innerText = "Aggiungi Nuovo Prodotto";
        form.reset();
        document.getElementById('id_prodotto').value = '';
    }
}

function chiudiFormProdotto() {
    document.getElementById('box-form-prodotto').classList.add('d-none');
}

async function eliminaProdotto(id) {
    if(!confirm("⚠️ ATTENZIONE: Vuoi davvero eliminare questo prodotto dal catalogo?")) return;
    
    try {
        const risposta = await fetch('../api/admin_prodotti.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_prodotto: id })
        });
        const risultato = await risposta.json();
        
        if (risposta.ok) {
            caricaAdminProdotti();
        } else {
            alert("❌ " + risultato.errore);
        }
    } catch (err) {
        alert("Errore durante l'eliminazione.");
    }
}

async function caricaAdminCategorie() {
    try {
        const risposta = await fetch('../api/admin_categorie.php');
        const categorie = await risposta.json();
        const contenitore = document.getElementById('tabella-categorie');
        if (!contenitore) return;
        contenitore.innerHTML = '';
        categorie.forEach(c => {
            contenitore.innerHTML += `<tr><td class="fw-bold">${c.id_categoria}</td><td>${c.nome}</td><td class="text-end"><button class="btn btn-sm btn-outline-danger" onclick="eliminaCategoria(${c.id_categoria})">🗑️ Elimina</button></td></tr>`;
        });
    } catch (e) {}
}

async function aggiungiCategoria(e) {
    e.preventDefault();
    try {
        const risposta = await fetch('../api/admin_categorie.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ nome: document.getElementById('nome_categoria').value }) });
        const res = await risposta.json();
        if(risposta.ok) { alert("✅ " + res.messaggio); document.getElementById('nome_categoria').value = ''; caricaAdminCategorie(); } 
        else alert("❌ " + res.errore);
    } catch(err) {}
}

async function eliminaCategoria(id) {
    if(!confirm("Sicuro di voler eliminare questa categoria?")) return;
    try {
        const risposta = await fetch('../api/admin_categorie.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_categoria: id }) });
        if(risposta.ok) caricaAdminCategorie(); else alert("❌ " + (await risposta.json()).errore);
    } catch(err) {}
}

async function caricaStatisticheAdmin() {
    try {
        // Aggiungi { cache: 'no-store' } alla fetch!
        const r = await fetch('../api/admin_stats.php', { cache: 'no-store' }); 
        const d = await r.json();

        if (d.errore) {
            console.error("Errore server:", d.errore);
            return;
        }

        // Usiamo Number() e un fallback a 0 per evitare NaN e undefined a video
        const ordini = d.totale_ordini || 0;
        const incasso = Number(d.incasso_totale) || 0;

        document.getElementById('stat-ordini').innerText = ordini;
        document.getElementById('stat-incasso').innerText = `€ ${incasso.toFixed(2)}`;
        
        // Gestione Allerta Scorte (il terzo quadratino)
        const boxScorte = document.getElementById('stat-scorte');
        if (boxScorte) {
            if (d.allerte_scorte && d.allerte_scorte.length > 0) {
                boxScorte.innerHTML = d.allerte_scorte.map(p => 
                    `<div class="text-danger">Low: ${p.nome} (${p.giacenza} pz)</div>`
                ).join('');
            } else {
                boxScorte.innerHTML = '<div class="text-success">Scorte OK ✅</div>';
            }
        }

        // Gestione Vendite Dettagliate
        const boxV = document.getElementById('stat-vendite');
        if (boxV) {
            if (d.vendite && d.vendite.length > 0) {
                boxV.innerHTML = d.vendite.map(v => `
                    <div class="d-flex justify-content-between border-bottom border-secondary py-2">
                        <span class="text-uppercase text-white">${v.stato}</span>
                        <span class="text-muted">${v.numero_ordini} ordini</span>
                        <strong class="text-success">€ ${Number(v.incasso).toFixed(2)}</strong>
                    </div>
                `).join('');
            } else {
                boxV.innerHTML = '<p class="text-muted">Nessuna vendita registrata.</p>';
            }
        }
    } catch (e) {
        console.error("Errore JS nel caricamento statistiche:", e);
    }
}

// ==========================================
// 7. INIZIALIZZATORE (Il cervello del sito)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {

    // Pagine protette carrello e ordini
    if (document.getElementById('lista-carrello') || document.getElementById('lista-ordini')) {
        controllaSessione(true).then(() => {
            aggiornaContatoreCarrello();
            if (document.getElementById('lista-carrello')) caricaDatiCarrello();
            if (document.getElementById('lista-ordini')) caricaOrdini();
        });
    } 
    // PAGINA ADMIN ORDINI
    else if (document.getElementById('tabella-ordini')) {
        controllaSessione(true).then(() => {
            caricaTuttiOrdini();
            caricaStatisticheAdmin(); // RICHIAMO CORRETTO DELLE STATISTICHE
        });
    }
    // PAGINA ADMIN CATEGORIE
    else if (document.getElementById('tabella-categorie')) {
        controllaSessione(true).then(() => {
            caricaAdminCategorie(); // RICHIAMO CORRETTO CATEGORIE
        });
    }
    // PAGINA ADMIN PRODOTTI
    else if (document.getElementById('tabella-prodotti')) {
        controllaSessione(true).then(() => {
            caricaAdminProdotti();
        });
    }
    // Pagine pubbliche
    else if (document.getElementById('catalogo') || document.getElementById('dettaglio-prodotto')) {
        controllaSessione(false).then(() => {
            aggiornaContatoreCarrello();
            if (document.getElementById('catalogo')) caricaCatalogo();
            if (document.getElementById('dettaglio-prodotto')) caricaDettaglio();
        });
    }

    // Auth Form: Login
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

    // Auth Form: Registrazione
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

    // Form Prodotto (Admin)
    const formProd = document.getElementById('formProdotto');
    if (formProd) {
        formProd.addEventListener('submit', async function(e) {
            e.preventDefault();
            const dati = {
                id_prodotto: document.getElementById('id_prodotto').value,
                nome: document.getElementById('nome_prodotto').value,
                categoria: document.getElementById('categoria_prodotto').value,
                prezzo: document.getElementById('prezzo_prodotto').value,
                giacenza: document.getElementById('giacenza_prodotto').value,
                immagine_url: document.getElementById('immagine_prodotto').value,
                descrizione: document.getElementById('descrizione_prodotto').value
            };
            try {
                const risposta = await fetch('../api/admin_prodotti.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dati)
                });
                const risultato = await risposta.json();
                if (risposta.ok) {
                    alert("✅ " + risultato.messaggio);
                    chiudiFormProdotto();
                    caricaAdminProdotti();
                } else {
                    alert("Errore: " + risultato.errore);
                }
            } catch (err) {
                alert("Errore di rete durante il salvataggio.");
            }
        });
    }
});