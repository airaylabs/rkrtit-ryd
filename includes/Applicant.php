<?php
/**
 * Applicant Model - FINAL OPTIMIZED VERSION
 */

require_once __DIR__ . '/../config/database.php';

class Applicant {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function generateShortId(string $nama): string {
        $nameClean = preg_replace('/[^A-Za-z]/', '', $nama);
        $namePrefix = strtoupper(substr($nameClean, 0, 3));
        $namePrefix = str_pad($namePrefix, 3, 'X');
        $random = str_pad(random_int(0, 99), 2, '0', STR_PAD_LEFT);
        return "RC-{$namePrefix}{$random}";
    }
    
    public function create(array $data): array {
        try {
            $this->db->beginTransaction();
            
            $id = $data['id'] ?? $this->generateShortId($data['nama'] ?? 'UNKN');
            
            $sql = "INSERT INTO applicants (
                id, nama, email, whatsapp, 
                cv_filename, cv_original_name, cv_mime_type,
                technical_score, technical_correct, technical_total,
                technical_answers, technical_details,
                psikotes_score, psikotes_categories,
                psikotes_answers, psikotes_details,
                overall_score, status, status_label, recommendation,
                timer_personal, timer_technical, timer_psikotes, timer_total
            ) VALUES (
                :id, :nama, :email, :whatsapp,
                :cv_filename, :cv_original_name, :cv_mime_type,
                :technical_score, :technical_correct, :technical_total,
                :technical_answers, :technical_details,
                :psikotes_score, :psikotes_categories,
                :psikotes_answers, :psikotes_details,
                :overall_score, :status, :status_label, :recommendation,
                :timer_personal, :timer_technical, :timer_psikotes, :timer_total
            )";

            $stmt = $this->db->prepare($sql);
            
            $params = [
                'id' => $id,
                'nama' => $data['nama'],
                'email' => $data['email'],
                'whatsapp' => $data['whatsapp'],
                'cv_filename' => $data['cv_filename'] ?? null,
                'cv_original_name' => $data['cv_original_name'] ?? null,
                'cv_mime_type' => $data['cv_mime_type'] ?? null,
                'technical_score' => $data['technical_score'] ?? 0,
                'technical_correct' => $data['technical_correct'] ?? 0,
                'technical_total' => $data['technical_total'] ?? 5,
                'technical_answers' => isset($data['technical_answers']) ? json_encode($data['technical_answers']) : null,
                'technical_details' => isset($data['technical_details']) ? json_encode($data['technical_details']) : null,
                'psikotes_score' => $data['psikotes_score'] ?? 0,
                'psikotes_categories' => isset($data['psikotes_categories']) ? json_encode($data['psikotes_categories']) : null,
                'psikotes_answers' => isset($data['psikotes_answers']) ? json_encode($data['psikotes_answers']) : null,
                'psikotes_details' => isset($data['psikotes_details']) ? json_encode($data['psikotes_details']) : null,
                'overall_score' => $data['overall_score'] ?? 0,
                'status' => $data['status'] ?? 'TIDAK LULUS',
                'status_label' => $data['status_label'] ?? null,
                'recommendation' => $data['recommendation'] ?? null,
                'timer_personal' => $data['timer_personal'] ?? 0,
                'timer_technical' => $data['timer_technical'] ?? 0,
                'timer_psikotes' => $data['timer_psikotes'] ?? 0,
                'timer_total' => $data['timer_total'] ?? 0
            ];
            
            $stmt->execute($params);
            $this->db->commit();
            
            return ['success' => true, 'id' => $id];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Applicant create failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to save applicant data'];
        }
    }
    
    public function getAll(?string $status = null): array {
        try {
            $sql = "SELECT * FROM applicants";
            $params = [];
            
            if ($status !== null && in_array($status, ['LULUS', 'REVIEW', 'TIDAK LULUS'])) {
                $sql .= " WHERE status = :status";
                $params['status'] = $status;
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Applicant getAll failed: ' . $e->getMessage());
            return [];
        }
    }
    
    public function getById(string $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM applicants WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            
            if ($result) {
                foreach (['technical_answers', 'technical_details', 'psikotes_categories', 'psikotes_answers', 'psikotes_details'] as $field) {
                    if ($result[$field]) {
                        $result[$field] = json_decode($result[$field], true);
                    }
                }
                return $result;
            }
            return null;
        } catch (PDOException $e) {
            error_log('Applicant getById failed: ' . $e->getMessage());
            return null;
        }
    }
    
    public function getStats(): array {
        try {
            $stmt = $this->db->query("SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'LULUS' THEN 1 ELSE 0 END) as lulus,
                SUM(CASE WHEN status = 'REVIEW' THEN 1 ELSE 0 END) as review,
                SUM(CASE WHEN status = 'TIDAK LULUS' THEN 1 ELSE 0 END) as tidak_lulus,
                AVG(technical_score) as avg_technical,
                AVG(psikotes_score) as avg_psikotes,
                AVG(overall_score) as avg_overall
            FROM applicants");
            
            $result = $stmt->fetch();
            
            return [
                'total' => (int)($result['total'] ?? 0),
                'lulus' => (int)($result['lulus'] ?? 0),
                'review' => (int)($result['review'] ?? 0),
                'tidak_lulus' => (int)($result['tidak_lulus'] ?? 0),
                'avg_technical' => round((float)($result['avg_technical'] ?? 0), 1),
                'avg_psikotes' => round((float)($result['avg_psikotes'] ?? 0), 1),
                'avg_overall' => round((float)($result['avg_overall'] ?? 0), 1)
            ];
        } catch (PDOException $e) {
            error_log('Applicant getStats failed: ' . $e->getMessage());
            return ['total' => 0, 'lulus' => 0, 'review' => 0, 'tidak_lulus' => 0, 'avg_technical' => 0, 'avg_psikotes' => 0, 'avg_overall' => 0];
        }
    }
}
