<?php
/**
 * Scoring Engine - Multi-Division Recruitment System
 * 
 * LOGIC TEST SCORING:
 * - 25 questions total (7 sections: A-G)
 * - Position-based passing thresholds
 * - Status: Aman / Rawan / Tidak Aman
 * 
 * PSYCHOLOGY TEST SCORING:
 * - 5 sections (A-E)
 * - Work Pattern identification (4 categories)
 * - Position-based weight matrix for Fit Score calculation
 * - Pattern mismatch detection
 * 
 * Requirements: 3.2, 3.3, 3.4, 3.5, 4.3, 4.4, 4.5, 4.6, 7.2, 7.3, 7.4, 7.5, 7.6, 8.1, 8.2, 8.5
 */

require_once __DIR__ . '/questions.php';
require_once __DIR__ . '/position_scoring_matrix.php';

/**
 * Logic Test Scorer
 * 
 * Scores 25 logic test questions with position-based thresholds.
 * Requirements: 3.2, 3.3, 3.4, 3.5, 7.2, 8.1
 */
class LogicScorer
{
    /**
     * Position-based passing thresholds
     * Requirement 7.2: Different thresholds per position
     */
    const THRESHOLD_MATRIX = [
        'operator_produksi'    => 12,  // ≥12/25 for operators
        'staff_kantor'         => 17,  // ≥17/25 for office staff
        'supervisor'           => 20,  // ≥20/25 for supervisors
        'rnd_qc_lab'           => 17,  // ≥17/25 for R&D/QC
        'kreatif'              => 14,  // ≥14/25 for creative
        'product_development'  => 17,  // ≥17/25 for product dev
        'management'           => 20,  // ≥20/25 for management
    ];

    /**
     * Status constants
     */
    const STATUS_AMAN = 'aman';
    const STATUS_RAWAN = 'rawan';
    const STATUS_TIDAK_AMAN = 'tidak_aman';

    /**
     * Total questions in logic test
     */
    const TOTAL_QUESTIONS = 25;

    private array $answerKeys;

    public function __construct()
    {
        $this->answerKeys = getLogicAnswerKeys();
    }

    /**
     * Calculate logic test score
     * 
     * @param array $answers User answers ['A1' => 'B', 'A2' => 'C', ...]
     * @param string $position Position code (e.g., 'operator_produksi')
     * @return array Score details including status based on position threshold
     */
    public function calculate(array $answers, string $position = 'staff_kantor'): array
    {
        $correctCount = 0;
        $details = [];
        $sectionScores = [];

        // Initialize section scores
        $sections = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        foreach ($sections as $section) {
            $sectionScores[$section] = ['correct' => 0, 'total' => 0];
        }

        // Check each answer
        foreach ($this->answerKeys as $questionId => $answerData) {
            $userAnswer = strtoupper(trim($answers[$questionId] ?? ''));
            $correctAnswer = $answerData['correct'];
            $isCorrect = $userAnswer === $correctAnswer;

            if ($isCorrect) {
                $correctCount++;
            }

            // Track section scores
            $section = substr($questionId, 0, 1);
            if (isset($sectionScores[$section])) {
                $sectionScores[$section]['total']++;
                if ($isCorrect) {
                    $sectionScores[$section]['correct']++;
                }
            }

            $details[$questionId] = [
                'answer' => $userAnswer,
                'correct' => $isCorrect,
                'correctAnswer' => $correctAnswer,
                'explanation' => $answerData['explanation']
            ];
        }

        // Get threshold and status based on position
        $threshold = $this->getThreshold($position);
        $status = $this->getStatus($correctCount, $position);
        $percentage = round(($correctCount / self::TOTAL_QUESTIONS) * 100, 1);

        return [
            'score' => $correctCount,
            'total' => self::TOTAL_QUESTIONS,
            'percentage' => $percentage,
            'threshold' => $threshold,
            'position' => $position,
            'status' => $status,
            'statusLabel' => $this->getStatusLabel($status),
            'sectionScores' => $sectionScores,
            'details' => $details,
            'passedThreshold' => $correctCount >= $threshold
        ];
    }

    /**
     * Get passing threshold for a position
     * 
     * @param string $position Position code
     * @return int Threshold value (default 17 if position not found)
     */
    public function getThreshold(string $position): int
    {
        return self::THRESHOLD_MATRIX[$position] ?? 17;
    }

