<?php
/**
 * Admin Dashboard - Multi-Division Recruitment System v2.0
 * 
 * Features:
 * - Position filter dropdown
 * - Logic status filter
 * - HR decision filter
 * - Display position, logic score, logic status, work pattern columns
 * - Search by nama/email
 */

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Applicant.php';
require_once __DIR__ . '/../includes/InputSanitizer.php';

// Load environment
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

$adminPasswordHash = getenv('ADMIN_PASSWORD_HASH');

// Fallback: jika hash kosong atau tidak valid, gunakan password default
if (empty($adminPasswordHash) || strlen($adminPasswordHash) < 20) {
    $adminPasswordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // admin123
}

// Handle login
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $password = $_POST['password'] ?? '';
    if (password_verify($password, $adminPasswordHash)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        header('Location: /admin/');
        exit;
    } else {
        $loginError = 'Password salah.';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /admin/');
    exit;
}

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Session timeout (2 hours)
if ($isLoggedIn && isset($_SESSION['admin_login_time']) && time() - $_SESSION['admin_login_time'] > 7200) {
    session_destroy();
    header('Location: /admin/?timeout=1');
    exit;
}

// Login form
if (!$isLoggedIn):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - RayCorp</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <h1 class="login-title">🔐 <span style="color: var(--primary);">RAY</span>CORP Admin</h1>
        <?php if ($loginError): ?><div class="alert error"><?php echo $loginError; ?></div><?php endif; ?>
        <?php if (isset($_GET['timeout'])): ?><div class="alert warning">Sesi berakhir. Login kembali.</div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        <p style="text-align: center; margin-top: 24px;"><a href="../" style="color: var(--primary);">← Kembali</a></p>
    </div>
</body>
</html>
<?php exit; endif;

// Get filter parameters
$positionFilter = isset($_GET['position']) ? InputSanitizer::sanitizeString($_GET['position']) : '';
$logicStatusFilter = isset($_GET['logic_status']) ? InputSanitizer::sanitizeString($_GET['logic_status']) : '';
$hrDecisionFilter = isset($_GET['hr_decision']) ? InputSanitizer::sanitizeString($_GET['hr_decision']) : '';
$searchQuery = isset($_GET['search']) ? InputSanitizer::sanitizeString($_GET['search']) : '';

// Build filters array
$filters = [];
if (!empty($positionFilter)) {
    $filters['position_applied'] = $positionFilter;
}
if (!empty($logicStatusFilter)) {
    $filters['logic_status'] = $logicStatusFilter;
}
if (!empty($hrDecisionFilter)) {
    $filters['hr_decision'] = $hrDecisionFilter;
}
if (!empty($searchQuery)) {
    $filters['search'] = $searchQuery;
}

// Dashboard
try {
    $applicantModel = new Applicant();
    $applicants = $applicantModel->getAllFiltered($filters);
    $stats = $applicantModel->getStatsV2();
} catch (Exception $e) {
    $applicants = [];
    $stats = [
        'total' => 0, 
        'aman' => 0, 
        'rawan' => 0, 
        'tidak_aman' => 0,
        'lanjut' => 0,
        'hold' => 0,
        'stop' => 0
    ];
}

// Position labels for display
$positionLabels = [
    'operator_produksi' => 'Operator Produksi',
    'staff_kantor' => 'Staff Kantor',
    'supervisor' => 'Supervisor',
    'rnd_qc_lab' => 'R&D/QC/Lab',
    'kreatif' => 'Kreatif',
    'product_development' => 'Product Dev',
    'management' => 'Management'
];

// Work pattern labels
$patternLabels = [
    'presisi_monoton' => 'Presisi Monoton',
    'presisi_dinamis' => 'Presisi Dinamis',
    'eksploratif_terstruktur' => 'Eksploratif Terstruktur',
    'eksploratif_dinamis' => 'Eksploratif Dinamis'
];

// Logic status labels
$logicStatusLabels = [
    'aman' => 'Aman',
    'rawan' => 'Rawan',
    'tidak_aman' => 'Tidak Aman'
];

