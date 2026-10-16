-- ---------------------------------------------------------------------------
-- Scratch Learn - UPGRADE (security step) for a database that already exists
-- (new installs just import scratch_learn_db.sql, which already contains this)
--
-- phpMyAdmin: click your database, open the "Import" tab, choose this FILE and press
-- Import. (In the "SQL" tab you must paste the file's CONTENTS, not its name.)
-- It does not touch users, comments, quizzes or scores, and is safe to run twice.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(30) NOT NULL,
  `identifier` char(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `lookup` (`action`,`identifier`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
