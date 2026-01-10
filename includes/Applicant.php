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
    
    /**
     * Get all applicants with filters (v2.0 - Multi-Division Support)
     * 
     * @param array $filters Associative array of filters:
     *   - position_applied: Filter by position
     *   - logic_status: Filter by logic test status (aman/rawan/tidak_aman)
     *   - hr_decision: Filter by HR decision (lanjut/hold/stop/pending)
     *   - search: Search by nama or email
     * @return array List of applicants
     */
    public function getAllFiltered(array $filters = []): array {
        try {
            $sql = "SELECT * FROM applicants WHERE 1=1";
            $params = [];
            
            // Position filter
            if (!empty($filters['position_applied'])) {
                $sql .= " AND position_applied = :position_applied";
                $params['position_applied'] = $filters['position_applied'];
            }
            
            // Logic status filter
            if (!empty($filters['logic_status'])) {
                $sql .= " AND logic_status = :logic_status";
                $params['logic_status'] = $filters['logic_status'];
            }
            
            // HR decision filter
            if (!empty($filters['hr_decision'])) {
                if ($filters['hr_decision'] === 'pending') {
                    $sql .= " AND (hr_decision IS NULL OR hr_decision = '')";
                } else {
                    $sql .= " AND hr_decision = :hr_decision";
                    $params['hr_decision'] = $filters['hr_decision'];
                }
            }
            
            // Search filter (nama or email)
            if (!empty($filters['search'])) {
                $sql .= " AND (nama LIKE :search OR email LIKE :search_email)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params['search'] = $searchTerm;
                $params['search_email'] = $searchTerm;
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Applicant getAllFiltered failed: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get statistics for v2.0 dashboard (Multi-Division Support)
     * 
     * @return array Statistics including logic status and HR decision counts
     */
    public function getStatsV2(): array {
        try {
            $stmt = $this->db->query("SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN logic_status = 'aman' THEN 1 ELSE 0 END) as aman,
                SUM(CASE WHEN logic_status = 'rawan' THEN 1 ELSE 0 END) as rawan,
                SUM(CASE WHEN logic_status = 'tidak_aman' THEN 1 ELSE 0 END) as tidak_aman,
                SUM(CASE WHEN hr_decision = 'lanjut' THEN 1 ELSE 0 END) as lanjut,
                SUM(CASE WHEN hr_decision = 'hold' THEN 1 ELSE 0 END) as hold,
                SUM(CASE WHEN hr_decision = 'stop' THEN 1 ELSE 0 END) as stop,
                SUM(CASE WHEN hr_decision IS NULL OR hr_decision = '' THEN 1 ELSE 0 END) as pending,
                AVG(logic_correct) as avg_logic,
                AVG(psychology_fit_score) as avg_fit_score
            FROM applicants");
            
            $result = $stmt->fetch();
            
            return [
                'total' => (int)($result['total'] ?? 0),
                'aman' => (int)($result['aman'] ?? 0),
                'rawan' => (int)($result['rawan'] ?? 0),
                'tidak_aman' => (int)($result['tidak_aman'] ?? 0),
                'lanjut' => (int)($result['lanjut'] ?? 0),
                'hold' => (int)($result['hold'] ?? 0),
                'stop' => (int)($result['stop'] ?? 0),
                'pending' => (int)($result['pending'] ?? 0),
                'avg_logic' => round((float)($result['avg_logic'] ?? 0), 1),
                'avg_fit_score' => round((float)($result['avg_fit_score'] ?? 0), 1)
            ];
        } catch (PDOException $e) {
            error_log('Applicant getStatsV2 failed: ' . $e->getMessage());
            return [
                'total' => 0, 
                'aman' => 0, 
                'rawan' => 0, 
                'tidak_aman' => 0,
                'lanjut' => 0,
                'hold' => 0,
                'stop' => 0,
                'pending' => 0,
                'avg_logic' => 0,
                'avg_fit_score' => 0
            ];
        }
    }
    
    /**
     * Update HR assessment for an applicant
     * 
     * @param string $id Applicant ID
     * @param array $assessment Assessment data
     * @return array Result with success status
     */
    public function updateAssessment(string $id, array $assessment): array {
        try {
            $sql = "UPDATE applicants SET 
                hr_adab_a_otoritas = :hr_adab_a_otoritas,
                hr_adab_b_koreksi = :hr_adab_b_koreksi,
                hr_adab_c_tidak_sepakat = :hr_adab_c_tidak_sepakat,
                hr_adab_d_kesadaran_diri = :hr_adab_d_kesadaran_diri,
                hr_adab_e_kecocokan_nilai = :hr_adab_e_kecocokan_nilai,
                hr_adab_f1_orientasi_niat = :hr_adab_f1_orientasi_niat,
                hr_adab_f2_respon_lelah = :hr_adab_f2_respon_lelah,
                hr_adab_f3_keikhlasan = :hr_adab_f3_keikhlasan,
                hr_adab_f4_spiritual = :hr_adab_f4_spiritual,
                hr_value_fit = :hr_value_fit,
                hr_adab_fit = :hr_adab_fit,
                hr_risk_note = :hr_risk_note,
                hr_decision = :hr_decision,
                hr_notes = :hr_notes,
                hr_assessed_by = :hr_assessed_by,
                hr_assessed_at = NOW(),
                updated_at = NOW()
            WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'hr_adab_a_otoritas' => $assessment['hr_adab_a_otoritas'] ?? null,
                'hr_adab_b_koreksi' => $assessment['hr_adab_b_koreksi'] ?? null,
                'hr_adab_c_tidak_sepakat' => $assessment['hr_adab_c_tidak_sepakat'] ?? null,
                'hr_adab_d_kesadaran_diri' => $assessment['hr_adab_d_kesadaran_diri'] ?? null,
                'hr_adab_e_kecocokan_nilai' => $assessment['hr_adab_e_kecocokan_nilai'] ?? null,
                'hr_adab_f1_orientasi_niat' => $assessment['hr_adab_f1_orientasi_niat'] ?? null,
                'hr_adab_f2_respon_lelah' => $assessment['hr_adab_f2_respon_lelah'] ?? null,
                'hr_adab_f3_keikhlasan' => $assessment['hr_adab_f3_keikhlasan'] ?? null,
                'hr_adab_f4_spiritual' => $assessment['hr_adab_f4_spiritual'] ?? null,
                'hr_value_fit' => $assessment['hr_value_fit'] ?? null,
                'hr_adab_fit' => $assessment['hr_adab_fit'] ?? null,
                'hr_risk_note' => $assessment['hr_risk_note'] ?? null,
                'hr_decision' => $assessment['hr_decision'] ?? null,
                'hr_notes' => $assessment['hr_notes'] ?? null,
                'hr_assessed_by' => $assessment['hr_assessed_by'] ?? null
            ]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log('Applicant updateAssessment failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update assessment'];
        }
    }
    
    /**
     * Update interview and probation tracking
     * 
     * @param string $id Applicant ID
     * @param array $data Interview/probation data
     * @return array Result with success status
     */
    public function updateInterviewProbation(string $id, array $data): array {
        try {
            $sql = "UPDATE applicants SET 
                interview_hrd_notes = :interview_hrd_notes,
                interview_hrd_date = :interview_hrd_date,
                interview_hrd_result = :interview_hrd_result,
                interview_user_notes = :interview_user_notes,
                interview_user_date = :interview_user_date,
                interview_user_result = :interview_user_result,
                probation_status = :probation_status,
                probation_start_date = :probation_start_date,
                probation_notes = :probation_notes,
                final_decision = :final_decision,
                final_decision_date = :final_decision_date,
                final_decision_by = :final_decision_by,
                updated_at = NOW()
            WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'interview_hrd_notes' => $data['interview_hrd_notes'] ?? null,
                'interview_hrd_date' => $data['interview_hrd_date'] ?? null,
                'interview_hrd_result' => $data['interview_hrd_result'] ?? null,
                'interview_user_notes' => $data['interview_user_notes'] ?? null,
                'interview_user_date' => $data['interview_user_date'] ?? null,
                'interview_user_result' => $data['interview_user_result'] ?? null,
                'probation_status' => $data['probation_status'] ?? 'belum',
                'probation_start_date' => $data['probation_start_date'] ?? null,
                'probation_notes' => $data['probation_notes'] ?? null,
                'final_decision' => $data['final_decision'] ?? 'pending',
                'final_decision_date' => $data['final_decision_date'] ?? null,
                'final_decision_by' => $data['final_decision_by'] ?? null
            ]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log('Applicant updateInterviewProbation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update interview/probation data'];
        }
    }
}
