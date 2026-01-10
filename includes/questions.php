<?php
/**
 * Questions Configuration - Multi-Division Recruitment System
 * 
 * LOGIC TEST: 25 soal dalam 30 menit
 * - Section A: Logika Pola Angka (4 soal)
 * - Section B: Logika Instruksi (3 soal)
 * - Section C: Hitung Praktis (5 soal)
 * - Section D: Ketelitian & Perbedaan (5 soal)
 * - Section E: Logika Urutan Kerja (3 soal)
 * - Section F: Logika Situasional (2 soal)
 * - Section G: Pemahaman Sederhana (3 soal)
 * 
 * PSYCHOLOGY TEST: 5 bagian (A-E)
 * - Section A: Ketelitian & Daya Tahan (4 sub-tests dalam 7 menit)
 * - Section B: Stabilitas & Respon Kejenuhan (4 soal)
 * - Section C: Pola Respon Perubahan (4 soal)
 * - Section D: Orientasi Kerja (4 soal)
 * - Section E: Logika Kerja Dasar (6 soal)
 * 
 * Requirements: 3.1, 4.1, 4.2, 4.3, 4.4
 */

// ============================================
// LOGIC TEST - 25 SOAL (7 SECTIONS)
// ============================================

$logicQuestions = [
    // Section A: Logika Pola Angka (4 soal)
    'section_a' => [
        'title' => 'Bagian A: Logika Pola Angka',
        'description' => 'Lengkapi deret angka berikut dengan memilih jawaban yang tepat.',
        'time_limit' => null, // No separate time limit, part of 30 min total
        'questions' => [
            [
                'id' => 'A1',
                'type' => 'multiple_choice',
                'question' => 'Lengkapi deret berikut: 2 - 4 - 6 - 8 - ... - ...',
                'options' => [
                    ['value' => 'A', 'label' => '9 - 10'],
                    ['value' => 'B', 'label' => '10 - 12'],
                    ['value' => 'C', 'label' => '10 - 11'],
                    ['value' => 'D', 'label' => '12 - 14']
                ],
                'correct_answer' => 'B'
            ],
            [
                'id' => 'A2',
                'type' => 'multiple_choice',
                'question' => 'Lengkapi deret berikut: 3 - 6 - 9 - 12 - ... - ...',
                'options' => [
                    ['value' => 'A', 'label' => '14 - 16'],
                    ['value' => 'B', 'label' => '13 - 14'],
                    ['value' => 'C', 'label' => '15 - 18'],
                    ['value' => 'D', 'label' => '15 - 17']
                ],
                'correct_answer' => 'C'
            ],
            [
                'id' => 'A3',
                'type' => 'multiple_choice',
                'question' => 'Lengkapi deret berikut: 1 - 2 - 4 - 7 - 11 - ...',
                'options' => [
                    ['value' => 'A', 'label' => '15'],
                    ['value' => 'B', 'label' => '16'],
                    ['value' => 'C', 'label' => '14'],
                    ['value' => 'D', 'label' => '17']
                ],
                'correct_answer' => 'B'
            ],
            [
                'id' => 'A4',
                'type' => 'multiple_choice',
                'question' => 'Lengkapi deret berikut: 5 - 10 - 10 - 15 - 15 - ...',
                'options' => [
                    ['value' => 'A', 'label' => '15'],
                    ['value' => 'B', 'label' => '25'],
                    ['value' => 'C', 'label' => '20'],
                    ['value' => 'D', 'label' => '30']
                ],
                'correct_answer' => 'C'
            ]
        ]
    ],

    // Section B: Logika Instruksi (3 soal)
    'section_b' => [
        'title' => 'Bagian B: Logika Instruksi',
        'description' => 'Ikuti instruksi dengan teliti dan pilih jawaban yang benar.',
        'time_limit' => null,
        'questions' => [
            [
                'id' => 'B1',
                'type' => 'multiple_choice',
                'question' => 'Instruksi: "Lingkari angka GENAP dan coret angka GANJIL dari: 3 4 6 7 9 10". Berapa jumlah angka yang harus dilingkari?',
                'options' => [
                    ['value' => 'A', 'label' => '2'],
                    ['value' => 'B', 'label' => '3'],
                    ['value' => 'C', 'label' => '4'],
                    ['value' => 'D', 'label' => '5']
                ],
                'correct_answer' => 'B'
            ],
            [
                'id' => 'B2',
                'type' => 'multiple_choice',
                'question' => 'Instruksi: "Dari deret A B C D E, tuliskan huruf ke-2 dan ke-4". Jawaban yang benar adalah:',
                'options' => [
                    ['value' => 'A', 'label' => 'A dan C'],
                    ['value' => 'B', 'label' => 'B dan D'],
                    ['value' => 'C', 'label' => 'B dan E'],
                    ['value' => 'D', 'label' => 'C dan D']
                ],
                'correct_answer' => 'B'
            ],
            [
                'id' => 'B3',
                'type' => 'multiple_choice',
                'question' => 'Instruksi: "Jika hari ini Senin, maka 3 hari lagi adalah hari apa?". Jawaban yang benar adalah:',
                'options' => [
                    ['value' => 'A', 'label' => 'Rabu'],
                    ['value' => 'B', 'label' => 'Kamis'],
                    ['value' => 'C', 'label' => 'Jumat'],
                    ['value' => 'D', 'label' => 'Selasa']
                ],
                'correct_answer' => 'B'
            ]
        ]
    ],

    // Section C: Hitung Praktis (5 soal)
    'section_c' => [
        'title' => 'Bagian C: Hitung Praktis',
        'description' => 'Selesaikan soal hitungan praktis berikut.',
        'time_limit' => null,
        'questions' => [
            [
                'id' => 'C1',
                'type' => 'multiple_choice',
                'question' => 'Jika 1 kotak berisi 12 unit produk, berapa unit produk dalam 6 kotak?',
                'options' => [
                    ['value' => 'A', 'label' => '60 unit'],
                    ['value' => 'B', 'label' => '72 unit'],
                    ['value' => 'C', 'label' => '66 unit'],
                    ['value' => 'D', 'label' => '78 unit']
                ],
                'correct_answer' => 'B'
            ],
            [
                'id' => 'C2',
                'type' => 'multiple_choice',
                'question' => 'Satu produk membutuhkan 2 gram bahan. Berapa gram bahan yang dibutuhkan untuk 50 produk?',
                'options' => [
                    ['value' => 'A', 'label' => '25 gram'],
                    ['value' => 'B', 'label' => '52 gram'],
                    ['value' => 'C', 'label' => '100 gram'],
                    ['value' => 'D', 'label' => '150 gram']
                ],
                'correct_answer' => 'C'
            ],
            [
                'id' => 'C3',
                'type' => 'multiple_choice',
                'question' => 'Jika 6 orang menyelesaikan pekerjaan dalam 8 jam, berapa jam yang dibutuhkan 12 orang untuk pekerjaan yang sama?',
                'options' => [
                    ['value' => 'A', 'label' => '16 jam'],
                    ['value' => 'B', 'label' => '6 jam'],
                    ['value' => 'C', 'label' => '4 jam'],
                    ['value' => 'D', 'label' => '2 jam']
                ],
                'correct_answer' => 'C'
            ],
            [
                'id' => 'C4',
                'type' => 'multiple_choice',
                'question' => 'Harga 1 botol Rp 15.000. Jika beli 5 botol dapat diskon 10%, berapa total yang harus dibayar?',
                'options' => [
                    ['value' => 'A', 'label' => 'Rp 67.500'],
                    ['value' => 'B', 'label' => 'Rp 70.000'],
                    ['value' => 'C', 'label' => 'Rp 75.000'],
                    ['value' => 'D', 'label' => 'Rp 72.500']
                ],
                'correct_answer' => 'A'
            ],
            [
                'id' => 'C5',
                'type' => 'multiple_choice',
                'question' => 'Mesin produksi menghasilkan 120 unit per jam. Berapa unit yang dihasilkan dalam 1 shift (8 jam)?',
                'options' => [
                    ['value' => 'A', 'label' => '860 unit'],
                    ['value' => 'B', 'label' => '960 unit'],
                    ['value' => 'C', 'label' => '1.060 unit'],
                    ['value' => 'D', 'label' => '1.200 unit']
                ],
                'correct_answer' => 'B'
            ]
        ]
    ],

    // Section D: Ketelitian & Perbedaan (5 soal)
    'section_d' => [
        'title' => 'Bagian D: Ketelitian & Perbedaan',
        'description' => 'Perhatikan dengan teliti dan temukan perbedaan atau jawaban yang tepat.',
        'time_limit' => null,
        'questions' => [
            [
                'id' => 'D1',
                'type' => 'multiple_choice',
                'question' => 'Manakah yang BERBEDA dari pilihan berikut? A. ■■■□  B. ■■■□  C. ■■□□  D. ■■■□',
                'options' => [
                    ['value' => 'A', 'label' => 'A'],
                    ['value' => 'B', 'label' => 'B'],
                    ['value' => 'C', 'label' => 'C'],
                    ['value' => 'D', 'label' => 'D']
                ],
                'correct_answer' => 'C'
            ],
            [
                'id' => 'D2',
                'type' => 'multiple_choice',
                'question' => 'Manakah pola yang TIDAK SESUAI? A. ▲▲●▲  B. ▲▲●▲  C. ▲●●▲  D. ▲▲●▲',
                'options' => [
                    ['value' => 'A', 'label' => 'A'],
                    ['value' => 'B', 'label' => 'B'],
                    ['value' => 'C', 'label' => 'C'],
                    ['value' => 'D', 'label' => 'D']
                ],
                'correct_answer' => 'C'
            ],
            [
                'id' => 'D3',
                'type' => 'multiple_choice',
                'question' => 'Dari deretan huruf "ABCBDABEBFBA", berapa kali huruf B muncul?',
                'options' => [
                    ['value' => 'A', 'label' => '3 kali'],
                    ['value' => 'B', 'label' => '4 kali'],
                    ['value' => 'C', 'label' => '5 kali'],
                    ['value' => 'D', 'label' => '6 kali']
                ],
                'correct_answer' => 'C'
            ],
            [
                'id' => 'D4',
                'type' => 'multiple_choice',
                'question' => 'Dari deretan angka "7392757671478707", berapa kali angka 7 muncul?',
                'options' => [
                    ['value' => 'A', 'label' => '5 kali'],
                    ['value' => 'B', 'label' => '6 kali'],
                    ['value' => 'C', 'label' => '7 kali'],
                    ['value' => 'D', 'label' => '8 kali']
                ],
                'correct_answer' => 'B'
            ],
            [
                'id' => 'D5',
                'type' => 'multiple_choice',
                'question' => 'Dari daftar angka: 14, 3, 22, 7, 9 - manakah angka TERKECIL dan TERBESAR?',
                'options' => [
                    ['value' => 'A', 'label' => 'Terkecil: 3, Terbesar: 22'],
                    ['value' => 'B', 'label' => 'Terkecil: 7, Terbesar: 22'],
                    ['value' => 'C', 'label' => 'Terkecil: 3, Terbesar: 14'],
                    ['value' => 'D', 'label' => 'Terkecil: 9, Terbesar: 22']
                ],
                'correct_answer' => 'A'
            ]
        ]
    ],

    // Section E: Logika Urutan Kerja (3 soal)
    'section_e' => [
        'title' => 'Bagian E: Logika Urutan Kerja',
        'description' => 'Tentukan urutan langkah kerja yang benar.',
        'time_limit' => null,
        'questions' => [
            [
                'id' => 'E1',
                'type' => 'multiple_choice',
                'question' => 'Urutan yang BENAR untuk menyalakan mesin produksi adalah:
1. Tekan tombol START
2. Periksa bahan baku sudah tersedia
3. Pastikan area kerja bersih
4. Cek kondisi mesin (oli, kabel)
5. Tunggu mesin stabil',
                'options' => [
                    ['value' => 'A', 'label' => '1 - 2 - 3 - 4 - 5'],
                    ['value' => 'B', 'label' => '3 - 4 - 2 - 1 - 5'],
                    ['value' => 'C', 'label' => '2 - 3 - 4 - 1 - 5'],
                    ['value' => 'D', 'label' => '4 - 3 - 2 - 1 - 5']
                ],
                'correct_answer' => 'B'
            ],
            [
                'id' => 'E2',
                'type' => 'multiple_choice',
                'question' => 'Urutan yang BENAR untuk menangani komplain pelanggan adalah:
1. Berikan solusi atau kompensasi
2. Dengarkan keluhan dengan sabar
3. Ucapkan terima kasih dan follow up
4. Minta maaf atas ketidaknyamanan
5. Catat detail masalah',
                'options' => [
                    ['value' => 'A', 'label' => '2 - 4 - 5 - 1 - 3'],
                    ['value' => 'B', 'label' => '4 - 2 - 5 - 1 - 3'],
                    ['value' => 'C', 'label' => '1 - 2 - 3 - 4 - 5'],
                    ['value' => 'D', 'label' => '2 - 5 - 4 - 1 - 3']
                ],
                'correct_answer' => 'A'
            ],
            [
                'id' => 'E3',
                'type' => 'multiple_choice',
                'question' => 'Urutan yang BENAR untuk proses quality control produk adalah:
1. Beri label PASS atau REJECT
2. Ambil sampel produk
3. Catat hasil di form QC
4. Periksa sesuai standar
5. Pisahkan produk yang tidak lolos',
                'options' => [
                    ['value' => 'A', 'label' => '2 - 4 - 1 - 3 - 5'],
                    ['value' => 'B', 'label' => '1 - 2 - 3 - 4 - 5'],
                    ['value' => 'C', 'label' => '2 - 4 - 3 - 1 - 5'],
                    ['value' => 'D', 'label' => '4 - 2 - 1 - 3 - 5']
                ],
                'correct_answer' => 'A'
            ]
        ]
    ],

    // Section F: Logika Situasional (2 soal)
    'section_f' => [
        'title' => 'Bagian F: Logika Situasional',
        'description' => 'Pilih respon yang paling tepat untuk situasi kerja berikut.',
        'time_limit' => null,
        'questions' => [
            [
                'id' => 'F1',
                'type' => 'multiple_choice',
                'question' => 'Anda terlambat masuk kerja karena macet. Apa yang sebaiknya Anda lakukan?',
                'options' => [
                    ['value' => 'A', 'label' => 'Langsung masuk dan bekerja seperti biasa tanpa bilang siapa-siapa'],
                    ['value' => 'B', 'label' => 'Hubungi atasan untuk memberitahu keterlambatan dan alasannya'],
                    ['value' => 'C', 'label' => 'Minta teman untuk absen dulu, nanti bilang kalau sudah sampai'],
                    ['value' => 'D', 'label' => 'Pulang saja karena sudah terlambat']
                ],
                'correct_answer' => 'B'
            ],
            [
                'id' => 'F2',
                'type' => 'multiple_choice',
                'question' => 'Anda menemukan kesalahan pada produk yang sudah dikemas. Apa yang sebaiknya Anda lakukan?',
                'options' => [
                    ['value' => 'A', 'label' => 'Biarkan saja karena sudah dikemas dan akan repot membukanya'],
                    ['value' => 'B', 'label' => 'Laporkan ke supervisor dan tunggu instruksi'],
                    ['value' => 'C', 'label' => 'Perbaiki sendiri tanpa memberitahu siapa-siapa'],
                    ['value' => 'D', 'label' => 'Tunggu sampai ada yang komplain baru diperbaiki']
                ],
                'correct_answer' => 'B'
            ]
        ]
    ],

    // Section G: Pemahaman Sederhana (3 soal)
    'section_g' => [
        'title' => 'Bagian G: Pemahaman Sederhana',
        'description' => 'Pilih jawaban yang paling tepat berdasarkan pemahaman Anda.',
        'time_limit' => null,
        'questions' => [
            [
                'id' => 'G1',
                'type' => 'multiple_choice',
                'question' => 'Mana yang termasuk contoh "bekerja dengan teliti"?',
                'options' => [
                    ['value' => 'A', 'label' => 'Menyelesaikan pekerjaan secepat mungkin tanpa memeriksa ulang'],
                    ['value' => 'B', 'label' => 'Memeriksa hasil kerja sebelum diserahkan ke tahap berikutnya'],
                    ['value' => 'C', 'label' => 'Mengerjakan banyak tugas sekaligus agar cepat selesai'],
                    ['value' => 'D', 'label' => 'Menyerahkan pekerjaan ke orang lain yang lebih ahli']
                ],
                'correct_answer' => 'B'
            ],
            [
                'id' => 'G2',
                'type' => 'multiple_choice',
                'question' => 'Apa yang dimaksud dengan "SOP" dalam dunia kerja?',
                'options' => [
                    ['value' => 'A', 'label' => 'Surat Operasional Perusahaan'],
                    ['value' => 'B', 'label' => 'Standar Operasional Prosedur - panduan langkah kerja yang baku'],
                    ['value' => 'C', 'label' => 'Sistem Online Perusahaan'],
                    ['value' => 'D', 'label' => 'Sertifikat Operasional Produksi']
                ],
                'correct_answer' => 'B'
            ],
            [
                'id' => 'G3',
                'type' => 'multiple_choice',
                'question' => 'Mengapa penting untuk datang tepat waktu ke tempat kerja?',
                'options' => [
                    ['value' => 'A', 'label' => 'Agar tidak dipotong gaji'],
                    ['value' => 'B', 'label' => 'Agar bisa pulang lebih awal'],
                    ['value' => 'C', 'label' => 'Menunjukkan tanggung jawab dan tidak mengganggu jadwal kerja tim'],
                    ['value' => 'D', 'label' => 'Agar bisa dapat tempat parkir yang bagus']
                ],
                'correct_answer' => 'C'
            ]
        ]
    ]
];

