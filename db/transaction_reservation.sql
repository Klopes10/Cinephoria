-- transaction_reservation.sql (version qui marche avec data.sql)
BEGIN;

-- Verrouille la séance #2
SELECT id FROM seance WHERE id = 2 FOR UPDATE;

WITH libres AS (
  SELECT id
  FROM siege
  WHERE seance_id = 2
    AND is_reserved = FALSE
    AND is_pmr = FALSE
  ORDER BY numero
  LIMIT 3
),
check_count AS (
  SELECT COUNT(*) AS c FROM libres
),
ins_resa AS (
  INSERT INTO reservation (user_id, seance_id, nombre_places, prix_total, created_at)
  SELECT 1, 2, 3, 27.00, NOW()
  FROM check_count
  WHERE c = 3
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
