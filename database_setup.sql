-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Mag 04, 2026 alle 08:59
-- Versione del server: 10.4.21-MariaDB
-- Versione PHP: 8.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thomann`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `categorie`
--

CREATE TABLE `categorie` (
  `id_categoria` int(5) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descrizione` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `categorie`
--

INSERT INTO `categorie` (`id_categoria`, `nome`, `descrizione`) VALUES
(1, 'Chitarre Elettriche', 'Strumenti solid body per rock, blues e metal.'),
(2, 'Chitarre Acustiche', 'Chitarre folk con cassa armonica.'),
(3, 'Bassi Elettrici', 'Il cuore della sezione ritmica.'),
(4, 'Amplificatori', 'Senza un buon motore, anche la supercar più estrema non va da nessuna parte. Nel mondo delle sei e quattro corde, l\'amplificatore è il tuo V8. È l\'altra metà del tuo strumento, il muro di mattoni su cui si infrange il tuo suono.\r\n\r\nChe tu stia cercando il calore organico delle valvole per portare il tuo crunch al limite, o la precisione chirurgica di un mostro high-gain per abbattere i muri a colpi di riff, in questa sezione troverai solo potenza senza compromessi.\r\n\r\n⚡ Chitarra Elettrica: Dai combo compatti e letali perfetti per lo studio e l\'home recording, alle testate valvolari da 100W pronte a dominare i palchi più grandi. Suoni puliti cristallini, distorsioni devastanti e una dinamica che risponde a ogni singola pennata.\r\n\r\n💥 Basso Elettrico: Frequenze sismiche e definizione assoluta. Perché la sezione ritmica non si deve solo sentire: si deve percepire nello stomaco. Scopri testate ad altissimo wattaggio e cabinet progettati per spostare tonnellate d\'aria senza mai perdere l\'attacco.\r\n\r\nTrova il tuo suono. Attacca il jack. Alza il volume.');

-- --------------------------------------------------------

--
-- Struttura della tabella `dettagli_ordine`
--

