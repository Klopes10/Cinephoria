-- =========================================
-- transaction_reservation.sql — réservation atomique
-- Compatible avec le schéma où seance.qualite_id existe
-- Variables psql à définir avant exécution :
--   \set seance_id 2
--   \set user_id   1
--   \set qty       3
-- =========================================

BEGIN;

-- Verrouille la ligne de séance
SELECT id FROM seance WHERE id = :seance_id FOR UPDATE;

WITH libres AS (
  SELECT id
  FROM siege
  WHERE seance_id = :seance_id
    AND is_reserved = FALSE
    AND is_pmr = FALSE
  ORDER BY numero
  LIMIT :qty
),
check_count AS (
  SELECT COUNT(*) AS c FROM libres
),
prix_unitaire AS (
  SELECT q.prix
  FROM qualite q
  JOIN seance s ON s.qualite_id = q.id
  WHERE s.id = :seance_id
),
ins_resa AS (
  INSERT INTO reservation (user_id, seance_id, nombre_places, created_at, prix_total)
  SELECT :user_id, :seance_id, :qty, NOW(), (SELECT prix FROM prix_unitaire) * :qty
  FROM check_count
  WHERE c = :qty
  RETURNING id
),
link AS (
  INSERT INTO reservation_siege (reservation_id, siege_id)
  SELECT r.id, l.id
  FROM ins_resa r
  CROSS JOIN libres l
  RETURNING siege_id
)
UPDATE siege s
SET is_reserved = TRUE
WHERE s.id IN (SELECT siege_id FROM link);

COMMIT;
