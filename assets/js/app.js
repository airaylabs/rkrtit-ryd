/**
 * RayCorp Recruitment System - FINAL OPTIMIZED VERSION
 * 
 * Technical: 5 soal (70%)
 * Psikotes: 3 skenario (30%)
 * 
 * Scoring: 0-10 scale
 * - Technical: 5 soal × 2 poin = max 10 × 70% = 7 poin
 * - Psikotes: 3 skenario, avg × 2 = max 10 × 30% = 3 poin
 * - Total: max 10 poin
 */

const state = {
    currentStep: 1,
    totalSteps: 4,
    testStarted: false,
    timer: {
        personal: 0,
        technical: 0,
        psikotes: 0,
        startTime: null,
        intervalId: null
    },
    formData: {
        nama: '',
        email: '',
        whatsapp: '',
        cv: null,
        technicalAnswers: {},  // tech1a, tech1b, tech2a, tech2b, tech3a
        psikotesAnswers: {}    // psi1, psi2, psi3
    }
};

// ============================================
// START TEST
// ============================================

function startTest() {
    state.testStarted = true;
    document.getElementById('landing-page').style.display = 'none';
    document.getElementById('main-form').style.display = 'block';
    startTimer();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ============================================
// TIMER
// ============================================

function startTimer() {
    state.timer.startTime = Date.now();
    state.timer.intervalId = setInterval(updateTimerDisplay, 1000);
}

function updateTimerDisplay() {
    const elapsed = Math.floor((Date.now() - state.timer.startTime) / 1000);
    const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
    const seconds = (elapsed % 60).toString().padStart(2, '0');
    document.getElementById('timer-display').textContent = `${minutes}:${seconds}`;
}

function recordStepTime() {
    if (!state.timer.startTime) return;
    const elapsed = Math.floor((Date.now() - state.timer.startTime) / 1000);
    
    switch (state.currentStep) {
        case 1: state.timer.personal = elapsed; break;
        case 2: state.timer.technical = elapsed; break;
        case 3: state.timer.psikotes = elapsed; break;
    }
    state.timer.startTime = Date.now();
}

function stopTimer() {
    if (state.timer.intervalId) {
        clearInterval(state.timer.intervalId);
        state.timer.intervalId = null;
    }
}

function formatTimeShort(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}m ${secs}s`;
}

// ============================================
// NAVIGATION
// ============================================

function goToStep(step) {
    if (step < 1 || step > state.totalSteps) return;
    if (step > state.currentStep) recordStepTime();
    
    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
    document.getElementById(`step-${step}`).classList.add('active');
    updateProgressIndicators(step);
    state.currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateProgressIndicators(step) {
    document.querySelectorAll('.progress-step').forEach((el, i) => {
        el.classList.remove('active', 'completed');
        if (i + 1 < step) el.classList.add('completed');
        else if (i + 1 === step) el.classList.add('active');
    });
}

function nextStep() {
    if (validateCurrentStep()) {
        saveCurrentStepData();
        goToStep(state.currentStep + 1);
    }
}

function prevStep() {
    goToStep(state.currentStep - 1);
}

// ============================================
// VALIDATION
// ============================================

function validateCurrentStep() {
    switch (state.currentStep) {
        case 1: return validatePersonalData();
        case 2: return validateTechnicalTest();
        case 3: return validatePsikotes();
        default: return true;
    }
}

function validatePersonalData() {
    let isValid = true;
    
    const nama = document.getElementById('nama');
    if (!nama.value.trim()) {
        showError(nama, 'Nama lengkap wajib diisi');
        isValid = false;
    } else clearError(nama);
    
    const email = document.getElementById('email');
    if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        showError(email, 'Email tidak valid');
        isValid = false;
    } else clearError(email);
    
    const whatsapp = document.getElementById('whatsapp');
    if (!whatsapp.value.trim() || !/^\+?[0-9]{10,15}$/.test(whatsapp.value.replace(/[\s-]/g, ''))) {
        showError(whatsapp, 'Nomor tidak valid (10-15 digit)');
        isValid = false;
    } else clearError(whatsapp);
    
    // Validate CV (required)
    const cvError = document.getElementById('cv-error');
    if (!state.formData.cv) {
        if (cvError) {
            cvError.style.display = 'block';
            document.getElementById('cv-dropzone').classList.add('error');
        }
        isValid = false;
    } else {
        if (cvError) {
            cvError.style.display = 'none';
            document.getElementById('cv-dropzone').classList.remove('error');
        }
    }
    
    return isValid;
}

function validateTechnicalTest() {
    const total = 5; // 5 sub-pertanyaan
    const answered = Object.keys(state.formData.technicalAnswers).length;
    if (answered < total) {
        alert(`Mohon jawab semua pertanyaan teknis. (${answered}/${total} terjawab)`);
        return false;
    }
    return true;
}

function validatePsikotes() {
    const total = 3; // 3 skenario
    const answered = Object.keys(state.formData.psikotesAnswers).length;
    if (answered < total) {
        alert(`Mohon jawab semua skenario. (${answered}/${total} terjawab)`);
        return false;
    }
    return true;
}

function showError(input, message) {
    input.classList.add('error');
    const errorEl = input.nextElementSibling;
    if (errorEl?.classList.contains('error-message')) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }
}

function clearError(input) {
    input.classList.remove('error');
    const errorEl = input.nextElementSibling;
    if (errorEl?.classList.contains('error-message')) {
        errorEl.style.display = 'none';
    }
}

// ============================================
// DATA COLLECTION
// ============================================

function saveCurrentStepData() {
    if (state.currentStep === 1) {
        state.formData.nama = document.getElementById('nama').value.trim();
        state.formData.email = document.getElementById('email').value.trim();
        state.formData.whatsapp = document.getElementById('whatsapp').value.replace(/[\s-]/g, '');
    }
}

function handleTechnicalAnswer(questionId, answer) {
    state.formData.technicalAnswers[questionId] = answer;
    const container = document.querySelector(`[data-question="${questionId}"]`);
    if (container) {
        container.querySelectorAll('.radio-option').forEach(opt => opt.classList.remove('selected'));
        container.querySelector(`input[value="${answer}"]`)?.closest('.radio-option')?.classList.add('selected');
    }
}

function handlePsikotesAnswer(scenarioId, answer) {
    state.formData.psikotesAnswers[scenarioId] = answer;
    const container = document.querySelector(`[data-scenario="${scenarioId}"]`);
    if (container) {
        container.querySelectorAll('.radio-option').forEach(opt => opt.classList.remove('selected'));
        container.querySelector(`input[value="${answer}"]`)?.closest('.radio-option')?.classList.add('selected');
    }
}

// ============================================
// FILE UPLOAD
// ============================================

function initFileUpload() {
    const dropZone = document.getElementById('cv-dropzone');
    const fileInput = document.getElementById('cv-input');
    if (!dropZone || !fileInput) return;
    
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) handleFileSelect(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', e => {
        if (e.target.files.length > 0) handleFileSelect(e.target.files[0]);
    });
}

function handleFileSelect(file) {
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!allowedTypes.includes(file.type)) {
        alert('Format file tidak didukung.');
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file terlalu besar. Maksimal 5MB.');
        return;
    }
    state.formData.cv = file;
    document.getElementById('cv-filename').textContent = '✓ ' + file.name;
    document.getElementById('cv-filename').style.display = 'inline-block';
    document.querySelector('.file-upload-text').textContent = 'File terpilih:';
}

// ============================================
// FORM SUBMISSION
// ============================================

async function submitForm() {
    recordStepTime();
    stopTimer();
    showLoading(true);
    
    try {
        const formData = new FormData();
        formData.append('nama', state.formData.nama);
        formData.append('email', state.formData.email);
        formData.append('whatsapp', state.formData.whatsapp);
        if (state.formData.cv) formData.append('cv', state.formData.cv);
        formData.append('technicalAnswers', JSON.stringify(state.formData.technicalAnswers));
        formData.append('psikotesAnswers', JSON.stringify(state.formData.psikotesAnswers));
        formData.append('timer', JSON.stringify({
            personal: state.timer.personal,
            technical: state.timer.technical,
            psikotes: state.timer.psikotes
        }));
        
        const response = await fetch('api/submit.php', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            result.result.id = result.applicantId;
            displayResult(result.result);
            goToStep(4);
        } else {
            throw new Error(result.error || 'Submission failed');
        }
    } catch (error) {
        console.error('Submission error:', error);
        alert('Terjadi kesalahan. Silakan coba lagi.\n\n' + error.message);
    } finally {
        showLoading(false);
    }
}

// ============================================
// RESULT DISPLAY
// ============================================

function displayResult(result) {
    const resultSection = document.getElementById('step-4');
    const totalTime = result.timer.personal + result.timer.technical + result.timer.psikotes;
    
    resultSection.innerHTML = `
        <div class="result-container">
            <div class="result-header">
                <div class="result-icon">✅</div>
                <h2 class="result-status success">Terima Kasih, ${state.formData.nama}!</h2>
                <p class="result-message">Data lamaran dan hasil test Anda telah berhasil dikirim.</p>
            </div>

            <div class="id-box">
                <p class="id-label">ID Lamaran Anda</p>
                <p class="id-value">${result.id || 'RC-XXX00'}</p>
                <p class="id-note">Simpan ID ini untuk referensi</p>
            </div>

            <div class="score-box">
                <p class="score-label">Skor Anda</p>
                <p class="score-value">${result.overallScore.toFixed(1)}<span class="score-max">/10</span></p>
            </div>

            <div class="timer-info">
                <h3 class="timer-info-title">⏱️ Waktu Pengerjaan</h3>
                <div class="timer-grid">
                    <div class="timer-item"><p class="label">Data Diri</p><p class="value">${formatTimeShort(result.timer.personal)}</p></div>
                    <div class="timer-item"><p class="label">Technical</p><p class="value">${formatTimeShort(result.timer.technical)}</p></div>
                    <div class="timer-item"><p class="label">Psikotes</p><p class="value">${formatTimeShort(result.timer.psikotes)}</p></div>
                    <div class="timer-item highlight"><p class="label">Total</p><p class="value">${formatTimeShort(totalTime)}</p></div>
                </div>
            </div>

            <div class="next-steps-box">
                <h3 class="next-steps-title">📋 Langkah Selanjutnya</h3>
                <div class="next-steps-content">
                    <p>Tim HR akan meninjau dan mengevaluasi kecocokan Anda dengan kebutuhan tim IT RayCorp.</p>
                    <div class="contact-box">
                        <p class="contact-label">Kami akan menghubungi Anda melalui:</p>
                        <div class="contact-item"><span class="icon">📱</span> WhatsApp: <span class="value">${state.formData.whatsapp}</span></div>
                        <div class="contact-item"><span class="icon">📧</span> Email: <span class="value">${state.formData.email}</span></div>
                    </div>
                    <p class="warning-note">⚠️ Pastikan WhatsApp dan Email Anda aktif untuk menerima informasi selanjutnya.</p>
                </div>
            </div>

            <div class="result-footer">
                <p>RayCorp Recruitment System</p>
                <p class="timestamp">${new Date().toLocaleString('id-ID')}</p>
            </div>
        </div>
    `;
}

function showLoading(show) {
    document.getElementById('loading-overlay').classList.toggle('active', show);
}

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    initFileUpload();
    
    document.querySelectorAll('#step-2 input[type="radio"]').forEach(input => {
        input.addEventListener('change', e => handleTechnicalAnswer(e.target.name, e.target.value));
    });
    
    document.querySelectorAll('.scenario-question input[type="radio"]').forEach(input => {
        input.addEventListener('change', e => handlePsikotesAnswer(e.target.name, e.target.value));
    });
});
