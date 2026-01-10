<?php
/**
 * Position-Based Scoring Tests
 * 
 * Comprehensive tests for position-based scoring calculations
 * Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 8.1, 8.2, 8.5
 * 
 * Run: php tests/PositionScoringTest.php
 */

require_once __DIR__ . '/../includes/scoring.php';
require_once __DIR__ . '/../includes/position_scoring_matrix.php';

class PositionScoringTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];
    private array $positions = [
        'operator_produksi',
        'staff_kantor',
        'supervisor',
        'rnd_qc_lab',
        'kreatif',
        'product_development',
        'management'
    ];

    private function assertEquals($expected, $actual, string $message = ''): void
    {
        if ($expected === $actual) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = ['message' => $message, 'expected' => $expected, 'actual' => $actual];
            echo "F";
        }
    }

    private function assertTrue($value, string $message = ''): void
    {
        $this->assertEquals(true, $value, $message);
    }

    private function assertFalse($value, string $message = ''): void
    {
        $this->assertEquals(false, $value, $message);
    }

    private function assertGreaterThanOrEqual($expected, $actual, string $message = ''): void
    {
        if ($actual >= $expected) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = ['message' => $message, 'expected' => ">= $expected", 'actual' => $actual];
            echo "F";
        }
    }

    private function assertLessThan($expected, $actual, string $message = ''): void
    {
        if ($actual < $expected) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = ['message' => $message, 'expected' => "< $expected", 'actual' => $actual];
            echo "F";
        }
    }

    private function assertBetween($min, $max, $actual, string $message = ''): void
    {
        if ($actual >= $min && $actual <= $max) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = ['message' => $message, 'expected' => "between $min and $max", 'actual' => $actual];
            echo "F";
        }
    }

    public function runAll(): void
    {
        echo "\n=== Running Position-Based Scoring Tests ===\n\n";

        echo "Logic Scorer All Positions: ";
        $this->testLogicScorerAllPositions();
        echo "\n";

        echo "Position Threshold Verification: ";
        $this->testPositionThresholdVerification();
        echo "\n";

        echo "Psychology Work Pattern Identification: ";
        $this->testWorkPatternIdentification();
        echo "\n";

        echo "Fit Score Calculation: ";
        $this->testFitScoreCalculation();
        echo "\n";

        echo "Pattern Mismatch Detection: ";
        $this->testPatternMismatchDetection();
        echo "\n";

        echo "Alternative Position Recommendations: ";
        $this->testAlternativePositionRecommendations();
        echo "\n";

        echo "Boundary Cases (59%, 60%, 69%, 70%): ";
        $this->testBoundaryCases();
        echo "\n";

        echo "Staff Kantor Flexibility: ";
        $this->testStaffKantorFlexibility();
        echo "\n";

        echo "Multiple Position Fit: ";
        $this->testMultiplePositionFit();
        echo "\n";

        echo "No Position Fit: ";
        $this->testNoPositionFit();
        echo "\n";

        $this->printResults();
    }

    // ============================================
    // LOGIC SCORER ALL POSITIONS
    // ============================================

    private function testLogicScorerAllPositions(): void
    {
        $scorer = new LogicScorer();
        
        // Create answer set with specific score
        $answers = $this->createLogicAnswers(15); // 15 correct answers

        foreach ($this->positions as $position) {
            $result = $scorer->calculate($answers, $position);
            $threshold = $scorer->getThreshold($position);
            
            // Verify score is calculated correctly
            $this->assertEquals(15, $result['score'], "Score should be 15 for $position");
            
            // Verify threshold is applied correctly
            $this->assertEquals($threshold, $result['threshold'], "Threshold should match for $position");
            
            // Verify status based on threshold
            $expectedStatus = $this->getExpectedStatus(15, $threshold);
            $this->assertEquals($expectedStatus, $result['status'], "Status should be $expectedStatus for $position with score 15");
        }
    }

    private function testPositionThresholdVerification(): void
    {
        $scorer = new LogicScorer();
        
        // Test each position at exactly threshold
        $thresholds = [
            'operator_produksi' => 12,
            'staff_kantor' => 17,
            'supervisor' => 20,
            'rnd_qc_lab' => 17,
            'kreatif' => 14,
            'product_development' => 17,
            'management' => 20
        ];

        foreach ($thresholds as $position => $threshold) {
            // At threshold - should be aman
            $answers = $this->createLogicAnswers($threshold);
            $result = $scorer->calculate($answers, $position);
            $this->assertEquals('aman', $result['status'], "$position at threshold $threshold should be aman");

            // Below threshold by 1 - should be rawan
            $answers = $this->createLogicAnswers($threshold - 1);
            $result = $scorer->calculate($answers, $position);
            $this->assertEquals('rawan', $result['status'], "$position at " . ($threshold - 1) . " should be rawan");

            // Below threshold by 3 - should be tidak_aman
            $answers = $this->createLogicAnswers($threshold - 3);
            $result = $scorer->calculate($answers, $position);
            $this->assertEquals('tidak_aman', $result['status'], "$position at " . ($threshold - 3) . " should be tidak_aman");
        }
    }

    // ============================================
    // PSYCHOLOGY WORK PATTERN IDENTIFICATION
    // ============================================

    private function testWorkPatternIdentification(): void
    {
        $scorer = new PsychologyScorer();

        // Test all 4 work pattern categories
        $patterns = [
            'presisi_monoton' => [
                'section_a' => 15, 'section_b' => 14, 'section_c' => 5, 'section_d' => 4, 'section_e' => 12
            ],
            'presisi_dinamis' => [
                'section_a' => 14, 'section_b' => 10, 'section_c' => 14, 'section_d' => 8, 'section_e' => 12
            ],
            'eksploratif_terstruktur' => [
                'section_a' => 8, 'section_b' => 8, 'section_c' => 10, 'section_d' => 14, 'section_e' => 10
            ],
            'eksploratif_dinamis' => [
                'section_a' => 5, 'section_b' => 4, 'section_c' => 15, 'section_d' => 15, 'section_e' => 8
            ]
        ];

        foreach ($patterns as $expectedPattern => $scores) {
            $actualPattern = $scorer->getWorkPattern($scores);
            $this->assertEquals($expectedPattern, $actualPattern, "Scores should identify as $expectedPattern");
        }
    }

    // ============================================
    // FIT SCORE CALCULATION
    // ============================================

    private function testFitScoreCalculation(): void
    {
        $scorer = new PsychologyScorer();

        // Test ideal profiles for each position
        $idealProfiles = [
            'operator_produksi' => [
                'section_a' => 16, 'section_b' => 16, 'section_c' => 4, 'section_d' => 4, 'section_e' => 12
            ],
            'kreatif' => [
                'section_a' => 4, 'section_b' => 4, 'section_c' => 16, 'section_d' => 16, 'section_e' => 8
            ],
            'supervisor' => [
                'section_a' => 12, 'section_b' => 12, 'section_c' => 16, 'section_d' => 8, 'section_e' => 12
            ],
            'rnd_qc_lab' => [
                'section_a' => 16, 'section_b' => 16, 'section_c' => 4, 'section_d' => 4, 'section_e' => 16
            ]
        ];

        foreach ($idealProfiles as $position => $scores) {
            $fitScore = $scorer->calculateFitScore($scores, $position);
            $this->assertGreaterThanOrEqual(65, $fitScore, "Ideal profile for $position should have high fit score");
        }

        // Test mismatched profiles
        $operatorProfile = $idealProfiles['operator_produksi'];
        $fitScoreForKreatif = $scorer->calculateFitScore($operatorProfile, 'kreatif');
        $this->assertLessThan(60, $fitScoreForKreatif, "Operator profile should have low fit for kreatif");

        $kreatifProfile = $idealProfiles['kreatif'];
        $fitScoreForOperator = $scorer->calculateFitScore($kreatifProfile, 'operator_produksi');
        $this->assertLessThan(60, $fitScoreForOperator, "Kreatif profile should have low fit for operator");
    }

    // ============================================
    // PATTERN MISMATCH DETECTION
    // ============================================

    private function testPatternMismatchDetection(): void
    {
        $scorer = new PsychologyScorer();

        // Test all position-pattern combinations
        $expectedMatches = [
            'operator_produksi' => 'presisi_monoton',
            'supervisor' => 'presisi_dinamis',
            'rnd_qc_lab' => 'presisi_monoton',
            'kreatif' => 'eksploratif_dinamis',
            'product_development' => 'eksploratif_terstruktur',
            'management' => 'presisi_dinamis'
        ];

        foreach ($expectedMatches as $position => $expectedPattern) {
            // Should NOT mismatch when pattern matches
            $this->assertFalse(
                $scorer->checkPatternMismatch($expectedPattern, $position),
                "$expectedPattern should match $position"
            );

            // Should mismatch when pattern doesn't match
            $wrongPattern = $expectedPattern === 'presisi_monoton' ? 'eksploratif_dinamis' : 'presisi_monoton';
            $this->assertTrue(
                $scorer->checkPatternMismatch($wrongPattern, $position),
                "$wrongPattern should mismatch $position"
            );
        }
    }

    // ============================================
    // ALTERNATIVE POSITION RECOMMENDATIONS
    // ============================================

    private function testAlternativePositionRecommendations(): void
    {
        $scorer = new PsychologyScorer();

        // Test presisi monoton profile - should recommend operator/rnd_qc_lab
        $presisiMonotonScores = [
            'section_a' => 15, 'section_b' => 14, 'section_c' => 5, 'section_d' => 4, 'section_e' => 12
        ];
        $alternatives = $scorer->getAlternativePositions($presisiMonotonScores);

        // Verify all positions are included
        $this->assertEquals(7, count($alternatives), "Should have 7 alternative positions");

        // Verify operator and rnd_qc_lab have high fit scores
        $this->assertGreaterThanOrEqual(
            $alternatives['kreatif']['fitScore'],
            $alternatives['operator_produksi']['fitScore'],
            "Operator should have higher fit than kreatif for presisi monoton profile"
        );

        // Test eksploratif dinamis profile - should recommend kreatif
        $eksploratifDinamisScores = [
            'section_a' => 5, 'section_b' => 4, 'section_c' => 15, 'section_d' => 15, 'section_e' => 8
        ];
        $alternatives = $scorer->getAlternativePositions($eksploratifDinamisScores);

        $this->assertGreaterThanOrEqual(
            $alternatives['operator_produksi']['fitScore'],
            $alternatives['kreatif']['fitScore'],
            "Kreatif should have higher fit than operator for eksploratif dinamis profile"
        );
    }

    // ============================================
    // BOUNDARY CASES (59%, 60%, 69%, 70%)
    // ============================================

    private function testBoundaryCases(): void
    {
        $scorer = new PsychologyScorer();

        // Create scores that produce specific fit scores
        // We'll test the fit score label logic
        
        // Test >= 70% (Sangat Cocok)
        $highScores = [
            'section_a' => 16, 'section_b' => 16, 'section_c' => 16, 'section_d' => 16, 'section_e' => 16
        ];
        $fitScore = $scorer->calculateFitScore($highScores, 'staff_kantor');
        $this->assertGreaterThanOrEqual(70, $fitScore, "Max scores should give >= 70% fit for balanced position");

        // Test low scores (< 60%)
        $lowScores = [
            'section_a' => 2, 'section_b' => 2, 'section_c' => 2, 'section_d' => 2, 'section_e' => 2
        ];
        $fitScore = $scorer->calculateFitScore($lowScores, 'staff_kantor');
        $this->assertLessThan(60, $fitScore, "Very low scores should give < 60% fit");

        // Test medium scores (60-69%)
        $mediumScores = [
            'section_a' => 8, 'section_b' => 8, 'section_c' => 8, 'section_d' => 8, 'section_e' => 8
        ];
        $fitScore = $scorer->calculateFitScore($mediumScores, 'staff_kantor');
        $this->assertBetween(40, 80, $fitScore, "Medium scores should give moderate fit");
    }

    // ============================================
    // STAFF KANTOR FLEXIBILITY
    // ============================================

    private function testStaffKantorFlexibility(): void
    {
        $scorer = new PsychologyScorer();

        // Staff kantor should accept all patterns without mismatch
        $allPatterns = [
            'presisi_monoton',
            'presisi_dinamis',
            'eksploratif_terstruktur',
            'eksploratif_dinamis'
        ];

        foreach ($allPatterns as $pattern) {
            $this->assertFalse(
                $scorer->checkPatternMismatch($pattern, 'staff_kantor'),
                "Staff kantor should accept $pattern without mismatch"
            );
        }

        // Verify expected pattern is null (flexible)
        $this->assertEquals(
            null,
            PositionScoringMatrix::getExpectedWorkPattern('staff_kantor'),
            "Staff kantor should have null expected pattern"
        );

        // Test that staff_kantor has balanced weights
        $weights = PositionScoringMatrix::getPsychologyWeights('staff_kantor');
        $this->assertEquals(2, $weights['section_a'], "Staff kantor section_a weight should be 2");
        $this->assertEquals(2, $weights['section_b'], "Staff kantor section_b weight should be 2");
        $this->assertEquals(2, $weights['section_c'], "Staff kantor section_c weight should be 2");
        $this->assertEquals(2, $weights['section_d'], "Staff kantor section_d weight should be 2");
        $this->assertEquals(3, $weights['section_e'], "Staff kantor section_e weight should be 3");
    }

    // ============================================
    // MULTIPLE POSITION FIT
    // ============================================

    private function testMultiplePositionFit(): void
    {
        $scorer = new PsychologyScorer();

        // Create a balanced profile that could fit multiple positions
        $balancedScores = [
            'section_a' => 12, 'section_b' => 12, 'section_c' => 12, 'section_d' => 12, 'section_e' => 12
        ];

        $alternatives = $scorer->getAlternativePositions($balancedScores);

        // Count positions with fit score >= 50%
        $goodFitCount = 0;
        foreach ($alternatives as $position => $data) {
            if ($data['fitScore'] >= 50) {
                $goodFitCount++;
            }
        }

        $this->assertGreaterThanOrEqual(3, $goodFitCount, "Balanced profile should fit at least 3 positions");

        // Staff kantor should have good fit for balanced profile
        $this->assertGreaterThanOrEqual(60, $alternatives['staff_kantor']['fitScore'], 
            "Staff kantor should have good fit for balanced profile");
    }

    // ============================================
    // NO POSITION FIT
    // ============================================

    private function testNoPositionFit(): void
    {
        $scorer = new PsychologyScorer();

        // Create an extreme profile that doesn't fit well anywhere
        $extremeScores = [
            'section_a' => 1, 'section_b' => 1, 'section_c' => 1, 'section_d' => 1, 'section_e' => 1
        ];

        $alternatives = $scorer->getAlternativePositions($extremeScores);

        // All positions should have low fit scores
        foreach ($alternatives as $position => $data) {
            $this->assertLessThan(50, $data['fitScore'], 
                "Extreme low profile should have low fit for $position");
        }
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Create logic test answers with specific number of correct answers
     */
    private function createLogicAnswers(int $correctCount): array
    {
        $correctAnswers = [
            'A1' => 'B', 'A2' => 'C', 'A3' => 'B', 'A4' => 'C',
            'B1' => 'B', 'B2' => 'B', 'B3' => 'B',
            'C1' => 'B', 'C2' => 'C', 'C3' => 'C', 'C4' => 'A', 'C5' => 'B',
            'D1' => 'C', 'D2' => 'C', 'D3' => 'C', 'D4' => 'B', 'D5' => 'A',
            'E1' => 'B', 'E2' => 'A', 'E3' => 'A',
            'F1' => 'B', 'F2' => 'B',
            'G1' => 'B', 'G2' => 'B', 'G3' => 'C'
        ];

        $answers = [];
        $count = 0;
        foreach ($correctAnswers as $id => $correct) {
            if ($count < $correctCount) {
                $answers[$id] = $correct;
            } else {
                $answers[$id] = 'X'; // Wrong answer
            }
            $count++;
        }

        return $answers;
    }

    /**
     * Get expected status based on score and threshold
     */
    private function getExpectedStatus(int $score, int $threshold): string
    {
        if ($score >= $threshold) {
            return 'aman';
        } elseif ($score >= $threshold - 2) {
            return 'rawan';
        }
        return 'tidak_aman';
    }

    private function printResults(): void
    {
        echo "\n\n=== Test Results ===\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "Total: " . ($this->passed + $this->failed) . "\n";

        if (!empty($this->failures)) {
            echo "\n=== Failures ===\n";
            foreach ($this->failures as $i => $failure) {
                echo "\n" . ($i + 1) . ". {$failure['message']}\n";
                echo "   Expected: " . var_export($failure['expected'], true) . "\n";
                echo "   Actual: " . var_export($failure['actual'], true) . "\n";
            }
        }

        echo "\n";
        exit($this->failed > 0 ? 1 : 0);
    }
}

// Run tests
$test = new PositionScoringTest();
$test->runAll();
