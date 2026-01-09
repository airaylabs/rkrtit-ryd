<?php
/**
 * Questions Configuration - FINAL OPTIMIZED VERSION
 * 
 * Technical: 3 soal (5 sub-pertanyaan) - 70% bobot
 * Psikotes: 3 skenario - 30% bobot
 * 
 * Scoring: 0-10 scale
 * - Bagus: 8-10 (LULUS)
 * - Butuh Review: 5-7 (REVIEW)
 * - Belum Lulus: <5 (TIDAK LULUS)
 * 
 * KALKULASI:
 * - Technical: 5 soal × 2 poin = 10 × 70% = 7 poin max
 * - Psikotes: 3 soal × (10/3) poin = 10 × 30% = 3 poin max
 * - Total: 7 + 3 = 10 poin max
 */

// ============================================
// TECHNICAL TEST - 3 SOAL (5 SUB-PERTANYAAN)
// ============================================

$technicalQuestions = [
    [
        'id' => 'tech1',
        'title' => 'SOAL 1: PHP & Laravel Fundamentals',
        'category' => 'php_laravel',
        'description' => 'Perhatikan code Laravel berikut:

```php
public function store(Request $request)
{
    $product = new Product();
    $product->name = $request->name;
    $product->price = $request->price;
    $product->save();
    
    return response()->json(["message" => "Product created"]);
}
```

Code di atas berjalan, tapi ada masalah keamanan dan best practice.',
        'questions' => [
            [
                'id' => 'tech1a',
                'type' => 'multiple',
                'label' => '1.1) Apa masalah utama pada code di atas?',
                'options' => [
                    ['value' => 'A', 'label' => 'Tidak ada validasi input dari request'],
                    ['value' => 'B', 'label' => 'Menggunakan new Product() bukan Product::create()'],
                    ['value' => 'C', 'label' => 'Response JSON formatnya salah'],
                    ['value' => 'D', 'label' => 'Method save() sudah deprecated']
                ],
                'correctAnswer' => 'A'
            ],
            [
                'id' => 'tech1b',
                'type' => 'multiple',
                'label' => '1.2) Di Laravel, cara yang BENAR untuk validasi input adalah?',
                'options' => [
                    ['value' => 'A', 'label' => '$request->validate([\'name\' => \'required\', \'price\' => \'required|numeric\'])'],
                    ['value' => 'B', 'label' => 'if(!empty($request->name)) { ... }'],
                    ['value' => 'C', 'label' => 'Validator::check($request->all())'],
                    ['value' => 'D', 'label' => '$request->filter([\'name\', \'price\'])']
                ],
                'correctAnswer' => 'A'
            ]
        ]
    ],
    [
        'id' => 'tech2',
        'title' => 'SOAL 2: SQL Database & Git Version Control',
        'category' => 'sql_git',
        'description' => 'Tabel `orders` menyimpan data transaksi:

```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM(\'pending\', \'paid\', \'completed\'),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

Tim butuh laporan total penjualan per customer untuk status "completed".

Selain itu, Anda sedang develop fitur di branch `feature/payment` dan ada bug urgent di production.',
        'questions' => [
            [
                'id' => 'tech2a',
                'type' => 'multiple',
                'label' => '2.1) Query mana yang BENAR untuk total penjualan per customer?',
                'options' => [
                    ['value' => 'A', 'label' => 'SELECT customer_id, SUM(total_amount) FROM orders WHERE status = \'completed\' GROUP BY customer_id'],
                    ['value' => 'B', 'label' => 'SELECT customer_id, COUNT(total_amount) FROM orders WHERE status = \'completed\''],
                    ['value' => 'C', 'label' => 'SELECT customer_id, total_amount FROM orders GROUP BY status'],
                    ['value' => 'D', 'label' => 'SELECT SUM(total_amount) FROM orders HAVING status = \'completed\'']
                ],
                'correctAnswer' => 'A'
            ],
            [
                'id' => 'tech2b',
                'type' => 'multiple',
                'label' => '2.2) Command Git untuk menyimpan sementara pekerjaan yang belum selesai agar bisa fix bug urgent?',
                'options' => [
                    ['value' => 'A', 'label' => 'git save'],
                    ['value' => 'B', 'label' => 'git stash'],
                    ['value' => 'C', 'label' => 'git store'],
                    ['value' => 'D', 'label' => 'git backup']
                ],
                'correctAnswer' => 'B'
            ]
        ]
    ],
    [
        'id' => 'tech3',
        'title' => 'SOAL 3: Automation & AI Workflow (n8n)',
        'category' => 'automation_ai',
        'description' => 'RayCorp menggunakan n8n untuk otomasi workflow bisnis. Contoh workflow:

```
Order Masuk (Webhook) → Validasi Data → Kirim Notifikasi WA → Simpan ke Database → Update Google Sheets
```

Anda diminta membuat workflow otomatis untuk notifikasi ketika ada order baru.',
        'questions' => [
            [
                'id' => 'tech3a',
                'type' => 'multiple',
                'label' => '3.1) Di n8n, node apa yang digunakan untuk menerima data dari sistem eksternal (seperti order baru dari website)?',
                'options' => [
                    ['value' => 'A', 'label' => 'HTTP Request node - untuk mengirim request ke API lain'],
                    ['value' => 'B', 'label' => 'Webhook node - untuk menerima data dari sistem eksternal'],
                    ['value' => 'C', 'label' => 'Trigger node - untuk menjalankan workflow manual'],
                    ['value' => 'D', 'label' => 'Start node - untuk memulai workflow']
                ],
                'correctAnswer' => 'B'
            ]
        ]
    ]
];

// ============================================
// PSIKOTES - 3 SKENARIO
// Mewakili: Multi-tasking, Adaptability, Initiative
// ============================================

$psikotesSkenario = [
    [
        'id' => 'psi1',
        'title' => 'Skenario 1: Multi-tasking & Prioritas',
        'category' => 'multitask',
        'description' => 'Anda sedang mengerjakan 2 project dengan deadline besok dan lusa. Rekan baru minta bantuan karena stuck dengan error selama 3 jam. Apa yang Anda lakukan?',
        'options' => [
            ['value' => 'A', 'label' => 'Fokus project sendiri dulu, minta rekan cari solusi sendiri'],
            ['value' => 'B', 'label' => 'Langsung bantu sampai selesai, deadline bisa dinego nanti'],
            ['value' => 'C', 'label' => 'Luangkan 15-20 menit kasih petunjuk arah, lalu kembali ke project'],
            ['value' => 'D', 'label' => 'Minta rekan catat masalahnya, bantu setelah deadline selesai'],
            ['value' => 'E', 'label' => 'Eskalasi ke lead bahwa ada konflik prioritas']
        ],
        'scoring' => ['A' => 2, 'B' => 3, 'C' => 5, 'D' => 3, 'E' => 4]
    ],
    [
        'id' => 'psi2',
        'title' => 'Skenario 2: Adaptability & Learning',
        'category' => 'adaptability',
        'description' => 'Project yang sudah Anda kerjakan 2 minggu tiba-tiba di-cancel. Anda diminta pindah ke project baru dengan teknologi yang belum pernah Anda pakai. Bagaimana respons Anda?',
        'options' => [
            ['value' => 'A', 'label' => 'Kecewa dan butuh waktu untuk menerima perubahan'],
            ['value' => 'B', 'label' => 'Excited dengan tantangan baru, langsung mulai belajar'],
            ['value' => 'C', 'label' => 'Netral saja, ini bagian dari pekerjaan'],
            ['value' => 'D', 'label' => 'Frustrasi karena effort terbuang, tapi tetap profesional'],
            ['value' => 'E', 'label' => 'Tanya management alasan perubahan untuk memahami konteks']
        ],
        'scoring' => ['A' => 2, 'B' => 5, 'C' => 4, 'D' => 3, 'E' => 4]
    ],
    [
        'id' => 'psi3',
        'title' => 'Skenario 3: Initiative & Problem Solving',
        'category' => 'initiative',
        'description' => 'Anda menemukan cara yang bisa menghemat 30% waktu development, tapi cara kerja tim sudah ditetapkan dan berjalan lama. Apa yang Anda lakukan?',
        'options' => [
            ['value' => 'A', 'label' => 'Simpan ide untuk diri sendiri, ikuti cara yang ada'],
            ['value' => 'B', 'label' => 'Implementasi di pekerjaan sendiri tanpa mengubah workflow tim'],
            ['value' => 'C', 'label' => 'Sampaikan ke lead/manager dengan data dan alasan yang jelas'],
            ['value' => 'D', 'label' => 'Diskusikan dulu dengan rekan setim untuk dapat feedback'],
            ['value' => 'E', 'label' => 'Tunggu momen yang tepat seperti retrospective meeting']
        ],
        'scoring' => ['A' => 1, 'B' => 3, 'C' => 5, 'D' => 4, 'E' => 4]
    ]
];

// ============================================
// ANSWER KEYS - TECHNICAL (5 soal)
// ============================================

$technicalAnswerKeys = [
    'tech1a' => ['correct' => 'A', 'explanation' => 'Input dari user harus selalu divalidasi untuk keamanan'],
    'tech1b' => ['correct' => 'A', 'explanation' => '$request->validate() adalah cara standar Laravel untuk validasi'],
    'tech2a' => ['correct' => 'A', 'explanation' => 'SUM dengan GROUP BY untuk aggregate per customer'],
    'tech2b' => ['correct' => 'B', 'explanation' => 'git stash menyimpan perubahan sementara'],
    'tech3a' => ['correct' => 'B', 'explanation' => 'Webhook node menerima data dari sistem eksternal']
];

// ============================================
// SCORING CONFIGURATION
// ============================================

/**
 * Technical: 5 soal, setiap soal bernilai 2 poin (max 10)
 * Lalu dikalikan 70% untuk kontribusi ke overall
 * 
 * Psikotes: 3 soal, setiap soal bernilai 1-5 (dikonversi ke 0-10)
 * Lalu dikalikan 30% untuk kontribusi ke overall
 */

$scoringConfig = [
    'technical' => [
        'total_questions' => 5,
        'points_per_question' => 2,  // 5 × 2 = 10 max
        'weight' => 0.70             // 70%
    ],
    'psikotes' => [
        'total_questions' => 3,
        'max_score_per_question' => 5,  // Scoring matrix 1-5
        'weight' => 0.30                 // 30%
    ]
];

// ============================================
// HELPER FUNCTIONS
// ============================================

function getAllTechnicalQuestions(): array {
    global $technicalQuestions;
    return $technicalQuestions;
}

function getPsikotesSkenario(): array {
    global $psikotesSkenario;
    return $psikotesSkenario;
}

function getTechnicalAnswerKeys(): array {
    global $technicalAnswerKeys;
    return $technicalAnswerKeys;
}

function getScoringConfig(): array {
    global $scoringConfig;
    return $scoringConfig;
}

function getScoreStatus(float $score): string {
    if ($score >= 8) {
        return 'Bagus';
    } elseif ($score >= 5) {
        return 'Butuh Review';
    } else {
        return 'Belum Lulus';
    }
}

function getOverallStatus(float $score): string {
    if ($score >= 8) {
        return 'LULUS';
    } elseif ($score >= 5) {
        return 'REVIEW';
    } else {
        return 'TIDAK LULUS';
    }
}