// HR decision labels
$hrDecisionLabels = [
    'lanjut' => 'Lanjut',
    'hold' => 'Hold',
    'stop' => 'Stop'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RayCorp Recruitment</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Additional styles for enhanced admin panel */
        .search-box {
            display: flex;
            gap: 12px;
            align-items: center;
            flex: 1;
            max-width: 400px;
        }
        .search-box input {
            flex: 1;
            padding: 10px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            background: var(--bg-input);
            color: var(--text-primary);
        }
        .search-box input::placeholder {
            color: var(--text-muted);
        }
        .search-box button {
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        .search-box button:hover {
            background: var(--primary-dark);
        }
        
        .filter-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-group label {
            font-size: 13px;
            color: var(--text-muted);
            white-space: nowrap;
        }
        
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 13px;
            background: var(--bg-input);
            color: var(--text-primary);
            min-width: 140px;
        }
        
        .clear-filters {
            color: var(--text-muted);
            font-size: 13px;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .clear-filters:hover {
            background: var(--bg-input);
            color: var(--text-primary);
        }
        
        .pattern-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            white-space: nowrap;
        }
        .pattern-badge.presisi_monoton {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }
        .pattern-badge.presisi_dinamis {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }
        .pattern-badge.eksploratif_terstruktur {
            background: rgba(139, 92, 246, 0.2);
            color: #a78bfa;
        }
        .pattern-badge.eksploratif_dinamis {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }
        
        .logic-status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .logic-status-badge.aman {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }
        .logic-status-badge.rawan {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }
        .logic-status-badge.tidak_aman {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }
        
        .hr-decision-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .hr-decision-badge.lanjut {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }
        .hr-decision-badge.hold {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }
        .hr-decision-badge.stop {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }
        .hr-decision-badge.pending {
            background: rgba(100, 116, 139, 0.2);
            color: var(--text-muted);
        }
        
        .position-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            background: var(--bg-input);
            color: var(--text-secondary);
        }
        
        .fit-score {
            font-weight: 600;
            font-size: 13px;
        }
        .fit-score.high { color: var(--success); }
        .fit-score.medium { color: var(--warning); }
        .fit-score.low { color: var(--danger); }
        
        .mismatch-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--danger);
            margin-left: 6px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            padding: 20px;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
        }
        
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            color: var(--text-muted);
            font-size: 14px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .data-table th, .data-table td {
            white-space: nowrap;
        }
        
        .data-table td.wrap {
            white-space: normal;
            max-width: 200px;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-logo">🚀 <span style="color: var(--primary);">RAY</span>CORP Admin</div>
        <nav class="admin-nav">
            <a href="?logout=1">Logout</a>
        </nav>
    </header>

    <div class="admin-container">
        <h1 class="admin-title">Dashboard Recruitment v2.0</h1>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Pelamar</div>
            </div>
            <div class="stat-card success">
                <div class="stat-value"><?php echo $stats['aman']; ?></div>
                <div class="stat-label">Logic: Aman</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-value"><?php echo $stats['rawan']; ?></div>
                <div class="stat-label">Logic: Rawan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['tidak_aman']; ?></div>
                <div class="stat-label">Logic: Tidak Aman</div>
            </div>
            <div class="stat-card success">
                <div class="stat-value"><?php echo $stats['lanjut']; ?></div>
                <div class="stat-label">HR: Lanjut</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-value"><?php echo $stats['hold']; ?></div>
                <div class="stat-label">HR: Hold</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" style="width: 100%;">
                <div class="filter-row" style="margin-bottom: 16px;">
                    <!-- Search Box -->
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Cari nama atau email..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit">🔍 Cari</button>
                    </div>
                    
                    <?php if (!empty($filters)): ?>
                    <a href="?" class="clear-filters">✕ Reset Filter</a>
                    <?php endif; ?>
                </div>
                
                <div class="filter-row">
                    <!-- Position Filter -->
                    <div class="filter-group">
                        <label>Posisi:</label>
                        <select name="position" onchange="this.form.submit()">
                            <option value="">Semua Posisi</option>
                            <?php foreach ($positionLabels as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo $positionFilter === $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Logic Status Filter -->
                    <div class="filter-group">
                        <label>Status Logic:</label>
                        <select name="logic_status" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <?php foreach ($logicStatusLabels as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo $logicStatusFilter === $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- HR Decision Filter -->
                    <div class="filter-group">
                        <label>Keputusan HR:</label>
                        <select name="hr_decision" onchange="this.form.submit()">
                            <option value="">Semua Keputusan</option>
                            <option value="pending" <?php echo $hrDecisionFilter === 'pending' ? 'selected' : ''; ?>>Belum Dinilai</option>
                            <?php foreach ($hrDecisionLabels as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo $hrDecisionFilter === $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Info -->
        <div class="results-info">
            <span>Menampilkan <?php echo count($applicants); ?> pelamar</span>
            <?php if (!empty($filters)): ?>
            <span>Filter aktif: <?php echo count($filters); ?></span>
            <?php endif; ?>
        </div>

        <!-- Data Table -->
        <div class="data-table">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>NAMA</th>
                            <th>POSISI</th>
                            <th style="text-align:center;">LOGIC</th>
                            <th style="text-align:center;">STATUS LOGIC</th>
                            <th style="text-align:center;">POLA KERJA</th>
                            <th style="text-align:center;">FIT SCORE</th>
                            <th style="text-align:center;">HR DECISION</th>
                            <th>TANGGAL</th>
                            <th style="text-align:center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applicants)): ?>
                        <tr>
                            <td colspan="9" style="text-align:center;padding:60px;color:var(--text-muted);">
                                <?php if (!empty($filters)): ?>
                                Tidak ada data yang sesuai dengan filter
                                <?php else: ?>
                                Belum ada data pelamar
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($applicants as $app): ?>
                        <tr>
                            <td class="wrap">
                                <strong><?php echo htmlspecialchars($app['nama']); ?></strong><br>
                                <span style="font-size:12px;color:var(--text-muted);"><?php echo htmlspecialchars($app['email']); ?></span>
                            </td>
                            <td>
                                <span class="position-badge">
                                    <?php echo $positionLabels[$app['position_applied']] ?? $app['position_applied'] ?? '-'; ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <strong><?php echo (int)($app['logic_correct'] ?? 0); ?>/<?php echo (int)($app['logic_total'] ?? 25); ?></strong>
                            </td>
                            <td style="text-align:center;">
                                <?php 
                                $logicStatus = $app['logic_status'] ?? 'tidak_aman';
                                $statusClass = str_replace('_', '_', $logicStatus);
                                ?>
                                <span class="logic-status-badge <?php echo $statusClass; ?>">
                                    <?php echo $logicStatusLabels[$logicStatus] ?? ucfirst($logicStatus); ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <?php 
                                $pattern = $app['psychology_pattern'] ?? null;
                                $mismatch = $app['psychology_pattern_mismatch'] ?? false;
                                if ($pattern): 
                                ?>
                                <span class="pattern-badge <?php echo $pattern; ?>">
                                    <?php echo $patternLabels[$pattern] ?? $pattern; ?>
                                </span>
                                <?php if ($mismatch): ?>
                                <span class="mismatch-indicator" title="Pattern Mismatch"></span>
                                <?php endif; ?>
                                <?php else: ?>
                                <span style="color:var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php 
                                $fitScore = $app['psychology_fit_score'] ?? null;
                                if ($fitScore !== null):
                                    $fitClass = $fitScore >= 70 ? 'high' : ($fitScore >= 60 ? 'medium' : 'low');
                                ?>
                                <span class="fit-score <?php echo $fitClass; ?>">
                                    <?php echo number_format($fitScore, 0); ?>%
                                </span>
                                <?php else: ?>
                                <span style="color:var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php 
                                $hrDecision = $app['hr_decision'] ?? null;
                                if ($hrDecision): 
                                ?>
                                <span class="hr-decision-badge <?php echo $hrDecision; ?>">
                                    <?php echo $hrDecisionLabels[$hrDecision] ?? ucfirst($hrDecision); ?>
                                </span>
                                <?php else: ?>
                                <span class="hr-decision-badge pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size:12px;color:var(--text-muted);">
                                    <?php echo date('d M Y', strtotime($app['created_at'])); ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <a href="detail.php?id=<?php echo urlencode($app['id']); ?>" 
                                   class="btn btn-secondary" style="padding:6px 12px;font-size:12px;">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