// ============================================
// LOGIC TEST ANSWER KEYS
// ============================================

$logicAnswerKeys = [
    // Section A: Logika Pola Angka
    'A1' => ['correct' => 'B', 'explanation' => 'Deret +2: 2, 4, 6, 8, 10, 12'],
    'A2' => ['correct' => 'C', 'explanation' => 'Deret +3: 3, 6, 9, 12, 15, 18'],
    'A3' => ['correct' => 'B', 'explanation' => 'Deret +1, +2, +3, +4, +5: 1, 2, 4, 7, 11, 16'],
    'A4' => ['correct' => 'C', 'explanation' => 'Pola: +5, 0, +5, 0, +5: 5, 10, 10, 15, 15, 20'],
    
    // Section B: Logika Instruksi
    'B1' => ['correct' => 'B', 'explanation' => 'Angka genap: 4, 6, 10 = 3 angka'],
    'B2' => ['correct' => 'B', 'explanation' => 'Huruf ke-2 = B, huruf ke-4 = D'],
    'B3' => ['correct' => 'B', 'explanation' => 'Senin + 3 hari = Kamis'],
    
    // Section C: Hitung Praktis
    'C1' => ['correct' => 'B', 'explanation' => '12 × 6 = 72 unit'],
    'C2' => ['correct' => 'C', 'explanation' => '2 × 50 = 100 gram'],
    'C3' => ['correct' => 'C', 'explanation' => '6 orang × 8 jam = 48 jam-orang. 48 ÷ 12 = 4 jam'],
    'C4' => ['correct' => 'A', 'explanation' => '5 × 15.000 = 75.000. Diskon 10% = 7.500. Total = 67.500'],
    'C5' => ['correct' => 'B', 'explanation' => '120 × 8 = 960 unit'],
    
    // Section D: Ketelitian & Perbedaan
    'D1' => ['correct' => 'C', 'explanation' => 'C memiliki pola ■■□□ berbeda dari yang lain ■■■□'],
    'D2' => ['correct' => 'C', 'explanation' => 'C memiliki pola ▲●●▲ berbeda dari yang lain ▲▲●▲'],
    'D3' => ['correct' => 'C', 'explanation' => 'Huruf B muncul di posisi: 2, 4, 7, 9, 11 = 5 kali'],
    'D4' => ['correct' => 'B', 'explanation' => 'Angka 7 muncul di posisi: 1, 5, 7, 10, 14, 16 = 6 kali'],
    'D5' => ['correct' => 'A', 'explanation' => 'Dari 14, 3, 22, 7, 9: terkecil = 3, terbesar = 22'],
    
    // Section E: Logika Urutan Kerja
    'E1' => ['correct' => 'B', 'explanation' => 'Urutan: Bersihkan area → Cek mesin → Siapkan bahan → Start → Tunggu stabil'],
    'E2' => ['correct' => 'A', 'explanation' => 'Urutan: Dengarkan → Minta maaf → Catat → Beri solusi → Follow up'],
    'E3' => ['correct' => 'A', 'explanation' => 'Urutan: Ambil sampel → Periksa → Beri label → Catat → Pisahkan reject'],
    
    // Section F: Logika Situasional
    'F1' => ['correct' => 'B', 'explanation' => 'Komunikasi dengan atasan menunjukkan tanggung jawab dan kejujuran'],
    'F2' => ['correct' => 'B', 'explanation' => 'Melaporkan ke supervisor adalah prosedur yang benar untuk menjaga kualitas'],
    
    // Section G: Pemahaman Sederhana
    'G1' => ['correct' => 'B', 'explanation' => 'Bekerja teliti = memeriksa hasil sebelum diserahkan'],
    'G2' => ['correct' => 'B', 'explanation' => 'SOP = Standar Operasional Prosedur'],
    'G3' => ['correct' => 'C', 'explanation' => 'Tepat waktu menunjukkan tanggung jawab dan menghargai tim']
];