    /**
     * Get status based on score and position threshold
     * 
     * Status logic:
     * - Aman: score >= threshold
     * - Rawan: score >= threshold - 2 AND score < threshold
     * - Tidak Aman: score < threshold - 2
     * 
     * @param int $correct Number of correct answers
     * @param string $position Position code
     * @return string Status (aman/rawan/tidak_aman)
     */
    public function getStatus(int $correct, string $position): string
    {
        $threshold = $this->getThreshold($position);
        
        if ($correct >= $threshold) {
            return self::STATUS_AMAN;
        } elseif ($correct >= $threshold - 2) {
            return self::STATUS_RAWAN;
        } else {
            return self::STATUS_TIDAK_AMAN;
        }
    }

    /**
     * Get human-readable status label
     * 
     * @param string $status Status code
     * @return string Status label in Indonesian
     */
    public function getStatusLabel(string $status): string
    {
        $labels = [
            self::STATUS_AMAN => 'Aman',
            self::STATUS_RAWAN => 'Rawan',
            self::STATUS_TIDAK_AMAN => 'Tidak Aman'
        ];
        return $labels[$status] ?? 'Unknown';
    }

    /**
     * Get all thresholds
     * 
     * @return array All position thresholds
     */
    public function getAllThresholds(): array
    {
        return self::THRESHOLD_MATRIX;
    }
}


/**
 * Psychology Test Scorer
 * 
 * Scores psychology test with 5 sections (A-E) and identifies work patterns.
 * Calculates Fit Score based on position weight matrix.
 * Requirements: 4.3, 4.4, 4.5, 4.6, 7.3, 7.4, 7.5, 7.6, 8.2, 8.5
 */
class PsychologyScorer
{
    /**
     * Work Pattern Constants
     */
    const PATTERN_PRESISI_MONOTON = 'presisi_monoton';
    const PATTERN_PRESISI_DINAMIS = 'presisi_dinamis';
    const PATTERN_EKSPLORATIF_TERSTRUKTUR = 'eksploratif_terstruktur';
    const PATTERN_EKSPLORATIF_DINAMIS = 'eksploratif_dinamis';

    /**
     * Position to expected pattern mapping
     * NULL means flexible (all patterns accepted)
     */
    const POSITION_PATTERN_MAP = [
        'operator_produksi'    => self::PATTERN_PRESISI_MONOTON,
        'staff_kantor'         => null,  // Flexible
        'supervisor'           => self::PATTERN_PRESISI_DINAMIS,
        'rnd_qc_lab'           => self::PATTERN_PRESISI_MONOTON,
        'kreatif'              => self::PATTERN_EKSPLORATIF_DINAMIS,
        'product_development'  => self::PATTERN_EKSPLORATIF_TERSTRUKTUR,
        'management'           => self::PATTERN_PRESISI_DINAMIS,
    ];

    /**
     * Position-Based Psychology Weight Matrix
     * Values: 0=tidak penting, 1=rendah, 2=sedang, 3=tinggi, 4=sangat tinggi
     * 
     * Sections:
     * - section_a: Ketelitian & Daya Tahan
     * - section_b: Stabilitas & Respon Kejenuhan
     * - section_c: Pola Respon Perubahan (Dinamis)
     * - section_d: Orientasi Kerja (Eksplorasi)
     * - section_e: Logika Kerja Dasar
     */
    const POSITION_WEIGHT_MATRIX = [
        'operator_produksi' => [
            'section_a' => 4,  // Ketelitian - sangat tinggi
            'section_b' => 4,  // Stabilitas - sangat tinggi
            'section_c' => 1,  // Dinamis - rendah
            'section_d' => 1,  // Eksplorasi - rendah
            'section_e' => 3,  // Logika Kerja - tinggi
        ],
        'staff_kantor' => [
            'section_a' => 2,  // Ketelitian - sedang
            'section_b' => 2,  // Stabilitas - sedang
            'section_c' => 2,  // Dinamis - sedang
            'section_d' => 2,  // Eksplorasi - sedang
            'section_e' => 3,  // Logika Kerja - tinggi
        ],
        'supervisor' => [
            'section_a' => 3,  // Ketelitian - tinggi
            'section_b' => 3,  // Stabilitas - tinggi
            'section_c' => 4,  // Dinamis - sangat tinggi
            'section_d' => 2,  // Eksplorasi - sedang
            'section_e' => 3,  // Logika Kerja - tinggi
        ],
        'rnd_qc_lab' => [
            'section_a' => 4,  // Ketelitian - sangat tinggi
            'section_b' => 4,  // Stabilitas - sangat tinggi
            'section_c' => 1,  // Dinamis - rendah
            'section_d' => 1,  // Eksplorasi - rendah
            'section_e' => 4,  // Logika Kerja - sangat tinggi
        ],
        'kreatif' => [
            'section_a' => 1,  // Ketelitian - rendah
            'section_b' => 1,  // Stabilitas - rendah
            'section_c' => 4,  // Dinamis - sangat tinggi
            'section_d' => 4,  // Eksplorasi - sangat tinggi
            'section_e' => 2,  // Logika Kerja - sedang
        ],
        'product_development' => [
            'section_a' => 2,  // Ketelitian - sedang
            'section_b' => 2,  // Stabilitas - sedang
            'section_c' => 3,  // Dinamis - tinggi
            'section_d' => 3,  // Eksplorasi - tinggi
            'section_e' => 3,  // Logika Kerja - tinggi
        ],
        'management' => [
            'section_a' => 2,  // Ketelitian - sedang
            'section_b' => 3,  // Stabilitas - tinggi
            'section_c' => 3,  // Dinamis - tinggi
            'section_d' => 2,  // Eksplorasi - sedang
            'section_e' => 4,  // Logika Kerja - sangat tinggi
        ],
    ];

