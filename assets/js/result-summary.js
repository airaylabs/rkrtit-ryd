/**
 * Result Summary Component - Fit Score Visualization
 * 
 * Requirements: 7.4, 7.5, 7.6, 8.2, 8.3, 8.4, 8.5, 4.5, 4.6, 10.1, 10.4, 10.5
 * 
 * Features:
 * - Logic test status (Aman/Rawan/Tidak Aman) based on position threshold
 * - Psychology test work pattern profile with 4-quadrant visualization
 * - Fit Score (0-100%) with color coding
 * - Placement recommendation based on Work_Pattern
 * - Pattern Mismatch alarm
 * - Alternative positions if Fit Score < 60%
 * - Thank you message and next steps info
 * - Print button for offline interview preparation
 */

// Work Pattern Definitions
const workPatternDefinitions = {
    'presisi_monoton': {
        name: 'Presisi Monoton',
        description: 'Kerja presisi dengan rutinitas tinggi. Butuh ketelitian tinggi dan tahan rutinitas.',
        icon: '🔬',
        positions: ['R&D Lab', 'QC', 'Produksi Detail'],
        quadrant: 0 // Top-left
    },
    'presisi_dinamis': {
        name: 'Presisi Dinamis',
        description: 'Kerja presisi dengan adaptasi cepat. Butuh ketelitian + kemampuan koordinasi.',
        icon: '👔',
        positions: ['Supervisor', 'Planner', 'Koordinator'],
        quadrant: 2 // Bottom-left
    },
    'eksploratif_terstruktur': {
        name: 'Eksploratif Terstruktur',
        description: 'Kreativitas dalam sistem terstruktur. Butuh inovasi dengan framework jelas.',
        icon: '💡',
        positions: ['Product Dev', 'R&D Konsep'],
        quadrant: 1 // Top-right
    },
    'eksploratif_dinamis': {
        name: 'Eksploratif Dinamis',
        description: 'Kreativitas bebas dengan adaptasi tinggi. Butuh ide out-of-the-box.',
        icon: '🎨',
        positions: ['Kreatif', 'Branding', 'Campaign'],
        quadrant: 3 // Bottom-right
    }
};

// Position to expected pattern mapping
const positionExpectedPattern = {
    'operator_produksi': 'presisi_monoton',
    'staff_kantor': null, // Flexible
    'supervisor': 'presisi_dinamis',
    'rnd_qc_lab': 'presisi_monoton',
    'kreatif': 'eksploratif_dinamis',
    'product_development': 'eksploratif_terstruktur',
    'management': 'presisi_dinamis'
};

// Position display names
const positionNames = {
    'operator_produksi': 'Operator Produksi',
    'staff_kantor': 'Staff Kantor',
    'supervisor': 'Supervisor',
    'rnd_qc_lab': 'R&D / QC / Lab',
    'kreatif': 'Kreatif / Branding',
    'product_development': 'Product Development',
    'management': 'Management'
};

/**
 * Get Fit Score level class
 */
function getFitScoreLevel(score) {
    if (score >= 70) return 'high';
    if (score >= 60) return 'medium';
    return 'low';
}

/**
 * Get Fit Score label
 */
function getFitScoreLabel(score) {
    if (score >= 70) return 'SANGAT COCOK';
    if (score >= 60) return 'COCOK DENGAN CATATAN';
    return 'TIDAK COCOK';
}

/**
 * Get Logic Status class
 */
function getLogicStatusClass(status) {
    const statusMap = {
        'aman': 'aman',
        'rawan': 'rawan',
        'tidak_aman': 'tidak-aman'
    };
    return statusMap[status] || 'tidak-aman';
}

/**
 * Get Logic Status label
 */
function getLogicStatusLabel(status) {
    const labelMap = {
        'aman': 'Aman',
        'rawan': 'Rawan',
        'tidak_aman': 'Tidak Aman'
    };
    return labelMap[status] || 'Tidak Aman';
}

/**
 * Format time in minutes and seconds
 */