// ============================================
// PSYCHOLOGY TEST - 5 SECTIONS (A-E)
// ============================================

$psychologyQuestions = [
    // Section A: Ketelitian & Daya Tahan (4 sub-tests dalam 7 menit)
    'section_a' => [
        'title' => 'Bagian A: Ketelitian & Daya Tahan',
        'description' => 'Dalam waktu 7 menit, kerjakan tugas berikut dengan teliti.',
        'time_limit' => 420, // 7 minutes in seconds
        'type' => 'interactive', // Special type for grid-based tests
        'sub_tests' => [
            [
                'id' => 'A1',
                'title' => 'Ketelitian Angka',
                'instruction' => 'Coret semua angka 7 dari deretan berikut:',
                'type' => 'mark_target',
                'target' => '7',
                'action' => 'coret',
                'rows' => [
                    '7 3 9 7 2 7 5 6 7 1 4 7 8 7 0 7',
                    '2 7 4 1 7 8 3 7 9 7 5 0 7 6 7 2',
                    '9 1 7 5 7 3 7 8 2 7 4 7 6 0 7 1',
                    '7 8 2 7 4 9 7 1 7 5 3 7 0 7 6 7',
                    '3 7 6 7 1 7 9 2 7 4 7 8 7 5 0 7'
                ],
                'correct_count' => 40 // Total 7s to find
            ],
            [
                'id' => 'A2',
                'title' => 'Konsistensi Simbol',
                'instruction' => 'Lingkari simbol ▲ dan coret simbol ■:',
                'type' => 'mark_dual',
                'target_circle' => '▲',
                'target_cross' => '■',
                'rows' => [
                    '▲ ■ ● ▲ ■ ▲ ● ■ ▲ ▲ ■ ● ■ ▲',
                    '■ ▲ ▲ ● ■ ▲ ■ ● ▲ ■ ▲ ■ ● ▲',
                    '▲ ● ■ ▲ ▲ ■ ● ▲ ■ ▲ ● ■ ▲ ■',
                    '● ▲ ■ ▲ ● ■ ▲ ▲ ■ ● ▲ ■ ▲ ●'
                ],
                'correct_circle_count' => 24,
                'correct_cross_count' => 16
            ],
            [
                'id' => 'A3',
                'title' => 'Ketelitian Huruf',
                'instruction' => 'Coret semua huruf B dari deretan berikut:',
                'type' => 'mark_target',
                'target' => 'B',
                'action' => 'coret',
                'rows' => [
                    'A B C B D A B E B F B A C B D',
                    'B A D B C E B A B F D B C A B',
                    'C B A B E D B F A B C B D E B',
                    'D A B C B F B E A B D C B A B'
                ],
                'correct_count' => 24
            ],
            [
                'id' => 'A4',
                'title' => 'Pola Konsistensi',
                'instruction' => 'Lingkari angka GANJIL dan coret angka GENAP:',
                'type' => 'mark_odd_even',
                'rows' => [
                    '2 3 4 5 6 7 8 9 10 11 12 13 14 15',
                    '16 17 18 19 20 21 22 23 24 25 26 27',
                    '1 4 7 2 9 6 3 8 5 10 11 14 13 16',
                    '28 29 30 31 32 33 34 35 36 37 38 39'
                ],
                'correct_circle_count' => 24, // Odd numbers
                'correct_cross_count' => 24  // Even numbers
            ]
        ]
    ],

    // Section B: Stabilitas & Respon Kejenuhan (4 soal)
    'section_b' => [
        'title' => 'Bagian B: Stabilitas & Respon Kejenuhan',
        'description' => 'Pilih jawaban yang paling menggambarkan diri Anda.',
        'time_limit' => null,
        'questions' => [
            [
                'id' => 'B1',
                'type' => 'multiple_choice',
                'question' => 'Saat mengerjakan tugas yang sama berulang-ulang, saya biasanya:',
                'options' => [
                    ['value' => 'A', 'label' => 'Tetap fokus dan menyelesaikan dengan rapi'],
                    ['value' => 'B', 'label' => 'Tetap bekerja meski mulai jenuh'],
                    ['value' => 'C', 'label' => 'Mudah kehilangan fokus'],
                    ['value' => 'D', 'label' => 'Mencari cara agar bisa berhenti']
                ],
                'scoring' => ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1]
            ],
            [
                'id' => 'B2',
                'type' => 'multiple_choice',
                'question' => 'Jika pekerjaan terasa membosankan:',
                'options' => [
                    ['value' => 'A', 'label' => 'Saya tetap menyelesaikannya dengan tanggung jawab'],
                    ['value' => 'B', 'label' => 'Saya menyelesaikan, tapi kualitas menurun'],
                    ['value' => 'C', 'label' => 'Saya menunda-nunda'],
                    ['value' => 'D', 'label' => 'Saya mengeluh dan kehilangan semangat']
                ],
                'scoring' => ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1]
            ],
            [
                'id' => 'B3',
                'type' => 'multiple_choice',
                'question' => 'Jika pekerjaan menuntut ketelitian tinggi dalam waktu lama:',
                'options' => [
                    ['value' => 'A', 'label' => 'Saya justru merasa tertantang'],
                    ['value' => 'B', 'label' => 'Saya bisa bertahan meski lelah'],
                    ['value' => 'C', 'label' => 'Saya cepat ingin pindah tugas'],
                    ['value' => 'D', 'label' => 'Saya kehilangan motivasi']
                ],
                'scoring' => ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1]
            ],
            [
                'id' => 'B4',
                'type' => 'multiple_choice',
                'question' => 'Dalam pekerjaan rutin, saya lebih sering merasa:',
                'options' => [
                    ['value' => 'A', 'label' => 'Tenang dan stabil'],
                    ['value' => 'B', 'label' => 'Netral'],
                    ['value' => 'C', 'label' => 'Gelisah'],
                    ['value' => 'D', 'label' => 'Tertekan']
                ],
                'scoring' => ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1]
            ]
        ]
    ],

    // Section C: Pola Respon Perubahan (4 soal)
    'section_c' => [
        'title' => 'Bagian C: Pola Respon Perubahan',
        'description' => 'Pilih jawaban yang paling menggambarkan diri Anda.',
        'time_limit' => null,
        'questions' => [
            [
                'id' => 'C1',
                'type' => 'multiple_choice',
                'question' => 'Jika instruksi kerja berubah di tengah jalan:',
                'options' => [
                    ['value' => 'A', 'label' => 'Saya menyesuaikan dan lanjut bekerja'],
                    ['value' => 'B', 'label' => 'Saya bertanya lalu menyesuaikan'],
                    ['value' => 'C', 'label' => 'Saya bingung dan melambat'],
                    ['value' => 'D', 'label' => 'Saya kesal dan kehilangan fokus']
                ],
                'scoring' => ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1]
            ],
            [
                'id' => 'C2',
                'type' => 'multiple_choice',
                'question' => 'Jika rencana kerja tidak sesuai harapan:',
                'options' => [
                    ['value' => 'A', 'label' => 'Saya mencari alternatif'],
                    ['value' => 'B', 'label' => 'Saya menunggu arahan'],
                    ['value' => 'C', 'label' => 'Saya ragu melanjutkan'],
                    ['value' => 'D', 'label' => 'Saya kehilangan motivasi']
                ],
                'scoring' => ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1]
            ],
            [
                'id' => 'C3',
                'type' => 'multiple_choice',
                'question' => 'Jika terjadi perubahan mendadak:',
                'options' => [
                    ['value' => 'A', 'label' => 'Saya cepat beradaptasi'],
                    ['value' => 'B', 'label' => 'Saya butuh waktu sebentar'],
                    ['value' => 'C', 'label' => 'Saya kesulitan menyesuaikan'],
                    ['value' => 'D', 'label' => 'Saya stres']
                ],
                'scoring' => ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1]
            ],
            [
                'id' => 'C4',
                'type' => 'multiple_choice',
                'question' => 'Saat menghadapi situasi baru:',
                'options' => [
                    ['value' => 'A', 'label' => 'Saya tertarik mencoba'],
                    ['value' => 'B', 'label' => 'Saya berhati-hati'],
                    ['value' => 'C', 'label' => 'Saya ragu'],
                    ['value' => 'D', 'label' => 'Saya menghindar']
                ],
                'scoring' => ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1]
            ]
        ]
    ],

    // Section D: Orientasi Kerja (4 soal)
    'section_d' => [
        'title' => 'Bagian D: Orientasi Kerja',
        'description' => 'Pilih jawaban yang paling menggambarkan preferensi kerja Anda.',
        'time_limit' => null,
        'questions' => [
            [
                'id' => 'D1',
                'type' => 'multiple_choice',
                'question' => 'Saya lebih nyaman bekerja dengan:',
                'options' => [
                    ['value' => 'A', 'label' => 'Aturan dan SOP yang jelas'],
                    ['value' => 'B', 'label' => 'Aturan umum dengan sedikit fleksibilitas'],
                    ['value' => 'C', 'label' => 'Target tanpa banyak aturan'],
                    ['value' => 'D', 'label' => 'Kebebasan penuh']
                ],
                'scoring' => ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4] // Higher = more explorative
            ],
            [
                'id' => 'D2',
                'type' => 'multiple_choice',
                'question' => 'Dalam bekerja, saya lebih suka:',
                'options' => [
                    ['value' => 'A', 'label' => 'Menyempurnakan hal yang sudah ada'],
                    ['value' => 'B', 'label' => 'Mengembangkan sedikit demi sedikit'],
                    ['value' => 'C', 'label' => 'Mencoba cara baru'],
                    ['value' => 'D', 'label' => 'Menciptakan sesuatu yang benar-benar baru']
                ],
                'scoring' => ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4]
            ],
            [
                'id' => 'D3',
                'type' => 'multiple_choice',
                'question' => 'Saat diberi ruang bebas dalam bekerja:',
                'options' => [
                    ['value' => 'A', 'label' => 'Saya tetap membuat struktur sendiri'],
                    ['value' => 'B', 'label' => 'Saya menyesuaikan dengan kebutuhan'],
                    ['value' => 'C', 'label' => 'Saya bereksperimen'],
                    ['value' => 'D', 'label' => 'Saya bekerja tanpa pola']
                ],
                'scoring' => ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4]
            ],
            [
                'id' => 'D4',
                'type' => 'multiple_choice',
                'question' => 'Saya merasa paling produktif ketika:',
                'options' => [
                    ['value' => 'A', 'label' => 'Semua langkah jelas'],
                    ['value' => 'B', 'label' => 'Tujuan jelas, cara fleksibel'],
                    ['value' => 'C', 'label' => 'Banyak variasi'],
                    ['value' => 'D', 'label' => 'Tidak ada batasan']
                ],
                'scoring' => ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4]
            ]
        ]
    ],

    // Section E: Logika Kerja Dasar (6 soal)
    'section_e' => [
        'title' => 'Bagian E: Logika Kerja Dasar',
        'description' => 'Selesaikan soal-soal logika praktis berikut.',
        'time_limit' => null,
        'questions' => [
            [
                'id' => 'E1',
                'type' => 'multiple_choice',
                'question' => 'Lengkapi deret berikut: 2 - 4 - 6 - 8 - ...',
                'options' => [
                    ['value' => 'A', 'label' => '9'],
                    ['value' => 'B', 'label' => '10'],
                    ['value' => 'C', 'label' => '11'],
                    ['value' => 'D', 'label' => '12']
                ],
                'correct_answer' => 'B',
                'scoring' => ['correct' => 4, 'incorrect' => 0]
            ],
            [
                'id' => 'E2',
                'type' => 'multiple_choice',
                'question' => 'Lengkapi deret berikut: 5 - 10 - 10 - 15 - 15 - ...',
                'options' => [
                    ['value' => 'A', 'label' => '15'],
                    ['value' => 'B', 'label' => '20'],
                    ['value' => 'C', 'label' => '25'],
                    ['value' => 'D', 'label' => '30']
                ],
                'correct_answer' => 'B',
                'scoring' => ['correct' => 4, 'incorrect' => 0]
            ],
            [
                'id' => 'E3',
                'type' => 'multiple_choice',
                'question' => 'Manakah yang BERBEDA dari pilihan berikut?\nA. ■■■□  B. ■■■□  C. ■■□□  D. ■■■□',
                'options' => [
                    ['value' => 'A', 'label' => 'A'],
                    ['value' => 'B', 'label' => 'B'],
                    ['value' => 'C', 'label' => 'C'],
                    ['value' => 'D', 'label' => 'D']
                ],
                'correct_answer' => 'C',
                'scoring' => ['correct' => 4, 'incorrect' => 0]
            ],
            [
                'id' => 'E4',
                'type' => 'multiple_choice',
                'question' => 'Manakah pola yang TIDAK SESUAI?\nA. ▲▲●▲  B. ▲▲●▲  C. ▲●●▲  D. ▲▲●▲',
                'options' => [
                    ['value' => 'A', 'label' => 'A'],
                    ['value' => 'B', 'label' => 'B'],
                    ['value' => 'C', 'label' => 'C'],
                    ['value' => 'D', 'label' => 'D']
                ],
                'correct_answer' => 'C',
                'scoring' => ['correct' => 4, 'incorrect' => 0]
            ],
            [
                'id' => 'E5',
                'type' => 'multiple_choice',
                'question' => 'Jika satu produk membutuhkan 2 gram bahan, berapa gram yang dibutuhkan untuk 50 produk?',
                'options' => [
                    ['value' => 'A', 'label' => '25 gram'],
                    ['value' => 'B', 'label' => '52 gram'],
                    ['value' => 'C', 'label' => '100 gram'],
                    ['value' => 'D', 'label' => '150 gram']
                ],
                'correct_answer' => 'C',
                'scoring' => ['correct' => 4, 'incorrect' => 0]
            ],
            [
                'id' => 'E6',
                'type' => 'multiple_choice',
                'question' => 'Jika 6 orang menyelesaikan pekerjaan dalam 8 jam, berapa jam yang dibutuhkan 12 orang untuk pekerjaan yang sama?',
                'options' => [
                    ['value' => 'A', 'label' => '16 jam'],
                    ['value' => 'B', 'label' => '6 jam'],
                    ['value' => 'C', 'label' => '4 jam'],
                    ['value' => 'D', 'label' => '2 jam']
                ],
                'correct_answer' => 'C',
                'scoring' => ['correct' => 4, 'incorrect' => 0]
            ]
        ]
    ]
];