    /**
     * Work Pattern Definitions
     */
    const WORK_PATTERN_DEFINITIONS = [
        self::PATTERN_PRESISI_MONOTON => [
            'name' => 'Presisi Monoton',
            'description' => 'Kerja presisi dengan rutinitas tinggi. Butuh ketelitian tinggi dan tahan rutinitas.',
            'suitable_positions' => ['operator_produksi', 'rnd_qc_lab'],
        ],
        self::PATTERN_PRESISI_DINAMIS => [
            'name' => 'Presisi Dinamis',
            'description' => 'Kerja presisi dengan adaptasi cepat. Butuh ketelitian + kemampuan koordinasi.',
            'suitable_positions' => ['supervisor', 'management'],
        ],
        self::PATTERN_EKSPLORATIF_TERSTRUKTUR => [
            'name' => 'Eksploratif Terstruktur',
            'description' => 'Kreativitas dalam sistem terstruktur. Butuh inovasi dengan framework jelas.',
            'suitable_positions' => ['product_development'],
        ],
        self::PATTERN_EKSPLORATIF_DINAMIS => [
            'name' => 'Eksploratif Dinamis',
            'description' => 'Kreativitas bebas dengan adaptasi tinggi. Butuh ide out-of-the-box.',
            'suitable_positions' => ['kreatif'],
        ],
    ];

    /**
     * Score thresholds for section categorization
     * Max score per section B-D: 16 (4 questions × 4 max)
     */
    const SCORE_THRESHOLDS = [
        'high' => 13,    // Score >= 13
        'medium' => 9,   // Score 9-12
        'low' => 0       // Score < 9
    ];

    /**
     * Section A accuracy thresholds (percentage)
     */
    const SECTION_A_THRESHOLDS = [
        'high' => 85,    // >= 85% accuracy
        'medium' => 65,  // 65-84% accuracy
        'low' => 0       // < 65% accuracy
    ];

    private array $psychologyQuestions;
    private array $scoringMatrix;

    public function __construct()
    {
        $this->psychologyQuestions = getPsychologyQuestions();
        $this->scoringMatrix = getPsychologyScoringMatrix();
    }

    /**
     * Calculate psychology test scores for all 5 sections
     * 
     * @param array $answers User answers for all sections
     * @return array Complete psychology test results
     */
    public function calculate(array $answers): array
    {
        $sectionScores = [];
        $details = [];

        // Calculate Section A (Ketelitian & Daya Tahan)
        $sectionAResult = $this->calculateSectionA($answers);
        $sectionScores['section_a'] = $sectionAResult['score'];
        $details['section_a'] = $sectionAResult;

        // Calculate Sections B-D (Multiple choice with scoring)
        foreach (['section_b', 'section_c', 'section_d'] as $section) {
            $result = $this->calculateMultipleChoiceSection($answers, $section);
            $sectionScores[$section] = $result['score'];
            $details[$section] = $result;
        }

        // Calculate Section E (Logika Kerja Dasar - correct/incorrect)
        $sectionEResult = $this->calculateSectionE($answers);
        $sectionScores['section_e'] = $sectionEResult['score'];
        $details['section_e'] = $sectionEResult;

        // Identify work pattern based on section scores
        $workPattern = $this->getWorkPattern($sectionScores);
        $patternDetails = self::WORK_PATTERN_DEFINITIONS[$workPattern] ?? null;

        return [
            'sectionScores' => $sectionScores,
            'workPattern' => $workPattern,
            'workPatternName' => $patternDetails['name'] ?? 'Unknown',
            'workPatternDescription' => $patternDetails['description'] ?? '',
            'details' => $details
        ];
    }


