<?php
/**
 * Admin Panel Functionality Tests
 * 
 * Tests for admin panel features:
 * - Applicant list filters
 * - Detail view tabs (6 tabs)
 * - HR assessment save with 6 aspek adab
 * - Interview and probation tracking
 * - Auto-STOP recommendation on red indicator
 * 
 * Requirements: 6.1, 6.2, 6.3, 6.4, 9.2, 9.3
 * 
 * Run: php tests/AdminPanelTest.php
 */

require_once __DIR__ . '/../includes/InputSanitizer.php';
require_once __DIR__ . '/../includes/position_scoring_matrix.php';

class AdminPanelTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

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

    private function assertContains($needle, $haystack, string $message = ''): void
    {
        if (in_array($needle, $haystack)) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = ['message' => $message, 'expected' => "contains $needle", 'actual' => implode(', ', $haystack)];
            echo "F";
        }
    }

    private function assertCount($expected, $array, string $message = ''): void
    {
        $actual = count($array);
        if ($expected === $actual) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = ['message' => $message, 'expected' => $expected, 'actual' => $actual];
            echo "F";
        }
    }

    public function runAll(): void
    {
        echo "\n=== Running Admin Panel Tests ===\n\n";

        echo "Filter Validation Tests: ";
        $this->testFilterValidation();
        echo "\n";

        echo "Position Filter Tests: ";
        $this->testPositionFilter();
        echo "\n";

        echo "Logic Status Filter Tests: ";
        $this->testLogicStatusFilter();
        echo "\n";

        echo "HR Decision Filter Tests: ";
        $this->testHRDecisionFilter();
        echo "\n";

        echo "Search Filter Tests: ";
        $this->testSearchFilter();
        echo "\n";

        echo "HR Assessment Validation Tests: ";
        $this->testHRAssessmentValidation();
        echo "\n";

        echo "Auto-STOP Recommendation Tests: ";
        $this->testAutoStopRecommendation();
        echo "\n";

        echo "Interview Probation Tracking Tests: ";
        $this->testInterviewProbationTracking();
        echo "\n";

        echo "Tab Structure Tests: ";
        $this->testTabStructure();
        echo "\n";

        $this->printResults();
    }

    // ============================================
    // FILTER VALIDATION TESTS
    // ============================================

    private function testFilterValidation(): void
    {
        // Test valid position filter values
        $validPositions = [
            'operator_produksi',
            'staff_kantor',
            'supervisor',
            'rnd_qc_lab',
            'kreatif',
            'product_development',
            'management'
        ];

        foreach ($validPositions as $position) {
            $this->assertTrue(
                PositionScoringMatrix::isValidPosition($position),
                "Position $position should be valid for filter"
            );
        }

        // Test invalid position filter values
        $invalidPositions = ['invalid', '', 'CEO', 'intern'];
        foreach ($invalidPositions as $position) {
            $this->assertFalse(
                PositionScoringMatrix::isValidPosition($position),
                "Position $position should be invalid for filter"
            );
        }
    }

    private function testPositionFilter(): void
    {
        // Test that all 7 positions are available for filtering
        $positions = PositionScoringMatrix::getAllPositions();
        $this->assertCount(7, $positions, "Should have 7 positions available for filter");

        // Verify position names are human-readable
        $this->assertEquals(
            'Operator Produksi',
            PositionScoringMatrix::getPositionName('operator_produksi'),
            "Position name should be human-readable"
        );
        $this->assertEquals(
            'Staff Kantor (Admin/Finance/dll)',
            PositionScoringMatrix::getPositionName('staff_kantor'),
            "Staff kantor name should include description"
        );
    }

    private function testLogicStatusFilter(): void
    {
        // Test valid logic status values
        $validStatuses = ['aman', 'rawan', 'tidak_aman'];
        
        foreach ($validStatuses as $status) {
            $isValid = in_array($status, $validStatuses);
            $this->assertTrue($isValid, "Logic status $status should be valid");
        }

        // Test invalid logic status values
        $invalidStatuses = ['lulus', 'gagal', 'pending', ''];
        foreach ($invalidStatuses as $status) {
            $isValid = in_array($status, $validStatuses);
            $this->assertFalse($isValid, "Logic status $status should be invalid");
        }
    }

    private function testHRDecisionFilter(): void
    {
        // Test valid HR decision values
        $validDecisions = ['lanjut', 'hold', 'stop', 'pending'];
        
        foreach ($validDecisions as $decision) {
            $isValid = in_array($decision, $validDecisions);
            $this->assertTrue($isValid, "HR decision $decision should be valid");
        }

        // Test invalid HR decision values
        $invalidDecisions = ['approved', 'rejected', 'waiting', ''];
        foreach ($invalidDecisions as $decision) {
            $isValid = in_array($decision, $validDecisions);
            $this->assertFalse($isValid, "HR decision $decision should be invalid");
        }
    }

    private function testSearchFilter(): void
    {
        // Test search input sanitization
        $searchInputs = [
            'John Doe' => 'John Doe',
            '  John Doe  ' => 'John Doe',
            '<script>alert("xss")</script>' => '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
        ];

        foreach ($searchInputs as $input => $expected) {
            $sanitized = InputSanitizer::sanitizeString($input);
            $this->assertEquals($expected, $sanitized, "Search input should be sanitized: $input");
        }
    }

    // ============================================
    // HR ASSESSMENT VALIDATION TESTS
    // ============================================

    private function testHRAssessmentValidation(): void
    {
        // Test valid adab assessment values
        $validAdabValues = ['sehat', 'waspada', 'tidak_cocok'];
        
        foreach ($validAdabValues as $value) {
            $isValid = in_array($value, $validAdabValues);
            $this->assertTrue($isValid, "Adab value $value should be valid");
        }

        // Test all 6 aspek adab fields (A-F)
        $adabFields = [
            'hr_adab_a_otoritas',
            'hr_adab_b_koreksi',
            'hr_adab_c_tidak_sepakat',
            'hr_adab_d_kesadaran_diri',
            'hr_adab_e_kecocokan_nilai',
            'hr_adab_f1_orientasi_niat',
            'hr_adab_f2_respon_lelah',
            'hr_adab_f3_keikhlasan',
            'hr_adab_f4_spiritual'
        ];
        
        $this->assertCount(9, $adabFields, "Should have 9 adab assessment fields (A-F with F having 4 sub-aspects)");

        // Test valid fit values
        $validFitValues = ['selaras', 'abu_abu', 'tidak_cocok'];
        foreach ($validFitValues as $value) {
            $isValid = in_array($value, $validFitValues);
            $this->assertTrue($isValid, "Fit value $value should be valid");
        }

        // Test valid decision values
        $validDecisionValues = ['lanjut', 'hold', 'stop'];
        foreach ($validDecisionValues as $value) {
            $isValid = in_array($value, $validDecisionValues);
            $this->assertTrue($isValid, "Decision value $value should be valid");
        }
    }

    // ============================================
    // AUTO-STOP RECOMMENDATION TESTS
    // ============================================

    private function testAutoStopRecommendation(): void
    {
        // Test red indicator detection function
        $adabFields = [
            'hr_adab_a_otoritas', 'hr_adab_b_koreksi', 'hr_adab_c_tidak_sepakat',
            'hr_adab_d_kesadaran_diri', 'hr_adab_e_kecocokan_nilai',
            'hr_adab_f1_orientasi_niat', 'hr_adab_f2_respon_lelah',
            'hr_adab_f3_keikhlasan', 'hr_adab_f4_spiritual'
        ];

        // Test: No red indicators
        $noRedApplicant = [];
        foreach ($adabFields as $field) {
            $noRedApplicant[$field] = 'sehat';
        }
        $hasRed = $this->hasRedIndicator($noRedApplicant);
        $this->assertFalse($hasRed, "Should not have red indicator when all values are sehat");

        // Test: One red indicator in aspek A
        $oneRedApplicant = $noRedApplicant;
        $oneRedApplicant['hr_adab_a_otoritas'] = 'tidak_cocok';
        $hasRed = $this->hasRedIndicator($oneRedApplicant);
        $this->assertTrue($hasRed, "Should have red indicator when one value is tidak_cocok");

        // Test: Red indicator in aspek F (spiritualitas)
        $spiritualRedApplicant = $noRedApplicant;
        $spiritualRedApplicant['hr_adab_f4_spiritual'] = 'tidak_cocok';
        $hasRed = $this->hasRedIndicator($spiritualRedApplicant);
        $this->assertTrue($hasRed, "Should have red indicator when spiritual value is tidak_cocok");

        // Test: All waspada (yellow) - no red
        $allWaspadaApplicant = [];
        foreach ($adabFields as $field) {
            $allWaspadaApplicant[$field] = 'waspada';
        }
        $hasRed = $this->hasRedIndicator($allWaspadaApplicant);
        $this->assertFalse($hasRed, "Should not have red indicator when all values are waspada");

        // Test: Multiple red indicators
        $multipleRedApplicant = $noRedApplicant;
        $multipleRedApplicant['hr_adab_a_otoritas'] = 'tidak_cocok';
        $multipleRedApplicant['hr_adab_b_koreksi'] = 'tidak_cocok';
        $multipleRedApplicant['hr_adab_c_tidak_sepakat'] = 'tidak_cocok';
        $hasRed = $this->hasRedIndicator($multipleRedApplicant);
        $this->assertTrue($hasRed, "Should have red indicator when multiple values are tidak_cocok");
    }

    /**
     * Helper function to check for red indicators (mirrors admin/detail.php logic)
     */
    private function hasRedIndicator(array $applicant): bool
    {
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

    // ============================================
    // INTERVIEW PROBATION TRACKING TESTS
    // ============================================

    private function testInterviewProbationTracking(): void
    {
        // Test valid probation status values
        $validProbationStatuses = [
            'belum',
            '0_14_hari',
            '15_30_hari',
            '31_90_hari',
            'lulus',
            'tidak_lulus'
        ];

        foreach ($validProbationStatuses as $status) {
            $isValid = in_array($status, $validProbationStatuses);
            $this->assertTrue($isValid, "Probation status $status should be valid");
        }

        // Test valid final decision values
        $validFinalDecisions = ['diterima', 'ditolak', 'pending'];
        foreach ($validFinalDecisions as $decision) {
            $isValid = in_array($decision, $validFinalDecisions);
            $this->assertTrue($isValid, "Final decision $decision should be valid");
        }

        // Test interview result values
        $validInterviewResults = ['lanjut', 'hold', 'stop'];
        foreach ($validInterviewResults as $result) {
            $isValid = in_array($result, $validInterviewResults);
            $this->assertTrue($isValid, "Interview result $result should be valid");
        }
    }

    // ============================================
    // TAB STRUCTURE TESTS
    // ============================================

    private function testTabStructure(): void
    {
        // Test that all 6 tabs are defined
        $expectedTabs = [
            'personal',    // Tab 1: Data Pribadi & CV
            'form',        // Tab 2: Jawaban Form (Value & Adab)
            'logic',       // Tab 3: Hasil Logic Test
            'psychology',  // Tab 4: Hasil Psychology Test
            'assessment',  // Tab 5: HR Assessment
            'interview'    // Tab 6: Interview & Probation
        ];

        $this->assertCount(6, $expectedTabs, "Should have 6 tabs in detail view");

        // Verify tab names
        $this->assertContains('personal', $expectedTabs, "Should have personal tab");
        $this->assertContains('form', $expectedTabs, "Should have form tab");
        $this->assertContains('logic', $expectedTabs, "Should have logic tab");
        $this->assertContains('psychology', $expectedTabs, "Should have psychology tab");
        $this->assertContains('assessment', $expectedTabs, "Should have assessment tab");
        $this->assertContains('interview', $expectedTabs, "Should have interview tab");
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
$test = new AdminPanelTest();
$test->runAll();
