-- ---------------------------------------------------------------------------
-- Scratch Learn - UPGRADE for a database that already exists
-- (use this on your live site; new installs just import scratch_learn_db.sql)
--
-- phpMyAdmin: click your database, open the "SQL" tab (or "Import"), paste/choose
-- this file and press Go. It does not touch users, comments or quizzes you already
-- have, and it is safe to run more than once.
-- ---------------------------------------------------------------------------

-- 1) Table that stores each user's best score per quiz (feeds the leaderboard)
CREATE TABLE IF NOT EXISTS `scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `taken_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_quiz` (`user_id`,`quiz_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2) Physics quiz questions 3 and 4 did not match the distance formula taught in the lesson
UPDATE `questions` SET `question_text` = 'What is the distance between the points (0, 0) and (3, 4)?' WHERE `id` = 7;
UPDATE `questions` SET `question_text` = 'What is the distance between the points (0, 0) and (5, 12)?' WHERE `id` = 8;

UPDATE `answers` SET `answer_text` = '7'  WHERE `id` = 19;
UPDATE `answers` SET `answer_text` = '10' WHERE `id` = 20;
UPDATE `answers` SET `answer_text` = '5'  WHERE `id` = 21;
UPDATE `answers` SET `answer_text` = '7'  WHERE `id` = 22;
UPDATE `answers` SET `answer_text` = '17' WHERE `id` = 23;
UPDATE `answers` SET `answer_text` = '13' WHERE `id` = 24;