    /**
     * Calculate Section A: Ketelitian & Daya Tahan
     * Based on accuracy percentage of interactive tasks
     * 
     * @param array $answers Section A answers
     * @return array Section A results
     */
    private function calculateSectionA(array $answers): array
    {
        $totalCorrect = 0;
        $totalExpected = 0;
        $subTestResults = [];

        // Get Section A configuration
        $sectionA = $this->psychologyQuestions['section_a'] ?? null;
        if (!$sectionA || !isset($sectionA['sub_tests'])) {
            return ['score' => 0, 'accuracy' => 0, 'level' => 'low', 'subTests' => []];
        }

        foreach ($sectionA['sub_tests'] as $subTest) {
            $subTestId = $subTest['id'];
            $userAnswer = $answers[$subTestId] ?? [];
            
            // Calculate based on sub-test type
            $expected = 0;
            $correct = 0;
            
            if ($subTest['type'] === 'mark_target') {
                $expected = $subTest['correct_count'] ?? 0;
                $correct = min($userAnswer['marked_count'] ?? 0, $expected);
            } elseif ($subTest['type'] === 'mark_dual' || $subTest['type'] === 'mark_odd_even') {
                $expectedCircle = $subTest['correct_circle_count'] ?? 0;
                $expectedCross = $subTest['correct_cross_count'] ?? 0;
                $expected = $expectedCircle + $expectedCross;
                
                $correctCircle = min($userAnswer['circle_count'] ?? 0, $expectedCircle);
                $correctCross = min($userAnswer['cross_count'] ?? 0, $expectedCross);
                $correct = $correctCircle + $correctCross;
            }

            $totalCorrect += $correct;
            $totalExpected += $expected;

            $subTestResults[$subTestId] = [
                'title' => $subTest['title'],
                'correct' => $correct,
                'expected' => $expected,
                'accuracy' => $expected > 0 ? round(($correct / $expected) * 100, 1) : 0
            ];
        }

        // Calculate overall accuracy
        $accuracy = $totalExpected > 0 ? round(($totalCorrect / $totalExpected) * 100, 1) : 0;
        
        // Determine level based on accuracy
        $level = 'low';
        if ($accuracy >= self::SECTION_A_THRESHOLDS['high']) {
            $level = 'high';
        } elseif ($accuracy >= self::SECTION_A_THRESHOLDS['medium']) {
            $level = 'medium';
        }

        // Convert accuracy to 0-16 scale for consistency with other sections
        $score = round(($accuracy / 100) * 16, 2);

        return [
            'score' => $score,
            'accuracy' => $accuracy,
            'level' => $level,
            'totalCorrect' => $totalCorrect,
            'totalExpected' => $totalExpected,
            'subTests' => $subTestResults
        ];
    }

    /**
     * Calculate multiple choice sections (B, C, D)
     * 
     * @param array $answers All answers
     * @param string $sectionKey Section key (section_b, section_c, section_d)
     * @return array Section results
     */
    private function calculateMultipleChoiceSection(array $answers, string $sectionKey): array
    {
        $section = $this->psychologyQuestions[$sectionKey] ?? null;
        if (!$section || !isset($section['questions'])) {
            return ['score' => 0, 'level' => 'low', 'questions' => []];
        }

        $totalScore = 0;
        $maxScore = 0;
        $questionResults = [];

        foreach ($section['questions'] as $question) {
            $questionId = $question['id'];
            $userAnswer = strtoupper(trim($answers[$questionId] ?? ''));
            $scoring = $question['scoring'] ?? [];
            
            $score = $scoring[$userAnswer] ?? 0;
            $maxPossible = max(array_values($scoring));
            
            $totalScore += $score;
            $maxScore += $maxPossible;

            $questionResults[$questionId] = [
                'answer' => $userAnswer,
                'score' => $score,
                'maxScore' => $maxPossible
            ];
        }

        // Determine level
        $level = 'low';
        if ($totalScore >= self::SCORE_THRESHOLDS['high']) {
            $level = 'high';
        } elseif ($totalScore >= self::SCORE_THRESHOLDS['medium']) {
            $level = 'medium';
        }

        return [
            'score' => $totalScore,
            'maxScore' => $maxScore,
            'level' => $level,
            'questions' => $questionResults
        ];
    }

