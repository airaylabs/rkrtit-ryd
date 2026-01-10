<?php
/**
 * Admin Detail Page - Multi-Division Recruitment System v2.0
 * 
 * 6-Tab Structure:
 * Tab 1: Data Pribadi & CV download
 * Tab 2: Jawaban Form (Value & Adab sections)
 * Tab 3: Hasil Logic Test with per-question breakdown
 * Tab 4: Hasil Psychology Test with work pattern visualization (4-quadrant)
 * Tab 5: HR Assessment form with 6 aspek adab (A-F)
 * Tab 6: Interview & Probation tracking
 * 
 * Requirements: 6.2, 9.2, 9.3, 6.3, 6.4, 5.1-5.6
 */

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Applicant.php';
require_once __DIR__ . '/../includes/InputSanitizer.php';
require_once __DIR__ . '/../includes/questions.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Get applicant ID
$applicantId = isset($_GET['id']) ? InputSanitizer::sanitizeString($_GET['id']) : '';

if (empty($applicantId)) {
    header('Location: index.php');
    exit;
}

// Handle form submissions
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicantModel = new Applicant();
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save_assessment') {
            // Save HR Assessment
            $assessment = [
                'hr_adab_a_otoritas' => $_POST['hr_adab_a_otoritas'] ?? null,
                'hr_adab_b_koreksi' => $_POST['hr_adab_b_koreksi'] ?? null,
                'hr_adab_c_tidak_sepakat' => $_POST['hr_adab_c_tidak_sepakat'] ?? null,
                'hr_adab_d_kesadaran_diri' => $_POST['hr_adab_d_kesadaran_diri'] ?? null,
                'hr_adab_e_kecocokan_nilai' => $_POST['hr_adab_e_kecocokan_nilai'] ?? null,
                'hr_adab_f1_orientasi_niat' => $_POST['hr_adab_f1_orientasi_niat'] ?? null,
                'hr_adab_f2_respon_lelah' => $_POST['hr_adab_f2_respon_lelah'] ?? null,
                'hr_adab_f3_keikhlasan' => $_POST['hr_adab_f3_keikhlasan'] ?? null,
                'hr_adab_f4_spiritual' => $_POST['hr_adab_f4_spiritual'] ?? null,
                'hr_value_fit' => $_POST['hr_value_fit'] ?? null,
                'hr_adab_fit' => $_POST['hr_adab_fit'] ?? null,
                'hr_risk_note' => $_POST['hr_risk_note'] ?? null,
                'hr_decision' => $_POST['hr_decision'] ?? null,
                'hr_notes' => $_POST['hr_notes'] ?? null,
                'hr_assessed_by' => $_SESSION['admin_username'] ?? 'Admin'
            ];
            
            $result = $applicantModel->updateAssessment($applicantId, $assessment);
            if ($result['success']) {
                $successMessage = 'Penilaian HR berhasil disimpan.';
            } else {
                $errorMessage = 'Gagal menyimpan penilaian: ' . ($result['error'] ?? 'Unknown error');
            }
        } elseif ($_POST['action'] === 'save_interview_probation') {
            // Save Interview & Probation data
            $data = [
                'interview_hrd_notes' => $_POST['interview_hrd_notes'] ?? null,
                'interview_hrd_date' => $_POST['interview_hrd_date'] ?: null,
                'interview_hrd_result' => $_POST['interview_hrd_result'] ?? null,
                'interview_user_notes' => $_POST['interview_user_notes'] ?? null,
                'interview_user_date' => $_POST['interview_user_date'] ?: null,
                'interview_user_result' => $_POST['interview_user_result'] ?? null,
                'probation_status' => $_POST['probation_status'] ?? 'belum',
                'probation_start_date' => $_POST['probation_start_date'] ?: null,
                'probation_notes' => $_POST['probation_notes'] ?? null,
                'final_decision' => $_POST['final_decision'] ?? 'pending',
                'final_decision_date' => $_POST['final_decision_date'] ?: null,
                'final_decision_by' => $_SESSION['admin_username'] ?? 'Admin'
            ];
            
            $result = $applicantModel->updateInterviewProbation($applicantId, $data);
            if ($result['success']) {
                $successMessage = 'Data Interview & Probation berhasil disimpan.';
            } else {
                $errorMessage = 'Gagal menyimpan data: ' . ($result['error'] ?? 'Unknown error');
            }
        }
    }
}

// Fetch applicant data
try {
    $applicantModel = new Applicant();
    $applicant = $applicantModel->getById($applicantId);
    
    if (!$applicant) {
        header('Location: index.php?error=not_found');
        exit;
    }
} catch (Exception $e) {
    header('Location: index.php?error=db_error');
    exit;
}

// Get logic answer keys
$logicAnswerKeys = getLogicAnswerKeys();
$logicQuestions = getLogicQuestions();

// Parse JSON fields safely
$logicAnswers = [];
$logicDetails = [];
$psychologyAnswers = [];
$psychologyDetails = [];
$alternativePositions = [];

if (!empty($applicant['logic_answers'])) {
    $logicAnswers = is_string($applicant['logic_answers']) 
        ? json_decode($applicant['logic_answers'], true) ?? []
        : $applicant['logic_answers'];
}

if (!empty($applicant['logic_details'])) {
    $logicDetails = is_string($applicant['logic_details']) 
        ? json_decode($applicant['logic_details'], true) ?? []
        : $applicant['logic_details'];
}

if (!empty($applicant['psychology_answers'])) {
    $psychologyAnswers = is_string($applicant['psychology_answers']) 
        ? json_decode($applicant['psychology_answers'], true) ?? []
        : $applicant['psychology_answers'];
}

if (!empty($applicant['psychology_details'])) {
    $psychologyDetails = is_string($applicant['psychology_details']) 
        ? json_decode($applicant['psychology_details'], true) ?? []
        : $applicant['psychology_details'];
}

if (!empty($applicant['psychology_alternative_positions'])) {
    $alternativePositions = is_string($applicant['psychology_alternative_positions']) 
        ? json_decode($applicant['psychology_alternative_positions'], true) ?? []
        : $applicant['psychology_alternative_positions'];
}

// Position labels
$positionLabels = [
    'operator_produksi' => 'Operator Produksi',
    'staff_kantor' => 'Staff Kantor',
    'supervisor' => 'Supervisor',
    'rnd_qc_lab' => 'R&D/QC/Lab',
    'kreatif' => 'Kreatif',
    'product_development' => 'Product Dev',
    'management' => 'Management'
];

// Work pattern labels
$patternLabels = [
    'presisi_monoton' => 'Presisi Monoton',
    'presisi_dinamis' => 'Presisi Dinamis',
    'eksploratif_terstruktur' => 'Eksploratif Terstruktur',
    'eksploratif_dinamis' => 'Eksploratif Dinamis'
];

// Logic status labels
$logicStatusLabels = [
    'aman' => 'Aman',
    'rawan' => 'Rawan',
    'tidak_aman' => 'Tidak Aman'
];

// Format time helper
function formatDuration($seconds) {
    if (!$seconds) return '0 menit 0 detik';
    $mins = floor($seconds / 60);
    $secs = $seconds % 60;
    return "{$mins} menit {$secs} detik";
}

function formatDurationShort($seconds) {
    if (!$seconds) return '0m 0s';
    $mins = floor($seconds / 60);
    $secs = $seconds % 60;
    return "{$mins}m {$secs}s";
}

