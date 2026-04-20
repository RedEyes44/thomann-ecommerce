CREATE DATABASE thomann;

use thomann;

CREATE TABLE `categorie` (
  `id_categoria` int(5) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `descrizione` text DEFAULT NULL,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `utenti` (
  `id_utente` int(5) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `data_registrazione` date DEFAULT NULL,
  PRIMARY KEY (`id_utente`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `prodotti` (
  `id_prodotto` int(5) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `giacenza` int(11) NOT NULL,
  `immagine_url` varchar(255) NOT NULL,
  `categoria` int(5) NOT NULL,
  PRIMARY KEY (`id_prodotto`),
  UNIQUE KEY `nome` (`nome`),
  CONSTRAINT `fk_prodotti_categorie` FOREIGN KEY (`categoria`) REFERENCES `categorie`(`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dati finti
INSERT INTO `categorie` (`nome`, `descrizione`) VALUES
('Chitarre Elettriche', 'Strumenti solid body per rock, blues e metal.'),
('Chitarre Acustiche', 'Chitarre folk con cassa armonica.'),
('Bassi Elettrici', 'Il cuore della sezione ritmica.');

INSERT INTO `prodotti` (`nome`, `descrizione`, `prezzo`, `giacenza`, `immagine_url`, `categoria`) VALUES
('Fender Player Stratocaster', 'Chitarra iconica, 3 single coil.', 750.00, 12, 'img/stratocaster.jpg', 1),
('Gibson Les Paul Standard', 'Il suono del rock. 2 humbucker.', 2499.99, 3, 'img/lespaul.jpg', 1),
('Martin D-28', 'Acustica dreadnought di fascia alta.', 3200.00, 2, 'img/martin.jpg', 2),
('Yamaha C40II', 'Chitarra classica per principianti.', 139.00, 45, 'img/classica.jpg', 2),
('Fender Player Jazz Bass', 'Basso a 4 corde, due single-coil.', 850.00, 8, 'img/jazzbass.jpg', 3);

