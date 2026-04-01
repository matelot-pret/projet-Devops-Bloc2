CREATE TABLE Pays(nom VARCHAR, population INTEGER, langue VARCHAR, id SERIAL PRIMARY KEY);
CREATE TABLE Ville(id_pays INTEGER REFERENCES Pays(id), id SERIAL PRIMARY KEY, nom VARCHAR);

-- Pays
INSERT INTO Pays (nom, population, langue) VALUES ('Roumanie', 19320442, 'Roumain');
INSERT INTO Pays (nom, population, langue) VALUES ('France', 68042591, 'Français');
INSERT INTO Pays (nom, population, langue) VALUES ('Belgique', 11632326, 'Français');
INSERT INTO Pays (nom, population, langue) VALUES ('Espagne', 47415750, 'Espagnol');
INSERT INTO Pays (nom, population, langue) VALUES ('Italie', 59554023, 'Italien');
INSERT INTO Pays (nom, population, langue) VALUES ('Allemagne', 83794000, 'Allemand');
INSERT INTO Pays (nom, population, langue) VALUES ('Portugal', 10347892, 'Portugais');
INSERT INTO Pays (nom, population, langue) VALUES ('Pays-Bas', 17890000, 'Néerlandais');

-- Villes Roumanie (id 1)
INSERT INTO Ville (id_pays, nom) VALUES (1, 'Tulcea');
INSERT INTO Ville (id_pays, nom) VALUES (1, 'Bucarest');
INSERT INTO Ville (id_pays, nom) VALUES (1, 'Cluj-Napoca');

-- Villes France (id 2)
INSERT INTO Ville (id_pays, nom) VALUES (2, 'Paris');
INSERT INTO Ville (id_pays, nom) VALUES (2, 'Lyon');
INSERT INTO Ville (id_pays, nom) VALUES (2, 'Marseille');

-- Villes Belgique (id 3)
INSERT INTO Ville (id_pays, nom) VALUES (3, 'Bruxelles');
INSERT INTO Ville (id_pays, nom) VALUES (3, 'Liège');
INSERT INTO Ville (id_pays, nom) VALUES (3, 'Gand');

-- Villes Espagne (id 4)
INSERT INTO Ville (id_pays, nom) VALUES (4, 'Madrid');
INSERT INTO Ville (id_pays, nom) VALUES (4, 'Barcelone');
INSERT INTO Ville (id_pays, nom) VALUES (4, 'Séville');

-- Villes Italie (id 5)
INSERT INTO Ville (id_pays, nom) VALUES (5, 'Rome');
INSERT INTO Ville (id_pays, nom) VALUES (5, 'Milan');
INSERT INTO Ville (id_pays, nom) VALUES (5, 'Naples');

-- Villes Allemagne (id 6)
INSERT INTO Ville (id_pays, nom) VALUES (6, 'Berlin');
INSERT INTO Ville (id_pays, nom) VALUES (6, 'Munich');
INSERT INTO Ville (id_pays, nom) VALUES (6, 'Hambourg');

-- Villes Portugal (id 7)
INSERT INTO Ville (id_pays, nom) VALUES (7, 'Lisbonne');
INSERT INTO Ville (id_pays, nom) VALUES (7, 'Porto');
INSERT INTO Ville (id_pays, nom) VALUES (7, 'Braga');

-- Villes Pays-Bas (id 8)
INSERT INTO Ville (id_pays, nom) VALUES (8, 'Amsterdam');
INSERT INTO Ville (id_pays, nom) VALUES (8, 'Rotterdam');
INSERT INTO Ville (id_pays, nom) VALUES (8, 'La Haye');