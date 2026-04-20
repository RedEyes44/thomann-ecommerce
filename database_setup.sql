-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Apr 20, 2026 alle 08:58
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
(3, 'Bassi Elettrici', 'Il cuore della sezione ritmica.');

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
(1, 1, 3, 1, '3200.00');

-- --------------------------------------------------------

--
-- Struttura della tabella `ordini`
--

CREATE TABLE `ordini` (
  `id_ordine` int(11) NOT NULL,
  `id_utente` int(5) NOT NULL,
  `data_ordine` datetime DEFAULT current_timestamp(),
  `totale_euro` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `ordini`
--

INSERT INTO `ordini` (`id_ordine`, `id_utente`, `data_ordine`, `totale_euro`) VALUES
(1, 1, '2026-04-20 08:55:50', '3200.00');

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
(1, 'Fender Player Stratocaster', 'Chitarra iconica, 3 single coil.', '750.00', 12, 'img/stratocaster.jpg', 1),
(2, 'Gibson Les Paul Standard', 'Il suono del rock. 2 humbucker.', '2499.99', 3, 'img/lespaul.jpg', 1),
(3, 'Martin D-28', 'Acustica dreadnought di fascia alta.', '3200.00', 2, 'img/martin.jpg', 2),
(4, 'Yamaha C40II', 'Chitarra classica per principianti.', '139.00', 45, 'img/classica.jpg', 2),
(5, 'Fender Player Jazz Bass', 'Basso a 4 corde, due single-coil.', '850.00', 8, 'img/jazzbass.jpg', 3);

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
  `data_registrazione` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`id_utente`, `nome`, `cognome`, `email`, `password`, `data_registrazione`) VALUES
(1, 'Riccardo', 'Tonetto', 'riccardotonetto06@gmail.com', '$2y$10$R5zihRx8Wulc19mMMWIam.j/CHvPcQ8SC5k2l4DiXvzkWhbEiIr/C', '2026-04-20');

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
  MODIFY `id_categoria` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `dettagli_ordine`
--
ALTER TABLE `dettagli_ordine`
  MODIFY `id_dettaglio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `ordini`
--
ALTER TABLE `ordini`
  MODIFY `id_ordine` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `prodotti`
--
ALTER TABLE `prodotti`
  MODIFY `id_prodotto` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id_utente` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
