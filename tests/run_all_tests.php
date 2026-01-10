<?php
/**
 * Test Runner - Runs all test files
 * 
 * Run: php tests/run_all_tests.php
 */

echo "========================================\n";
echo "  RECRUITMENT SYSTEM TEST SUITE\n";
echo "========================================\n\n";

$testFiles = [
    'FormValidationTest.php',
    'ScoringTest.php',
    'PositionScoringTest.php',
    'AdminPanelTest.php',
    'EdgeCasesTest.php'
];

$totalPassed = 0;
$totalFailed = 0;
$failedTests = [];

foreach ($testFiles as $testFile) {
    $testPath = __DIR__ . '/' . $testFile;
    
    if (!file_exists($testPath)) {
        echo "⚠️  Test file not found: $testFile\n";
        continue;
    }
    
    echo "Running $testFile...\n";
    
    // Run test in separate process to capture output
    $output = [];
    $returnCode = 0;
    exec("php \"$testPath\" 2>&1", $output, $returnCode);
    
    // Parse results from output
    $outputStr = implode("\n", $output);
    
    if (preg_match('/Passed: (\d+)/', $outputStr, $passedMatch)) {
        $passed = (int)$passedMatch[1];
        $totalPassed += $passed;
    }
    
    if (preg_match('/Failed: (\d+)/', $outputStr, $failedMatch)) {
        $failed = (int)$failedMatch[1];
        $totalFailed += $failed;
        
        if ($failed > 0) {
            $failedTests[] = $testFile;
        }
    }
    
    // Show status
    if ($returnCode === 0) {
        echo "✅ $testFile - PASSED\n";
    } else {
        echo "❌ $testFile - FAILED\n";
        echo $outputStr . "\n";
    }
    
    echo "\n";
}

echo "========================================\n";
echo "  FINAL RESULTS\n";
echo "========================================\n";
echo "Total Passed: $totalPassed\n";
echo "Total Failed: $totalFailed\n";
echo "Total Tests: " . ($totalPassed + $totalFailed) . "\n";

if (!empty($failedTests)) {
    echo "\nFailed Test Files:\n";
    foreach ($failedTests as $test) {
        echo "  - $test\n";
    }
}

echo "\n";

// Exit with appropriate code
exit($totalFailed > 0 ? 1 : 0);