    /**
     * Calculate Section E: Logika Kerja Dasar
     * Correct/incorrect scoring
     * 
     * @param array $answers All answers
     * @return array Section E results
     */
    private function calculateSectionE(array $answers): array
    {
        $section = $this->psychologyQuestions['section_e'] ?? null;
        if (!$section || !isset($section['questions'])) {
            return ['score' => 0, 'level' => 'low', 'questions' => []];
        }

        $totalScore = 0;
        $correctCount = 0;
        $questionResults = [];

        foreach ($section['questions'] as $question) {
            $questionId = $question['id'];
            $userAnswer = strtoupper(trim($answers[$questionId] ?? ''));
            $correctAnswer = $question['correct_answer'] ?? '';
            $scoring = $question['scoring'] ?? ['correct' => 4, 'incorrect' => 0];
            
            $isCorrect = $userAnswer === $correctAnswer;
            $score = $isCorrect ? $scoring['correct'] : $scoring['incorrect'];
            
            $totalScore += $score;
            if ($isCorrect) {
                $correctCount++;
            }

            $questionResults[$questionId] = [
                'answer' => $userAnswer,
                'correct' => $isCorrect,
                'correctAnswer' => $correctAnswer,
                'score' => $score
            ];
        }

        // Max score: 6 questions × 4 = 24, normalize to 16 scale
        $normalizedScore = round(($totalScore / 24) * 16, 2);

        // Determine level
        $level = 'low';
        if ($normalizedScore >= self::SCORE_THRESHOLDS['high']) {
            $level = 'high';
        } elseif ($normalizedScore >= self::SCORE_THRESHOLDS['medium']) {
            $level = 'medium';
        }

        return [
            'score' => $normalizedScore,
            'rawScore' => $totalScore,
            'correctCount' => $correctCount,
            'totalQuestions' => count($section['questions']),
            'level' => $level,
            'questions' => $questionResults
        ];
    }


    /**
     * Identify work pattern based on section scores
     * 
     * Work Pattern Logic:
     * - Presisi_Monoton: High A & B, Low C & D
     * - Presisi_Dinamis: High A, Medium B, High C, Medium D
     * - Eksploratif_Terstruktur: Medium A & B, Medium C, High D
     * - Eksploratif_Dinamis: Low A & B, High C & D
     * 
     * @param array $scores Section scores
     * @return string Work pattern code
     */
    public function getWorkPattern(array $scores): string
    {
        // Get levels for each section
        $levelA = $this->getScoreLevel($scores['section_a'] ?? 0);
        $levelB = $this->getScoreLevel($scores['section_b'] ?? 0);
        $levelC = $this->getScoreLevel($scores['section_c'] ?? 0);
        $levelD = $this->getScoreLevel($scores['section_d'] ?? 0);

        // Pattern identification logic
        // Presisi Monoton: High precision (A), High stability (B), Low dynamism (C), Low exploration (D)
        if ($levelA === 'high' && $levelB === 'high' && $levelC !== 'high' && $levelD !== 'high') {
            return self::PATTERN_PRESISI_MONOTON;
        }

        // Presisi Dinamis: High/Medium precision (A), Medium+ stability (B), High dynamism (C)
        if ($levelA !== 'low' && $levelB !== 'low' && $levelC === 'high') {
            return self::PATTERN_PRESISI_DINAMIS;
        }

        // Eksploratif Dinamis: Low precision (A), Low stability (B), High dynamism (C), High exploration (D)
        if ($levelA !== 'high' && $levelB !== 'high' && $levelC === 'high' && $levelD === 'high') {
            return self::PATTERN_EKSPLORATIF_DINAMIS;
        }

        // Eksploratif Terstruktur: Medium precision, High exploration (D)
        if ($levelD === 'high' || ($levelD === 'medium' && $levelC !== 'low')) {
            return self::PATTERN_EKSPLORATIF_TERSTRUKTUR;
        }

        // Default: Based on dominant characteristics
        $precisionScore = ($scores['section_a'] ?? 0) + ($scores['section_b'] ?? 0);
        $explorationScore = ($scores['section_c'] ?? 0) + ($scores['section_d'] ?? 0);

        if ($precisionScore > $explorationScore) {
            return $levelC === 'high' ? self::PATTERN_PRESISI_DINAMIS : self::PATTERN_PRESISI_MONOTON;
        } else {
            return $levelA !== 'low' ? self::PATTERN_EKSPLORATIF_TERSTRUKTUR : self::PATTERN_EKSPLORATIF_DINAMIS;
        }
    }