// ============================================
// PSYCHOLOGY SCORING MATRIX
// 4 Work Pattern Categories
// ============================================

/**
 * Work Pattern Categories:
 * - PRESISI_MONOTON: Cocok untuk R&D Lab, QC, Produksi detail
 * - PRESISI_DINAMIS: Cocok untuk Supervisor, Planner, Koordinator
 * - EKSPLORATIF_TERSTRUKTUR: Cocok untuk Product Development, R&D konsep
 * - EKSPLORATIF_DINAMIS: Cocok untuk Kreatif, Branding, Campaign
 */

$psychologyScoringMatrix = [
    'work_patterns' => [
        'presisi_monoton' => [
            'name' => 'Presisi & Monoton',
            'description' => 'Kuat di ketelitian dan ketahanan pada tugas berulang',
            'suitable_positions' => ['R&D Lab', 'QC', 'Produksi detail', 'Operator Produksi'],
            'indicators' => [
                'section_a' => 'high',      // Ketelitian tinggi
                'section_b' => 'high',      // Stabilitas tinggi
                'section_c' => 'low',       // Adaptasi rendah (tidak perlu)
                'section_d' => 'low'        // Eksplorasi rendah (tidak perlu)
            ]
        ],
        'presisi_dinamis' => [
            'name' => 'Presisi & Dinamis',
            'description' => 'Kuat di ketelitian dengan kemampuan adaptasi',
            'suitable_positions' => ['Supervisor', 'Planner', 'Koordinator', 'Management'],
            'indicators' => [
                'section_a' => 'high',      // Ketelitian tinggi
                'section_b' => 'medium',    // Stabilitas sedang
                'section_c' => 'high',      // Adaptasi tinggi
                'section_d' => 'medium'     // Eksplorasi sedang
            ]
        ],
        'eksploratif_terstruktur' => [
            'name' => 'Eksploratif & Terstruktur',
            'description' => 'Kreatif dalam kerangka yang terstruktur',
            'suitable_positions' => ['Product Development', 'R&D konsep', 'Staff Kantor'],
            'indicators' => [
                'section_a' => 'medium',    // Ketelitian sedang
                'section_b' => 'medium',    // Stabilitas sedang
                'section_c' => 'medium',    // Adaptasi sedang
                'section_d' => 'high'       // Eksplorasi tinggi
            ]
        ],
        'eksploratif_dinamis' => [
            'name' => 'Eksploratif & Dinamis',
            'description' => 'Sangat kreatif dan adaptif',
            'suitable_positions' => ['Kreatif', 'Branding', 'Campaign', 'Product Development'],
            'indicators' => [
                'section_a' => 'low',       // Ketelitian rendah (tidak fokus)
                'section_b' => 'low',       // Stabilitas rendah (tidak suka rutinitas)
                'section_c' => 'high',      // Adaptasi tinggi
                'section_d' => 'high'       // Eksplorasi tinggi
            ]
        ]
    ],
    
    // Score thresholds for categorization
    'thresholds' => [
        'high' => 13,    // Score >= 13 out of 16 (4 questions × 4 max)
        'medium' => 9,   // Score 9-12
        'low' => 0       // Score < 9
    ],
    
    // Section A (Ketelitian) scoring - based on accuracy percentage
    'section_a_thresholds' => [
        'high' => 85,    // >= 85% accuracy
        'medium' => 65,  // 65-84% accuracy
        'low' => 0       // < 65% accuracy
    ]
];