CREATE TABLE `dettagli_ordine` (
  `id_dettaglio` int(11) NOT NULL,
  `id_ordine` int(11) NOT NULL,
  `id_prodotto` int(5) NOT NULL,
  `quantita` int(11) NOT NULL,
  `prezzo_acquisto` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `dettagli_ordine`
--

INSERT INTO `dettagli_ordine` (`id_dettaglio`, `id_ordine`, `id_prodotto`, `quantita`, `prezzo_acquisto`) VALUES
(1, 1, 3, 1, '3200.00'),
(2, 2, 2, 1, '2499.99'),
(3, 3, 2, 1, '2499.99'),
(4, 4, 3, 1, '3200.00'),
(5, 5, 6, 1, '345.00'),
(6, 6, 7, 1, '685.00'),
(7, 7, 1, 1, '750.00'),
(8, 8, 2, 1, '2499.99'),
(9, 9, 2, 3, '2499.99'),
(10, 10, 1, 2, '750.00'),
(11, 11, 1, 1, '750.00'),
(12, 12, 1, 7, '750.00'),
(13, 13, 1, 2, '750.00'),
(14, 14, 1, 11, '750.00'),
(15, 15, 1, 39, '750.00'),
(16, 16, 8, 1, '1299.00'),
(17, 17, 2, 1, '2499.99'),
(18, 18, 4, 1, '139.00'),
(19, 19, 1, 1, '750.00'),
(20, 20, 3, 2, '3200.00'),
(21, 21, 2, 1, '2499.99');

-- --------------------------------------------------------

--
-- Struttura della tabella `ordini`
--

CREATE TABLE `ordini` (
  `id_ordine` int(11) NOT NULL,
  `id_utente` int(5) NOT NULL,
  `indirizzo_spedizione` varchar(255) NOT NULL,
  `data_ordine` datetime DEFAULT current_timestamp(),
  `totale_euro` decimal(10,2) NOT NULL,
  `stato` varchar(50) NOT NULL DEFAULT 'in attesa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `ordini`
--

INSERT INTO `ordini` (`id_ordine`, `id_utente`, `indirizzo_spedizione`, `data_ordine`, `totale_euro`, `stato`) VALUES
(1, 1, '', '2026-04-20 08:55:50', '3200.00', 'in attesa'),
(2, 2, '', '2026-04-23 11:02:37', '2499.99', 'in attesa'),
(3, 2, '', '2026-04-23 11:14:49', '2499.99', 'in attesa'),
(4, 1, '', '2026-04-30 10:13:03', '3200.00', 'in attesa'),
(5, 1, '', '2026-04-30 10:22:07', '345.00', 'in attesa'),
(6, 1, '', '2026-04-30 10:49:14', '685.00', 'in attesa'),
(7, 1, '', '2026-04-30 10:49:56', '750.00', 'in attesa'),
(8, 1, '', '2026-04-30 10:53:20', '2499.99', 'in attesa'),
(9, 1, '', '2026-04-30 10:53:31', '7499.97', 'in attesa'),
(10, 1, '', '2026-04-30 10:55:54', '1500.00', 'in attesa'),
(11, 1, '', '2026-04-30 10:56:05', '750.00', 'in attesa'),
(12, 1, '', '2026-04-30 10:56:27', '5250.00', 'in attesa'),
(13, 1, '', '2026-04-30 10:56:37', '1500.00', 'in attesa'),
(14, 1, '', '2026-04-30 10:57:09', '8250.00', 'in attesa'),
(15, 1, '', '2026-04-30 10:57:22', '29250.00', 'in attesa'),
(16, 1, '', '2026-04-30 11:03:53', '1299.00', 'in attesa'),
(17, 1, '', '2026-04-30 11:20:12', '2499.99', 'in attesa'),
(18, 3, '', '2026-04-30 11:21:09', '139.00', 'in attesa'),
(19, 1, '', '2026-04-30 11:23:17', '750.00', 'in attesa'),
(20, 1, '', '2026-04-30 11:26:15', '6400.00', 'confermato'),
(21, 1, 'sdlkjsdsdefg', '2026-04-30 11:36:27', '2499.99', 'confermato');

-- --------------------------------------------------------

--
-- Struttura della tabella `prodotti`
--

CREATE TABLE `prodotti` (
  `id_prodotto` int(5) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `giacenza` int(11) NOT NULL,
  `immagine_url` varchar(255) NOT NULL,
  `categoria` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `prodotti`
--

INSERT INTO `prodotti` (`id_prodotto`, `nome`, `descrizione`, `prezzo`, `giacenza`, `immagine_url`, `categoria`) VALUES
(1, 'Fender Player Stratocaster', 'Chitarra iconica, 3 single coil.', '750.00', 2, 'img/stratocaster.png', 1),
(2, 'Gibson Les Paul Standard', 'Il suono del rock. 2 humbucker.', '2499.99', 1, 'img/lespaul.png', 1),
(3, 'Martin D-28', 'Acustica dreadnought di fascia alta.', '3200.00', 0, 'img/martin.png', 2),
(4, 'Yamaha C40II', 'Chitarra classica per principianti.', '139.00', 44, 'img/classica.png', 2),
(5, 'Fender Player Jazz Bass', 'Basso a 4 corde, due single-coil.', '850.00', 8, 'img/jazzbass.png', 3),
(6, 'Orange Crush Bass 100', 'Amplificatore combo per Basso\r\nPotenza: 100 W\r\nConfigurazione: 1 altoparlante da 15\"\r\nControlli: Volume, Treble, Middle, (Mid) Freq., Bass, Blend, Gain\r\nFX Loop\r\nAccordatore integrato\r\nPercorso analogico del segnale\r\nIngresso Aux\r\nCollegamento per le cuffie con simulazione di cabinet\r\nFunzione Blend attivabile tramite footswitch (art. 170368 - non incluso, acquistabile separatamente)\r\nDimensioni (L x P x A): 510 x 355 x 550 mm\r\nPeso: 24,25 kg', '518.00', 5, 'img/orange_crush_bass.png', 4),
(7, 'Marshall DSL40CR', 'Amplificatore Combo interamente valvolare per chitarra elettrica\r\nSerie Reissue - autentico tono DSL\r\nPotenza: 40 W\r\n2 canali commutabili tramite footswitch con Classic Gain e Ultra Gain\r\nConfigurazione: altoparlante Celestion V Type da 12\"\r\nValvole di preamplificazione: 4 x ECC83\r\nValvole di potenza: 2 x EL34\r\nControllo indipendente del Gain e del volume per entrambi i canali\r\nModalità Clean e Crunch sul canale Classic Gain\r\nModalità Lead1 e Lead2 sul canale Ultra Gain\r\nControllo del tono classico con bassi, medi e alti\r\nControllo Presence\r\nInterruttore Tone Shift\r\nControllo variabile della risonanza\r\nInterruttore pentodo/triodo\r\nRiverbero digitale indipendente per ogni canale\r\nCollegamento cuffie possibile in modalità standby\r\nUscite altoparlanti: 2 x 16 Ohm, 1 x 8 Ohm e 1 x 16 Ohm\r\nDimensioni: 620 x 490 x 252 mm\r\nInclude doppio footswitch (selezione dei canali e riverbero on/off)', '685.00', 2, 'img/marshall_DSL40CR.png', 4),
(8, 'Ibanez BTB805MS-TGF', 'Basso elettrico multiscala a 5 corde\r\nSerie Bass Workshop\r\nCorpo: frassino/okumè\r\nTop: pioppo\r\nManico passante in 5 pezzi: acero/noce\r\nTastieta: panga panga\r\nDot inlays: abalone\r\nProfilo del manico: BTB5\r\nLunghezza della scala (multiscala): 889 - 940 mm (35\" - 37\")\r\nRaggio della tastiera: 400 mm (15,75\")\r\nLarghezza del capotasto: 47 mm (1,85\")\r\nCapotasto: plastica\r\n24 tasti: Medium in acciaio inossidabile\r\nPick-up: 2 humbucker T1\r\nElettronica Custom Ibanez con equalizzatore attivo a 3 bande\r\nControlli: Volume, Balance, Bass, Mid, Treble\r\nSelettore a 3 vie per le frequenze medie\r\nInterruttore per il bypass dell\'equalizzatore\r\nPonte: MR5S\r\nHardware: nero\r\nMeccaniche: Ibanez\r\nCorde di fabbrica: .045 - .130\r\nColore: Transparent Gray Flat\r\nInclude custodia rigida', '1299.00', 1, 'img/ibanez_btb.png', 3);

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id_utente` int(5) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `data_registrazione` date DEFAULT NULL,
  `ruolo` varchar(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`id_utente`, `nome`, `cognome`, `email`, `password`, `data_registrazione`, `ruolo`) VALUES
(1, 'Riccardo', 'Tonetto', 'riccardotonetto06@gmail.com', '$2y$10$R5zihRx8Wulc19mMMWIam.j/CHvPcQ8SC5k2l4DiXvzkWhbEiIr/C', '2026-04-20', 'admin'),
(2, 'Matteo', 'Uvaldi', 'ciccio@gmail.com', '$2y$10$PTidxcunRoWYX0uxKCaH6.hFFdebj3zpJN6REttMVtjtrRl3Vdldu', '2026-04-23', 'user'),
(3, 'Pippo', 'Baudo', 'milos@gmail.com', '$2y$10$zQEBD8bMCDSC3JND5MYgwe8TRw232z3ywSIiNz5VvjQl0LH0.NV4u', '2026-04-30', 'user');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indici per le tabelle `dettagli_ordine`
--
ALTER TABLE `dettagli_ordine`
  ADD PRIMARY KEY (`id_dettaglio`),
  ADD KEY `fk_dettagli_ordine` (`id_ordine`),
  ADD KEY `fk_dettagli_prodotto` (`id_prodotto`);

--
-- Indici per le tabelle `ordini`
--
ALTER TABLE `ordini`
  ADD PRIMARY KEY (`id_ordine`),
  ADD KEY `fk_ordini_utenti` (`id_utente`);

--
-- Indici per le tabelle `prodotti`
--
ALTER TABLE `prodotti`
  ADD PRIMARY KEY (`id_prodotto`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD KEY `fk_prodotti_categorie` (`categoria`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id_utente`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_categoria` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `dettagli_ordine`
--
ALTER TABLE `dettagli_ordine`
  MODIFY `id_dettaglio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT per la tabella `ordini`
--
ALTER TABLE `ordini`
  MODIFY `id_ordine` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT per la tabella `prodotti`
--
ALTER TABLE `prodotti`
  MODIFY `id_prodotto` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id_utente` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `dettagli_ordine`
--
ALTER TABLE `dettagli_ordine`
  ADD CONSTRAINT `fk_dettagli_ordine` FOREIGN KEY (`id_ordine`) REFERENCES `ordini` (`id_ordine`),
  ADD CONSTRAINT `fk_dettagli_prodotto` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotti` (`id_prodotto`);

--
-- Limiti per la tabella `ordini`
--
ALTER TABLE `ordini`
  ADD CONSTRAINT `fk_ordini_utenti` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id_utente`);

--
-- Limiti per la tabella `prodotti`
--
ALTER TABLE `prodotti`
  ADD CONSTRAINT `fk_prodotti_categorie` FOREIGN KEY (`categoria`) REFERENCES `categorie` (`id_categoria`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
