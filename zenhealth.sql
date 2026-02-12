CREATE TABLE cabine (
    numcab INT,
    nbplace INT,
    PRIMARY KEY(numcab)
);

CREATE TABLE service (
    numserv INT,
    libelle VARCHAR(40),
    prixunit DECIMAL(6,2),
    nbrinterventions INT,
    PRIMARY KEY (numserv)
);

CREATE TABLE hotesse (
    numhot INT,
    email VARCHAR(255),
    passwd VARCHAR(255),
    nomserv VARCHAR(25),
    grade VARCHAR(20),
    PRIMARY KEY(numhot),
    CONSTRAINT ck_grade CHECK (grade IN ('hotesse', 'gestionnaire'))
);

CREATE INDEX idx_hotesse_grade ON hotesse(grade);

CREATE TABLE reservation (
    numres INT,
    numcab INT,
    datres DATETIME,
    nbpers INT,
    datpaie DATETIME,
    modpaie VARCHAR(15),
    montcom DECIMAL(8,2),
    PRIMARY KEY (numres),
    FOREIGN KEY (numcab) REFERENCES cabine(numcab)
);

CREATE TABLE commande (
    numres INT,
    numserv INT,
    nbrinterevntions INT,
    PRIMARY KEY(numres, numserv),
    FOREIGN KEY (numres) REFERENCES reservation(numres),
    FOREIGN KEY (numserv) REFERENCES service(numserv)
);

CREATE TABLE affecter (
    numcab INT,
    dataff DATE,
    numhot INT,
    PRIMARY KEY(numcab, dataff),
    FOREIGN KEY (numcab) REFERENCES cabine(numcab),
    FOREIGN KEY (numhot) REFERENCES hotesse(numhot)
);

INSERT INTO cabine VALUES(10,4),(11,6),(12,8),(13,4),(14,6),(15,4),(16,4),(17,6),(18,2),(19,4);

INSERT INTO service VALUES
(1,'soins visage',90,25),(2,'epilation',90,25),(3,'soins mains',90,35),
(4,'soins pieds',90,62),(5,'massage classique',90,15),(6,'soins amincissants',90,21),
(7,'soins fessiers',90,25),(8,'soins jambes',90,30),(9,'soins sourcils',90,58),
(10,'manicure',90,42),(11,'massage asiatique',90,68),(12,'massage orientale',90,56),
(13,'maquillage',90,15),(14,'sauna',90,18),(15,'soins pour cheveux',90,70),(16,'massage pour veterans',90,61);

INSERT INTO hotesse VALUES
(1,'user1@mail.com','$#;§èm$$$$$0','Tutus Peter','gestionnaire'),
(2,'user2@mail.com','$xy#;§èm$$$$$1','Lilo Vito','hotesse'),
(3,'user3@mail.com','$ab#;§èm$$$$$2','Don Carl','hotesse'),
(4,'user4@mail.com','$cd#;§èm$$$$$3','Leo Jon','hotesse'),
(5,'user5@mail.com','$mm#;§èm$$$$$4','Dean Geak','gestionnaire');

INSERT INTO reservation VALUES
(100,10,STR_TO_DATE('10/09/2021 19:00','%d/%m/%Y %H:%i'),2,STR_TO_DATE('10/09/2021 20:50','%d/%m/%Y %H:%i'),'Carte',null),
(101,11,STR_TO_DATE('10/09/2021 20:00','%d/%m/%Y %H:%i'),4,STR_TO_DATE('10/09/2021 21:20','%d/%m/%Y %H:%i'),'Chèque',null),
(102,17,STR_TO_DATE('10/09/2021 18:00','%d/%m/%Y %H:%i'),2,STR_TO_DATE('10/09/2021 20:55','%d/%m/%Y %H:%i'),'Carte',null),
(103,12,STR_TO_DATE('10/09/2021 19:00','%d/%m/%Y %H:%i'),2,STR_TO_DATE('10/09/2021 21:10','%d/%m/%Y %H:%i'),'Espèces',null),
(104,18,STR_TO_DATE('10/09/2021 19:00','%d/%m/%Y %H:%i'),1,STR_TO_DATE('10/09/2021 21:00','%d/%m/%Y %H:%i'),'Chèque',null),
(105,10,STR_TO_DATE('10/09/2021 19:00','%d/%m/%Y %H:%i'),2,STR_TO_DATE('10/09/2021 20:45','%d/%m/%Y %H:%i'),'Carte',null),
(106,14,STR_TO_DATE('11/10/2021 19:00','%d/%m/%Y %H:%i'),2,STR_TO_DATE('11/10/2021 22:45','%d/%m/%Y %H:%i'),'Carte',null);

INSERT INTO commande VALUES
(100,4,2),(100,5,2),(100,13,1),(100,3,1),(101,7,2),(101,16,2),(101,12,2),(101,15,2),(101,2,2),(101,3,2),
(102,1,2),(102,10,2),(102,14,2),(102,2,1),(102,3,1),(103,9,2),(103,14,2),(103,2,1),(103,3,1),(104,7,1),
(104,11,1),(104,14,1),(104,3,1),(105,3,2),(106,3,2);

INSERT INTO affecter VALUES
(10,STR_TO_DATE('10/09/2021','%d/%m/%Y'),1),
(11,STR_TO_DATE('10/09/2021','%d/%m/%Y'),1),
(12,STR_TO_DATE('10/09/2021','%d/%m/%Y'),1),
(17,STR_TO_DATE('10/09/2021','%d/%m/%Y'),2),
(18,STR_TO_DATE('10/09/2021','%d/%m/%Y'),2),
(15,STR_TO_DATE('10/09/2021','%d/%m/%Y'),3),
(16,STR_TO_DATE('10/09/2021','%d/%m/%Y'),3),
(10,STR_TO_DATE('11/09/2021','%d/%m/%Y'),1);