// ============================================
// POSITION TO EXPECTED PATTERN MAPPING
// For Pattern Mismatch Detection
// ============================================

$positionPatternMapping = [
    'operator_produksi' => [
        'expected_pattern' => 'presisi_monoton',
        'alternative_patterns' => ['presisi_dinamis'],
        'description' => 'Operator Produksi membutuhkan ketelitian tinggi dan ketahanan pada tugas berulang'
    ],
    'staff_kantor' => [
        'expected_pattern' => null, // Flexible - semua pattern diterima
        'alternative_patterns' => ['presisi_monoton', 'presisi_dinamis', 'eksploratif_terstruktur', 'eksploratif_dinamis'],
        'description' => 'Staff Kantor dapat menerima berbagai pola kerja'
    ],
    'supervisor' => [
        'expected_pattern' => 'presisi_dinamis',
        'alternative_patterns' => ['eksploratif_terstruktur'],
        'description' => 'Supervisor membutuhkan ketelitian dengan kemampuan adaptasi dan koordinasi'
    ],
    'rnd_qc_lab' => [
        'expected_pattern' => 'presisi_monoton',
        'alternative_patterns' => ['presisi_dinamis'],
        'description' => 'R&D/QC/Lab membutuhkan ketelitian ekstrem dan fokus pada detail'
    ],
    'kreatif' => [
        'expected_pattern' => 'eksploratif_dinamis',
        'alternative_patterns' => ['eksploratif_terstruktur'],
        'description' => 'Kreatif membutuhkan kreativitas tinggi dan kemampuan adaptasi'
    ],
    'product_development' => [
        'expected_pattern' => 'eksploratif_terstruktur',
        'alternative_patterns' => ['eksploratif_dinamis', 'presisi_dinamis'],
        'description' => 'Product Development membutuhkan kreativitas dalam kerangka terstruktur'
    ],
    'management' => [
        'expected_pattern' => 'presisi_dinamis',
        'alternative_patterns' => ['eksploratif_terstruktur'],
        'description' => 'Management membutuhkan ketelitian strategis dengan kemampuan adaptasi'
    ]
];

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get all logic test questions
 */
