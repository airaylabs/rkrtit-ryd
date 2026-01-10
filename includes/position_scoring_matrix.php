<?php
/**
 * Position Scoring Matrix Configuration
 * 
 * Defines position-based scoring thresholds and weights for:
 * - Logic Test: Different passing thresholds per position
 * - Psychology Test: Different weight matrices per position
 * - Work Pattern: Expected patterns for each position
 * 
 * Requirements: 7.1, 7.2, 7.3
 * 
 * Position Tracks:
 * - operator: Focus on compliance & honesty (Track A)
 * - staff: Focus on consistency & reflection (Track B)
 * - supervisor_management: Focus on accuracy & value awareness (Track C)
 * 
 * Work Patterns:
 * - presisi_monoton: R&D Lab, QC, Production detail - high precision, routine tolerance
 * - presisi_dinamis: Supervisor, Planner, Coordinator - precision + quick adaptation
 * - eksploratif_terstruktur: Product Dev, R&D concept - creativity within system
 * - eksploratif_dinamis: Creative, Branding, Campaign - out-of-the-box ideas
 */

class PositionScoringMatrix {
    
    /**
     * Logic Test Thresholds per Position
     * Requirement 7.2: Different passing thresholds
     * 
     * @var array<string, int>
     */
    const LOGIC_THRESHOLD = [
        'operator_produksi'    => 12,  // ≥12/25 for operators
        'staff_kantor'         => 17,  // ≥17/25 for office staff
        'supervisor'           => 20,  // ≥20/25 for supervisors
        'rnd_qc_lab'           => 17,  // ≥17/25 for R&D/QC
        'kreatif'              => 14,  // ≥14/25 for creative
        'product_development'  => 17,  // ≥17/25 for product dev
        'management'           => 20,  // ≥20/25 for management
    ];
    
    /**
     * Position Track Mapping
     * Maps each position to its assessment track
     * 
     * @var array<string, string>
     */
    const POSITION_TRACK = [
        'operator_produksi'    => 'operator',
        'staff_kantor'         => 'staff',
        'supervisor'           => 'supervisor_management',
        'rnd_qc_lab'           => 'staff',
        'kreatif'              => 'staff',
        'product_development'  => 'staff',
        'management'           => 'supervisor_management',
    ];
    
    /**
     * Expected Work Pattern per Position
     * Requirement 7.3: Position to pattern mapping
     * NULL means flexible (all patterns accepted)
     * 
     * @var array<string, string|null>
     */
    const EXPECTED_WORK_PATTERN = [
        'operator_produksi'    => 'presisi_monoton',
        'staff_kantor'         => null,  // Flexible - all patterns accepted
        'supervisor'           => 'presisi_dinamis',
        'rnd_qc_lab'           => 'presisi_monoton',
        'kreatif'              => 'eksploratif_dinamis',
        'product_development'  => 'eksploratif_terstruktur',
        'management'           => 'presisi_dinamis',
    ];
    
