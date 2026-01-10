<?php
/**
 * Edge Cases Tests for Position-Based Scoring
 * 
 * Tests for edge cases:
 * - Kandidat dengan profil yang cocok untuk multiple positions
 * - Kandidat dengan profil yang tidak cocok untuk semua positions
 * - Boundary cases untuk Fit Score (59%, 60%, 69%, 70%)
 * - Staff Kantor position (flexible - semua pattern diterima)
 * 
 * Requirements: 7.4, 7.5, 7.6, 8.2, 8.3, 8.5
 * 
 * Run: php tests/EdgeCasesTest.php
 */

require_once __DIR__ . '/../includes/scoring.php';
require_once __DIR__ . '/../includes/position_scoring_matrix.php';

class EdgeCasesTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];
    private PsychologyScorer $psychologyScorer;
    private LogicScorer $logicScorer;

    public function __construct()
    {
        $this->psychologyScorer = new PsychologyScorer();
        $this->logicScorer = new LogicScorer();
    }

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

    private function assertLessThanOrEqual($expected, $actual, string $message = ''): void
    {
        if ($actual <= $expected) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = ['message' => $message, 'expected' => "<= $expected", 'actual' => $actual];
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
        echo "\n=== Running Edge Cases Tests ===\n\n";

        echo "Multiple Position Fit Tests: ";
        $this->testMultiplePositionFit();
        echo "\n";

        echo "No Position Fit Tests: ";
        $this->testNoPositionFit();
        echo "\n";

        echo "Fit Score Boundary 59% Tests: ";
        $this->testFitScoreBoundary59();
        echo "\n";

        echo "Fit Score Boundary 60% Tests: ";
        $this->testFitScoreBoundary60();
        echo "\n";

        echo "Fit Score Boundary 69% Tests: ";
        $this->testFitScoreBoundary69();
        echo "\n";

        echo "Fit Score Boundary 70% Tests: ";
        $this->testFitScoreBoundary70();
        echo "\n";

        echo "Staff Kantor Flexibility Tests: ";
        $this->testStaffKantorFlexibility();
        echo "\n";

        echo "Extreme Score Tests: ";
        $this->testExtremeScores();
        echo "\n";

        echo "Logic Test Boundary Tests: ";
        $this->testLogicTestBoundaries();
        echo "\n";

        echo "Combined Status Determination Tests: ";
        $this->testCombinedStatusDetermination();
        echo "\n";

        $this->printResults();
    }

    // ============================================
    // MULTIPLE POSITION FIT TESTS
    // ============================================

    private function testMultiplePositionFit(): void
    {
        // Create a balanced profile that could fit multiple positions
        $balancedScores = [
            'section_a' => 10,
            'section_b' => 10,
            'section_c' => 10,
            'section_d' => 10,
            'section_e' => 10
        ];

        $alternatives = $this->psychologyScorer->getAlternativePositions($balancedScores);

        // Count positions with fit score >= 50%
        $goodFitCount = 0;
        $positionsWithGoodFit = [];
        foreach ($alternatives as $position => $data) {
            if ($data['fitScore'] >= 50) {
                $goodFitCount++;
                $positionsWithGoodFit[] = $position;
            }
        }

        $this->assertGreaterThanOrEqual(3, $goodFitCount, 
            "Balanced profile should fit at least 3 positions (got: " . implode(', ', $positionsWithGoodFit) . ")");

        // Test profile that fits both operator and rnd_qc_lab (both presisi_monoton)
        $presisiMonotonScores = [
            'section_a' => 15,
            'section_b' => 14,
            'section_c' => 5,
            'section_d' => 4,
            'section_e' => 12
        ];

        $operatorFit = $this->psychologyScorer->calculateFitScore($presisiMonotonScores, 'operator_produksi');
        $rndFit = $this->psychologyScorer->calculateFitScore($presisiMonotonScores, 'rnd_qc_lab');

        // Both should have similar fit scores since they expect same pattern
        $fitDifference = abs($operatorFit - $rndFit);
        $this->assertLessThanOrEqual(15, $fitDifference, 
            "Operator and R&D/QC should have similar fit for presisi_monoton profile");

        // Test profile that fits both supervisor and management (both presisi_dinamis)
        $presisiDinamisScores = [
            'section_a' => 12,
            'section_b' => 12,
            'section_c' => 15,
            'section_d' => 8,
            'section_e' => 12
        ];

        $supervisorFit = $this->psychologyScorer->calculateFitScore($presisiDinamisScores, 'supervisor');
        $managementFit = $this->psychologyScorer->calculateFitScore($presisiDinamisScores, 'management');

        // Both should have reasonable fit scores
        $this->assertGreaterThanOrEqual(50, $supervisorFit, "Supervisor should have good fit for presisi_dinamis profile");
        $this->assertGreaterThanOrEqual(50, $managementFit, "Management should have good fit for presisi_dinamis profile");
    }

    // ============================================
    // NO POSITION FIT TESTS
    // ============================================

    private function testNoPositionFit(): void
    {
        // Create an extreme low profile that doesn't fit well anywhere
        $extremeLowScores = [
            'section_a' => 1,
            'section_b' => 1,
            'section_c' => 1,
            'section_d' => 1,
            'section_e' => 1
        ];

        $alternatives = $this->psychologyScorer->getAlternativePositions($extremeLowScores);

        // All positions should have low fit scores
        foreach ($alternatives as $position => $data) {
            $this->assertLessThan(50, $data['fitScore'], 
                "Extreme low profile should have low fit for $position");
        }

        // Create a contradictory profile (high in opposing dimensions)
        $contradictoryScores = [
            'section_a' => 16, // High precision
            'section_b' => 2,  // Low stability
            'section_c' => 16, // High dynamism
            'section_d' => 2,  // Low exploration
            'section_e' => 8
        ];

        $alternatives = $this->psychologyScorer->getAlternativePositions($contradictoryScores);

        // This profile should have moderate fit at best
        $maxFit = 0;
        foreach ($alternatives as $position => $data) {
            if ($data['fitScore'] > $maxFit) {
                $maxFit = $data['fitScore'];
            }
        }
        
        // Even the best fit should not be extremely high for contradictory profile
        $this->assertLessThan(90, $maxFit, "Contradictory profile should not have very high fit anywhere");
    }

    // ============================================
    // FIT SCORE BOUNDARY TESTS
    // ============================================

    private function testFitScoreBoundary59(): void
    {
        // Test scores that should produce fit score around 59% (below threshold)
        // This is the "Tidak Cocok" boundary
        
        // For staff_kantor (balanced weights), we need low scores
        $lowScores = [
            'section_a' => 4,
            'section_b' => 4,
            'section_c' => 4,
            'section_d' => 4,
            'section_e' => 4
        ];

        $fitScore = $this->psychologyScorer->calculateFitScore($lowScores, 'staff_kantor');
        
        // Verify it's in the low range
        $this->assertLessThan(60, $fitScore, "Low scores should produce fit score < 60%");

        // Test status determination for < 60%
        $status = $this->determineFitStatus($fitScore);
        $this->assertEquals('tidak_cocok', $status, "Fit score < 60% should be tidak_cocok");
    }

    private function testFitScoreBoundary60(): void
    {
        // Test scores that should produce fit score around 60% (threshold)
        // This is the "Cocok dengan Catatan" boundary
        
        $mediumScores = [
            'section_a' => 8,
            'section_b' => 8,
            'section_c' => 8,
            'section_d' => 8,
            'section_e' => 8
        ];

        $fitScore = $this->psychologyScorer->calculateFitScore($mediumScores, 'staff_kantor');
        
        // Verify it's in the medium range
        $this->assertBetween(40, 70, $fitScore, "Medium scores should produce moderate fit score");

        // Test that 60% is the boundary for "Cocok dengan Catatan"
        $status60 = $this->determineFitStatus(60.0);
        $this->assertEquals('cocok_catatan', $status60, "Fit score 60% should be cocok_catatan");

        $status59 = $this->determineFitStatus(59.9);
        $this->assertEquals('tidak_cocok', $status59, "Fit score 59.9% should be tidak_cocok");
    }

    private function testFitScoreBoundary69(): void
    {
        // Test scores that should produce fit score around 69%
        // This is still "Cocok dengan Catatan"
        
        $status69 = $this->determineFitStatus(69.0);
        $this->assertEquals('cocok_catatan', $status69, "Fit score 69% should be cocok_catatan");

        $status69_9 = $this->determineFitStatus(69.9);
        $this->assertEquals('cocok_catatan', $status69_9, "Fit score 69.9% should be cocok_catatan");
    }

    private function testFitScoreBoundary70(): void
    {
        // Test scores that should produce fit score >= 70% (Sangat Cocok)
        
        $highScores = [
            'section_a' => 14,
            'section_b' => 14,
            'section_c' => 14,
            'section_d' => 14,
            'section_e' => 14
        ];

        $fitScore = $this->psychologyScorer->calculateFitScore($highScores, 'staff_kantor');
        
        // Verify it's in the high range
        $this->assertGreaterThanOrEqual(70, $fitScore, "High scores should produce fit score >= 70%");

        // Test that 70% is the boundary for "Sangat Cocok"
        $status70 = $this->determineFitStatus(70.0);
        $this->assertEquals('sangat_cocok', $status70, "Fit score 70% should be sangat_cocok");

        $status80 = $this->determineFitStatus(80.0);
        $this->assertEquals('sangat_cocok', $status80, "Fit score 80% should be sangat_cocok");
    }

    /**
     * Helper function to determine fit status based on score
     */
    private function determineFitStatus(float $fitScore): string
    {
        if ($fitScore >= 70) {
            return 'sangat_cocok';
        } elseif ($fitScore >= 60) {
            return 'cocok_catatan';
        }
        return 'tidak_cocok';
    }

    // ============================================
    // STAFF KANTOR FLEXIBILITY TESTS
    // ============================================

    private function testStaffKantorFlexibility(): void
    {
        // Staff Kantor should accept ALL work patterns without mismatch
        $allPatterns = [
            'presisi_monoton',
            'presisi_dinamis',
            'eksploratif_terstruktur',
            'eksploratif_dinamis'
        ];

        foreach ($allPatterns as $pattern) {
            $mismatch = $this->psychologyScorer->checkPatternMismatch($pattern, 'staff_kantor');
            $this->assertFalse($mismatch, "Staff kantor should accept $pattern without mismatch");
        }

        // Verify expected pattern is null (flexible)
        $expectedPattern = PositionScoringMatrix::getExpectedWorkPattern('staff_kantor');
        $this->assertEquals(null, $expectedPattern, "Staff kantor should have null expected pattern");

        // Test that staff_kantor has balanced weights
        $weights = PositionScoringMatrix::getPsychologyWeights('staff_kantor');
        $this->assertEquals(2, $weights['section_a'], "Staff kantor section_a weight should be 2");
        $this->assertEquals(2, $weights['section_b'], "Staff kantor section_b weight should be 2");
        $this->assertEquals(2, $weights['section_c'], "Staff kantor section_c weight should be 2");
        $this->assertEquals(2, $weights['section_d'], "Staff kantor section_d weight should be 2");
        $this->assertEquals(3, $weights['section_e'], "Staff kantor section_e weight should be 3");

        // Test that various profiles can fit staff_kantor reasonably well
        $profiles = [
            'presisi_monoton' => ['section_a' => 15, 'section_b' => 14, 'section_c' => 5, 'section_d' => 4, 'section_e' => 12],
            'eksploratif_dinamis' => ['section_a' => 5, 'section_b' => 4, 'section_c' => 15, 'section_d' => 15, 'section_e' => 8],
            'balanced' => ['section_a' => 10, 'section_b' => 10, 'section_c' => 10, 'section_d' => 10, 'section_e' => 10]
        ];

        foreach ($profiles as $profileName => $scores) {
            $fitScore = $this->psychologyScorer->calculateFitScore($scores, 'staff_kantor');
            $this->assertGreaterThanOrEqual(40, $fitScore, 
                "Staff kantor should have reasonable fit for $profileName profile");
        }
    }

    // ============================================
    // EXTREME SCORE TESTS
    // ============================================

    private function testExtremeScores(): void
    {
        // Test maximum scores (all 16)
        $maxScores = [
            'section_a' => 16,
            'section_b' => 16,
            'section_c' => 16,
            'section_d' => 16,
            'section_e' => 16
        ];

        $fitScore = $this->psychologyScorer->calculateFitScore($maxScores, 'staff_kantor');
        $this->assertGreaterThanOrEqual(90, $fitScore, "Max scores should produce very high fit score");

        // Test minimum scores (all 0)
        $minScores = [
            'section_a' => 0,
            'section_b' => 0,
            'section_c' => 0,
            'section_d' => 0,
            'section_e' => 0
        ];

        $fitScore = $this->psychologyScorer->calculateFitScore($minScores, 'staff_kantor');
        $this->assertEquals(0.0, $fitScore, "Zero scores should produce 0% fit score");

        // Test mixed extreme scores
        $mixedExtremeScores = [
            'section_a' => 16,
            'section_b' => 0,
            'section_c' => 16,
            'section_d' => 0,
            'section_e' => 16
        ];

        $fitScore = $this->psychologyScorer->calculateFitScore($mixedExtremeScores, 'staff_kantor');
        $this->assertBetween(30, 70, $fitScore, "Mixed extreme scores should produce moderate fit");
    }

    // ============================================
    // LOGIC TEST BOUNDARY TESTS
    // ============================================

    private function testLogicTestBoundaries(): void
    {
        // Test exact threshold boundaries for each position
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
            $status = $this->logicScorer->getStatus($threshold, $position);
            $this->assertEquals('aman', $status, "$position at threshold $threshold should be aman");

            // One below threshold - should be rawan
            $status = $this->logicScorer->getStatus($threshold - 1, $position);
            $this->assertEquals('rawan', $status, "$position at " . ($threshold - 1) . " should be rawan");

            // Two below threshold - should be rawan
            $status = $this->logicScorer->getStatus($threshold - 2, $position);
            $this->assertEquals('rawan', $status, "$position at " . ($threshold - 2) . " should be rawan");

            // Three below threshold - should be tidak_aman
            $status = $this->logicScorer->getStatus($threshold - 3, $position);
            $this->assertEquals('tidak_aman', $status, "$position at " . ($threshold - 3) . " should be tidak_aman");
        }
    }

    // ============================================
    // COMBINED STATUS DETERMINATION TESTS
    // ============================================

    private function testCombinedStatusDetermination(): void
    {
        // Test overall status determination logic
        // Status Logic:
        // - Aman: Logic passed + Fit Score ≥70%
        // - Rawan: Logic passed + (Fit Score 60-69% OR pattern mismatch)
        // - Tidak Aman: Logic failed OR Fit Score <60%

        // Test: Logic aman + Fit >= 70% = Aman
        $status = $this->determineOverallStatus('aman', 75.0, false);
        $this->assertEquals('aman', $status, "Logic aman + Fit 75% + no mismatch = aman");

        // Test: Logic aman + Fit 60-69% = Rawan
        $status = $this->determineOverallStatus('aman', 65.0, false);
        $this->assertEquals('rawan', $status, "Logic aman + Fit 65% = rawan");

        // Test: Logic aman + Fit >= 70% + mismatch = Rawan
        $status = $this->determineOverallStatus('aman', 75.0, true);
        $this->assertEquals('rawan', $status, "Logic aman + Fit 75% + mismatch = rawan");

        // Test: Logic aman + Fit < 60% = Tidak Aman
        $status = $this->determineOverallStatus('aman', 55.0, false);
        $this->assertEquals('tidak_aman', $status, "Logic aman + Fit 55% = tidak_aman");

        // Test: Logic rawan + Fit >= 70% = Rawan
        $status = $this->determineOverallStatus('rawan', 75.0, false);
        $this->assertEquals('rawan', $status, "Logic rawan + Fit 75% = rawan");

        // Test: Logic tidak_aman = Tidak Aman (regardless of fit)
        $status = $this->determineOverallStatus('tidak_aman', 90.0, false);
        $this->assertEquals('tidak_aman', $status, "Logic tidak_aman = tidak_aman regardless of fit");
    }

    /**
     * Helper function to determine overall status (mirrors submit.php logic)
     */
    private function determineOverallStatus(string $logicStatus, float $fitScore, bool $patternMismatch): string
    {
        // If logic test failed, overall is tidak_aman
        if ($logicStatus === 'tidak_aman') {
            return 'tidak_aman';
        }
        
        // If fit score is below 60%, overall is tidak_aman
        if ($fitScore < 60) {
            return 'tidak_aman';
        }
        
        // If logic is rawan, overall is rawan
        if ($logicStatus === 'rawan') {
            return 'rawan';
        }
        
        // If fit score is 60-69% or pattern mismatch, overall is rawan
        if ($fitScore < 70 || $patternMismatch) {
            return 'rawan';
        }
        
        // All good - aman
        return 'aman';
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
$test = new EdgeCasesTest();
$test->runAll();