function getLogicQuestions(): array {
    global $logicQuestions;
    return $logicQuestions;
}

/**
 * Get logic test answer keys
 */
function getLogicAnswerKeys(): array {
    global $logicAnswerKeys;
    return $logicAnswerKeys;
}

/**
 * Get all psychology test questions
 */
function getPsychologyQuestions(): array {
    global $psychologyQuestions;
    return $psychologyQuestions;
}

/**
 * Get psychology scoring matrix
 */
function getPsychologyScoringMatrix(): array {
    global $psychologyScoringMatrix;
    return $psychologyScoringMatrix;
}

/**
 * Get position to pattern mapping
 */
function getPositionPatternMapping(): array {
    global $positionPatternMapping;
    return $positionPatternMapping;
}

/**
 * Get total logic test questions count
 */
function getLogicQuestionsCount(): int {
    global $logicQuestions;
    $count = 0;
    foreach ($logicQuestions as $section) {
        $count += count($section['questions']);
    }
    return $count;
}

/**
 * Get logic test time limit in minutes
 */
function getLogicTestTimeLimit(): int {
    return 30; // 30 minutes
}

/**
 * Get psychology test Section A time limit in seconds
 */
function getPsychologySectionATimeLimit(): int {
    return 420; // 7 minutes
}

/**
 * Get expected pattern for a position
 */