function formatTimeDisplay(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}m ${secs}s`;
}

/**
 * Render 4-Quadrant Work Pattern Visualization
 */
function renderWorkPatternQuadrant(candidatePattern, expectedPattern) {
    const quadrants = [
        { key: 'presisi_monoton', label: 'Presisi Monoton', positions: 'R&D/QC/Lab', icon: '🔬' },
        { key: 'eksploratif_terstruktur', label: 'Eksploratif Terstruktur', positions: 'Product Dev', icon: '💡' },
        { key: 'presisi_dinamis', label: 'Presisi Dinamis', positions: 'Supervisor', icon: '👔' },
        { key: 'eksploratif_dinamis', label: 'Eksploratif Dinamis', positions: 'Kreatif', icon: '🎨' }
    ];

    let html = '<div class="quadrant-grid">';
    
    quadrants.forEach((quad, index) => {
        const isActive = candidatePattern === quad.key;
        const isExpected = expectedPattern === quad.key;
        
        let classes = 'quadrant-cell';
        if (isActive) classes += ' active';
        if (isExpected) classes += ' expected';
        
        let marker = '';
        if (isActive && isExpected) {
            marker = '<div class="quadrant-marker">✓</div>';
        } else if (isActive) {
            marker = '<div class="quadrant-marker">●</div>';
        } else if (isExpected) {
            marker = '<div class="quadrant-marker">○</div>';
        }
        
        html += `
            <div class="${classes}">
                ${marker}
                <div class="quadrant-icon">${quad.icon}</div>
                <div class="quadrant-name">${quad.label}</div>
                <div class="quadrant-positions">${quad.positions}</div>
            </div>
        `;
    });
    
    html += '</div>';
    
    // Add legend
    html += `
        <div class="quadrant-labels">
            <div class="quadrant-label-item">
                <div class="quadrant-label-dot candidate"></div>
                <span>Pola Kerja Anda</span>
            </div>
            ${expectedPattern ? `
            <div class="quadrant-label-item">
                <div class="quadrant-label-dot expected"></div>
                <span>Pola yang Diharapkan</span>
            </div>
            ` : ''}
        </div>
    `;
    
    return html;
}

/**
 * Render Alternative Positions
 */
function renderAlternativePositions(alternatives) {
    if (!alternatives || alternatives.length === 0) return '';
    
    // Filter to show top 3 alternatives with fit score >= 60%
    const topAlternatives = alternatives
        .filter(alt => alt.fitScore >= 60)
        .slice(0, 3);
    
    if (topAlternatives.length === 0) return '';
    
    let html = `
        <div class="alternative-positions-section">
            <h3 class="alternative-positions-title">💡 Posisi Alternatif yang Lebih Cocok</h3>
            <div class="alternative-positions-grid">
    `;
    
    topAlternatives.forEach(alt => {
        const level = getFitScoreLevel(alt.fitScore);
        html += `
            <div class="alternative-position-card">
                <div class="alternative-position-name">${alt.positionName || positionNames[alt.position] || alt.position}</div>
                <div class="alternative-position-score">
                    <div class="alternative-score-bar">
                        <div class="alternative-score-fill ${level}" style="width: ${alt.fitScore}%"></div>
                    </div>
                    <span class="alternative-score-value ${level}">${alt.fitScore.toFixed(0)}%</span>
                </div>
                <div class="alternative-position-recommendation">${alt.recommendation || ''}</div>
            </div>
        `;
    });
    
    html += '</div></div>';
    return html;
}

/**
 * Main function to display result summary
 * Called from app.js after form submission
 */
function displayResultSummary(result) {
    const resultSection = document.getElementById('step-4');
    if (!resultSection) return;
    
    // Extract data from result
    const applicantId = result.applicantId || 'RC-XXX00';
    const nama = result.result?.nama || state.formData.nama || 'Kandidat';
    const email = result.result?.email || state.formData.email || '';
    const whatsapp = state.formData.whatsapp || '';
    const positionApplied = state.formData.position_applied || 'staff_kantor';
    const positionLabel = state.selectedPositionData?.label || positionNames[positionApplied] || positionApplied;
    
    // Logic test results
    const logicScore = result.result?.logic_score ?? result.result?.technicalCorrect ?? 0;
    const logicTotal = result.result?.logic_total ?? 25;
    const logicThreshold = result.result?.logic_threshold ?? state.selectedPositionData?.logic_threshold ?? 17;
    const logicStatus = result.result?.logic_status || 'tidak_aman';
    
    // Psychology test results
    const workPattern = result.result?.psychology_pattern || result.result?.workPattern || 'presisi_monoton';
    const workPatternName = result.result?.psychology_pattern_name || workPatternDefinitions[workPattern]?.name || 'Unknown';
    const workPatternDesc = result.result?.psychology_pattern_description || workPatternDefinitions[workPattern]?.description || '';
    const fitScore = result.result?.psychology_fit_score ?? result.result?.fitScore ?? 75;
    const patternMismatch = result.result?.psychology_pattern_mismatch ?? result.result?.patternMismatch ?? false;
    const alternativePositions = result.result?.alternative_positions || result.result?.alternativePositions || [];
    const placementRecommendation = result.result?.placement_recommendation || result.result?.placementRecommendation || '';
    
    // Expected pattern for position
    const expectedPattern = positionExpectedPattern[positionApplied];
    
    // Timer data
    const timerForm = state.timer?.form || 0;
    const timerLogic = state.timer?.logic || 0;
    const timerPsychology = state.timer?.psychology || 0;
    const timerTotal = timerForm + timerLogic + timerPsychology;
    
    // Determine overall status
    const fitScoreLevel = getFitScoreLevel(fitScore);
    let overallIcon = '✅';
    let overallTitle = 'Terima Kasih!';
    let overallClass = 'success';
    
    if (logicStatus === 'tidak_aman' || fitScore < 60) {
        overallIcon = '⚠️';
        overallTitle = 'Terima Kasih!';
        overallClass = 'warning';
    }
    
    // Build HTML
    let html = `
        <div class="result-summary">
            <!-- Header -->
            <div class="result-summary-header">
                <div class="result-summary-icon">${overallIcon}</div>
                <h2 class="result-summary-title ${overallClass}">${overallTitle} ${escapeHtml(nama)}!</h2>
                <p class="result-summary-subtitle">Data lamaran dan hasil test Anda telah berhasil dikirim.</p>
            </div>

            <!-- Applicant ID -->
            <div class="applicant-id-box">
                <p class="applicant-id-label">ID Lamaran Anda</p>
                <p class="applicant-id-value">${escapeHtml(applicantId)}</p>
            </div>

            <!-- Position Applied -->
            <div class="position-applied-box">
                <p class="position-applied-label">Posisi yang Dilamar</p>
                <p class="position-applied-value">${escapeHtml(positionLabel)}</p>
            </div>

            <!-- Score Cards Grid -->
            <div class="result-scores-grid">
                <!-- Logic Test Result -->
                <div class="logic-result-card">
                    <div class="logic-result-header">
                        <h3 class="logic-result-title">🧮 Tes Logika</h3>
                        <span class="logic-status-badge ${getLogicStatusClass(logicStatus)}">${getLogicStatusLabel(logicStatus)}</span>
                    </div>
                    <div class="logic-score-display">
                        <span class="logic-score-value">${logicScore}</span>
                        <span class="logic-score-max">/ ${logicTotal}</span>
                        <p class="logic-score-threshold">Standar untuk ${escapeHtml(positionLabel)}: ≥${logicThreshold}</p>
                    </div>
                </div>

                <!-- Fit Score -->
                <div class="fit-score-card">
                    <div class="fit-score-header">
                        <h3 class="fit-score-title">🎯 Fit Score</h3>
                    </div>
                    <div class="fit-score-display ${fitScoreLevel}">
                        <span class="fit-score-value">${fitScore.toFixed(0)}%</span>
                        <p class="fit-score-label">${getFitScoreLabel(fitScore)}</p>
                    </div>
                </div>
            </div>
    `;
    
    // Pattern Mismatch Alarm
    if (patternMismatch && expectedPattern) {
        const expectedPatternName = workPatternDefinitions[expectedPattern]?.name || expectedPattern;
        html += `
            <div class="pattern-mismatch-alarm">
                <div class="alarm-icon">⚠️</div>
                <div class="alarm-content">
                    <h4 class="alarm-title">Perhatian: Pola Kerja Tidak Sesuai</h4>
                    <p class="alarm-text">
                        Pola kerja Anda (<strong>${escapeHtml(workPatternName)}</strong>) tidak sesuai dengan pola yang diharapkan 
                        untuk posisi ${escapeHtml(positionLabel)} (<strong>${escapeHtml(expectedPatternName)}</strong>). 
                        Hal ini akan didiskusikan lebih lanjut pada saat interview.
                    </p>
                </div>
            </div>
        `;
    }
    
    // Work Pattern Section
    html += `
        <div class="work-pattern-section">
            <div class="work-pattern-header">
                <h3 class="work-pattern-title">🧠 Profil Pola Kerja</h3>
                <span class="work-pattern-badge">${escapeHtml(workPatternName)}</span>
            </div>
            
            <div class="quadrant-container">
                ${renderWorkPatternQuadrant(workPattern, expectedPattern)}
            </div>
            
            <div class="work-pattern-description">
                <h4 class="work-pattern-desc-title">${escapeHtml(workPatternName)}</h4>
                <p class="work-pattern-desc-text">${escapeHtml(workPatternDesc)}</p>
            </div>
        </div>
    `;
    
    // Alternative Positions (if Fit Score < 60%)
    if (fitScore < 60 && alternativePositions.length > 0) {
        html += renderAlternativePositions(alternativePositions);
    }
    
    // Placement Recommendation
    if (placementRecommendation) {
        html += `
            <div class="placement-recommendation">
                <h4 class="placement-recommendation-title">📋 Rekomendasi Penempatan</h4>
                <p class="placement-recommendation-text">${escapeHtml(placementRecommendation)}</p>
            </div>
        `;
    }
    
    // Timer Summary
    html += `
        <div class="timer-summary">
            <h3 class="timer-summary-title">⏱️ Waktu Pengerjaan</h3>
            <div class="timer-summary-grid">
                <div class="timer-summary-item">
                    <p class="timer-summary-label">Form Aplikasi</p>
                    <p class="timer-summary-value">${formatTimeDisplay(timerForm)}</p>
                </div>
                <div class="timer-summary-item">
                    <p class="timer-summary-label">Tes Logika</p>
                    <p class="timer-summary-value">${formatTimeDisplay(timerLogic)}</p>
                </div>
                <div class="timer-summary-item">
                    <p class="timer-summary-label">Tes Psikologi</p>
                    <p class="timer-summary-value">${formatTimeDisplay(timerPsychology)}</p>
                </div>
                <div class="timer-summary-item highlight">
                    <p class="timer-summary-label">Total</p>
                    <p class="timer-summary-value">${formatTimeDisplay(timerTotal)}</p>
                </div>
            </div>
        </div>
    `;
    
    // Next Steps
    html += `
        <div class="next-steps-section">
            <h3 class="next-steps-title">📋 Proses Selanjutnya</h3>
            <p class="next-steps-intro">Setelah menyelesaikan tes online, proses selanjutnya adalah:</p>
            <ul class="next-steps-list">
                <li>
                    <span class="next-step-number">1</span>
                    <span><strong>Penilaian Value & Adab</strong> - Tim HR akan mengevaluasi jawaban Anda</span>
                </li>
                <li>
                    <span class="next-step-number">2</span>
                    <span><strong>Interview HRD</strong> - Jika lolos, Anda akan dihubungi untuk interview</span>
                </li>
                <li>
                    <span class="next-step-number">3</span>
                    <span><strong>Interview User</strong> - Interview dengan calon atasan langsung</span>
                </li>
                <li>
                    <span class="next-step-number">4</span>
                    <span><strong>Probation</strong> - Masa percobaan 0-90 hari (adaptasi → berdiri → berdampak)</span>
                </li>
            </ul>
            
            <div class="contact-info-box">
                <p class="contact-info-label">Kami akan menghubungi Anda melalui:</p>
                <div class="contact-info-item">
                    <span class="contact-info-icon">📱</span>
                    <span>WhatsApp:</span>
                    <span class="contact-info-value">${escapeHtml(whatsapp)}</span>
                </div>
                <div class="contact-info-item">
                    <span class="contact-info-icon">📧</span>
                    <span>Email:</span>
                    <span class="contact-info-value">${escapeHtml(email)}</span>
                </div>
            </div>
            
            <div class="result-warning-note">
                ⚠️ Pastikan WhatsApp dan Email Anda aktif untuk menerima informasi selanjutnya.
            </div>
        </div>
    `;
    
    // Print Button
    html += `
        <div class="print-button-section">
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ Cetak Ringkasan
            </button>
        </div>
    `;
    
    // Footer
    html += `
        <div class="result-summary-footer">
            <p>RayCorp & PT Lunaray Cahya Abadi - Recruitment System</p>
            <p class="timestamp">${new Date().toLocaleString('id-ID')}</p>
        </div>
    </div>
    `;
    
    resultSection.innerHTML = html;
}

// Helper function to escape HTML (if not already defined)
if (typeof escapeHtml === 'undefined') {
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Export for use in app.js
window.displayResultSummary = displayResultSummary;
