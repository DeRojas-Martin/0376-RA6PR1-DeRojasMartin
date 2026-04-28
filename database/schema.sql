USE control_horari;

CREATE TABLE departaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    descripcio TEXT
);

CREATE TABLE horaris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    hora_entrada TIME NOT NULL,
    hora_sortida TIME NOT NULL,
    hores_minimes INT NOT NULL,
    marge_retard INT NOT NULL DEFAULT 10
);

CREATE TABLE usuaris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    cognoms VARCHAR(150),
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('empleat','admin','rrhh','comptabilitat','direccio') NOT NULL DEFAULT 'empleat',
    departament_id INT,
    horari_id INT,
    actiu TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departament_id) REFERENCES departaments(id),
    FOREIGN KEY (horari_id) REFERENCES horaris(id)
);

CREATE TABLE fitxatges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuari_id INT NOT NULL,
    data DATE NOT NULL,
    hora_entrada TIME,
    hora_sortida TIME,
    total_minuts INT DEFAULT 0,
    estat VARCHAR(30) DEFAULT 'obert',
    retard_minuts INT DEFAULT 0,
    sortida_anticipada_minuts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fitxatge_dia (usuari_id, data),
    FOREIGN KEY (usuari_id) REFERENCES usuaris(id)
);

CREATE TABLE projectes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    client VARCHAR(150),
    descripcio TEXT,
    hores_estimades INT DEFAULT 0,
    estat ENUM('actiu','pausat','finalitzat') DEFAULT 'actiu',
    data_inici DATE,
    data_fi DATE
);

CREATE TABLE tasques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuari_id INT NOT NULL,
    projecte_id INT NOT NULL,
    data DATE NOT NULL,
    hora_inici TIME NOT NULL,
    hora_fi TIME NOT NULL,
    total_minuts INT NOT NULL,
    descripcio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuari_id) REFERENCES usuaris(id),
    FOREIGN KEY (projecte_id) REFERENCES projectes(id)
);

CREATE TABLE incidencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuari_id INT NOT NULL,
    fitxatge_id INT,
    tipus VARCHAR(100) NOT NULL,
    descripcio TEXT,
    data DATE NOT NULL,
    estat ENUM('pendent','revisada','resolta') DEFAULT 'pendent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuari_id) REFERENCES usuaris(id),
    FOREIGN KEY (fitxatge_id) REFERENCES fitxatges(id)
);