// Check for red indicators in assessment
function hasRedIndicator($applicant) {
    $adabFields = [
        'hr_adab_a_otoritas', 'hr_adab_b_koreksi', 'hr_adab_c_tidak_sepakat',
        'hr_adab_d_kesadaran_diri', 'hr_adab_e_kecocokan_nilai',
        'hr_adab_f1_orientasi_niat', 'hr_adab_f2_respon_lelah',
        'hr_adab_f3_keikhlasan', 'hr_adab_f4_spiritual'
    ];
    
    foreach ($adabFields as $field) {
        if (isset($applicant[$field]) && $applicant[$field] === 'tidak_cocok') {
            return true;
        }
    }
    return false;
}

$hasRed = hasRedIndicator($applicant);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pelamar - <?php echo htmlspecialchars($applicant['nama']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Tab Navigation */
        .tab-nav {
            display: flex;
            gap: 4px;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0;
            overflow-x: auto;
        }
        .tab-btn {
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid var(--border-color);
            border-bottom: none;
            background: var(--bg-input);
            color: var(--text-secondary);
            transition: all 0.2s;
            white-space: nowrap;
        }
        .tab-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .tab-btn.active {
            background: var(--bg-card);
            border-color: var(--primary);
            color: var(--primary);
            position: relative;
        }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--bg-card);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Detail Header */
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding: 20px;
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .detail-info h2 {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        .detail-meta {
            color: var(--text-muted);
            font-size: 14px;
        }
        .detail-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.aman { background: rgba(16, 185, 129, 0.2); color: var(--success); }
        .status-badge.rawan { background: rgba(245, 158, 11, 0.2); color: var(--warning); }
        .status-badge.tidak_aman, .status-badge.tidak-aman { background: rgba(239, 68, 68, 0.2); color: var(--danger); }
        .status-badge.lanjut { background: rgba(16, 185, 129, 0.2); color: var(--success); }
        .status-badge.hold { background: rgba(245, 158, 11, 0.2); color: var(--warning); }
        .status-badge.stop { background: rgba(239, 68, 68, 0.2); color: var(--danger); }
        .status-badge.pending { background: rgba(100, 116, 139, 0.2); color: var(--text-muted); }
        
        /* Detail Sections */
        .detail-section {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }
        .detail-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }
        .info-item {
            padding: 12px 16px;
            background: var(--bg-input);
            border-radius: 8px;
        }
        .info-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        .info-value {
            font-weight: 500;
            color: var(--text-primary);
        }
        
        /* Answer Items */
        .answer-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: var(--bg-input);
            border-radius: 8px;
            border-left: 3px solid var(--border-color);
            margin-bottom: 8px;
        }
        .answer-item.correct {
            border-left-color: var(--success);
            background: rgba(16, 185, 129, 0.1);
        }
        .answer-item.incorrect {
            border-left-color: var(--danger);
            background: rgba(239, 68, 68, 0.1);
        }
        .answer-question {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 13px;
        }
        .answer-values {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .answer-given {
            font-weight: 500;
        }
        .answer-item.correct .answer-given { color: var(--success); }
        .answer-item.incorrect .answer-given { color: var(--danger); }
        .answer-correct {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        /* Score Summary */
        .score-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .score-summary-item {
            background: var(--bg-input);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .score-summary-value {
            font-size: 2rem;
            font-weight: 700;
        }
        .score-summary-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        
        /* Category Section */
        .category-section {
            background: var(--bg-input);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .category-title {
            font-weight: 600;
            color: var(--primary-light);
        }
        .category-score {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .category-score.good { background: rgba(16, 185, 129, 0.2); color: var(--success); }
        .category-score.warning { background: rgba(245, 158, 11, 0.2); color: var(--warning); }
        .category-score.bad { background: rgba(239, 68, 68, 0.2); color: var(--danger); }
        
        /* Work Pattern Quadrant */
        .quadrant-container {
            display: flex;
            justify-content: center;
            margin: 24px 0;
        }
        .quadrant-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
            width: 400px;
            max-width: 100%;
        }
        .quadrant-cell {
            padding: 24px 16px;
            text-align: center;
            border-radius: 8px;
            background: var(--bg-input);
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        .quadrant-cell.active {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.2);
        }
        .quadrant-cell.mismatch {
            border-color: var(--danger);
            background: rgba(239, 68, 68, 0.1);
        }
        .quadrant-label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .quadrant-positions {
            font-size: 11px;
            color: var(--text-muted);
        }
        .quadrant-axis {
            text-align: center;
            font-size: 11px;
            color: var(--text-muted);
            padding: 8px;
        }
        
        /* CV Preview */
        .cv-preview-container {
            text-align: center;
        }
        .cv-preview-image {
            max-width: 100%;
            max-height: 70vh;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .cv-preview-pdf {
            width: 100%;
            height: 70vh;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .cv-download-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 16px;
            transition: all 0.2s;
        }
        .cv-download-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        /* Form Styles */
        .form-section {
            margin-bottom: 24px;
            padding: 20px;
            background: var(--bg-input);
            border-radius: 12px;
        }
        .form-section-title {
            font-weight: 600;
            color: var(--primary-light);
            margin-bottom: 16px;
            font-size: 15px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 14px;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        /* Radio Group */
        .radio-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .radio-option {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 13px;
        }
        .radio-option:hover {
            border-color: var(--primary);
        }
        .radio-option input[type="radio"] {
            accent-color: var(--primary);
        }
        .radio-option.sehat { border-color: var(--success); background: rgba(16, 185, 129, 0.1); }
        .radio-option.waspada { border-color: var(--warning); background: rgba(245, 158, 11, 0.1); }
        .radio-option.tidak_cocok { border-color: var(--danger); background: rgba(239, 68, 68, 0.1); }
        
        /* Alert Messages */
        .alert {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert.success {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
            border: 1px solid var(--success);
        }
        .alert.error {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        .alert.warning {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        
        /* Button Styles */
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            font-size: 14px;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        .btn-secondary {
            background: var(--bg-input);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        .btn-secondary:hover {
            border-color: var(--primary);
        }
        
        /* Fit Score Display */
        .fit-score-display {
            text-align: center;
            padding: 24px;
            background: var(--bg-input);
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .fit-score-value {
            font-size: 3rem;
            font-weight: 700;
        }
        .fit-score-value.high { color: var(--success); }
        .fit-score-value.medium { color: var(--warning); }
        .fit-score-value.low { color: var(--danger); }
        .fit-score-label {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        
        /* Mismatch Alert */
        .mismatch-alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid var(--danger);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .mismatch-alert .icon {
            font-size: 24px;
        }
        .mismatch-alert .text {
            color: var(--danger);
            font-weight: 500;
        }
        
        /* Textarea Display */
        .textarea-display {
            background: var(--bg-secondary);
            padding: 16px;
            border-radius: 8px;
            white-space: pre-wrap;
            font-size: 14px;
            line-height: 1.6;
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        /* Print Button */
        .print-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 14px 24px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            z-index: 100;
        }
        .print-btn:hover {
            background: var(--primary-dark);
        }
        
        @media print {
            .admin-header, .tab-nav, .print-btn, .btn { display: none !important; }
            .tab-content { display: block !important; }
            body { background: white; color: black; }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-logo">🚀 <span class="brand">Ray</span>Corp Admin</div>
        <nav class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="index.php?logout=1">Logout</a>
        </nav>
    </header>

    <div class="admin-container">
        <a href="index.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; font-size: 14px; margin-bottom: 16px;">
            ← Kembali ke Dashboard
        </a>

        <?php if ($successMessage): ?>
        <div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
        <div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="detail-header">
            <div class="detail-info">
                <h2><?php echo htmlspecialchars($applicant['nama']); ?></h2>
                <div class="detail-meta">
                    <?php echo htmlspecialchars($applicant['email']); ?> • 
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $applicant['whatsapp']); ?>" 
                       target="_blank" style="color: #25d366;">
                        📱 <?php echo htmlspecialchars($applicant['whatsapp']); ?>
                    </a>
                    <br>
                    <span style="color: var(--primary);">
                        <?php echo $positionLabels[$applicant['position_applied']] ?? $applicant['position_applied'] ?? '-'; ?>
                    </span>
                </div>
            </div>
            <div class="detail-badges">
                <?php 
                $logicStatus = $applicant['logic_status'] ?? 'tidak_aman';
                $hrDecision = $applicant['hr_decision'] ?? null;
                ?>
                <span class="status-badge <?php echo $logicStatus; ?>">
                    Logic: <?php echo $logicStatusLabels[$logicStatus] ?? ucfirst($logicStatus); ?>
                </span>
                <?php if ($hrDecision): ?>
                <span class="status-badge <?php echo $hrDecision; ?>">
                    HR: <?php echo ucfirst($hrDecision); ?>
                </span>
                <?php else: ?>
                <span class="status-badge pending">HR: Pending</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab Navigation - 6 Tabs -->
        <div class="tab-nav">
            <button class="tab-btn active" data-tab="personal">📋 Data Pribadi</button>
            <button class="tab-btn" data-tab="form">📝 Jawaban Form</button>
            <button class="tab-btn" data-tab="logic">🧮 Logic Test</button>
            <button class="tab-btn" data-tab="psychology">🧠 Psychology Test</button>
            <button class="tab-btn" data-tab="assessment">⚖️ HR Assessment</button>
            <button class="tab-btn" data-tab="interview">📅 Interview & Probation</button>
        </div>

        <!-- Tab 1: Data Pribadi & CV -->
        <div id="tab-personal" class="tab-content active">
            <div class="detail-section">
                <h3 class="detail-section-title">👤 Data Pribadi</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-value"><?php echo htmlspecialchars($applicant['nama']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($applicant['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">WhatsApp</div>
                        <div class="info-value">
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $applicant['whatsapp']); ?>" 
                               target="_blank" style="color: #25d366;">
                                <?php echo htmlspecialchars($applicant['whatsapp']); ?>
                            </a>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tempat, Tanggal Lahir</div>
                        <div class="info-value">
                            <?php 
                            $tempat = $applicant['tempat_lahir'] ?? '-';
                            $tgl = $applicant['tanggal_lahir'] ? date('d F Y', strtotime($applicant['tanggal_lahir'])) : '-';
                            echo htmlspecialchars($tempat) . ', ' . $tgl;
                            ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Alamat Domisili</div>
                        <div class="info-value"><?php echo htmlspecialchars($applicant['alamat_domisili'] ?? '-'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status Pernikahan</div>
                        <div class="info-value">
                            <?php 
                            $statusMap = ['belum_menikah' => 'Belum Menikah', 'menikah' => 'Menikah', 'janda_duda' => 'Janda/Duda'];
                            echo $statusMap[$applicant['status_pernikahan']] ?? '-';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">💼 Posisi & Latar Belakang</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Posisi yang Dilamar</div>
                        <div class="info-value" style="color: var(--primary);">
                            <?php echo $positionLabels[$applicant['position_applied']] ?? $applicant['position_applied'] ?? '-'; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Track Penilaian</div>
                        <div class="info-value">
                            <?php 
                            $trackMap = ['operator' => 'Track A (Operator)', 'staff' => 'Track B (Staff)', 'supervisor_management' => 'Track C (Supervisor/Management)'];
                            echo $trackMap[$applicant['position_track']] ?? '-';
                            ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Aktivitas Saat Ini</div>
                        <div class="info-value"><?php echo htmlspecialchars($applicant['aktivitas_saat_ini'] ?? '-'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Pengalaman Relevan</div>
                        <div class="info-value"><?php echo htmlspecialchars($applicant['pengalaman_relevan'] ?? '-'); ?></div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">🎓 Riwayat Pendidikan</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Institusi</div>
                        <div class="info-value"><?php echo htmlspecialchars($applicant['pendidikan_institusi'] ?? '-'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jurusan</div>
                        <div class="info-value"><?php echo htmlspecialchars($applicant['pendidikan_jurusan'] ?? '-'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tahun Lulus</div>
                        <div class="info-value"><?php echo htmlspecialchars($applicant['pendidikan_tahun_lulus'] ?? '-'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Alasan Memilih Jurusan</div>
                        <div class="info-value"><?php echo htmlspecialchars($applicant['alasan_jurusan'] ?? '-'); ?></div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">📄 CV / Resume</h3>
                <?php if (!empty($applicant['cv_filename'])): 
                    $cvPath = '../uploads/' . $applicant['cv_filename'];
                    $cvExt = strtolower(pathinfo($applicant['cv_filename'], PATHINFO_EXTENSION));
                    $isImage = in_array($cvExt, ['jpg', 'jpeg', 'png', 'gif']);
                    $isPdf = $cvExt === 'pdf';
                ?>
                <div class="cv-preview-container">
                    <?php if ($isImage): ?>
                    <img src="<?php echo htmlspecialchars($cvPath); ?>" alt="CV Preview" class="cv-preview-image">
                    <?php elseif ($isPdf): ?>
                    <iframe src="<?php echo htmlspecialchars($cvPath); ?>" class="cv-preview-pdf" title="CV Preview"></iframe>
                    <?php else: ?>
                    <p style="color: var(--text-muted); margin-bottom: 16px;">Preview tidak tersedia untuk format file ini.</p>
                    <?php endif; ?>
                    
                    <div>
                        <a href="<?php echo htmlspecialchars($cvPath); ?>" 
                           download="<?php echo htmlspecialchars($applicant['cv_original_name'] ?? $applicant['cv_filename']); ?>"
                           class="cv-download-btn">
                            📥 Download CV (<?php echo htmlspecialchars($applicant['cv_original_name'] ?? $applicant['cv_filename']); ?>)
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 40px;">CV tidak tersedia.</p>
                <?php endif; ?>
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">⏱️ Waktu Pengerjaan</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Form Aplikasi</div>
                        <div class="info-value"><?php echo formatDuration($applicant['timer_form'] ?? 0); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Logic Test</div>
                        <div class="info-value"><?php echo formatDuration($applicant['timer_logic'] ?? 0); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Psychology Test</div>
                        <div class="info-value"><?php echo formatDuration($applicant['timer_psychology'] ?? 0); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Waktu</div>
                        <div class="info-value" style="color: var(--primary); font-weight: 600;">
                            <?php echo formatDuration($applicant['timer_total'] ?? 0); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Jawaban Form (Value & Adab) -->
        <div id="tab-form" class="tab-content">
            <div class="detail-section">
                <h3 class="detail-section-title">💡 Section D: Value & Cara Pandang Kerja</h3>
                
                <div class="form-group">
                    <div class="form-label">Arti bekerja dengan tanggung jawab menurut Anda:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['arti_tanggung_jawab'] ?? '-')); ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Ceritakan pengalaman kesalahan kerja dan pembelajaran yang didapat:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['cerita_kesalahan'] ?? '-')); ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Langkah yang diambil saat diberi target dengan arahan minim:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['langkah_target_minim_arahan'] ?? '-')); ?></div>
                </div>
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">🤝 Section E: Adab & Sikap Profesional</h3>
                
                <div class="form-group">
                    <div class="form-label">Arti adab dalam dunia kerja:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['arti_adab'] ?? '-')); ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Respon saat tidak sepakat dengan atasan:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['respon_tidak_sepakat'] ?? '-')); ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Cara menyampaikan kritik/ketidaksetujuan:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['cara_sampaikan_kritik'] ?? '-')); ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Pengalaman merasa diperlakukan tidak adil:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['pengalaman_tidak_adil'] ?? '-')); ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Prioritas: menyampaikan pendapat vs menjaga sikap:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['prioritas_pendapat_vs_sikap'] ?? '-')); ?></div>
                </div>
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">🌟 Section F: Motivasi & Kebermanfaatan</h3>
                
                <div class="form-group">
                    <div class="form-label">Alasan melamar di RayCorp:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['alasan_melamar'] ?? '-')); ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Harapan selain gaji:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['harapan_selain_gaji'] ?? '-')); ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Makna "bermanfaat" dalam bekerja:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['makna_bermanfaat'] ?? '-')); ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Apa yang membuat bertahan saat lelah:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['bertahan_saat_lelah'] ?? '-')); ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Respon jika tidak cocok dengan sistem kerja:</div>
                    <div class="textarea-display"><?php echo nl2br(htmlspecialchars($applicant['respon_tidak_cocok_sistem'] ?? '-')); ?></div>
                </div>
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">✅ Section G: Ketersediaan & Komitmen</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Bersedia Probation dengan Evaluasi</div>
                        <div class="info-value" style="color: <?php echo ($applicant['bersedia_probation'] ?? true) ? 'var(--success)' : 'var(--danger)'; ?>">
                            <?php echo ($applicant['bersedia_probation'] ?? true) ? '✓ Ya' : '✗ Tidak'; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Bersedia Menerima Feedback Jujur</div>
                        <div class="info-value" style="color: <?php echo ($applicant['bersedia_feedback'] ?? true) ? 'var(--success)' : 'var(--danger)'; ?>">
                            <?php echo ($applicant['bersedia_feedback'] ?? true) ? '✓ Ya' : '✗ Tidak'; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kapan Bisa Mulai Bekerja</div>
                        <div class="info-value">
                            <?php echo $applicant['kapan_mulai'] ? date('d F Y', strtotime($applicant['kapan_mulai'])) : '-'; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ekspektasi Gaji</div>
                        <div class="info-value">
                            <?php echo $applicant['ekspektasi_gaji'] ? 'Rp ' . number_format($applicant['ekspektasi_gaji'], 0, ',', '.') : '-'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Logic Test Results -->
        <div id="tab-logic" class="tab-content">
            <!-- Score Summary -->
            <div class="score-summary">
                <div class="score-summary-item">
                    <div class="score-summary-value" style="color: var(--primary);">
                        <?php echo (int)($applicant['logic_correct'] ?? 0); ?>/<?php echo (int)($applicant['logic_total'] ?? 25); ?>
                    </div>
                    <div class="score-summary-label">Jawaban Benar</div>
                </div>
                <div class="score-summary-item">
                    <div class="score-summary-value">
                        <?php echo (int)($applicant['logic_threshold'] ?? 17); ?>
                    </div>
                    <div class="score-summary-label">Threshold Posisi</div>
                </div>
                <div class="score-summary-item">
                    <?php 
                    $logicStatus = $applicant['logic_status'] ?? 'tidak_aman';
                    $statusColor = $logicStatus === 'aman' ? 'var(--success)' : ($logicStatus === 'rawan' ? 'var(--warning)' : 'var(--danger)');
                    ?>
                    <div class="score-summary-value" style="color: <?php echo $statusColor; ?>;">
                        <?php echo $logicStatusLabels[$logicStatus] ?? ucfirst($logicStatus); ?>
                    </div>
                    <div class="score-summary-label">Status</div>
                </div>
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">📊 Detail Jawaban per Section</h3>
                
                <?php
                // Section definitions
                $sections = [
                    'section_a' => ['title' => 'Section A: Logika Pola Angka', 'prefix' => 'A'],
                    'section_b' => ['title' => 'Section B: Logika Instruksi', 'prefix' => 'B'],
                    'section_c' => ['title' => 'Section C: Hitung Praktis', 'prefix' => 'C'],
                    'section_d' => ['title' => 'Section D: Ketelitian & Perbedaan', 'prefix' => 'D'],
                    'section_e' => ['title' => 'Section E: Logika Urutan Kerja', 'prefix' => 'E'],
                    'section_f' => ['title' => 'Section F: Logika Situasional', 'prefix' => 'F'],
                    'section_g' => ['title' => 'Section G: Pemahaman Sederhana', 'prefix' => 'G'],
                ];
                
                foreach ($sections as $sectionKey => $sectionInfo):
                    // Get questions for this section
                    $sectionQuestions = [];
                    foreach ($logicAnswerKeys as $qId => $answerData) {
                        if (strpos($qId, $sectionInfo['prefix']) === 0) {
                            $sectionQuestions[$qId] = $answerData;
                        }
                    }
                    
                    if (empty($sectionQuestions)) continue;
                    
                    // Calculate section score
                    $correctCount = 0;
                    $totalCount = count($sectionQuestions);
                    foreach ($sectionQuestions as $qId => $answerData) {
                        $userAnswer = strtoupper(trim($logicAnswers[$qId] ?? ''));
                        if ($userAnswer === $answerData['correct']) {
                            $correctCount++;
                        }
                    }
                    $scoreClass = $correctCount === $totalCount ? 'good' : ($correctCount >= $totalCount / 2 ? 'warning' : 'bad');
                ?>
                <div class="category-section">
                    <div class="category-header">
                        <span class="category-title"><?php echo $sectionInfo['title']; ?></span>
                        <span class="category-score <?php echo $scoreClass; ?>">
                            <?php echo $correctCount; ?>/<?php echo $totalCount; ?> benar
                        </span>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <?php foreach ($sectionQuestions as $qId => $answerData):
                            $userAnswer = strtoupper(trim($logicAnswers[$qId] ?? ''));
                            $isCorrect = $userAnswer === $answerData['correct'];
                        ?>
                        <div class="answer-item <?php echo $isCorrect ? 'correct' : 'incorrect'; ?>">
                            <span class="answer-question"><?php echo htmlspecialchars($qId); ?></span>
                            <div class="answer-values">
                                <span class="answer-given">
                                    <?php echo $isCorrect ? '✓' : '✗'; ?> 
                                    <?php echo htmlspecialchars($userAnswer ?: '-'); ?>
                                </span>
                                <?php if (!$isCorrect): ?>
                                <span class="answer-correct">
                                    Benar: <?php echo htmlspecialchars($answerData['correct']); ?>
                                    <span style="color: var(--text-muted);">(<?php echo htmlspecialchars($answerData['explanation']); ?>)</span>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($logicAnswers)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 40px;">
                    Tidak ada data jawaban logic test.
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab 4: Psychology Test Results -->
        <div id="tab-psychology" class="tab-content">
            <?php 
            $fitScore = $applicant['psychology_fit_score'] ?? 0;
            $fitClass = $fitScore >= 70 ? 'high' : ($fitScore >= 60 ? 'medium' : 'low');
            $pattern = $applicant['psychology_pattern'] ?? null;
            $mismatch = $applicant['psychology_pattern_mismatch'] ?? false;
            $expectedPattern = $applicant['expected_work_pattern'] ?? null;
            ?>
            
            <!-- Fit Score Display -->
            <div class="fit-score-display">
                <div class="fit-score-value <?php echo $fitClass; ?>">
                    <?php echo number_format($fitScore, 0); ?>%
                </div>
                <div class="fit-score-label">
                    Fit Score untuk posisi <?php echo $positionLabels[$applicant['position_applied']] ?? '-'; ?>
                </div>
            </div>
            
            <?php if ($mismatch): ?>
            <div class="mismatch-alert">
                <span class="icon">⚠️</span>
                <span class="text">
                    Pattern Mismatch Detected! Pola kerja kandidat (<?php echo $patternLabels[$pattern] ?? $pattern; ?>) 
                    tidak sesuai dengan yang diharapkan untuk posisi ini 
                    (<?php echo $patternLabels[$expectedPattern] ?? $expectedPattern ?? 'Flexible'; ?>).
                </span>
            </div>
            <?php endif; ?>
            
            <!-- Work Pattern Quadrant Visualization -->
            <div class="detail-section">
                <h3 class="detail-section-title">🎯 Work Pattern Profile</h3>
                
                <div class="quadrant-container">
                    <div>
                        <div class="quadrant-axis">← PRESISI | EKSPLORATIF →</div>
                        <div class="quadrant-grid">
                            <div class="quadrant-cell <?php echo $pattern === 'presisi_monoton' ? ($mismatch ? 'mismatch' : 'active') : ''; ?>">
                                <div class="quadrant-label">Presisi Monoton</div>
                                <div class="quadrant-positions">R&D Lab, QC, Produksi</div>
                                <?php if ($pattern === 'presisi_monoton'): ?>
                                <div style="margin-top: 8px; font-size: 20px;">●</div>
                                <?php endif; ?>
                            </div>
                            <div class="quadrant-cell <?php echo $pattern === 'eksploratif_terstruktur' ? ($mismatch ? 'mismatch' : 'active') : ''; ?>">
                                <div class="quadrant-label">Eksploratif Terstruktur</div>
                                <div class="quadrant-positions">Product Dev, R&D Konsep</div>
                                <?php if ($pattern === 'eksploratif_terstruktur'): ?>
                                <div style="margin-top: 8px; font-size: 20px;">●</div>
                                <?php endif; ?>
                            </div>
                            <div class="quadrant-cell <?php echo $pattern === 'presisi_dinamis' ? ($mismatch ? 'mismatch' : 'active') : ''; ?>">
                                <div class="quadrant-label">Presisi Dinamis</div>
                                <div class="quadrant-positions">Supervisor, Planner</div>
                                <?php if ($pattern === 'presisi_dinamis'): ?>
                                <div style="margin-top: 8px; font-size: 20px;">●</div>
                                <?php endif; ?>
                            </div>
                            <div class="quadrant-cell <?php echo $pattern === 'eksploratif_dinamis' ? ($mismatch ? 'mismatch' : 'active') : ''; ?>">
                                <div class="quadrant-label">Eksploratif Dinamis</div>
                                <div class="quadrant-positions">Kreatif, Branding</div>
                                <?php if ($pattern === 'eksploratif_dinamis'): ?>
                                <div style="margin-top: 8px; font-size: 20px;">●</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="quadrant-axis">↑ TERSTRUKTUR | DINAMIS ↓</div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 16px;">
                    <p style="color: var(--text-secondary);">
                        Pola Kerja Kandidat: 
                        <strong style="color: var(--primary);"><?php echo $patternLabels[$pattern] ?? $pattern ?? '-'; ?></strong>
                    </p>
                    <?php if ($applicant['psychology_placement_recommendation']): ?>
                    <p style="color: var(--text-muted); font-size: 13px; margin-top: 8px;">
                        <?php echo htmlspecialchars($applicant['psychology_placement_recommendation']); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Section Scores -->
            <div class="detail-section">
                <h3 class="detail-section-title">📈 Skor per Section</h3>
                <div class="info-grid">
                    <?php
                    $psychSections = [
                        'section_a' => ['label' => 'Section A: Ketelitian & Daya Tahan', 'field' => 'psychology_section_a_score'],
                        'section_b' => ['label' => 'Section B: Stabilitas & Respon Kejenuhan', 'field' => 'psychology_section_b_score'],
                        'section_c' => ['label' => 'Section C: Pola Respon Perubahan', 'field' => 'psychology_section_c_score'],
                        'section_d' => ['label' => 'Section D: Orientasi Kerja', 'field' => 'psychology_section_d_score'],
                        'section_e' => ['label' => 'Section E: Logika Kerja Dasar', 'field' => 'psychology_section_e_score'],
                    ];
                    
                    foreach ($psychSections as $key => $section):
                        $score = $applicant[$section['field']] ?? 0;
                        $percentage = ($score / 16) * 100;
                        $barClass = $percentage >= 80 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                    ?>
                    <div class="info-item">
                        <div class="info-label"><?php echo $section['label']; ?></div>
                        <div class="info-value"><?php echo number_format($score, 1); ?>/16</div>
                        <div style="background: var(--bg-secondary); border-radius: 4px; height: 8px; margin-top: 8px; overflow: hidden;">
                            <div style="background: var(--<?php echo $barClass; ?>); height: 100%; width: <?php echo min($percentage, 100); ?>%; transition: width 0.3s;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Alternative Positions -->
            <?php if (!empty($alternativePositions)): ?>
            <div class="detail-section">
                <h3 class="detail-section-title">🔄 Posisi Alternatif yang Lebih Cocok</h3>
                <div class="info-grid">
                    <?php 
                    $count = 0;
                    foreach ($alternativePositions as $pos):
                        if ($count >= 3) break;
                        $altFitScore = $pos['fitScore'] ?? 0;
                        $altFitClass = $altFitScore >= 70 ? 'var(--success)' : ($altFitScore >= 60 ? 'var(--warning)' : 'var(--danger)');
                    ?>
                    <div class="info-item">
                        <div class="info-label"><?php echo htmlspecialchars($pos['positionName'] ?? $pos['position']); ?></div>
                        <div class="info-value" style="color: <?php echo $altFitClass; ?>;">
                            Fit Score: <?php echo number_format($altFitScore, 0); ?>%
                            <?php if ($pos['patternMatch'] ?? false): ?>
                            <span style="color: var(--success);">✓ Pattern Match</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php 
                        $count++;
                    endforeach; 
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tab 5: HR Assessment -->
        <div id="tab-assessment" class="tab-content">
            <?php if ($hasRed): ?>
            <div class="alert warning">
                ⚠️ <strong>PERHATIAN:</strong> Terdapat indikator MERAH pada penilaian adab. 
                Sistem merekomendasikan keputusan <strong>STOP</strong> - skill tinggi tidak menebus adab buruk.
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="save_assessment">
                
                <!-- Rubrik Adab 6 Aspek (A-F) -->
                <div class="detail-section">
                    <h3 class="detail-section-title">⚖️ Rubrik Penilaian Adab (6 Aspek)</h3>
                    
                    <!-- Aspek A -->
                    <div class="form-section">
                        <div class="form-section-title">Aspek A: Cara Memandang Otoritas & Atasan</div>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
                            Bagaimana kandidat memandang dan merespon otoritas/atasan?
                        </p>
                        <div class="radio-group">
                            <label class="radio-option sehat">
                                <input type="radio" name="hr_adab_a_otoritas" value="sehat" 
                                    <?php echo ($applicant['hr_adab_a_otoritas'] ?? '') === 'sehat' ? 'checked' : ''; ?>>
                                Sehat
                            </label>
                            <label class="radio-option waspada">
                                <input type="radio" name="hr_adab_a_otoritas" value="waspada"
                                    <?php echo ($applicant['hr_adab_a_otoritas'] ?? '') === 'waspada' ? 'checked' : ''; ?>>
                                Waspada
                            </label>
                            <label class="radio-option tidak_cocok">
                                <input type="radio" name="hr_adab_a_otoritas" value="tidak_cocok"
                                    <?php echo ($applicant['hr_adab_a_otoritas'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                Tidak Cocok
                            </label>
                        </div>
                    </div>
                    
                    <!-- Aspek B -->
                    <div class="form-section">
                        <div class="form-section-title">Aspek B: Respon Terhadap Koreksi & Umpan Balik</div>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
                            Bagaimana kandidat merespon koreksi dan feedback?
                        </p>
                        <div class="radio-group">
                            <label class="radio-option sehat">
                                <input type="radio" name="hr_adab_b_koreksi" value="sehat"
                                    <?php echo ($applicant['hr_adab_b_koreksi'] ?? '') === 'sehat' ? 'checked' : ''; ?>>
                                Sehat
                            </label>
                            <label class="radio-option waspada">
                                <input type="radio" name="hr_adab_b_koreksi" value="waspada"
                                    <?php echo ($applicant['hr_adab_b_koreksi'] ?? '') === 'waspada' ? 'checked' : ''; ?>>
                                Waspada
                            </label>
                            <label class="radio-option tidak_cocok">
                                <input type="radio" name="hr_adab_b_koreksi" value="tidak_cocok"
                                    <?php echo ($applicant['hr_adab_b_koreksi'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                Tidak Cocok
                            </label>
                        </div>
                    </div>
                    
                    <!-- Aspek C -->
                    <div class="form-section">
                        <div class="form-section-title">Aspek C: Sikap Saat Tidak Sepakat</div>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
                            Bagaimana kandidat bersikap saat tidak setuju dengan keputusan?
                        </p>
                        <div class="radio-group">
                            <label class="radio-option sehat">
                                <input type="radio" name="hr_adab_c_tidak_sepakat" value="sehat"
                                    <?php echo ($applicant['hr_adab_c_tidak_sepakat'] ?? '') === 'sehat' ? 'checked' : ''; ?>>
                                Sehat
                            </label>
                            <label class="radio-option waspada">
                                <input type="radio" name="hr_adab_c_tidak_sepakat" value="waspada"
                                    <?php echo ($applicant['hr_adab_c_tidak_sepakat'] ?? '') === 'waspada' ? 'checked' : ''; ?>>
                                Waspada
                            </label>
                            <label class="radio-option tidak_cocok">
                                <input type="radio" name="hr_adab_c_tidak_sepakat" value="tidak_cocok"
                                    <?php echo ($applicant['hr_adab_c_tidak_sepakat'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                Tidak Cocok
                            </label>
                        </div>
                    </div>
                    
                    <!-- Aspek D -->
                    <div class="form-section">
                        <div class="form-section-title">Aspek D: Kesadaran Diri & Refleksi</div>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
                            Seberapa dalam kandidat mampu merefleksikan diri?
                        </p>
                        <div class="radio-group">
                            <label class="radio-option sehat">
                                <input type="radio" name="hr_adab_d_kesadaran_diri" value="sehat"
                                    <?php echo ($applicant['hr_adab_d_kesadaran_diri'] ?? '') === 'sehat' ? 'checked' : ''; ?>>
                                Sehat
                            </label>
                            <label class="radio-option waspada">
                                <input type="radio" name="hr_adab_d_kesadaran_diri" value="waspada"
                                    <?php echo ($applicant['hr_adab_d_kesadaran_diri'] ?? '') === 'waspada' ? 'checked' : ''; ?>>
                                Waspada
                            </label>
                            <label class="radio-option tidak_cocok">
                                <input type="radio" name="hr_adab_d_kesadaran_diri" value="tidak_cocok"
                                    <?php echo ($applicant['hr_adab_d_kesadaran_diri'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                Tidak Cocok
                            </label>
                        </div>
                    </div>
                    
                    <!-- Aspek E -->
                    <div class="form-section">
                        <div class="form-section-title">Aspek E: Kecocokan dengan Nilai RayCorp</div>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
                            Seberapa selaras nilai kandidat dengan nilai perusahaan?
                        </p>
                        <div class="radio-group">
                            <label class="radio-option sehat">
                                <input type="radio" name="hr_adab_e_kecocokan_nilai" value="sehat"
                                    <?php echo ($applicant['hr_adab_e_kecocokan_nilai'] ?? '') === 'sehat' ? 'checked' : ''; ?>>
                                Sehat
                            </label>
                            <label class="radio-option waspada">
                                <input type="radio" name="hr_adab_e_kecocokan_nilai" value="waspada"
                                    <?php echo ($applicant['hr_adab_e_kecocokan_nilai'] ?? '') === 'waspada' ? 'checked' : ''; ?>>
                                Waspada
                            </label>
                            <label class="radio-option tidak_cocok">
                                <input type="radio" name="hr_adab_e_kecocokan_nilai" value="tidak_cocok"
                                    <?php echo ($applicant['hr_adab_e_kecocokan_nilai'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                Tidak Cocok
                            </label>
                        </div>
                    </div>
                    
                    <!-- Aspek F: Spiritualitas Kerja (4 sub-aspek) -->
                    <div class="form-section" style="background: rgba(139, 92, 246, 0.1); border: 1px solid var(--accent);">
                        <div class="form-section-title" style="color: var(--accent);">
                            Aspek F: Value Kebermanfaatan & Spiritualitas Kerja
                        </div>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
                            Penilaian aspek spiritualitas kerja "lelah jadi lillah" - 4 sub-aspek
                        </p>
                        
                        <!-- F1 -->
                        <div class="form-group">
                            <div class="form-label">F1: Orientasi Niat Bekerja (amanah vs transaksional)</div>
                            <div class="radio-group">
                                <label class="radio-option sehat">
                                    <input type="radio" name="hr_adab_f1_orientasi_niat" value="sehat"
                                        <?php echo ($applicant['hr_adab_f1_orientasi_niat'] ?? '') === 'sehat' ? 'checked' : ''; ?>>
                                    Sehat
                                </label>
                                <label class="radio-option waspada">
                                    <input type="radio" name="hr_adab_f1_orientasi_niat" value="waspada"
                                        <?php echo ($applicant['hr_adab_f1_orientasi_niat'] ?? '') === 'waspada' ? 'checked' : ''; ?>>
                                    Waspada
                                </label>
                                <label class="radio-option tidak_cocok">
                                    <input type="radio" name="hr_adab_f1_orientasi_niat" value="tidak_cocok"
                                        <?php echo ($applicant['hr_adab_f1_orientasi_niat'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                    Tidak Cocok
                                </label>
                            </div>
                        </div>
                        
                        <!-- F2 -->
                        <div class="form-group">
                            <div class="form-label">F2: Respon Terhadap Lelah & Kesulitan (memaknai vs mengeluh)</div>
                            <div class="radio-group">
                                <label class="radio-option sehat">
                                    <input type="radio" name="hr_adab_f2_respon_lelah" value="sehat"
                                        <?php echo ($applicant['hr_adab_f2_respon_lelah'] ?? '') === 'sehat' ? 'checked' : ''; ?>>
                                    Sehat
                                </label>
                                <label class="radio-option waspada">
                                    <input type="radio" name="hr_adab_f2_respon_lelah" value="waspada"
                                        <?php echo ($applicant['hr_adab_f2_respon_lelah'] ?? '') === 'waspada' ? 'checked' : ''; ?>>
                                    Waspada
                                </label>
                                <label class="radio-option tidak_cocok">
                                    <input type="radio" name="hr_adab_f2_respon_lelah" value="tidak_cocok"
                                        <?php echo ($applicant['hr_adab_f2_respon_lelah'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                    Tidak Cocok
                                </label>
                            </div>
                        </div>
                        
                        <!-- F3 -->
                        <div class="form-group">
                            <div class="form-label">F3: Keikhlasan & Kerja Tanpa Sorotan</div>
                            <div class="radio-group">
                                <label class="radio-option sehat">
                                    <input type="radio" name="hr_adab_f3_keikhlasan" value="sehat"
                                        <?php echo ($applicant['hr_adab_f3_keikhlasan'] ?? '') === 'sehat' ? 'checked' : ''; ?>>
                                    Sehat
                                </label>
                                <label class="radio-option waspada">
                                    <input type="radio" name="hr_adab_f3_keikhlasan" value="waspada"
                                        <?php echo ($applicant['hr_adab_f3_keikhlasan'] ?? '') === 'waspada' ? 'checked' : ''; ?>>
                                    Waspada
                                </label>
                                <label class="radio-option tidak_cocok">
                                    <input type="radio" name="hr_adab_f3_keikhlasan" value="tidak_cocok"
                                        <?php echo ($applicant['hr_adab_f3_keikhlasan'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                    Tidak Cocok
                                </label>
                            </div>
                        </div>
                        
                        <!-- F4 -->
                        <div class="form-group">
                            <div class="form-label">F4: Keselarasan dengan Nilai Spiritual RayCorp</div>
                            <div class="radio-group">
                                <label class="radio-option sehat">
                                    <input type="radio" name="hr_adab_f4_spiritual" value="sehat"
                                        <?php echo ($applicant['hr_adab_f4_spiritual'] ?? '') === 'sehat' ? 'checked' : ''; ?>>
                                    Sehat
                                </label>
                                <label class="radio-option waspada">
                                    <input type="radio" name="hr_adab_f4_spiritual" value="waspada"
                                        <?php echo ($applicant['hr_adab_f4_spiritual'] ?? '') === 'waspada' ? 'checked' : ''; ?>>
                                    Waspada
                                </label>
                                <label class="radio-option tidak_cocok">
                                    <input type="radio" name="hr_adab_f4_spiritual" value="tidak_cocok"
                                        <?php echo ($applicant['hr_adab_f4_spiritual'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                    Tidak Cocok
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Assessment -->
                <div class="detail-section">
                    <h3 class="detail-section-title">📋 Ringkasan Penilaian</h3>
                    
                    <div class="info-grid">
                        <div class="form-group">
                            <div class="form-label">Value Fit</div>
                            <div class="radio-group">
                                <label class="radio-option sehat">
                                    <input type="radio" name="hr_value_fit" value="selaras"
                                        <?php echo ($applicant['hr_value_fit'] ?? '') === 'selaras' ? 'checked' : ''; ?>>
                                    Selaras
                                </label>
                                <label class="radio-option waspada">
                                    <input type="radio" name="hr_value_fit" value="abu_abu"
                                        <?php echo ($applicant['hr_value_fit'] ?? '') === 'abu_abu' ? 'checked' : ''; ?>>
                                    Abu-abu
                                </label>
                                <label class="radio-option tidak_cocok">
                                    <input type="radio" name="hr_value_fit" value="tidak_cocok"
                                        <?php echo ($applicant['hr_value_fit'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                    Tidak Cocok
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-label">Adab Fit</div>
                            <div class="radio-group">
                                <label class="radio-option sehat">
                                    <input type="radio" name="hr_adab_fit" value="selaras"
                                        <?php echo ($applicant['hr_adab_fit'] ?? '') === 'selaras' ? 'checked' : ''; ?>>
                                    Selaras
                                </label>
                                <label class="radio-option waspada">
                                    <input type="radio" name="hr_adab_fit" value="abu_abu"
                                        <?php echo ($applicant['hr_adab_fit'] ?? '') === 'abu_abu' ? 'checked' : ''; ?>>
                                    Abu-abu
                                </label>
                                <label class="radio-option tidak_cocok">
                                    <input type="radio" name="hr_adab_fit" value="tidak_cocok"
                                        <?php echo ($applicant['hr_adab_fit'] ?? '') === 'tidak_cocok' ? 'checked' : ''; ?>>
                                    Tidak Cocok
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Risk Note</label>
                        <textarea name="hr_risk_note" class="form-textarea" placeholder="Catatan risiko atau hal yang perlu diperhatikan..."><?php echo htmlspecialchars($applicant['hr_risk_note'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Catatan HR</label>
                        <textarea name="hr_notes" class="form-textarea" placeholder="Catatan tambahan dari HR..."><?php echo htmlspecialchars($applicant['hr_notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-label" style="font-weight: 600; font-size: 15px;">Keputusan HR</div>
                        <div class="radio-group" style="margin-top: 12px;">
                            <label class="radio-option sehat" style="padding: 12px 24px;">
                                <input type="radio" name="hr_decision" value="lanjut"
                                    <?php echo ($applicant['hr_decision'] ?? '') === 'lanjut' ? 'checked' : ''; ?>>
                                ✓ LANJUT
                            </label>
                            <label class="radio-option waspada" style="padding: 12px 24px;">
                                <input type="radio" name="hr_decision" value="hold"
                                    <?php echo ($applicant['hr_decision'] ?? '') === 'hold' ? 'checked' : ''; ?>>
                                ⏸ HOLD
                            </label>
                            <label class="radio-option tidak_cocok" style="padding: 12px 24px;">
                                <input type="radio" name="hr_decision" value="stop"
                                    <?php echo ($applicant['hr_decision'] ?? '') === 'stop' ? 'checked' : ''; ?>>
                                ✗ STOP
                            </label>
                        </div>
                    </div>
                    
                    <div style="margin-top: 24px;">
                        <button type="submit" class="btn btn-primary">💾 Simpan Penilaian HR</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab 6: Interview & Probation -->
        <div id="tab-interview" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="save_interview_probation">
                
                <!-- Interview HRD -->
                <div class="detail-section">
                    <h3 class="detail-section-title">👔 Interview HRD</h3>
                    
                    <div class="info-grid">
                        <div class="form-group">
                            <label class="form-label">Tanggal Interview HRD</label>
                            <input type="date" name="interview_hrd_date" class="form-input" 
                                value="<?php echo htmlspecialchars($applicant['interview_hrd_date'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Hasil Interview HRD</label>
                            <div class="radio-group">
                                <label class="radio-option sehat">
                                    <input type="radio" name="interview_hrd_result" value="lanjut"
                                        <?php echo ($applicant['interview_hrd_result'] ?? '') === 'lanjut' ? 'checked' : ''; ?>>
                                    Lanjut
                                </label>
                                <label class="radio-option waspada">
                                    <input type="radio" name="interview_hrd_result" value="hold"
                                        <?php echo ($applicant['interview_hrd_result'] ?? '') === 'hold' ? 'checked' : ''; ?>>
                                    Hold
                                </label>
                                <label class="radio-option tidak_cocok">
                                    <input type="radio" name="interview_hrd_result" value="stop"
                                        <?php echo ($applicant['interview_hrd_result'] ?? '') === 'stop' ? 'checked' : ''; ?>>
                                    Stop
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Catatan Interview HRD</label>
                        <textarea name="interview_hrd_notes" class="form-textarea" 
                            placeholder="Catatan dari interview HRD..."><?php echo htmlspecialchars($applicant['interview_hrd_notes'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Interview User -->
                <div class="detail-section">
                    <h3 class="detail-section-title">👥 Interview User (Department Head)</h3>
                    
                    <div class="info-grid">
                        <div class="form-group">
                            <label class="form-label">Tanggal Interview User</label>
                            <input type="date" name="interview_user_date" class="form-input" 
                                value="<?php echo htmlspecialchars($applicant['interview_user_date'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Hasil Interview User</label>
                            <div class="radio-group">
                                <label class="radio-option sehat">
                                    <input type="radio" name="interview_user_result" value="lanjut"
                                        <?php echo ($applicant['interview_user_result'] ?? '') === 'lanjut' ? 'checked' : ''; ?>>
                                    Lanjut
                                </label>
                                <label class="radio-option waspada">
                                    <input type="radio" name="interview_user_result" value="hold"
                                        <?php echo ($applicant['interview_user_result'] ?? '') === 'hold' ? 'checked' : ''; ?>>
                                    Hold
                                </label>
                                <label class="radio-option tidak_cocok">
                                    <input type="radio" name="interview_user_result" value="stop"
                                        <?php echo ($applicant['interview_user_result'] ?? '') === 'stop' ? 'checked' : ''; ?>>
                                    Stop
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Catatan Interview User</label>
                        <textarea name="interview_user_notes" class="form-textarea" 
                            placeholder="Catatan dari interview User/Department Head..."><?php echo htmlspecialchars($applicant['interview_user_notes'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Probation Tracking -->
                <div class="detail-section">
                    <h3 class="detail-section-title">📅 Probation Tracking (0-90 Hari)</h3>
                    
                    <div class="info-grid">
                        <div class="form-group">
                            <label class="form-label">Tanggal Mulai Probation</label>
                            <input type="date" name="probation_start_date" class="form-input" 
                                value="<?php echo htmlspecialchars($applicant['probation_start_date'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status Probation</label>
                            <select name="probation_status" class="form-select">
                                <option value="belum" <?php echo ($applicant['probation_status'] ?? 'belum') === 'belum' ? 'selected' : ''; ?>>
                                    Belum Mulai
                                </option>
                                <option value="0_14_hari" <?php echo ($applicant['probation_status'] ?? '') === '0_14_hari' ? 'selected' : ''; ?>>
                                    0-14 Hari (Adaptasi)
                                </option>
                                <option value="15_30_hari" <?php echo ($applicant['probation_status'] ?? '') === '15_30_hari' ? 'selected' : ''; ?>>
                                    15-30 Hari (Mulai Berdiri)
                                </option>
                                <option value="31_90_hari" <?php echo ($applicant['probation_status'] ?? '') === '31_90_hari' ? 'selected' : ''; ?>>
                                    31-90 Hari (Mulai Berdampak)
                                </option>
                                <option value="lulus" <?php echo ($applicant['probation_status'] ?? '') === 'lulus' ? 'selected' : ''; ?>>
                                    ✓ Lulus Probation
                                </option>
                                <option value="tidak_lulus" <?php echo ($applicant['probation_status'] ?? '') === 'tidak_lulus' ? 'selected' : ''; ?>>
                                    ✗ Tidak Lulus Probation
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Catatan Probation</label>
                        <textarea name="probation_notes" class="form-textarea" 
                            placeholder="Catatan evaluasi probation..."><?php echo htmlspecialchars($applicant['probation_notes'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Final Decision -->
                <div class="detail-section" style="background: var(--bg-secondary); border: 2px solid var(--primary);">
                    <h3 class="detail-section-title">🏆 Keputusan Final</h3>
                    
                    <div class="info-grid">
                        <div class="form-group">
                            <label class="form-label">Tanggal Keputusan Final</label>
                            <input type="date" name="final_decision_date" class="form-input" 
                                value="<?php echo htmlspecialchars($applicant['final_decision_date'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Keputusan Final</label>
                            <div class="radio-group">
                                <label class="radio-option sehat" style="padding: 12px 24px;">
                                    <input type="radio" name="final_decision" value="diterima"
                                        <?php echo ($applicant['final_decision'] ?? '') === 'diterima' ? 'checked' : ''; ?>>
                                    ✓ DITERIMA
                                </label>
                                <label class="radio-option waspada" style="padding: 12px 24px;">
                                    <input type="radio" name="final_decision" value="pending"
                                        <?php echo ($applicant['final_decision'] ?? 'pending') === 'pending' ? 'checked' : ''; ?>>
                                    ⏳ PENDING
                                </label>
                                <label class="radio-option tidak_cocok" style="padding: 12px 24px;">
                                    <input type="radio" name="final_decision" value="ditolak"
                                        <?php echo ($applicant['final_decision'] ?? '') === 'ditolak' ? 'checked' : ''; ?>>
                                    ✗ DITOLAK
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 24px;">
                        <button type="submit" class="btn btn-primary">💾 Simpan Data Interview & Probation</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Print Button -->
    <button class="print-btn" onclick="window.print()">🖨️ Cetak</button>

    <script>
        // Tab Navigation
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('active');
                });
                
                // Show selected tab
                document.getElementById('tab-' + tabName).classList.add('active');
                this.classList.add('active');
            });
        });
        
        // Auto-recommend STOP if red indicator detected
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                checkRedIndicators();
            });
        });
        
        function checkRedIndicators() {
            const adabFields = [
                'hr_adab_a_otoritas', 'hr_adab_b_koreksi', 'hr_adab_c_tidak_sepakat',
                'hr_adab_d_kesadaran_diri', 'hr_adab_e_kecocokan_nilai',
                'hr_adab_f1_orientasi_niat', 'hr_adab_f2_respon_lelah',
                'hr_adab_f3_keikhlasan', 'hr_adab_f4_spiritual'
            ];
            
            let hasRed = false;
            adabFields.forEach(field => {
                const selected = document.querySelector(`input[name="${field}"]:checked`);
                if (selected && selected.value === 'tidak_cocok') {
                    hasRed = true;
                }
            });
            
            if (hasRed) {
                // Auto-select STOP
                const stopRadio = document.querySelector('input[name="hr_decision"][value="stop"]');
                if (stopRadio && !stopRadio.checked) {
                    stopRadio.checked = true;
                    // Show alert
                    alert('⚠️ Indikator MERAH terdeteksi!\n\nSistem merekomendasikan keputusan STOP.\nSkill tinggi tidak menebus adab buruk.');
                }
            }
        }
        
        // Check on page load
        checkRedIndicators();
    </script>
</body>
</html>
