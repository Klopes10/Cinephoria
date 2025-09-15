-- transaction_reservation.sql
-- Exemple : réserver 3 sièges pour la séance 42 par l'utilisateur 7 (hors PMR)

BEGIN;

-- 1) Verrouiller la séance pour éviter la concurrence
SELECT id FROM seance WHERE id = 5 FOR UPDATE;

-- 2) Pipeline complet en CTE : on prend 3 sièges libres, on insère la réservation,
--    on lie les sièges, on marque comme réservés.
WITH libres AS (
  SELECT id
  FROM siege
  WHERE seance_id = 5
    AND is_reserved = FALSE
    AND is_pmr = FALSE
  LIMIT 3
),
check_count AS (
  SELECT COUNT(*) AS c FROM libres
),
ins_resa AS (
  -- IMPORTANT : on s’assure qu’on a bien 3 sièges
  INSERT INTO reservation (user_id, seance_id, nombre_places, prix_total, created_at)
  SELECT 1, 5, 3, 27.00, NOW()
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

-- Si la réservation n’a pas été créée (pas assez de sièges), rien n’est mis à jour
-- -> on peut vérifier :
-- SELECT * FROM ins_resa; -- (aucun résultat si c<3)

COMMIT;
