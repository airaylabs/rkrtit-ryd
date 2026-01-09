<?php
/**
 * Scoring Logic - FINAL OPTIMIZED VERSION
 * 
 * STRUKTUR:
 * - Technical: 5 soal (70% bobot)
 * - Psikotes: 3 skenario (30% bobot)
 * 
 * KALKULASI TECHNICAL (70%):
 * - 5 soal, setiap soal benar = 2 poin
 * - Max score = 5 × 2 = 10
 * - Kontribusi ke overall = score × 0.70
 * 
 * KALKULASI PSIKOTES (30%):
 * - 3 skenario, setiap skenario scoring 1-5
 * - Rata-rata × 2 = score (0-10 scale)
 * - Kontribusi ke overall = score × 0.30
 * 
 * OVERALL:
 * - (Technical × 70%) + (Psikotes × 30%) = 0-10 scale
 * - Bagus: 8-10 (LULUS)
 * - Butuh Review: 5-7 (REVIEW)
 * - Belum Lulus: <5 (TIDAK LULUS)
 */

require_once __DIR__ . '/questions.php';

/**
 * Technical Test Scorer
 * 5 soal × 2 poin = max 10
 */
class TechnicalScorer
{
    private array $answerKeys;
    private const POINTS_PER_QUESTION = 2;
    private const TOTAL_QUESTIONS = 5;

    public function __construct()
    {
        $this->answerKeys = getTechnicalAnswerKeys();
    }

    /**
     * Calculate technical test score
     * @param array $answers User answers ['tech1a' => 'A', 'tech1b' => 'B', ...]
     * @return array Score details
     */
    public function calculate(array $answers): array
    {
        $correctCount = 0;
        $details = [];

        // Check each answer
        foreach ($this->answerKeys as $questionId => $answerData) {
            $userAnswer = strtoupper(trim($answers[$questionId] ?? ''));
            $correctAnswer = $answerData['correct'];
            $isCorrect = $userAnswer === $correctAnswer;

            if ($isCorrect) {
                $correctCount++;
            }

            $details[$questionId] = [
                'answer' => $userAnswer,
                'correct' => $isCorrect,
                'correctAnswer' => $correctAnswer,
                'explanation' => $answerData['explanation']
            ];
        }

        // Calculate score: correctCount × 2 = score (max 10)
        $score = $correctCount * self::POINTS_PER_QUESTION;
        
        // Ensure max is 10
        $score = min($score, 10);

        return [
            'score' => round($score, 1),
            'correctCount' => $correctCount,
            'totalQuestions' => self::TOTAL_QUESTIONS,
            'pointsPerQuestion' => self::POINTS_PER_QUESTION,
            'details' => $details,
            'status' => getScoreStatus($score)
        ];
    }
}

/**
 * Psikotes Scorer
 * 3 skenario, scoring 1-5 per skenario
 * Rata-rata × 2 = score (0-10 scale)
 */
class PsikotesScorer
{
    private array $scenarios;
    private const TOTAL_SCENARIOS = 3;

    public function __construct()
    {
        $this->scenarios = getPsikotesSkenario();
    }

    /**
     * Calculate psikotes score
     * @param array $answers User answers ['psi1' => 'C', 'psi2' => 'B', ...]
     * @return array Score details
     */
    public function calculate(array $answers): array
    {
        $totalRawScore = 0;
        $details = [];
        $categoryScores = [];

        foreach ($this->scenarios as $scenario) {
            $scenarioId = $scenario['id'];
            $category = $scenario['category'];
            $userAnswer = strtoupper(trim($answers[$scenarioId] ?? ''));
            
            // Get score from scoring matrix (1-5), default 3 if not answered
            $rawScore = $scenario['scoring'][$userAnswer] ?? 3;
            $totalRawScore += $rawScore;
            
            // Convert to 0-10 scale for display: (rawScore / 5) × 10 = rawScore × 2
            $scaledScore = $rawScore * 2;
            $categoryScores[$category] = $scaledScore;

            $details[$scenarioId] = [
                'answer' => $userAnswer,
                'rawScore' => $rawScore,
                'scaledScore' => $scaledScore,
                'category' => $category
            ];
        }

        // Calculate final score: (totalRawScore / totalScenarios) × 2 = average × 2
        // This gives us 0-10 scale
        $averageRaw = $totalRawScore / self::TOTAL_SCENARIOS;
        $score = $averageRaw * 2;
        $score = round(min($score, 10), 1);

        // Generate feedback
        $feedback = $this->generateFeedback($score);

        return [
            'score' => $score,
            'averageRaw' => round($averageRaw, 2),
            'totalRawScore' => $totalRawScore,
            'totalScenarios' => self::TOTAL_SCENARIOS,
            'categories' => $categoryScores,
            'details' => $details,
            'feedback' => $feedback,
            'status' => getScoreStatus($score)
        ];
    }

