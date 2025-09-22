-- =========================================
-- data.sql — Cinéphoria (PostgreSQL)
-- =========================================

--  Utilisateurs 
INSERT INTO "user" (id, email, roles, password, name, forname, username, create_at)
VALUES
  (1, 'alice@example.com', '["ROLE_USER"]', 'hashedpwd1', 'Dupont', 'Alice', 'alice', NOW()),
  (2, 'bob@example.com',   '["ROLE_ADMIN"]', 'hashedpwd2', 'Martin', 'Bob', 'bob', NOW());

-- ===== Cinémas =====
INSERT INTO cinema (id, nom, ville, pays, adresse, code_postal)
VALUES
  (1, 'Cinéma Lumière', 'Paris', 'France', '10 rue du Test', '75001');

-- ===== Qualités =====
INSERT INTO qualite (id, label, prix)
VALUES
  (1, 'Standard 2D', 9.00),
  (2, 'IMAX 3D', 15.00);

-- ===== Genres =====
INSERT INTO genre (id, nom)
VALUES
  (1, 'Action'),
  (2, 'Comédie');

-- ===== Salles =====
INSERT INTO salle (id, nom, nombre_places, created_at, cinema_id, qualite_id)
VALUES
  (1, 'Salle 1', 100, NOW(), 1, 1),
  (2, 'Salle 2', 80,  NOW(), 1, 2);

-- ===== Films =====
INSERT INTO film (id, titre, synopsis, age_minimum, affiche, coup_de_coeur, note_moyenne, date_publication, genre_id)
VALUES
  (1, 'Film Action',  'Un film plein d’action.', 12,  'affiche1.jpg', TRUE,  4.5, NOW(), 1),
  (2, 'Film Comédie', 'Une comédie hilarante.',  NULL,'affiche2.jpg', FALSE, 3.8, NOW(), 2);

-- ===== Séances =====
INSERT INTO seance (id, date, heure_debut, heure_fin, places_disponible, created_at, film_id, salle_id, cinema_id)
VALUES
  (1, CURRENT_DATE, '18:00', '20:00', 100, NOW(), 1, 1, 1),
  (2, CURRENT_DATE, '20:30', '22:30',  80, NOW(), 2, 2, 1);

-- ===== Sièges =====
-- 20 sièges pour la séance 1 : A1..A20 (tous libres)
INSERT INTO siege (seance_id, numero, code, is_pmr, is_reserved)
SELECT 1, gs, 'A' || gs, FALSE, FALSE
FROM generate_series(1, 20) AS gs;

-- 20 sièges pour la séance 2 : B1..B20 (tous libres)
INSERT INTO siege (seance_id, numero, code, is_pmr, is_reserved)
SELECT 2, gs, 'B' || gs, FALSE, FALSE
FROM generate_series(1, 20) AS gs;

-- ===== Démo de réservation existante (séance 1) =====
-- 1) Crée une réservation de 3 places pour l'utilisateur 1 sur la séance 1
WITH ins_resa AS (
  INSERT INTO reservation (user_id, seance_id, nombre_places, created_at, prix_total)
  VALUES (1, 1, 3, NOW(), 27.00)               -- 3 places x 9.00 = 27.00
  RETURNING id
),
-- 2) Prend les 3 premiers sièges libres de la séance 1
libres AS (
  SELECT id
  FROM siege
  WHERE seance_id = 1 AND is_reserved = FALSE AND is_pmr = FALSE
  ORDER BY numero
  LIMIT 3
),
-- 3) Lie ces sièges à la réservation créée
link AS (
  INSERT INTO reservation_siege (reservation_id, siege_id)
  SELECT (SELECT id FROM ins_resa), l.id
  FROM libres l
  RETURNING siege_id
)
-- 4) Marque les sièges comme réservés
UPDATE siege s
SET is_reserved = TRUE
WHERE s.id IN (SELECT siege_id FROM link);

-- ===== Avis =====
INSERT INTO avis (id, user_id, film_id, note_sur5, commentaire, valide, created_at)
VALUES (1, 1, 1, 4, 'Très bon film d’action !', TRUE, NOW());

-- ===== Incident =====
INSERT INTO incident (id, salle_id, titre, description, date_signalement, resolu, created_at)
VALUES (1, 1, 'Bruit projecteur', 'Un bruit intermittent lors de la projection.', NOW(), FALSE, NOW());

-- ===== Contact =====
INSERT INTO contact (id, nom_utilisateur, titre, description, date_envoi)
VALUES (1, 'alice', 'Question sur les tarifs', 'Bonjour, y a-t-il des réductions étudiant ?', NOW());
