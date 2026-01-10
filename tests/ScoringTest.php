<?php
/**
 * Scoring Engine Tests
 * 
 * Tests for LogicScorer and PsychologyScorer classes
 * Requirements: 3.2, 3.3, 3.4, 3.5, 4.3, 4.4, 4.5, 4.6, 7.2, 7.3, 7.4, 7.5, 7.6, 8.1, 8.2, 8.5
 * 
 * Run: php tests/ScoringTest.php
 */

require_once __DIR__ . '/../includes/scoring.php';
require_once __DIR__ . '/../includes/position_scoring_matrix.php';

class ScoringTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    /**
     * Assert that two values are equal
     */
    private function assertEquals($expected, $actual, string $message = ''): void
    {
        if ($expected === $actual) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = [
                'message' => $message,
                'expected' => $expected,
                'actual' => $actual
            ];
            echo "F";
        }
    }

    /**
     * Assert that a value is true
     */
    private function assertTrue($value, string $message = ''): void
    {
        $this->assertEquals(true, $value, $message);
    }

    /**
     * Assert that a value is false
     */
    private function assertFalse($value, string $message = ''): void
    {
        $this->assertEquals(false, $value, $message);
    }

    /**
     * Assert that a value is greater than or equal to another
     */
    private function assertGreaterThanOrEqual($expected, $actual, string $message = ''): void
    {
        if ($actual >= $expected) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = [
                'message' => $message,
                'expected' => ">= $expected",
                'actual' => $actual
            ];
            echo "F";
        }
    }

    /**
     * Assert that a value is less than another
     */
    private function assertLessThan($expected, $actual, string $message = ''): void
    {
        if ($actual < $expected) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = [
                'message' => $message,
                'expected' => "< $expected",
                'actual' => $actual
            ];
            echo "F";
        }
    }

    /**
     * Run all tests
     */
    public function runAll(): void
    {
        echo "\n=== Running Scoring Tests ===\n\n";

        // Logic Scorer Tests
        echo "Logic Scorer Tests: ";
        $this->testLogicScorerThresholds();
        $this->testLogicScorerCalculation();
        $this->testLogicScorerStatus();
        echo "\n";

        // Psychology Scorer Tests
        echo "Psychology Scorer Tests: ";
        $this->testPsychologyScorerWorkPattern();
        $this->testPsychologyScorerFitScore();
        $this->testPsychologyScorerPatternMismatch();
        $this->testPsychologyScorerAlternativePositions();
        echo "\n";

        // Position Scoring Matrix Tests
        echo "Position Scoring Matrix Tests: ";
        $this->testPositionScoringMatrix();
        echo "\n";

        // Edge Cases Tests
        echo "Edge Cases Tests: ";
        $this->testEdgeCases();
        echo "\n";

        // Print results
        $this->printResults();
    }

    // ============================================
    // LOGIC SCORER TESTS
    // ============================================

    private function testLogicScorerThresholds(): void
    {
        $scorer = new LogicScorer();

        // Test all 7 position thresholds (Requirement 7.2)
        $this->assertEquals(12, $scorer->getThreshold('operator_produksi'), 'Operator threshold should be 12');
        $this->assertEquals(17, $scorer->getThreshold('staff_kantor'), 'Staff threshold should be 17');
        $this->assertEquals(20, $scorer->getThreshold('supervisor'), 'Supervisor threshold should be 20');
        $this->assertEquals(17, $scorer->getThreshold('rnd_qc_lab'), 'R&D/QC threshold should be 17');
        $this->assertEquals(14, $scorer->getThreshold('kreatif'), 'Kreatif threshold should be 14');
        $this->assertEquals(17, $scorer->getThreshold('product_development'), 'Product Dev threshold should be 17');
        $this->assertEquals(20, $scorer->getThreshold('management'), 'Management threshold should be 20');
        
        // Test default threshold for unknown position
        $this->assertEquals(17, $scorer->getThreshold('unknown_position'), 'Unknown position should default to 17');
    }

    private function testLogicScorerCalculation(): void
    {
        $scorer = new LogicScorer();

        // Create perfect answers (all correct)
        $perfectAnswers = [
            'A1' => 'B', 'A2' => 'C', 'A3' => 'B', 'A4' => 'C',
            'B1' => 'B', 'B2' => 'B', 'B3' => 'B',
            'C1' => 'B', 'C2' => 'C', 'C3' => 'C', 'C4' => 'A', 'C5' => 'B',
            'D1' => 'C', 'D2' => 'C', 'D3' => 'C', 'D4' => 'B', 'D5' => 'A',
            'E1' => 'B', 'E2' => 'A', 'E3' => 'A',
            'F1' => 'B', 'F2' => 'B',
            'G1' => 'B', 'G2' => 'B', 'G3' => 'C'
        ];

        $result = $scorer->calculate($perfectAnswers, 'staff_kantor');
        $this->assertEquals(25, $result['score'], 'Perfect score should be 25');
        $this->assertEquals(25, $result['total'], 'Total should be 25');
        $this->assertEquals(100.0, $result['percentage'], 'Perfect percentage should be 100');
        $this->assertTrue($result['passedThreshold'], 'Should pass threshold with perfect score');

        // Test with all wrong answers
        $wrongAnswers = array_fill_keys(array_keys($perfectAnswers), 'X');
        $result = $scorer->calculate($wrongAnswers, 'staff_kantor');
        $this->assertEquals(0, $result['score'], 'All wrong should score 0');
        $this->assertFalse($result['passedThreshold'], 'Should not pass threshold with 0 score');
    }

    private function testLogicScorerStatus(): void
    {
        $scorer = new LogicScorer();

        // Test status for operator (threshold 12)
        $this->assertEquals('aman', $scorer->getStatus(12, 'operator_produksi'), 'Score 12 should be aman for operator');
        $this->assertEquals('aman', $scorer->getStatus(15, 'operator_produksi'), 'Score 15 should be aman for operator');
        $this->assertEquals('rawan', $scorer->getStatus(11, 'operator_produksi'), 'Score 11 should be rawan for operator');
        $this->assertEquals('rawan', $scorer->getStatus(10, 'operator_produksi'), 'Score 10 should be rawan for operator');
        $this->assertEquals('tidak_aman', $scorer->getStatus(9, 'operator_produksi'), 'Score 9 should be tidak_aman for operator');

        // Test status for staff (threshold 17)
        $this->assertEquals('aman', $scorer->getStatus(17, 'staff_kantor'), 'Score 17 should be aman for staff');
        $this->assertEquals('rawan', $scorer->getStatus(16, 'staff_kantor'), 'Score 16 should be rawan for staff');
        $this->assertEquals('rawan', $scorer->getStatus(15, 'staff_kantor'), 'Score 15 should be rawan for staff');
        $this->assertEquals('tidak_aman', $scorer->getStatus(14, 'staff_kantor'), 'Score 14 should be tidak_aman for staff');

        // Test status for management (threshold 20)
        $this->assertEquals('aman', $scorer->getStatus(20, 'management'), 'Score 20 should be aman for management');
        $this->assertEquals('rawan', $scorer->getStatus(19, 'management'), 'Score 19 should be rawan for management');
        $this->assertEquals('tidak_aman', $scorer->getStatus(17, 'management'), 'Score 17 should be tidak_aman for management');
    }

    // ============================================
    // PSYCHOLOGY SCORER TESTS
    // ============================================

    private function testPsychologyScorerWorkPattern(): void
    {
        $scorer = new PsychologyScorer();

        // Test Presisi Monoton pattern (high A & B, low C & D)
        $presisiMonotonScores = [
            'section_a' => 15, // high
            'section_b' => 14, // high
            'section_c' => 6,  // low
            'section_d' => 5,  // low
            'section_e' => 12
        ];
        $pattern = $scorer->getWorkPattern($presisiMonotonScores);
        $this->assertEquals('presisi_monoton', $pattern, 'High A&B, Low C&D should be presisi_monoton');

        // Test Presisi Dinamis pattern (high A, medium B, high C)
        $presisiDinamisScores = [
            'section_a' => 14, // high
            'section_b' => 10, // medium
            'section_c' => 14, // high
            'section_d' => 8,  // low-medium
            'section_e' => 12
        ];
        $pattern = $scorer->getWorkPattern($presisiDinamisScores);
        $this->assertEquals('presisi_dinamis', $pattern, 'High A, Medium B, High C should be presisi_dinamis');

        // Test Eksploratif Dinamis pattern (low A & B, high C & D)
        $eksploratifDinamisScores = [
            'section_a' => 6,  // low
            'section_b' => 5,  // low
            'section_c' => 14, // high
            'section_d' => 15, // high
            'section_e' => 10
        ];
        $pattern = $scorer->getWorkPattern($eksploratifDinamisScores);
        $this->assertEquals('eksploratif_dinamis', $pattern, 'Low A&B, High C&D should be eksploratif_dinamis');
    }

    private function testPsychologyScorerFitScore(): void
    {
        $scorer = new PsychologyScorer();

        // Test fit score for operator with presisi monoton profile
        $operatorIdealScores = [
            'section_a' => 16, // max - weight 4
            'section_b' => 16, // max - weight 4
            'section_c' => 4,  // low - weight 1
            'section_d' => 4,  // low - weight 1
            'section_e' => 12  // good - weight 3
        ];
        $fitScore = $scorer->calculateFitScore($operatorIdealScores, 'operator_produksi');
        $this->assertGreaterThanOrEqual(70, $fitScore, 'Ideal operator profile should have fit score >= 70%');

        // Test fit score for kreatif with eksploratif dinamis profile
        $kreatifIdealScores = [
            'section_a' => 4,  // low - weight 1
            'section_b' => 4,  // low - weight 1
            'section_c' => 16, // max - weight 4
            'section_d' => 16, // max - weight 4
            'section_e' => 8   // medium - weight 2
        ];
        $fitScore = $scorer->calculateFitScore($kreatifIdealScores, 'kreatif');
        $this->assertGreaterThanOrEqual(70, $fitScore, 'Ideal kreatif profile should have fit score >= 70%');

        // Test mismatched profile (operator profile for kreatif position)
        $fitScore = $scorer->calculateFitScore($operatorIdealScores, 'kreatif');
        $this->assertLessThan(70, $fitScore, 'Operator profile for kreatif position should have lower fit score');
    }

    private function testPsychologyScorerPatternMismatch(): void
    {
        $scorer = new PsychologyScorer();

        // Test pattern mismatch detection
        $this->assertTrue(
            $scorer->checkPatternMismatch('eksploratif_dinamis', 'operator_produksi'),
            'Eksploratif dinamis should mismatch with operator'
        );
        $this->assertTrue(
            $scorer->checkPatternMismatch('presisi_monoton', 'kreatif'),
            'Presisi monoton should mismatch with kreatif'
        );

        // Test pattern match
        $this->assertFalse(
            $scorer->checkPatternMismatch('presisi_monoton', 'operator_produksi'),
            'Presisi monoton should match with operator'
        );
        $this->assertFalse(
            $scorer->checkPatternMismatch('eksploratif_dinamis', 'kreatif'),
            'Eksploratif dinamis should match with kreatif'
        );

        // Test flexible position (staff_kantor accepts all patterns)
        $this->assertFalse(
            $scorer->checkPatternMismatch('presisi_monoton', 'staff_kantor'),
            'Staff kantor should accept presisi_monoton'
        );
        $this->assertFalse(
            $scorer->checkPatternMismatch('eksploratif_dinamis', 'staff_kantor'),
            'Staff kantor should accept eksploratif_dinamis'
        );
    }

    private function testPsychologyScorerAlternativePositions(): void
    {
        $scorer = new PsychologyScorer();

        // Test alternative positions for presisi monoton profile
        $presisiMonotonScores = [
            'section_a' => 15,
            'section_b' => 14,
            'section_c' => 6,
            'section_d' => 5,
            'section_e' => 12
        ];
        $alternatives = $scorer->getAlternativePositions($presisiMonotonScores);
        
        $this->assertTrue(isset($alternatives['operator_produksi']), 'Should include operator_produksi');
        $this->assertTrue(isset($alternatives['rnd_qc_lab']), 'Should include rnd_qc_lab');
        
        // Verify fit scores are calculated
        $this->assertGreaterThanOrEqual(0, $alternatives['operator_produksi']['fitScore'], 'Fit score should be >= 0');
    }

    // ============================================
    // POSITION SCORING MATRIX TESTS
    // ============================================

    private function testPositionScoringMatrix(): void
    {
        // Test position validation
        $this->assertTrue(PositionScoringMatrix::isValidPosition('operator_produksi'), 'operator_produksi should be valid');
        $this->assertTrue(PositionScoringMatrix::isValidPosition('staff_kantor'), 'staff_kantor should be valid');
        $this->assertFalse(PositionScoringMatrix::isValidPosition('invalid_position'), 'invalid_position should be invalid');

        // Test position track mapping
        $this->assertEquals('operator', PositionScoringMatrix::getPositionTrack('operator_produksi'), 'Operator track');
        $this->assertEquals('staff', PositionScoringMatrix::getPositionTrack('staff_kantor'), 'Staff track');
        $this->assertEquals('supervisor_management', PositionScoringMatrix::getPositionTrack('supervisor'), 'Supervisor track');

        // Test expected work pattern
        $this->assertEquals('presisi_monoton', PositionScoringMatrix::getExpectedWorkPattern('operator_produksi'), 'Operator expected pattern');
        $this->assertEquals('presisi_dinamis', PositionScoringMatrix::getExpectedWorkPattern('supervisor'), 'Supervisor expected pattern');
        $this->assertEquals('eksploratif_dinamis', PositionScoringMatrix::getExpectedWorkPattern('kreatif'), 'Kreatif expected pattern');
        $this->assertEquals(null, PositionScoringMatrix::getExpectedWorkPattern('staff_kantor'), 'Staff kantor should be flexible');

        // Test pattern match
        $this->assertTrue(PositionScoringMatrix::isPatternMatch('presisi_monoton', 'operator_produksi'), 'Pattern should match');
        $this->assertFalse(PositionScoringMatrix::isPatternMatch('eksploratif_dinamis', 'operator_produksi'), 'Pattern should not match');
        $this->assertTrue(PositionScoringMatrix::isPatternMatch('eksploratif_dinamis', 'staff_kantor'), 'Flexible position should match any');
    }

    // ============================================
    // EDGE CASES TESTS
    // ============================================

    private function testEdgeCases(): void
    {
        $scorer = new PsychologyScorer();

        // Test boundary cases for Fit Score (59%, 60%, 69%, 70%)
        // These are approximate tests since exact scores depend on weight calculations

        // Test candidate suitable for multiple positions
        $balancedScores = [
            'section_a' => 10,
            'section_b' => 10,
            'section_c' => 10,
            'section_d' => 10,
            'section_e' => 10
        ];
        $alternatives = $scorer->getAlternativePositions($balancedScores);
        
        // Staff kantor should have good fit for balanced profile
        $staffFitScore = $alternatives['staff_kantor']['fitScore'];
        $this->assertGreaterThanOrEqual(50, $staffFitScore, 'Balanced profile should have decent fit for staff_kantor');

        // Test candidate not suitable for any position (extreme low scores)
        $lowScores = [
            'section_a' => 2,
            'section_b' => 2,
            'section_c' => 2,
            'section_d' => 2,
            'section_e' => 2
        ];
        $alternatives = $scorer->getAlternativePositions($lowScores);
        
        // All positions should have low fit scores
        foreach ($alternatives as $position => $data) {
            $this->assertLessThan(80, $data['fitScore'], "Low scores should result in lower fit for $position");
        }

        // Test Staff Kantor flexibility (all patterns accepted)
        $patterns = ['presisi_monoton', 'presisi_dinamis', 'eksploratif_terstruktur', 'eksploratif_dinamis'];
        foreach ($patterns as $pattern) {
            $this->assertFalse(
                $scorer->checkPatternMismatch($pattern, 'staff_kantor'),
                "Staff kantor should accept $pattern"
            );
        }
    }

    /**
     * Print test results
     */
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
        
        // Exit with appropriate code
        exit($this->failed > 0 ? 1 : 0);
    }
}

// Run tests
$test = new ScoringTest();
$test->runAll();