    private function generateFeedback(float $score): string
    {
        if ($score >= 8) {
            return 'Excellent! Sangat cocok dengan budaya kerja RayCorp.';
        } elseif ($score >= 6) {
            return 'Good fit. Cocok dengan beberapa area pengembangan.';
        } elseif ($score >= 5) {
            return 'Moderate. Perlu diskusi lebih lanjut.';
        } else {
            return 'Perlu evaluasi lebih lanjut.';
        }
    }
}

/**
 * Overall Score Calculator
 * Technical: 70%, Psikotes: 30%
 * 
 * FORMULA:
 * Overall = (Technical Score × 0.70) + (Psikotes Score × 0.30)
 * 
 * CONTOH:
 * - Technical: 10/10 × 0.70 = 7.0
 * - Psikotes: 10/10 × 0.30 = 3.0
 * - Overall: 7.0 + 3.0 = 10.0 ✓
 * 
 * - Technical: 8/10 × 0.70 = 5.6
 * - Psikotes: 8/10 × 0.30 = 2.4
 * - Overall: 5.6 + 2.4 = 8.0 ✓
 */
class OverallScorer
{
    private const TECHNICAL_WEIGHT = 0.70;  // 70%
    private const PSIKOTES_WEIGHT = 0.30;   // 30%
    
    private const THRESHOLD_LULUS = 8;      // >= 8: LULUS
    private const THRESHOLD_REVIEW = 5;     // >= 5: REVIEW

    /**
     * Calculate overall score
     * @param float $technicalScore Technical score (0-10)
     * @param float $psikotesScore Psikotes score (0-10)
     * @return array Overall result
     */
    public static function calculate(float $technicalScore, float $psikotesScore): array
    {
        // Calculate weighted score
        $technicalContribution = $technicalScore * self::TECHNICAL_WEIGHT;
        $psikotesContribution = $psikotesScore * self::PSIKOTES_WEIGHT;
        
        $overallScore = round($technicalContribution + $psikotesContribution, 1);
        
        // Ensure max is 10
        $overallScore = min($overallScore, 10);
        
        // Determine status
        if ($overallScore >= self::THRESHOLD_LULUS) {
            $status = 'LULUS';
            $statusLabel = 'Bagus';
            $recommendation = 'Lanjut ke tahap interview';
        } elseif ($overallScore >= self::THRESHOLD_REVIEW) {
            $status = 'REVIEW';
            $statusLabel = 'Butuh Review';
            $recommendation = 'Perlu review manual oleh HR/Manager';
        } else {
            $status = 'TIDAK LULUS';
            $statusLabel = 'Belum Lulus';
            $recommendation = 'Belum memenuhi kriteria minimum';
        }

        return [
            'overallScore' => $overallScore,
            'technicalScore' => $technicalScore,
            'psikotesScore' => $psikotesScore,
            'technicalContribution' => round($technicalContribution, 2),
            'psikotesContribution' => round($psikotesContribution, 2),
            'status' => $status,
            'statusLabel' => $statusLabel,
            'recommendation' => $recommendation,
            'weights' => [
                'technical' => (self::TECHNICAL_WEIGHT * 100) . '%',
                'psikotes' => (self::PSIKOTES_WEIGHT * 100) . '%'
            ],
            'thresholds' => [
                'lulus' => self::THRESHOLD_LULUS,
                'review' => self::THRESHOLD_REVIEW
            ]
        ];
    }
}
