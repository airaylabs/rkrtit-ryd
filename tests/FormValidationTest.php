<?php
/**
 * Form Validation Tests
 * 
 * Tests for InputSanitizer and form submission validation
 * Requirements: 2.1, 2.5, 9.1
 * 
 * Run: php tests/FormValidationTest.php
 */

require_once __DIR__ . '/../includes/InputSanitizer.php';
require_once __DIR__ . '/../includes/position_scoring_matrix.php';

class FormValidationTest
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
            $this->failures[] = [
                'message' => $message,
                'expected' => $expected,
                'actual' => $actual
            ];
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

    private function assertNull($value, string $message = ''): void
    {
        $this->assertEquals(null, $value, $message);
    }

    private function assertNotNull($value, string $message = ''): void
    {
        if ($value !== null) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = [
                'message' => $message,
                'expected' => 'not null',
                'actual' => 'null'
            ];
            echo "F";
        }
    }

    private function assertEmpty($value, string $message = ''): void
    {
        if (empty($value)) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = [
                'message' => $message,
                'expected' => 'empty',
                'actual' => var_export($value, true)
            ];
            echo "F";
        }
    }

    private function assertNotEmpty($value, string $message = ''): void
    {
        if (!empty($value)) {
            $this->passed++;
            echo ".";
        } else {
            $this->failed++;
            $this->failures[] = [
                'message' => $message,
                'expected' => 'not empty',
                'actual' => 'empty'
            ];
            echo "F";
        }
    }

    public function runAll(): void
    {
        echo "\n=== Running Form Validation Tests ===\n\n";

        echo "String Sanitization Tests: ";
        $this->testStringSanitization();
        echo "\n";

        echo "Email Validation Tests: ";
        $this->testEmailValidation();
        echo "\n";

        echo "Phone Validation Tests: ";
        $this->testPhoneValidation();
        echo "\n";

        echo "Required Fields Tests: ";
        $this->testRequiredFields();
        echo "\n";

        echo "Position Selection Tests: ";
        $this->testPositionSelection();
        echo "\n";

        echo "Position Track Mapping Tests: ";
        $this->testPositionTrackMapping();
        echo "\n";

        echo "Work Pattern Assignment Tests: ";
        $this->testWorkPatternAssignment();
        echo "\n";

        $this->printResults();
    }

    // ============================================
    // STRING SANITIZATION TESTS
    // ============================================

    private function testStringSanitization(): void
    {
        // Test XSS prevention
        $xssInput = '<script>alert("xss")</script>';
        $sanitized = InputSanitizer::sanitizeString($xssInput);
        $this->assertFalse(
            strpos($sanitized, '<script>') !== false,
            'Should escape script tags'
        );

        // Test HTML entity encoding
        $htmlInput = '<div onclick="evil()">Test</div>';
        $sanitized = InputSanitizer::sanitizeString($htmlInput);
        $this->assertFalse(
            strpos($sanitized, '<div') !== false,
            'Should escape HTML tags'
        );

        // Test normal string passes through
        $normalInput = 'John Doe';
        $sanitized = InputSanitizer::sanitizeString($normalInput);
        $this->assertEquals('John Doe', $sanitized, 'Normal string should pass through');

        // Test trimming
        $spacedInput = '  John Doe  ';
        $sanitized = InputSanitizer::sanitizeString($spacedInput);
        $this->assertEquals('John Doe', $sanitized, 'Should trim whitespace');

        // Test special characters encoding
        $specialInput = "Test & 'quotes' \"double\"";
        $sanitized = InputSanitizer::sanitizeString($specialInput);
        $this->assertTrue(
            strpos($sanitized, '&amp;') !== false,
            'Should encode ampersand'
        );
    }

    // ============================================
    // EMAIL VALIDATION TESTS
    // ============================================

    private function testEmailValidation(): void
    {
        // Valid emails
        $this->assertNotNull(
            InputSanitizer::validateEmail('test@example.com'),
            'Standard email should be valid'
        );
        $this->assertNotNull(
            InputSanitizer::validateEmail('user.name@domain.co.id'),
            'Email with dots should be valid'
        );
        $this->assertNotNull(
            InputSanitizer::validateEmail('user+tag@example.com'),
            'Email with plus should be valid'
        );

        // Invalid emails
        $this->assertNull(
            InputSanitizer::validateEmail('invalid-email'),
            'Email without @ should be invalid'
        );
        $this->assertNull(
            InputSanitizer::validateEmail('test@'),
            'Email without domain should be invalid'
        );
        $this->assertNull(
            InputSanitizer::validateEmail('@example.com'),
            'Email without local part should be invalid'
        );
        $this->assertNull(
            InputSanitizer::validateEmail(''),
            'Empty email should be invalid'
        );
    }

    // ============================================
    // PHONE VALIDATION TESTS
    // ============================================

    private function testPhoneValidation(): void
    {
        // Valid phone numbers
        $this->assertNotNull(
            InputSanitizer::validatePhone('08123456789'),
            'Indonesian mobile should be valid'
        );
        $this->assertNotNull(
            InputSanitizer::validatePhone('+6281234567890'),
            'Phone with country code should be valid'
        );
        $this->assertNotNull(
            InputSanitizer::validatePhone('081-234-567-890'),
            'Phone with dashes should be valid (dashes stripped)'
        );

        // Invalid phone numbers
        $this->assertNull(
            InputSanitizer::validatePhone('123'),
            'Too short phone should be invalid'
        );
        $this->assertNull(
            InputSanitizer::validatePhone('abcdefghij'),
            'Letters should be invalid'
        );
        $this->assertNull(
            InputSanitizer::validatePhone(''),
            'Empty phone should be invalid'
        );
    }

    // ============================================
    // REQUIRED FIELDS TESTS
    // ============================================

    private function testRequiredFields(): void
    {
        $requiredFields = ['nama', 'email', 'whatsapp', 'position_applied'];

        // Test with all fields present
        $validData = [
            'nama' => 'John Doe',
            'email' => 'john@example.com',
            'whatsapp' => '08123456789',
            'position_applied' => 'staff_kantor'
        ];
        $errors = InputSanitizer::validateRequired($validData, $requiredFields);
        $this->assertEmpty($errors, 'No errors when all required fields present');

        // Test with missing field
        $missingData = [
            'nama' => 'John Doe',
            'email' => 'john@example.com',
            'whatsapp' => '08123456789'
            // position_applied missing
        ];
        $errors = InputSanitizer::validateRequired($missingData, $requiredFields);
        $this->assertNotEmpty($errors, 'Should have error when field missing');

        // Test with empty field
        $emptyData = [
            'nama' => '',
            'email' => 'john@example.com',
            'whatsapp' => '08123456789',
            'position_applied' => 'staff_kantor'
        ];
        $errors = InputSanitizer::validateRequired($emptyData, $requiredFields);
        $this->assertNotEmpty($errors, 'Should have error when field empty');

        // Test with null field
        $nullData = [
            'nama' => null,
            'email' => 'john@example.com',
            'whatsapp' => '08123456789',
            'position_applied' => 'staff_kantor'
        ];
        $errors = InputSanitizer::validateRequired($nullData, $requiredFields);
        $this->assertNotEmpty($errors, 'Should have error when field null');
    }

    // ============================================
    // POSITION SELECTION TESTS
    // ============================================

    private function testPositionSelection(): void
    {
        // Test all valid positions
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
                "$position should be valid"
            );
        }

        // Test invalid positions
        $invalidPositions = [
            'invalid',
            'ceo',
            'intern',
            '',
            'OPERATOR_PRODUKSI', // case sensitive
        ];

        foreach ($invalidPositions as $position) {
            $this->assertFalse(
                PositionScoringMatrix::isValidPosition($position),
                "$position should be invalid"
            );
        }
    }

    // ============================================
    // POSITION TRACK MAPPING TESTS
    // ============================================

    private function testPositionTrackMapping(): void
    {
        // Test Track A (Operator)
        $this->assertEquals(
            'operator',
            PositionScoringMatrix::getPositionTrack('operator_produksi'),
            'Operator should be in operator track'
        );

        // Test Track B (Staff)
        $staffPositions = ['staff_kantor', 'rnd_qc_lab', 'kreatif', 'product_development'];
        foreach ($staffPositions as $position) {
            $this->assertEquals(
                'staff',
                PositionScoringMatrix::getPositionTrack($position),
                "$position should be in staff track"
            );
        }

        // Test Track C (Supervisor/Management)
        $managementPositions = ['supervisor', 'management'];
        foreach ($managementPositions as $position) {
            $this->assertEquals(
                'supervisor_management',
                PositionScoringMatrix::getPositionTrack($position),
                "$position should be in supervisor_management track"
            );
        }

        // Test default track for unknown position
        $this->assertEquals(
            'staff',
            PositionScoringMatrix::getPositionTrack('unknown'),
            'Unknown position should default to staff track'
        );
    }

    // ============================================
    // WORK PATTERN ASSIGNMENT TESTS
    // ============================================

    private function testWorkPatternAssignment(): void
    {
        // Test expected work patterns per position
        $expectedPatterns = [
            'operator_produksi' => 'presisi_monoton',
            'staff_kantor' => null, // flexible
            'supervisor' => 'presisi_dinamis',
            'rnd_qc_lab' => 'presisi_monoton',
            'kreatif' => 'eksploratif_dinamis',
            'product_development' => 'eksploratif_terstruktur',
            'management' => 'presisi_dinamis'
        ];

        foreach ($expectedPatterns as $position => $expectedPattern) {
            $actualPattern = PositionScoringMatrix::getExpectedWorkPattern($position);
            $this->assertEquals(
                $expectedPattern,
                $actualPattern,
                "$position should have expected pattern: " . ($expectedPattern ?? 'null')
            );
        }

        // Test that staff_kantor is truly flexible
        $this->assertNull(
            PositionScoringMatrix::getExpectedWorkPattern('staff_kantor'),
            'Staff kantor should have null (flexible) expected pattern'
        );
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
$test = new FormValidationTest();
$test->runAll();
