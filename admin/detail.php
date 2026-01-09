<?php
/**
 * Admin Detail Page - Enhanced Version
 * Complete applicant information with tabs
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

// Get answer keys for comparison
$answerKeys = getTechnicalAnswerKeys();
$psikotesSkenario = getPsikotesSkenario();
$psikotesStatements = getPsikotesStatements();

// Parse JSON fields safely
$technicalAnswers = [];
$technicalDetails = [];
$psikotesAnswers = [];
$psikotesDetails = [];

if (!empty($applicant['technical_answers'])) {
    $technicalAnswers = is_string($applicant['technical_answers']) 
        ? json_decode($applicant['technical_answers'], true) ?? []
        : $applicant['technical_answers'];
}

if (!empty($applicant['technical_details'])) {
    $technicalDetails = is_string($applicant['technical_details']) 
        ? json_decode($applicant['technical_details'], true) ?? []
        : $applicant['technical_details'];
}

if (!empty($applicant['psikotes_answers'])) {
    $psikotesAnswers = is_string($applicant['psikotes_answers']) 
        ? json_decode($applicant['psikotes_answers'], true) ?? []
        : $applicant['psikotes_answers'];
}

if (!empty($applicant['psikotes_details'])) {
    $psikotesDetails = is_string($applicant['psikotes_details']) 
        ? json_decode($applicant['psikotes_details'], true) ?? []
        : $applicant['psikotes_details'];
}

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
            gap: 8px;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
        }
        .tab-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid var(--border-color);
            background: var(--bg-input);
            color: var(--text-secondary);
            transition: all 0.2s;
        }
        .tab-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .tab-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
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
        
        /* Answer Items */
        .answer-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: var(--bg-input);
            border-radius: 8px;
            border-left: 3px solid var(--border-color);
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
        .answer-item.correct .answer-given {
            color: var(--success);
        }
        .answer-item.incorrect .answer-given {
            color: var(--danger);
        }
        .answer-correct {
            font-size: 12px;
            color: var(--text-muted);
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
        
        /* Score Summary */
        .score-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
        .category-score.good {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }
        .category-score.warning {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }
        .category-score.bad {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
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
                </div>
            </div>
            <div>
                <span class="status-badge <?php echo $applicant['status'] === 'LULUS' ? 'lulus' : 'tidak-lulus'; ?>" 
                      style="font-size: 16px; padding: 8px 20px;">
                    <?php echo htmlspecialchars($applicant['status']); ?>
                </span>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-nav">
            <button class="tab-btn active" onclick="showTab('overview')">Overview</button>
            <button class="tab-btn" onclick="showTab('technical')">Technical</button>
            <button class="tab-btn" onclick="showTab('psikotes')">Psikotes</button>
            <button class="tab-btn" onclick="showTab('cv')">CV</button>
        </div>

        <!-- Tab: Overview -->
        <div id="tab-overview" class="tab-content active">
            <!-- Score Summary -->
            <div class="score-summary">
                <div class="score-summary-item">
                    <div class="score-summary-value" style="color: <?php echo $applicant['status'] === 'LULUS' ? 'var(--success)' : 'var(--danger)'; ?>">
                        <?php echo number_format($applicant['overall_score'], 1); ?>
                    </div>
                    <div class="score-summary-label">Overall Score</div>
                </div>
                <div class="score-summary-item">
                    <div class="score-summary-value" style="color: var(--primary);">
                        <?php echo number_format($applicant['technical_score'], 1); ?>
                    </div>
                    <div class="score-summary-label">Technical (70%)</div>
                </div>
                <div class="score-summary-item">
                    <div class="score-summary-value" style="color: #a78bfa;">
                        <?php echo number_format($applicant['psikotes_score'], 1); ?>
                    </div>
                    <div class="score-summary-label">Psikotes (30%)</div>
                </div>
            </div>

            <div class="detail-grid">
                <!-- Contact Info -->
                <div class="detail-section">
                    <h3 class="detail-section-title">📞 Kontak</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($applicant['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">WhatsApp</div>
                            <div class="info-value">
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $applicant['whatsapp']); ?>" 
                                   target="_blank" style="color: #25d366; text-decoration: none;">
                                    <?php echo htmlspecialchars($applicant['whatsapp']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timer Info -->
                <div class="detail-section">
                    <h3 class="detail-section-title">⏱️ Waktu Pengerjaan</h3>
                    <div class="timer-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                        <div class="timer-item" style="background: var(--bg-input); border-radius: 8px; padding: 12px; text-align: center;">
                            <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Data Diri</p>
                            <p style="font-weight: 600; color: var(--text-primary);"><?php echo formatDurationShort($applicant['timer_personal'] ?? 0); ?></p>
                        </div>
                        <div class="timer-item" style="background: var(--bg-input); border-radius: 8px; padding: 12px; text-align: center;">
                            <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Technical</p>
                            <p style="font-weight: 600; color: var(--text-primary);"><?php echo formatDurationShort($applicant['timer_technical'] ?? 0); ?></p>
                        </div>
                        <div class="timer-item" style="background: var(--bg-input); border-radius: 8px; padding: 12px; text-align: center;">
                            <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Psikotes</p>
                            <p style="font-weight: 600; color: var(--text-primary);"><?php echo formatDurationShort($applicant['timer_psikotes'] ?? 0); ?></p>
                        </div>
                        <div class="timer-item" style="background: rgba(59, 130, 246, 0.2); border-radius: 8px; padding: 12px; text-align: center;">
                            <p style="font-size: 11px; color: var(--primary-light); margin-bottom: 4px;">Total</p>
                            <p style="font-weight: 700; color: var(--text-primary);"><?php echo formatDurationShort($applicant['timer_total'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Breakdown -->
            <div class="detail-section">
                <h3 class="detail-section-title">💻 Technical: <?php echo number_format($applicant['technical_score'], 1); ?>/10</h3>
                <div class="info-grid">
                    <?php
                    $technicalCategories = [
                        ['key' => 'technical_php_laravel', 'label' => 'PHP/Laravel (35%)'],
                        ['key' => 'technical_mysql_git', 'label' => 'MySQL & Git (25%)'],
                        ['key' => 'technical_problem_solving', 'label' => 'Problem Solving (25%)'],
                        ['key' => 'technical_ai_automation', 'label' => 'AI/Automation (15%)'],
                    ];
                    foreach ($technicalCategories as $cat):
                        $score = $applicant[$cat['key']] ?? 0;
                        $barClass = $score >= 70 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
                    ?>
                    <div class="info-item">
                        <div class="info-label"><?php echo $cat['label']; ?></div>
                        <div class="info-value"><?php echo number_format($score, 0); ?>%</div>
                        <div class="score-bar">
                            <div class="score-bar-fill <?php echo $barClass; ?>" style="width: <?php echo min($score, 100); ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p style="margin-top: 12px; text-align: right;">
                    <a href="#" onclick="showTab('technical'); return false;" style="color: var(--primary); text-decoration: none; font-size: 13px;">
                        Lihat Detail Jawaban →
                    </a>
                </p>
            </div>

            <!-- Psikotes Breakdown -->
            <div class="detail-section">
                <h3 class="detail-section-title">🧠 Psikotes: <?php echo number_format($applicant['psikotes_score'], 1); ?>/10</h3>
                <div class="info-grid">
                    <?php
                    $psikotesCategories = [
                        ['key' => 'psikotes_multi_project', 'label' => 'Multi-Project'],
                        ['key' => 'psikotes_learning', 'label' => 'Learning'],
                        ['key' => 'psikotes_initiative', 'label' => 'Initiative'],
                        ['key' => 'psikotes_team', 'label' => 'Team'],
                        ['key' => 'psikotes_change', 'label' => 'Change'],
                    ];
                    foreach ($psikotesCategories as $cat):
                        $score = $applicant[$cat['key']] ?? 0;
                        $percentage = ($score / 10) * 100;
                        $barClass = $score >= 7 ? 'success' : ($score >= 5 ? 'warning' : 'danger');
                    ?>
                    <div class="info-item">
                        <div class="info-label"><?php echo $cat['label']; ?></div>
                        <div class="info-value"><?php echo number_format($score, 1); ?>/10</div>
                        <div class="score-bar">
                            <div class="score-bar-fill <?php echo $barClass; ?>" style="width: <?php echo $percentage; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p style="margin-top: 12px; text-align: right;">
                    <a href="#" onclick="showTab('psikotes'); return false;" style="color: var(--primary); text-decoration: none; font-size: 13px;">
                        Lihat Detail Jawaban →
                    </a>
                </p>
            </div>

            <!-- Metadata -->
            <div class="detail-section">
                <h3 class="detail-section-title">ℹ️ Informasi</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">ID Pelamar</div>
                        <div class="info-value" style="font-family: monospace; font-size: 12px;">
                            <?php echo htmlspecialchars($applicant['id']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tanggal Daftar</div>
                        <div class="info-value">
                            <?php 
                            $date = new DateTime($applicant['created_at']);
                            echo $date->format('d F Y, H:i'); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Technical -->
        <div id="tab-technical" class="tab-content">
            <div class="detail-section">
                <h3 class="detail-section-title">💻 Detail Jawaban Technical Test</h3>
                
                <?php
                // Group questions by category
                $categories = [
                    'php_laravel' => ['title' => 'PHP/Laravel (35%)', 'prefix' => 'soal1', 'questions' => []],
                    'mysql' => ['title' => 'MySQL (bagian dari 25%)', 'prefix' => 'soal2', 'questions' => []],
                    'git' => ['title' => 'Git (bagian dari 25%)', 'prefix' => 'soal3', 'questions' => []],
                    'problem_solving' => ['title' => 'Problem Solving (25%)', 'prefix' => 'soal4', 'questions' => []],
                    'ai_automation' => ['title' => 'AI & Automation (15%)', 'prefix' => 'soal5', 'questions' => []],
                ];
                
                // Organize answers by category
                foreach ($technicalAnswers as $qId => $answer) {
                    foreach ($categories as $catKey => &$cat) {
                        if (strpos($qId, $cat['prefix']) === 0) {
                            $cat['questions'][$qId] = $answer;
                            break;
                        }
                    }
                }
                
                foreach ($categories as $catKey => $cat):
                    if (empty($cat['questions'])) continue;
                    
                    $correctCount = 0;
                    $totalCount = count($cat['questions']);
                    foreach ($cat['questions'] as $qId => $answer) {
                        $correctAnswer = $answerKeys[$qId]['correct'] ?? '';
                        if (strtoupper($answer) === strtoupper($correctAnswer)) {
                            $correctCount++;
                        }
                    }
                    $catScore = $totalCount > 0 ? round(($correctCount / $totalCount) * 10, 1) : 0;
                    $scoreClass = $catScore >= 7 ? 'good' : ($catScore >= 5 ? 'warning' : 'bad');
                ?>
                <div class="category-section">
                    <div class="category-header">
                        <span class="category-title"><?php echo $cat['title']; ?></span>
                        <span class="category-score <?php echo $scoreClass; ?>">
                            <?php echo $correctCount; ?>/<?php echo $totalCount; ?> benar (<?php echo $catScore; ?>/10)
                        </span>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <?php foreach ($cat['questions'] as $qId => $givenAnswer):
                            $correctAnswer = $answerKeys[$qId]['correct'] ?? '-';
                            $isCorrect = strtoupper($givenAnswer) === strtoupper($correctAnswer);
                            $explanation = $answerKeys[$qId]['explanation'] ?? '';
                        ?>
                        <div class="answer-item <?php echo $isCorrect ? 'correct' : 'incorrect'; ?>">
                            <span class="answer-question"><?php echo htmlspecialchars($qId); ?></span>
                            <div class="answer-values">
                                <span class="answer-given">
                                    <?php echo $isCorrect ? '✓' : '✗'; ?> 
                                    <?php echo htmlspecialchars($givenAnswer); ?>
                                </span>
                                <?php if (!$isCorrect): ?>
                                <span class="answer-correct">
                                    Benar: <?php echo htmlspecialchars($correctAnswer); ?>
                                    <?php if ($explanation): ?>
                                    <span style="color: var(--text-muted);">(<?php echo htmlspecialchars($explanation); ?>)</span>
                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($technicalAnswers)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 40px;">
                    Tidak ada data jawaban technical test.
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: Psikotes -->
        <div id="tab-psikotes" class="tab-content">
            <div class="detail-section">
                <h3 class="detail-section-title">🧠 Detail Jawaban Psikotes</h3>
                
                <!-- Scenario Answers -->
                <h4 style="color: var(--primary-light); font-size: 1rem; margin-bottom: 16px;">Bagian A: Situational Judgment</h4>
                
                <?php 
                $scenarioAnswers = $psikotesAnswers['scenarios'] ?? $psikotesAnswers;
                foreach ($psikotesSkenario as $scenario):
                    $givenAnswer = $scenarioAnswers[$scenario['id']] ?? '-';
                    $idealAnswer = $scenario['idealAnswer'] ?? '';
                    $score = $scenario['scoring'][$givenAnswer] ?? 0;
                    $isIdeal = strtoupper($givenAnswer) === strtoupper($idealAnswer);
                    
                    // Find the label for the given answer
                    $answerLabel = '';
                    foreach ($scenario['options'] as $opt) {
                        if ($opt['value'] === $givenAnswer) {
                            $answerLabel = $opt['label'];
                            break;
                        }
                    }
                ?>
                <div style="padding: 16px; background: var(--bg-input); border-radius: 8px; margin-bottom: 12px; border-left: 3px solid <?php echo $isIdeal ? 'var(--success)' : 'var(--warning)'; ?>;">
                    <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">
                        <?php echo htmlspecialchars($scenario['title']); ?>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
                        <?php echo htmlspecialchars($scenario['description']); ?>
                    </div>
                    <div style="display: flex; gap: 20px; font-size: 14px; flex-wrap: wrap;">
                        <span>
                            <strong>Jawaban:</strong> 
                            <span style="color: <?php echo $isIdeal ? 'var(--success)' : 'var(--warning)'; ?>;">
                                <?php echo htmlspecialchars($givenAnswer); ?>. <?php echo htmlspecialchars($answerLabel); ?>
                            </span>
                        </span>
                        <span>
                            <strong>Skor:</strong> <?php echo $score; ?>/5
                        </span>
                        <?php if (!$isIdeal): ?>
                        <span style="color: var(--text-muted);">
                            (Ideal: <?php echo htmlspecialchars($idealAnswer); ?>)
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Statement Ratings -->
                <h4 style="color: var(--primary-light); font-size: 1rem; margin: 32px 0 16px;">Bagian B: Self-Assessment</h4>
                
                <?php 
                $statementRatings = $psikotesAnswers['statements'] ?? [];
                if (!empty($statementRatings)):
                ?>
                <div class="info-grid">
                    <?php foreach ($psikotesStatements as $statement): 
                        $rating = $statementRatings[$statement['id']] ?? 0;
                        $barClass = $rating >= 4 ? 'success' : ($rating >= 3 ? 'warning' : 'danger');
                    ?>
                    <div class="info-item">
                        <div class="info-label" style="font-size: 12px;"><?php echo htmlspecialchars($statement['text']); ?></div>
                        <div class="info-value"><?php echo $rating; ?>/5</div>
                        <div class="score-bar">
                            <div class="score-bar-fill <?php echo $barClass; ?>" style="width: <?php echo ($rating / 5) * 100; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color: var(--text-muted);">Tidak ada data rating pernyataan.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: CV -->
        <div id="tab-cv" class="tab-content">
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
                    <img src="<?php echo htmlspecialchars($cvPath); ?>" 
                         alt="CV Preview" 
                         class="cv-preview-image">
                    <?php elseif ($isPdf): ?>
                    <iframe src="<?php echo htmlspecialchars($cvPath); ?>" 
                            class="cv-preview-pdf"
                            title="CV Preview"></iframe>
                    <?php else: ?>
                    <p style="color: var(--text-muted); margin-bottom: 16px;">
                        Preview tidak tersedia untuk format file ini.
                    </p>
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
                <p style="color: var(--text-muted); text-align: center; padding: 40px;">
                    CV tidak tersedia.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
            
            // Update button states
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.textContent.toLowerCase().includes(tabName.toLowerCase()) || 
                    (tabName === 'overview' && btn.textContent === 'Overview')) {
                    btn.classList.add('active');
                }
            });
        }
        
        // Handle tab clicks properly
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabName = this.textContent.toLowerCase();
                showTab(tabName);
            });
        });
    </script>
</body>
</html>