    /**
     * Psychology Weight Matrix per Position
     * Requirement 7.3: Different weights for each psychology section
     * 
     * Values: 0=tidak penting, 1=rendah, 2=sedang, 3=tinggi, 4=sangat tinggi
     * 
     * Sections:
     * - section_a: Ketelitian & Daya Tahan
     * - section_b: Stabilitas & Respon Kejenuhan
     * - section_c: Pola Respon Perubahan (Dinamis)
     * - section_d: Orientasi Kerja (Eksplorasi)
     * - section_e: Logika Kerja Dasar
     * 
     * @var array<string, array<string, int>>
     */
    const PSYCHOLOGY_WEIGHT_MATRIX = [
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
     * Describes each work pattern and suitable positions
     * 
     * @var array<string, array>
     */
    const WORK_PATTERN_DEFINITIONS = [
        'presisi_monoton' => [
            'name' => 'Presisi Monoton',
            'description' => 'Kerja presisi dengan rutinitas tinggi. Butuh ketelitian tinggi dan tahan rutinitas.',
            'suitable_positions' => ['operator_produksi', 'rnd_qc_lab'],
            'characteristics' => [
                'Ketelitian tinggi',
                'Tahan tugas berulang',
                'Fokus pada detail',
                'Konsisten dalam rutinitas',
            ],
        ],
        'presisi_dinamis' => [
            'name' => 'Presisi Dinamis',
            'description' => 'Kerja presisi dengan adaptasi cepat. Butuh ketelitian + kemampuan koordinasi.',
            'suitable_positions' => ['supervisor', 'management'],
            'characteristics' => [
                'Ketelitian tinggi',
                'Adaptasi cepat',
                'Koordinasi tim',
                'Pengambilan keputusan',
            ],
        ],
        'eksploratif_terstruktur' => [
            'name' => 'Eksploratif Terstruktur',
            'description' => 'Kreativitas dalam sistem terstruktur. Butuh inovasi dengan framework jelas.',
            'suitable_positions' => ['product_development'],
            'characteristics' => [
                'Kreativitas terarah',
                'Bekerja dalam sistem',
                'Inovasi terukur',
                'Problem solving',
            ],
        ],
        'eksploratif_dinamis' => [
            'name' => 'Eksploratif Dinamis',
            'description' => 'Kreativitas bebas dengan adaptasi tinggi. Butuh ide out-of-the-box.',
            'suitable_positions' => ['kreatif'],
            'characteristics' => [
                'Ide out-of-the-box',
                'Fleksibilitas tinggi',
                'Tidak terikat rutinitas',
                'Eksplorasi bebas',
            ],
        ],
    ];
    
    /**
     * Position Display Names
     * Human-readable names for each position
     * 
     * @var array<string, string>
     */
    const POSITION_NAMES = [
        'operator_produksi'    => 'Operator Produksi',
        'staff_kantor'         => 'Staff Kantor (Admin/Finance/dll)',
        'supervisor'           => 'Supervisor',
        'rnd_qc_lab'           => 'R&D / QC / Lab',
        'kreatif'              => 'Kreatif / Branding / Campaign',
        'product_development'  => 'Product Development',
        'management'           => 'Management',
    ];
    
    /**
     * Get logic test threshold for a position
     * 
     * @param string $position Position code
     * @return int Threshold value (default 17 if position not found)
     */
    public static function getLogicThreshold(string $position): int {
        return self::LOGIC_THRESHOLD[$position] ?? 17;
    }
    
    /**
     * Get position track for a position
     * 
     * @param string $position Position code
     * @return string Track name (default 'staff' if position not found)
     */
    public static function getPositionTrack(string $position): string {
        return self::POSITION_TRACK[$position] ?? 'staff';
    }
    
    /**
     * Get expected work pattern for a position
     * 
     * @param string $position Position code
     * @return string|null Expected pattern or null if flexible
     */
    public static function getExpectedWorkPattern(string $position): ?string {
        return self::EXPECTED_WORK_PATTERN[$position] ?? null;
    }
    
    /**
     * Get psychology weight matrix for a position
     * 
     * @param string $position Position code
     * @return array<string, int> Weight matrix
     */
    public static function getPsychologyWeights(string $position): array {
        return self::PSYCHOLOGY_WEIGHT_MATRIX[$position] ?? self::PSYCHOLOGY_WEIGHT_MATRIX['staff_kantor'];
    }
    
    /**
     * Get position display name
     * 
     * @param string $position Position code
     * @return string Display name
     */
    public static function getPositionName(string $position): string {
        return self::POSITION_NAMES[$position] ?? $position;
    }
    
    /**
     * Get work pattern definition
     * 
     * @param string $pattern Pattern code
     * @return array|null Pattern definition or null if not found
     */
    public static function getWorkPatternDefinition(string $pattern): ?array {
        return self::WORK_PATTERN_DEFINITIONS[$pattern] ?? null;
    }
    
    /**
     * Get all available positions
     * 
     * @return array<string, string> Position codes and names
     */
    public static function getAllPositions(): array {
        return self::POSITION_NAMES;
    }
    
    /**
     * Get all work patterns
     * 
     * @return array<string, array> All work pattern definitions
     */
    public static function getAllWorkPatterns(): array {
        return self::WORK_PATTERN_DEFINITIONS;
    }
    
    /**
     * Check if a work pattern matches the expected pattern for a position
     * 
     * @param string $actualPattern Candidate's actual work pattern
     * @param string $position Position code
     * @return bool True if pattern matches or position is flexible
     */
    public static function isPatternMatch(string $actualPattern, string $position): bool {
        $expectedPattern = self::getExpectedWorkPattern($position);
        
        // If position is flexible (null), all patterns match
        if ($expectedPattern === null) {
            return true;
        }
        
        return $actualPattern === $expectedPattern;
    }
    
    /**
     * Get alternative positions that match a work pattern
     * 
     * @param string $pattern Work pattern code
     * @return array<string> List of position codes that match the pattern
     */
    public static function getAlternativePositions(string $pattern): array {
        $alternatives = [];
        
        foreach (self::EXPECTED_WORK_PATTERN as $position => $expectedPattern) {
            // Include positions where pattern matches or position is flexible
            if ($expectedPattern === $pattern || $expectedPattern === null) {
                $alternatives[] = $position;
            }
        }
        
        return $alternatives;
    }
    
    /**
     * Validate if a position code is valid
     * 
     * @param string $position Position code to validate
     * @return bool True if valid
     */
    public static function isValidPosition(string $position): bool {
        return isset(self::POSITION_NAMES[$position]);
    }
}