function getExpectedPattern(string $position): ?string {
    global $positionPatternMapping;
    return $positionPatternMapping[$position]['expected_pattern'] ?? null;
}

/**
 * Check if pattern matches expected for position
 */
function isPatternMatch(string $pattern, string $position): bool {
    global $positionPatternMapping;
    
    if (!isset($positionPatternMapping[$position])) {
        return false;
    }
    
    $mapping = $positionPatternMapping[$position];
    
    // If no expected pattern (flexible position like staff_kantor), always match
    if ($mapping['expected_pattern'] === null) {
        return true;
    }
    
    // Check if matches expected or alternative patterns
    if ($pattern === $mapping['expected_pattern']) {
        return true;
    }
    
    return in_array($pattern, $mapping['alternative_patterns']);
}

/**
 * Get alternative positions for a work pattern
 */
function getAlternativePositions(string $pattern): array {
    global $psychologyScoringMatrix;
    
    if (!isset($psychologyScoringMatrix['work_patterns'][$pattern])) {
        return [];
    }
    
    return $psychologyScoringMatrix['work_patterns'][$pattern]['suitable_positions'];
}

/**
 * Get work pattern details
 */
function getWorkPatternDetails(string $pattern): ?array {
    global $psychologyScoringMatrix;
    return $psychologyScoringMatrix['work_patterns'][$pattern] ?? null;
}

/**
 * Get all available positions
 */
function getAvailablePositions(): array {
    return [
        'operator_produksi' => 'Operator Produksi',
        'staff_kantor' => 'Staff Kantor (Admin/Finance/dll)',
        'supervisor' => 'Supervisor',
        'rnd_qc_lab' => 'R&D / QC / Lab',
        'kreatif' => 'Kreatif / Branding',
        'product_development' => 'Product Development',
        'management' => 'Management'
    ];
}

/**
 * Get position track (operator/staff/supervisor_management)
 */
function getPositionTrack(string $position): string {
    $trackMapping = [
        'operator_produksi' => 'operator',
        'staff_kantor' => 'staff',
        'supervisor' => 'supervisor_management',
        'rnd_qc_lab' => 'staff',
        'kreatif' => 'staff',
        'product_development' => 'staff',
        'management' => 'supervisor_management'
    ];
    
    return $trackMapping[$position] ?? 'staff';
}
