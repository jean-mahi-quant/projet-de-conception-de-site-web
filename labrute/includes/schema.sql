CREATE TABLE joueur (
  id INT AUTO_INCREMENT PRIMARY KEY,
  login VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  mdp_hash VARCHAR(255) NOT NULL,
  role ENUM('joueur','admin') NOT NULL DEFAULT 'joueur',
  actif TINYINT(1) NOT NULL DEFAULT 1
);
CREATE TABLE brute (
  id INT AUTO_INCREMENT PRIMARY KEY,
  joueur_id INT NOT NULL UNIQUE,
  nom VARCHAR(50) NOT NULL,
  description VARCHAR(255),
  avatar VARCHAR(100),
  pv INT NOT NULL DEFAULT 30,
  force_brute INT NOT NULL,
  defense INT NOT NULL,
  FOREIGN KEY (joueur_id) REFERENCES joueur(id)
);
CREATE TABLE defi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(100) NOT NULL,
  description TEXT,
  brute_id INT NOT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  etat ENUM('ouvert','termine') NOT NULL DEFAULT 'ouvert',
  FOREIGN KEY (brute_id) REFERENCES brute(id)
);
