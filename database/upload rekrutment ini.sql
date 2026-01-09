/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP TABLE IF EXISTS `applicants`;
CREATE TABLE `applicants` (
  `id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cv_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cv_original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cv_mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `technical_score` decimal(4,1) NOT NULL DEFAULT '0.0',
  `technical_correct` int DEFAULT '0',
  `technical_total` int DEFAULT '5',
  `technical_answers` json DEFAULT NULL,
  `technical_details` json DEFAULT NULL,
  `psikotes_score` decimal(4,1) NOT NULL DEFAULT '0.0',
  `psikotes_categories` json DEFAULT NULL,
  `psikotes_answers` json DEFAULT NULL,
  `psikotes_details` json DEFAULT NULL,
  `overall_score` decimal(4,1) NOT NULL DEFAULT '0.0',
  `status` enum('LULUS','REVIEW','TIDAK LULUS') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TIDAK LULUS',
  `status_label` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recommendation` text COLLATE utf8mb4_unicode_ci,
  `timer_personal` int DEFAULT '0',
  `timer_technical` int DEFAULT '0',
  `timer_psikotes` int DEFAULT '0',
  `timer_total` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_overall_score` (`overall_score`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `applicants` (`id`, `nama`, `email`, `whatsapp`, `cv_filename`, `cv_original_name`, `cv_mime_type`, `technical_score`, `technical_correct`, `technical_total`, `technical_answers`, `technical_details`, `psikotes_score`, `psikotes_categories`, `psikotes_answers`, `psikotes_details`, `overall_score`, `status`, `status_label`, `recommendation`, `timer_personal`, `timer_technical`, `timer_psikotes`, `timer_total`, `created_at`, `updated_at`) VALUES
('RC-REV32', 'reva', 'it@rayandra.com', '085644738211', 'ca4ea2ebda39b9e31e9e0efc065a14d8.jpg', 'f1e4c2b1b7044e8d61831beefa81d013.jpg', 'image/jpeg', '2.0', 1, 5, '{\"tech1a\": \"A\", \"tech1b\": \"B\", \"tech2a\": \"C\", \"tech2b\": \"D\", \"tech3a\": \"C\"}', '{\"tech1a\": {\"answer\": \"A\", \"correct\": true, \"explanation\": \"Input dari user harus selalu divalidasi untuk keamanan\", \"correctAnswer\": \"A\"}, \"tech1b\": {\"answer\": \"B\", \"correct\": false, \"explanation\": \"$request->validate() adalah cara standar Laravel untuk validasi\", \"correctAnswer\": \"A\"}, \"tech2a\": {\"answer\": \"C\", \"correct\": false, \"explanation\": \"SUM dengan GROUP BY untuk aggregate per customer\", \"correctAnswer\": \"A\"}, \"tech2b\": {\"answer\": \"D\", \"correct\": false, \"explanation\": \"git stash menyimpan perubahan sementara\", \"correctAnswer\": \"B\"}, \"tech3a\": {\"answer\": \"C\", \"correct\": false, \"explanation\": \"Webhook node menerima data dari sistem eksternal\", \"correctAnswer\": \"B\"}}', '6.7', '{\"multitask\": 4, \"initiative\": 6, \"adaptability\": 10}', '{\"psi1\": \"A\", \"psi2\": \"B\", \"psi3\": \"B\"}', '{\"psi1\": {\"answer\": \"A\", \"category\": \"multitask\", \"rawScore\": 2, \"scaledScore\": 4}, \"psi2\": {\"answer\": \"B\", \"category\": \"adaptability\", \"rawScore\": 5, \"scaledScore\": 10}, \"psi3\": {\"answer\": \"B\", \"category\": \"initiative\", \"rawScore\": 3, \"scaledScore\": 6}}', '3.4', 'TIDAK LULUS', 'Belum Lulus', 'Belum memenuhi kriteria minimum', 13, 8, 4, 25, '2026-01-09 19:32:07', '2026-01-09 19:32:07'),
('RC-RIOA09012GW7', 'rio agustina', 'rio@gmail.com', '085644738211', 'a294b55cce7dd73ce8438a19b694bfbe.jpg', 'f1e4c2b1b7044e8d61831beefa81d013.jpg', 'image/jpeg', '6.0', 3, 5, '{\"tech1a\": \"B\", \"tech1b\": \"B\", \"tech2a\": \"A\", \"tech2b\": \"B\", \"tech3a\": \"B\"}', '{\"tech1a\": {\"answer\": \"B\", \"correct\": false, \"explanation\": \"Input dari user harus selalu divalidasi untuk keamanan\", \"correctAnswer\": \"A\"}, \"tech1b\": {\"answer\": \"B\", \"correct\": false, \"explanation\": \"$request->validate() adalah cara standar Laravel untuk validasi\", \"correctAnswer\": \"A\"}, \"tech2a\": {\"answer\": \"A\", \"correct\": true, \"explanation\": \"SUM dengan GROUP BY untuk aggregate per customer\", \"correctAnswer\": \"A\"}, \"tech2b\": {\"answer\": \"B\", \"correct\": true, \"explanation\": \"git stash menyimpan perubahan sementara\", \"correctAnswer\": \"B\"}, \"tech3a\": {\"answer\": \"B\", \"correct\": true, \"explanation\": \"Webhook node menerima data dari sistem eksternal\", \"correctAnswer\": \"B\"}}', '7.3', '{\"multitask\": 6, \"initiative\": 6, \"adaptability\": 10}', '{\"psi1\": \"B\", \"psi2\": \"B\", \"psi3\": \"B\"}', '{\"psi1\": {\"answer\": \"B\", \"category\": \"multitask\", \"rawScore\": 3, \"scaledScore\": 6}, \"psi2\": {\"answer\": \"B\", \"category\": \"adaptability\", \"rawScore\": 5, \"scaledScore\": 10}, \"psi3\": {\"answer\": \"B\", \"category\": \"initiative\", \"rawScore\": 3, \"scaledScore\": 6}}', '6.4', 'REVIEW', 'Butuh Review', 'Perlu review manual oleh HR/Manager', 15, 47, 108, 170, '2026-01-09 19:19:42', '2026-01-09 19:19:42');


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;