    /**
     * Get score level (high/medium/low)
     * 
     * @param float $score Section score (0-16 scale)
     * @return string Level
     */
    private function getScoreLevel(float $score): string
    {
        if ($score >= self::SCORE_THRESHOLDS['high']) {
            return 'high';
        } elseif ($score >= self::SCORE_THRESHOLDS['medium']) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Get placement recommendation based on work pattern
     * 
     * @param string $pattern Work pattern code
     * @return string Placement recommendation text
     */
    public function getPlacementRecommendation(string $pattern): string
    {
        $recommendations = [
            self::PATTERN_PRESISI_MONOTON => 'Cocok untuk posisi yang membutuhkan ketelitian tinggi dan ketahanan pada tugas berulang seperti R&D Lab, QC, Operator Produksi.',
            self::PATTERN_PRESISI_DINAMIS => 'Cocok untuk posisi yang membutuhkan ketelitian dengan kemampuan adaptasi dan koordinasi seperti Supervisor, Management, Planner.',
            self::PATTERN_EKSPLORATIF_TERSTRUKTUR => 'Cocok untuk posisi yang membutuhkan kreativitas dalam kerangka terstruktur seperti Product Development, R&D konsep.',
            self::PATTERN_EKSPLORATIF_DINAMIS => 'Cocok untuk posisi yang membutuhkan kreativitas tinggi dan fleksibilitas seperti Kreatif, Branding, Campaign.',
        ];

        return $recommendations[$pattern] ?? 'Perlu evaluasi lebih lanjut untuk penempatan yang tepat.';
    }

    /**
     * Check if work pattern matches expected pattern for position
     * 
     * @param string $pattern Candidate's work pattern
     * @param string $position Position code
     * @return bool True if mismatch detected (alarm should be shown)
     */
    public function checkPatternMismatch(string $pattern, string $position): bool
    {
        $expectedPattern = self::POSITION_PATTERN_MAP[$position] ?? null;
        
        // If position is flexible (null), no mismatch
        if ($expectedPattern === null) {
            return false;
        }

        // Check if pattern matches expected
        return $pattern !== $expectedPattern;
    }

    /**
     * Calculate Fit Score (0-100%) based on position weight matrix
     * 
     * @param array $scores Section scores
     * @param string $position Position code
     * @return float Fit score percentage (0-100)
     */
    public function calculateFitScore(array $scores, string $position): float
    {
        $weights = self::POSITION_WEIGHT_MATRIX[$position] ?? self::POSITION_WEIGHT_MATRIX['staff_kantor'];
        
        $totalWeightedScore = 0;
        $totalWeight = 0;
        $maxScorePerSection = 16; // Max score per section

        foreach ($weights as $section => $weight) {
            $sectionScore = $scores[$section] ?? 0;
            
            // Calculate weighted contribution
            // Higher weight means the section is more important for this position
            $normalizedScore = ($sectionScore / $maxScorePerSection) * 100;
            $totalWeightedScore += $normalizedScore * $weight;
            $totalWeight += $weight * 100; // Max possible weighted score
        }

        // Calculate fit score as percentage
        $fitScore = $totalWeight > 0 ? ($totalWeightedScore / $totalWeight) * 100 : 0;
        
        return round($fitScore, 1);
    }

    /**
     * Get alternative positions that better match the candidate's work pattern
     * 
     * @param array $scores Section scores
     * @return array List of alternative positions with fit scores
     */
    public function getAlternativePositions(array $scores): array
    {
        $alternatives = [];
        $workPattern = $this->getWorkPattern($scores);

        foreach (self::POSITION_WEIGHT_MATRIX as $position => $weights) {
            $fitScore = $this->calculateFitScore($scores, $position);
            $expectedPattern = self::POSITION_PATTERN_MAP[$position];
            $patternMatch = ($expectedPattern === null || $expectedPattern === $workPattern);

            $alternatives[$position] = [
                'position' => $position,
                'positionName' => PositionScoringMatrix::getPositionName($position),
                'fitScore' => $fitScore,
                'patternMatch' => $patternMatch,
                'expectedPattern' => $expectedPattern,
                'recommendation' => $this->getFitRecommendation($fitScore, $patternMatch)
            ];
        }

        // Sort by fit score descending
        uasort($alternatives, function($a, $b) {
            return $b['fitScore'] <=> $a['fitScore'];
        });

        return $alternatives;
    }

    /**
     * Get fit recommendation based on score and pattern match
     * 
     * @param float $fitScore Fit score percentage
     * @param bool $patternMatch Whether pattern matches
     * @return string Recommendation
     */
    private function getFitRecommendation(float $fitScore, bool $patternMatch): string
    {
        if ($fitScore >= 70 && $patternMatch) {
            return 'Sangat Cocok';
        } elseif ($fitScore >= 60) {
            return $patternMatch ? 'Cocok' : 'Cocok dengan Catatan';
        } else {
            return 'Tidak Cocok';
        }
    }

    /**
     * Get complete psychology assessment for a position
     * 
     * @param array $answers All psychology test answers
     * @param string $position Position code
     * @return array Complete assessment results
     */
    public function getFullAssessment(array $answers, string $position): array
    {
        // Calculate base scores
        $result = $this->calculate($answers);
        $scores = $result['sectionScores'];
        $workPattern = $result['workPattern'];

        // Calculate fit score
        $fitScore = $this->calculateFitScore($scores, $position);

        // Check pattern mismatch
        $patternMismatch = $this->checkPatternMismatch($workPattern, $position);

        // Get alternative positions
        $alternatives = $this->getAlternativePositions($scores);

        // Get placement recommendation
        $placementRecommendation = $this->getPlacementRecommendation($workPattern);

        // Determine overall status
        $status = 'aman';
        if ($fitScore < 60 || $patternMismatch) {
            $status = $fitScore < 60 ? 'tidak_aman' : 'rawan';
        }

        return [
            'sectionScores' => $scores,
            'workPattern' => $workPattern,
            'workPatternName' => $result['workPatternName'],
            'workPatternDescription' => $result['workPatternDescription'],
            'fitScore' => $fitScore,
            'fitScoreLabel' => $this->getFitScoreLabel($fitScore),
            'patternMismatch' => $patternMismatch,
            'expectedPattern' => self::POSITION_PATTERN_MAP[$position],
            'placementRecommendation' => $placementRecommendation,
            'alternativePositions' => $alternatives,
            'status' => $status,
            'details' => $result['details']
        ];
    }

    /**
     * Get fit score label with color indication
     * 
     * @param float $fitScore Fit score percentage
     * @return array Label and color
     */
    private function getFitScoreLabel(float $fitScore): array
    {
        if ($fitScore >= 70) {
            return ['label' => 'Sangat Cocok', 'color' => 'green'];
        } elseif ($fitScore >= 60) {
            return ['label' => 'Cocok dengan Catatan', 'color' => 'yellow'];
        } else {
            return ['label' => 'Tidak Cocok', 'color' => 'red'];
        }
    }

    /**
     * Get all work pattern definitions
     * 
     * @return array All work pattern definitions
     */
    public function getAllWorkPatterns(): array
    {
        return self::WORK_PATTERN_DEFINITIONS;
    }

    /**
     * Get position weight matrix
     * 
     * @param string|null $position Position code (null for all)
     * @return array Weight matrix
     */
    public function getPositionWeights(?string $position = null): array
    {
        if ($position !== null) {
            return self::POSITION_WEIGHT_MATRIX[$position] ?? self::POSITION_WEIGHT_MATRIX['staff_kantor'];
        }
        return self::POSITION_WEIGHT_MATRIX;
    }
}


/**
 * Helper function to get score status label
 * 
 * @param float $score Score value
 * @return string Status label
 */
function getScoreStatus(float $score): string
{
    if ($score >= 8) {
        return 'LULUS';
    } elseif ($score >= 5) {
        return 'REVIEW';
    }
    return 'TIDAK LULUS';
}
