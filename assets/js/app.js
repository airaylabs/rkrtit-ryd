/**
 * RayCorp Recruitment System - Multi-Division Version
 * 
 * Flow: Pilih Posisi → Form Aplikasi → Tes Logika → Tes Psikologi → Hasil
 * 
 * Supports multiple positions with different scoring thresholds
 */

const state = {
    currentStep: 1,
    currentSubStep: 'a', // For form sub-steps (1a, 1b, 1c)
    totalSteps: 4,
    testStarted: false,
    selectedPosition: null,
    selectedPositionData: null,
    timer: {
        form: 0,
        logic: 0,
        psychology: 0,
        startTime: null,
        intervalId: null
    },
    formData: {
        // Position
        position_applied: '',
        position_track: '',
        expected_work_pattern: '',
        
        // Section A: Personal Data
        nama: '',
        tempat_lahir: '',
        tanggal_lahir: '',
        alamat_domisili: '',
        whatsapp: '',
        email: '',
        status_pernikahan: '',
        cv: null,
        
        // Section B: Background
        aktivitas_saat_ini: '',
        pengalaman_relevan: '',
        
        // Section C: Education
        pendidikan_institusi: '',
        pendidikan_jurusan: '',
        pendidikan_tahun_lulus: '',
        alasan_jurusan: '',
        
        // Section D: Value & Work View
        arti_tanggung_jawab: '',
        cerita_kesalahan: '',
        langkah_target_minim_arahan: '',

        // Section E: Adab & Professional Attitude
        arti_adab: '',
        respon_tidak_sepakat: '',
        cara_sampaikan_kritik: '',
        pengalaman_tidak_adil: '',
        prioritas_pendapat_vs_sikap: '',
        prioritas_penjelasan: '',
        
        // Section F: Motivation
        alasan_melamar: '',
        harapan_selain_gaji: '',
        makna_bermanfaat: '',
        bertahan_saat_lelah: '',
        respon_tidak_cocok_sistem: '',
        
        // Section G: Availability
        bersedia_probation: '',
        bersedia_feedback: '',
        kapan_mulai: '',
        ekspektasi_gaji: '',
        
        // Test Answers (will be populated by test components)
        logicAnswers: {},
        psychologyAnswers: {}
    }
};

// ============================================
// POSITION SELECTION
// ============================================

function showPositionSelection() {
    document.getElementById('landing-page').style.display = 'none';
    document.getElementById('position-selection-page').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function backToLanding() {
    document.getElementById('position-selection-page').style.display = 'none';
    document.getElementById('landing-page').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function selectPosition(positionKey) {
    // Remove selection from all cards
    document.querySelectorAll('.position-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selection to clicked card
    const selectedCard = document.querySelector(`[data-position="${positionKey}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
    
    // Store selected position
    state.selectedPosition = positionKey;
    state.selectedPositionData = positionData[positionKey];
    
    // Update form data
    state.formData.position_applied = positionKey;
    state.formData.position_track = state.selectedPositionData.track;
    state.formData.expected_work_pattern = state.selectedPositionData.expected_pattern || '';
    
    // Show position info
    const infoBox = document.getElementById('selected-position-info');
    infoBox.style.display = 'block';
    
    document.getElementById('selected-position-name').textContent = state.selectedPositionData.label;
    
    const trackLabels = {
        'operator': 'Track A - Operator (fokus kepatuhan & kejujuran)',
        'staff': 'Track B - Staff (fokus konsistensi & refleksi)',
        'supervisor_management': 'Track C - Supervisor/Management (fokus akurasi & kesadaran nilai)'
    };
    document.getElementById('selected-track').textContent = trackLabels[state.selectedPositionData.track];
    document.getElementById('selected-threshold').textContent = `Minimal ${state.selectedPositionData.logic_threshold}/25 jawaban benar`;
    document.getElementById('selected-pattern').textContent = patternLabels[state.selectedPositionData.expected_pattern || ''];
    
    // Enable start button
    document.getElementById('btn-start-test').disabled = false;
}

// ============================================
// START TEST
// ============================================

function startTest() {
    if (!state.selectedPosition) {
        alert('Silakan pilih posisi terlebih dahulu.');
        return;
    }
    
    state.testStarted = true;
    document.getElementById('position-selection-page').style.display = 'none';
    document.getElementById('main-form').style.display = 'block';
    
    // Display selected position in form
    document.getElementById('form-position-display').textContent = state.selectedPositionData.label;
    
    startTimer();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function backToPositionSelection() {
    document.getElementById('main-form').style.display = 'none';
    document.getElementById('position-selection-page').style.display = 'block';
    stopTimer();
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
    
    // Record time based on current step
    if (state.currentStep === 1) {
        state.timer.form = elapsed;
    } else if (state.currentStep === 2) {
        state.timer.logic = elapsed;
    } else if (state.currentStep === 3) {
        state.timer.psychology = elapsed;
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

function goToStep(step, subStep = 'a') {
    if (step < 1 || step > state.totalSteps) return;
    
    // Record time when moving to next major step
    if (step > state.currentStep) {
        recordStepTime();
    }
    
    // Stop logic timer when leaving step 2
    if (state.currentStep === 2 && step !== 2) {
        stopLogicTimer();
    }
    
    // Hide all sections
    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
    
    // Show appropriate section
    if (step === 1) {
        // Form has sub-steps: 1a (personal), 1b (value/adab), 1c (motivation)
        const subStepId = subStep === 'a' ? 'step-1' : `step-1${subStep}`;
        document.getElementById(subStepId).classList.add('active');
        state.currentSubStep = subStep;
    } else {
        document.getElementById(`step-${step}`).classList.add('active');
        state.currentSubStep = 'a';
    }
    
    // Initialize logic test when entering step 2
    if (step === 2 && state.currentStep !== 2) {
        initLogicTest();
    }
    
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
    if (!validateCurrentStep()) return;
    saveCurrentStepData();
    
    // Handle form sub-steps
    if (state.currentStep === 1) {
        if (state.currentSubStep === 'a') {
            goToStep(1, 'b');
            return;
        } else if (state.currentSubStep === 'b') {
            goToStep(1, 'c');
            return;
        } else if (state.currentSubStep === 'c') {
            goToStep(2);
            return;
        }
    }
    
    goToStep(state.currentStep + 1);
}

function prevStep() {
    // Handle form sub-steps
    if (state.currentStep === 1) {
        if (state.currentSubStep === 'c') {
            goToStep(1, 'b');
            return;
        } else if (state.currentSubStep === 'b') {
            goToStep(1, 'a');
            return;
        }
    }
    
    if (state.currentStep === 2) {
        goToStep(1, 'c');
        return;
    }
    
    goToStep(state.currentStep - 1);
}

// ============================================
// VALIDATION
// ============================================

function validateCurrentStep() {
    if (state.currentStep === 1) {
        if (state.currentSubStep === 'a') return validatePersonalData();
        if (state.currentSubStep === 'b') return validateValueAdab();
        if (state.currentSubStep === 'c') return validateMotivation();
    }
    if (state.currentStep === 2) return validateLogicTest();
    if (state.currentStep === 3) return validatePsychologyTest();
    return true;
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

function validateValueAdab() {
    let isValid = true;
    const requiredFields = [
        'arti_tanggung_jawab',
        'cerita_kesalahan',
        'langkah_target_minim_arahan',
        'arti_adab',
        'respon_tidak_sepakat',
        'cara_sampaikan_kritik'
    ];
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && !field.value.trim()) {
            showError(field, 'Pertanyaan ini wajib dijawab');
            isValid = false;
        } else if (field) {
            clearError(field);
        }
    });
    
    // Check radio for prioritas
    const prioritasRadio = document.querySelector('input[name="prioritas_pendapat_vs_sikap"]:checked');
    if (!prioritasRadio) {
        alert('Mohon pilih prioritas antara menyampaikan pendapat atau menjaga sikap.');
        isValid = false;
    }
    
    return isValid;
}

function validateMotivation() {
    let isValid = true;
    const requiredFields = [
        'alasan_melamar',
        'makna_bermanfaat',
        'bertahan_saat_lelah'
    ];
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && !field.value.trim()) {
            showError(field, 'Pertanyaan ini wajib dijawab');
            isValid = false;
        } else if (field) {
            clearError(field);
        }
    });
    
    // Check radios
    const probationRadio = document.querySelector('input[name="bersedia_probation"]:checked');
    const feedbackRadio = document.querySelector('input[name="bersedia_feedback"]:checked');
    
    if (!probationRadio || !feedbackRadio) {
        alert('Mohon jawab semua pertanyaan ketersediaan.');
        isValid = false;
    }
    
    return isValid;
}

function validateLogicTest() {
    // Check if at least some questions are answered
    const answeredCount = Object.keys(state.formData.logicAnswers).length;
    if (answeredCount === 0) {
        alert('Mohon jawab minimal beberapa soal tes logika sebelum melanjutkan.');
        return false;
    }
    return true;
}

function validatePsychologyTest() {
    // Placeholder - will be implemented in Task 7
    // For now, allow proceeding
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
        if (state.currentSubStep === 'a') {
            // Section A: Personal Data
            state.formData.nama = document.getElementById('nama').value.trim();
            state.formData.tempat_lahir = document.getElementById('tempat_lahir').value.trim();
            state.formData.tanggal_lahir = document.getElementById('tanggal_lahir').value;
            state.formData.alamat_domisili = document.getElementById('alamat_domisili').value.trim();
            state.formData.whatsapp = document.getElementById('whatsapp').value.replace(/[\s-]/g, '');
            state.formData.email = document.getElementById('email').value.trim();
            state.formData.status_pernikahan = document.getElementById('status_pernikahan').value;
            
            // Section B: Background
            state.formData.aktivitas_saat_ini = document.getElementById('aktivitas_saat_ini').value.trim();
            state.formData.pengalaman_relevan = document.getElementById('pengalaman_relevan').value.trim();
            
            // Section C: Education
            state.formData.pendidikan_institusi = document.getElementById('pendidikan_institusi').value.trim();
            state.formData.pendidikan_jurusan = document.getElementById('pendidikan_jurusan').value.trim();
            state.formData.pendidikan_tahun_lulus = document.getElementById('pendidikan_tahun_lulus').value.trim();
            state.formData.alasan_jurusan = document.getElementById('alasan_jurusan').value.trim();
        } else if (state.currentSubStep === 'b') {
            // Section D: Value & Work View
            state.formData.arti_tanggung_jawab = document.getElementById('arti_tanggung_jawab').value.trim();
            state.formData.cerita_kesalahan = document.getElementById('cerita_kesalahan').value.trim();
            state.formData.langkah_target_minim_arahan = document.getElementById('langkah_target_minim_arahan').value.trim();
            
            // Section E: Adab & Professional Attitude
            state.formData.arti_adab = document.getElementById('arti_adab').value.trim();
            state.formData.respon_tidak_sepakat = document.getElementById('respon_tidak_sepakat').value.trim();
            state.formData.cara_sampaikan_kritik = document.getElementById('cara_sampaikan_kritik').value.trim();
            state.formData.pengalaman_tidak_adil = document.getElementById('pengalaman_tidak_adil').value.trim();
            
            const prioritasRadio = document.querySelector('input[name="prioritas_pendapat_vs_sikap"]:checked');
            state.formData.prioritas_pendapat_vs_sikap = prioritasRadio ? prioritasRadio.value : '';
            state.formData.prioritas_penjelasan = document.getElementById('prioritas_penjelasan').value.trim();
        } else if (state.currentSubStep === 'c') {
            // Section F: Motivation
            state.formData.alasan_melamar = document.getElementById('alasan_melamar').value.trim();
            state.formData.harapan_selain_gaji = document.getElementById('harapan_selain_gaji').value.trim();
            state.formData.makna_bermanfaat = document.getElementById('makna_bermanfaat').value.trim();
            state.formData.bertahan_saat_lelah = document.getElementById('bertahan_saat_lelah').value.trim();
            state.formData.respon_tidak_cocok_sistem = document.getElementById('respon_tidak_cocok_sistem').value.trim();
            
            // Section G: Availability
            const probationRadio = document.querySelector('input[name="bersedia_probation"]:checked');
            state.formData.bersedia_probation = probationRadio ? probationRadio.value === 'ya' : false;
            
            const feedbackRadio = document.querySelector('input[name="bersedia_feedback"]:checked');
            state.formData.bersedia_feedback = feedbackRadio ? feedbackRadio.value === 'ya' : false;
            
            state.formData.kapan_mulai = document.getElementById('kapan_mulai').value;
            state.formData.ekspektasi_gaji = document.getElementById('ekspektasi_gaji').value;
        }
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
    const allowedTypes = [
        'application/pdf', 
        'image/jpeg', 
        'image/png', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    if (!allowedTypes.includes(file.type)) {
        alert('Format file tidak didukung. Gunakan PDF, JPG, PNG, atau DOC.');
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
    
    // Clear error if any
    document.getElementById('cv-error').style.display = 'none';
    document.getElementById('cv-dropzone').classList.remove('error');
}

// ============================================
// FORM SUBMISSION
// ============================================

async function submitForm() {
    saveCurrentStepData();
    recordStepTime();
    stopTimer();
    stopLogicTimer(); // Stop logic test timer
    showLoading(true);
    
    try {
        const formData = new FormData();
        
        // Position data
        formData.append('position_applied', state.formData.position_applied);
        formData.append('position_track', state.formData.position_track);
        formData.append('expected_work_pattern', state.formData.expected_work_pattern);
        
        // Personal data
        formData.append('nama', state.formData.nama);
        formData.append('tempat_lahir', state.formData.tempat_lahir);
        formData.append('tanggal_lahir', state.formData.tanggal_lahir);
        formData.append('alamat_domisili', state.formData.alamat_domisili);
        formData.append('whatsapp', state.formData.whatsapp);
        formData.append('email', state.formData.email);
        formData.append('status_pernikahan', state.formData.status_pernikahan);
        
        if (state.formData.cv) {
            formData.append('cv', state.formData.cv);
        }
        
        // Background
        formData.append('aktivitas_saat_ini', state.formData.aktivitas_saat_ini);
        formData.append('pengalaman_relevan', state.formData.pengalaman_relevan);
        
        // Education
        formData.append('pendidikan_institusi', state.formData.pendidikan_institusi);
        formData.append('pendidikan_jurusan', state.formData.pendidikan_jurusan);
        formData.append('pendidikan_tahun_lulus', state.formData.pendidikan_tahun_lulus);
        formData.append('alasan_jurusan', state.formData.alasan_jurusan);
        
        // Value & Adab
        formData.append('arti_tanggung_jawab', state.formData.arti_tanggung_jawab);
        formData.append('cerita_kesalahan', state.formData.cerita_kesalahan);
        formData.append('langkah_target_minim_arahan', state.formData.langkah_target_minim_arahan);
        formData.append('arti_adab', state.formData.arti_adab);
        formData.append('respon_tidak_sepakat', state.formData.respon_tidak_sepakat);
        formData.append('cara_sampaikan_kritik', state.formData.cara_sampaikan_kritik);
        formData.append('pengalaman_tidak_adil', state.formData.pengalaman_tidak_adil);
        formData.append('prioritas_pendapat_vs_sikap', state.formData.prioritas_pendapat_vs_sikap);
        formData.append('prioritas_penjelasan', state.formData.prioritas_penjelasan);
        
        // Motivation
        formData.append('alasan_melamar', state.formData.alasan_melamar);
        formData.append('harapan_selain_gaji', state.formData.harapan_selain_gaji);
        formData.append('makna_bermanfaat', state.formData.makna_bermanfaat);
        formData.append('bertahan_saat_lelah', state.formData.bertahan_saat_lelah);
        formData.append('respon_tidak_cocok_sistem', state.formData.respon_tidak_cocok_sistem);
        
        // Availability
        formData.append('bersedia_probation', state.formData.bersedia_probation ? '1' : '0');
        formData.append('bersedia_feedback', state.formData.bersedia_feedback ? '1' : '0');
        formData.append('kapan_mulai', state.formData.kapan_mulai);
        formData.append('ekspektasi_gaji', state.formData.ekspektasi_gaji);
        
        // Test answers (placeholder for now)
        formData.append('logicAnswers', JSON.stringify(state.formData.logicAnswers));
        formData.append('psychologyAnswers', JSON.stringify(state.formData.psychologyAnswers));
        
        // Timer data
        formData.append('timer', JSON.stringify({
            form: state.timer.form,
            logic: state.timer.logic,
            psychology: state.timer.psychology
        }));
        
        const response = await fetch('api/submit.php', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            displayResult(result);
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
    // Use the new result summary component with Fit Score visualization
    // This component handles:
    // - Logic test status (Aman/Rawan/Tidak Aman) based on position threshold
    // - Psychology test work pattern profile with 4-quadrant visualization
    // - Fit Score (0-100%) with color coding
    // - Placement recommendation based on Work_Pattern
    // - Pattern Mismatch alarm
    // - Alternative positions if Fit Score < 60%
    // - Thank you message and next steps info
    // - Print button for offline interview preparation
    
    if (typeof displayResultSummary === 'function') {
        displayResultSummary(result);
    } else {
        // Fallback to basic display if result-summary.js is not loaded
        displayResultBasic(result);
    }
}

function displayResultBasic(result) {
    const resultSection = document.getElementById('step-4');
    const totalTime = state.timer.form + state.timer.logic + state.timer.psychology;
    const applicantId = result.applicantId || 'RC-XXX00';
    
    // Determine status display based on result
    let statusIcon = '✅';
    let statusText = 'Terima Kasih!';
    let statusClass = 'success';
    
    if (result.result && result.result.logic_status) {
        if (result.result.logic_status === 'tidak_aman') {
            statusIcon = '⚠️';
            statusClass = 'warning';
        }
    }
    
    resultSection.innerHTML = `
        <div class="result-container">
            <div class="result-header">
                <div class="result-icon">${statusIcon}</div>
                <h2 class="result-status ${statusClass}">${statusText} ${state.formData.nama}!</h2>
                <p class="result-message">Data lamaran dan hasil test Anda telah berhasil dikirim.</p>
            </div>

            <div class="id-box">
                <p class="id-label">ID Lamaran Anda</p>
                <p class="id-value">${applicantId}</p>
                <p class="id-note">Simpan ID ini untuk referensi</p>
            </div>

            <div class="position-result-box">
                <p class="position-label">Posisi yang Dilamar</p>
                <p class="position-value">${state.selectedPositionData.label}</p>
            </div>

            <div class="timer-info">
                <h3 class="timer-info-title">⏱️ Waktu Pengerjaan</h3>
                <div class="timer-grid">
                    <div class="timer-item">
                        <p class="label">Form Aplikasi</p>
                        <p class="value">${formatTimeShort(state.timer.form)}</p>
                    </div>
                    <div class="timer-item">
                        <p class="label">Tes Logika</p>
                        <p class="value">${formatTimeShort(state.timer.logic)}</p>
                    </div>
                    <div class="timer-item">
                        <p class="label">Tes Psikologi</p>
                        <p class="value">${formatTimeShort(state.timer.psychology)}</p>
                    </div>
                    <div class="timer-item highlight">
                        <p class="label">Total</p>
                        <p class="value">${formatTimeShort(totalTime)}</p>
                    </div>
                </div>
            </div>

            <div class="next-steps-box">
                <h3 class="next-steps-title">📋 Proses Selanjutnya</h3>
                <div class="next-steps-content">
                    <p>Setelah menyelesaikan tes online, proses selanjutnya adalah:</p>
                    <ul class="next-steps-list">
                        <li><span class="step-num">1</span> <strong>Penilaian Value & Adab</strong> - Tim HR akan mengevaluasi jawaban Anda</li>
                        <li><span class="step-num">2</span> <strong>Interview HRD</strong> - Jika lolos, Anda akan dihubungi untuk interview</li>
                        <li><span class="step-num">3</span> <strong>Interview User</strong> - Interview dengan calon atasan langsung</li>
                        <li><span class="step-num">4</span> <strong>Probation</strong> - Masa percobaan 0-90 hari</li>
                    </ul>
                    <div class="contact-box">
                        <p class="contact-label">Kami akan menghubungi Anda melalui:</p>
                        <div class="contact-item">
                            <span class="icon">📱</span> WhatsApp: <span class="value">${state.formData.whatsapp}</span>
                        </div>
                        <div class="contact-item">
                            <span class="icon">📧</span> Email: <span class="value">${state.formData.email}</span>
                        </div>
                    </div>
                    <p class="warning-note">⚠️ Pastikan WhatsApp dan Email Anda aktif untuk menerima informasi selanjutnya.</p>
                </div>
            </div>

            <div class="print-section">
                <button type="button" class="btn btn-secondary" onclick="window.print()">🖨️ Cetak Ringkasan</button>
            </div>

            <div class="result-footer">
                <p>RayCorp & PT Lunaray Cahya Abadi - Recruitment System</p>
                <p class="timestamp">${new Date().toLocaleString('id-ID')}</p>
            </div>
        </div>
    `;
}

function showLoading(show) {
    document.getElementById('loading-overlay').classList.toggle('active', show);
}

// ============================================
// LOGIC TEST
// ============================================

const logicTestState = {
    currentSection: 'section_a',
    sections: ['section_a', 'section_b', 'section_c', 'section_d', 'section_e', 'section_f', 'section_g'],
    sectionQuestionCounts: {
        'section_a': 4,
        'section_b': 3,
        'section_c': 5,
        'section_d': 5,
        'section_e': 3,
        'section_f': 2,
        'section_g': 3
    },
    timerInterval: null,
    timeRemaining: 30 * 60, // 30 minutes in seconds
    timerStarted: false
};

function initLogicTest() {
    if (typeof logicQuestions === 'undefined') {
        console.error('Logic questions data not loaded');
        return;
    }
    
    renderLogicTest();
    updateLogicProgress();
}

function renderLogicTest() {
    const container = document.getElementById('logic-test-container');
    if (!container) return;
    
    let html = '';
    
    // Render each section
    logicTestState.sections.forEach((sectionKey, sectionIndex) => {
        const section = logicQuestions[sectionKey];
        if (!section) return;
        
        const isActive = sectionKey === logicTestState.currentSection;
        
        html += `
            <div class="logic-section ${isActive ? 'active' : ''}" id="logic-${sectionKey}">
                <div class="logic-section-header">
                    <h3 class="logic-section-title">${section.title}</h3>
                    <p class="logic-section-desc">${section.description}</p>
                </div>
                <div class="logic-questions">
        `;
        
        // Render questions in this section
        section.questions.forEach((question, qIndex) => {
            html += renderLogicQuestion(question, sectionKey);
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Add event listeners to options
    container.querySelectorAll('.logic-option').forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                handleLogicAnswer(radio);
            }
        });
    });
}

function renderLogicQuestion(question, sectionKey) {
    const questionId = question.id;
    const savedAnswer = state.formData.logicAnswers[questionId] || '';
    const isAnswered = savedAnswer !== '';
    
    let html = `
        <div class="logic-question-card ${isAnswered ? 'answered' : ''}" id="question-${questionId}">
            <div class="logic-question-header">
                <div class="logic-question-number">${questionId}</div>
                <div class="logic-question-text">${escapeHtml(question.question)}</div>
            </div>
    `;
    
    // Render based on question type
    if (question.type === 'multiple_choice') {
        html += `<div class="logic-options">`;
        question.options.forEach(option => {
            const isSelected = savedAnswer === option.value;
            html += `
                <label class="logic-option ${isSelected ? 'selected' : ''}">
                    <input type="radio" 
                           name="logic_${questionId}" 
                           value="${option.value}"
                           data-question-id="${questionId}"
                           data-section="${sectionKey}"
                           ${isSelected ? 'checked' : ''}
                           onchange="handleLogicAnswer(this)">
                    <span class="logic-option-label">${option.value}. ${escapeHtml(option.label)}</span>
                </label>
            `;
        });
        html += `</div>`;
    } else if (question.type === 'fill_in') {
        html += `
            <input type="text" 
                   class="logic-fill-input" 
                   id="fill_${questionId}"
                   data-question-id="${questionId}"
                   data-section="${sectionKey}"
                   value="${escapeHtml(savedAnswer)}"
                   placeholder="Ketik jawaban Anda..."
                   onchange="handleLogicFillAnswer(this)">
        `;
    }
    
    html += `</div>`;
    return html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function handleLogicAnswer(input) {
    const questionId = input.dataset.questionId;
    const sectionKey = input.dataset.section;
    const value = input.value;
    
    // Save answer
    state.formData.logicAnswers[questionId] = value;
    
    // Update UI
    const questionCard = document.getElementById(`question-${questionId}`);
    if (questionCard) {
        questionCard.classList.add('answered');
    }
    
    // Update option styling
    const options = input.closest('.logic-options');
    if (options) {
        options.querySelectorAll('.logic-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        input.closest('.logic-option')?.classList.add('selected');
    }
    
    // Start timer on first answer
    if (!logicTestState.timerStarted) {
        startLogicTimer();
    }
    
    updateLogicProgress();
}

function handleLogicFillAnswer(input) {
    const questionId = input.dataset.questionId;
    const value = input.value.trim();
    
    if (value) {
        state.formData.logicAnswers[questionId] = value;
        const questionCard = document.getElementById(`question-${questionId}`);
        if (questionCard) {
            questionCard.classList.add('answered');
        }
    } else {
        delete state.formData.logicAnswers[questionId];
        const questionCard = document.getElementById(`question-${questionId}`);
        if (questionCard) {
            questionCard.classList.remove('answered');
        }
    }
    
    if (!logicTestState.timerStarted) {
        startLogicTimer();
    }
    
    updateLogicProgress();
}

function updateLogicProgress() {
    const totalQuestions = 25;
    const answeredCount = Object.keys(state.formData.logicAnswers).length;
    const percentage = (answeredCount / totalQuestions) * 100;
    
    // Update progress bar
    const progressFill = document.getElementById('logic-progress-fill');
    if (progressFill) {
        progressFill.style.width = `${percentage}%`;
    }
    
    // Update answered count
    const answeredEl = document.getElementById('logic-answered');
    if (answeredEl) {
        answeredEl.textContent = answeredCount;
    }
    
    // Update section nav counts
    logicTestState.sections.forEach(sectionKey => {
        const section = logicQuestions[sectionKey];
        if (!section) return;
        
        let sectionAnswered = 0;
        section.questions.forEach(q => {
            if (state.formData.logicAnswers[q.id]) {
                sectionAnswered++;
            }
        });
        
        const countEl = document.getElementById(`nav-count-${sectionKey}`);
        if (countEl) {
            countEl.textContent = `${sectionAnswered}/${section.questions.length}`;
        }
        
        // Update nav button state
        const navBtn = document.querySelector(`.section-nav-btn[data-section="${sectionKey}"]`);
        if (navBtn) {
            if (sectionAnswered === section.questions.length) {
                navBtn.classList.add('completed');
            } else {
                navBtn.classList.remove('completed');
            }
        }
    });
}

function showLogicSection(sectionKey) {
    logicTestState.currentSection = sectionKey;
    
    // Hide all sections
    document.querySelectorAll('.logic-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Show selected section
    const targetSection = document.getElementById(`logic-${sectionKey}`);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Update nav buttons
    document.querySelectorAll('.section-nav-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeBtn = document.querySelector(`.section-nav-btn[data-section="${sectionKey}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    // Update prev/next buttons
    updateSectionNavButtons();
    
    // Scroll to top of test container
    const container = document.getElementById('logic-test-container');
    if (container) {
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function updateSectionNavButtons() {
    const currentIndex = logicTestState.sections.indexOf(logicTestState.currentSection);
    const prevBtn = document.getElementById('btn-prev-section');
    const nextBtn = document.getElementById('btn-next-section');
    
    if (prevBtn) {
        prevBtn.style.display = currentIndex > 0 ? 'inline-flex' : 'none';
    }
    
    if (nextBtn) {
        if (currentIndex < logicTestState.sections.length - 1) {
            nextBtn.textContent = 'Bagian Selanjutnya →';
            nextBtn.style.display = 'inline-flex';
        } else {
            nextBtn.style.display = 'none';
        }
    }
}

function prevLogicSection() {
    const currentIndex = logicTestState.sections.indexOf(logicTestState.currentSection);
    if (currentIndex > 0) {
        showLogicSection(logicTestState.sections[currentIndex - 1]);
    }
}

function nextLogicSection() {
    const currentIndex = logicTestState.sections.indexOf(logicTestState.currentSection);
    if (currentIndex < logicTestState.sections.length - 1) {
        showLogicSection(logicTestState.sections[currentIndex + 1]);
    }
}

function startLogicTimer() {
    if (logicTestState.timerStarted) return;
    
    logicTestState.timerStarted = true;
    logicTestState.timerInterval = setInterval(updateLogicTimer, 1000);
}

function updateLogicTimer() {
    logicTestState.timeRemaining--;
    
    const minutes = Math.floor(logicTestState.timeRemaining / 60);
    const seconds = logicTestState.timeRemaining % 60;
    const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    const timerEl = document.getElementById('logic-timer');
    if (timerEl) {
        timerEl.textContent = display;
        
        // Add warning classes
        if (logicTestState.timeRemaining <= 60) {
            timerEl.classList.add('danger');
            timerEl.classList.remove('warning');
        } else if (logicTestState.timeRemaining <= 300) {
            timerEl.classList.add('warning');
            timerEl.classList.remove('danger');
        }
    }
    
    // Time's up
    if (logicTestState.timeRemaining <= 0) {
        clearInterval(logicTestState.timerInterval);
        alert('Waktu tes logika telah habis! Jawaban Anda akan disimpan dan dilanjutkan ke tes berikutnya.');
        nextStep();
    }
}

function stopLogicTimer() {
    if (logicTestState.timerInterval) {
        clearInterval(logicTestState.timerInterval);
        logicTestState.timerInterval = null;
    }
}

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    initFileUpload();
    
    // Initialize radio option selection styling
    document.querySelectorAll('.radio-option input[type="radio"]').forEach(input => {
        input.addEventListener('change', e => {
            // Remove selected class from siblings
            const group = e.target.closest('.radio-group-vertical, .radio-group-horizontal, .radio-group');
            if (group) {
                group.querySelectorAll('.radio-option').forEach(opt => opt.classList.remove('selected'));
            }
            // Add selected class to parent
            e.target.closest('.radio-option')?.classList.add('selected');
        });
    });
});


// ============================================
// PSYCHOLOGY TEST
// ============================================

const psychologyTestState = {
    currentSection: 'section_a',
    sections: ['section_a', 'section_b', 'section_c', 'section_d', 'section_e'],
    sectionNames: {
        'section_a': 'Ketelitian & Daya Tahan',
        'section_b': 'Stabilitas & Respon Kejenuhan',
        'section_c': 'Pola Respon Perubahan',
        'section_d': 'Orientasi Kerja',
        'section_e': 'Logika Kerja Dasar'
    },
    timerInterval: null,
    timeRemaining: 7 * 60, // 7 minutes for Section A
    timerStarted: false,
    sectionACompleted: false,
    // Section A marking state
    sectionAMarks: {
        A1: {}, // { cellIndex: 'circle' | 'cross' }
        A2: {},
        A3: {},
        A4: {}
    }
};

function initPsychologyTest() {
    if (typeof psychologyQuestions === 'undefined') {
        console.error('Psychology questions data not loaded');
        return;
    }
    
    renderPsychologyTest();
    updatePsychologyProgress();
}

function renderPsychologyTest() {
    const container = document.getElementById('psychology-test-container');
    if (!container) return;
    
    let html = '';
    
    // Render each section
    psychologyTestState.sections.forEach((sectionKey, sectionIndex) => {
        const section = psychologyQuestions[sectionKey];
        if (!section) return;
        
        const isActive = sectionKey === psychologyTestState.currentSection;
        
        html += `
            <div class="psychology-section ${isActive ? 'active' : ''}" id="psychology-${sectionKey}">
                <div class="psychology-section-header">
                    <h3 class="psychology-section-title">${section.title}</h3>
                    <p class="psychology-section-desc">${section.description}</p>
                </div>
        `;
        
        // Section A is special - interactive grid tests
        if (sectionKey === 'section_a') {
            html += renderSectionAKetelitian(section);
        } else {
            // Sections B-E are multiple choice
            html += `<div class="psychology-questions">`;
            section.questions.forEach((question, qIndex) => {
                html += renderPsychologyQuestion(question, sectionKey);
            });
            html += `</div>`;
        }
        
        html += `</div>`;
    });
    
    container.innerHTML = html;
    
    // Add event listeners to options for sections B-E
    container.querySelectorAll('.psychology-option').forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                handlePsychologyAnswer(radio);
            }
        });
    });
    
    // Initialize Section A grid interactions
    initSectionAGrids();
}

function renderSectionAKetelitian(section) {
    let html = '<div class="ketelitian-container">';
    
    section.sub_tests.forEach((subTest, index) => {
        html += `
            <div class="ketelitian-subtest" id="subtest-${subTest.id}">
                <div class="ketelitian-subtest-header">
                    <h4 class="ketelitian-subtest-title">
                        <span class="subtest-badge">${subTest.id}</span>
                        ${subTest.title}
                    </h4>
                    <div class="ketelitian-subtest-instruction">${subTest.instruction}</div>
                </div>
        `;
        
        // Render the grid based on test type
        if (subTest.type === 'mark_target') {
            html += renderMarkTargetGrid(subTest);
        } else if (subTest.type === 'mark_dual') {
            html += renderMarkDualGrid(subTest);
        } else if (subTest.type === 'mark_odd_even') {
            html += renderMarkOddEvenGrid(subTest);
        }
        
        // Stats display
        html += `
                <div class="ketelitian-stats" id="stats-${subTest.id}">
                    <div class="ketelitian-stat">
                        <span class="ketelitian-stat-label">Ditandai:</span>
                        <span class="ketelitian-stat-value" id="marked-count-${subTest.id}">0</span>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    // Legend
    html += `
        <div class="ketelitian-legend">
            <div class="ketelitian-legend-item">
                <div class="ketelitian-legend-icon circle">○</div>
                <span>Klik sekali = Lingkari</span>
            </div>
            <div class="ketelitian-legend-item">
                <div class="ketelitian-legend-icon cross">✕</div>
                <span>Klik dua kali = Coret</span>
            </div>
            <div class="ketelitian-legend-item">
                <span>Klik tiga kali = Hapus</span>
            </div>
        </div>
    `;
    
    return html;
}

function renderMarkTargetGrid(subTest) {
    let html = '<div class="ketelitian-grid">';
    
    subTest.rows.forEach((row, rowIndex) => {
        html += '<div class="ketelitian-row">';
        const items = row.split(' ');
        items.forEach((item, itemIndex) => {
            const cellId = `${subTest.id}-${rowIndex}-${itemIndex}`;
            html += `
                <div class="ketelitian-cell" 
                     data-subtest="${subTest.id}" 
                     data-cell-id="${cellId}"
                     data-value="${item}"
                     data-target="${subTest.target}"
                     data-action="${subTest.action}"
                     onclick="handleKetelitianClick(this)">
                    ${item}
                </div>
            `;
        });
        html += '</div>';
    });
    
    html += '</div>';
    return html;
}

function renderMarkDualGrid(subTest) {
    let html = '<div class="ketelitian-grid">';
    
    subTest.rows.forEach((row, rowIndex) => {
        html += '<div class="ketelitian-row">';
        const items = row.split(' ');
        items.forEach((item, itemIndex) => {
            const cellId = `${subTest.id}-${rowIndex}-${itemIndex}`;
            html += `
                <div class="ketelitian-cell" 
                     data-subtest="${subTest.id}" 
                     data-cell-id="${cellId}"
                     data-value="${item}"
                     data-target-circle="${subTest.target_circle}"
                     data-target-cross="${subTest.target_cross}"
                     data-type="dual"
                     onclick="handleKetelitianClick(this)">
                    ${item}
                </div>
            `;
        });
        html += '</div>';
    });
    
    html += '</div>';
    return html;
}

function renderMarkOddEvenGrid(subTest) {
    let html = '<div class="ketelitian-grid">';
    
    subTest.rows.forEach((row, rowIndex) => {
        html += '<div class="ketelitian-row">';
        const items = row.split(' ');
        items.forEach((item, itemIndex) => {
            const cellId = `${subTest.id}-${rowIndex}-${itemIndex}`;
            html += `
                <div class="ketelitian-cell" 
                     data-subtest="${subTest.id}" 
                     data-cell-id="${cellId}"
                     data-value="${item}"
                     data-type="odd_even"
                     onclick="handleKetelitianClick(this)">
                    ${item}
                </div>
            `;
        });
        html += '</div>';
    });
    
    html += '</div>';
    return html;
}

function initSectionAGrids() {
    // Start timer when user first interacts with Section A
    const container = document.getElementById('psychology-test-container');
    if (container) {
        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('ketelitian-cell') && !psychologyTestState.timerStarted) {
                startPsychologyTimer();
            }
        }, { once: false });
    }
}

function handleKetelitianClick(cell) {
    const subtestId = cell.dataset.subtest;
    const cellId = cell.dataset.cellId;
    
    // Start timer on first click
    if (!psychologyTestState.timerStarted && psychologyTestState.currentSection === 'section_a') {
        startPsychologyTimer();
    }
    
    // Get current state
    const currentMark = psychologyTestState.sectionAMarks[subtestId][cellId];
    
    // Cycle through states: none -> circle -> cross -> none
    let newMark = null;
    if (!currentMark) {
        newMark = 'circle';
        cell.classList.add('marked-circle');
        cell.classList.remove('marked-cross');
    } else if (currentMark === 'circle') {
        newMark = 'cross';
        cell.classList.remove('marked-circle');
        cell.classList.add('marked-cross');
    } else {
        newMark = null;
        cell.classList.remove('marked-circle', 'marked-cross');
    }
    
    // Update state
    if (newMark) {
        psychologyTestState.sectionAMarks[subtestId][cellId] = newMark;
    } else {
        delete psychologyTestState.sectionAMarks[subtestId][cellId];
    }
    
    // Update stats
    updateKetelitianStats(subtestId);
    
    // Save to form data
    saveSectionAData();
}

function updateKetelitianStats(subtestId) {
    const marks = psychologyTestState.sectionAMarks[subtestId];
    const circleCount = Object.values(marks).filter(m => m === 'circle').length;
    const crossCount = Object.values(marks).filter(m => m === 'cross').length;
    const totalCount = circleCount + crossCount;
    
    const countEl = document.getElementById(`marked-count-${subtestId}`);
    if (countEl) {
        countEl.textContent = totalCount;
    }
}

function saveSectionAData() {
    // Calculate scores for Section A
    const sectionAData = {
        marks: psychologyTestState.sectionAMarks,
        scores: {}
    };
    
    // Calculate accuracy for each subtest
    ['A1', 'A2', 'A3', 'A4'].forEach(subtestId => {
        const marks = psychologyTestState.sectionAMarks[subtestId];
        sectionAData.scores[subtestId] = {
            circleCount: Object.values(marks).filter(m => m === 'circle').length,
            crossCount: Object.values(marks).filter(m => m === 'cross').length,
            totalMarked: Object.keys(marks).length
        };
    });
    
    state.formData.psychologyAnswers.section_a = sectionAData;
}

function renderPsychologyQuestion(question, sectionKey) {
    const questionId = question.id;
    const savedAnswer = state.formData.psychologyAnswers[questionId] || '';
    const isAnswered = savedAnswer !== '';
    
    let html = `
        <div class="psychology-question-card ${isAnswered ? 'answered' : ''}" id="psych-question-${questionId}">
            <div class="psychology-question-header">
                <div class="psychology-question-number">${questionId}</div>
                <div class="psychology-question-text">${escapeHtml(question.question)}</div>
            </div>
            <div class="psychology-options">
    `;
    
    question.options.forEach(option => {
        const isSelected = savedAnswer === option.value;
        html += `
            <label class="psychology-option ${isSelected ? 'selected' : ''}">
                <input type="radio" 
                       name="psych_${questionId}" 
                       value="${option.value}"
                       data-question-id="${questionId}"
                       data-section="${sectionKey}"
                       ${isSelected ? 'checked' : ''}
                       onchange="handlePsychologyAnswer(this)">
                <span class="psychology-option-label">${option.value}. ${escapeHtml(option.label)}</span>
            </label>
        `;
    });
    
    html += `
            </div>
        </div>
    `;
    
    return html;
}

function handlePsychologyAnswer(input) {
    const questionId = input.dataset.questionId;
    const sectionKey = input.dataset.section;
    const value = input.value;
    
    // Save answer
    state.formData.psychologyAnswers[questionId] = value;
    
    // Update UI
    const questionCard = document.getElementById(`psych-question-${questionId}`);
    if (questionCard) {
        questionCard.classList.add('answered');
    }
    
    // Update option styling
    const options = input.closest('.psychology-options');
    if (options) {
        options.querySelectorAll('.psychology-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        input.closest('.psychology-option')?.classList.add('selected');
    }
    
    updatePsychologyProgress();
    updatePsychologySectionNav();
}

function updatePsychologyProgress() {
    const currentIndex = psychologyTestState.sections.indexOf(psychologyTestState.currentSection);
    const percentage = ((currentIndex + 1) / psychologyTestState.sections.length) * 100;
    
    // Update progress bar
    const progressFill = document.getElementById('psychology-progress-fill');
    if (progressFill) {
        progressFill.style.width = `${percentage}%`;
    }
    
    // Update current section number
    const currentSectionEl = document.getElementById('psychology-current-section');
    if (currentSectionEl) {
        currentSectionEl.textContent = currentIndex + 1;
    }
}

function updatePsychologySectionNav() {
    psychologyTestState.sections.forEach((sectionKey, index) => {
        const navBtn = document.querySelector(`.psych-section-nav-btn[data-section="${sectionKey}"]`);
        if (!navBtn) return;
        
        let isCompleted = false;
        
        if (sectionKey === 'section_a') {
            // Section A is completed if timer ran out or user moved to next section
            isCompleted = psychologyTestState.sectionACompleted;
        } else {
            // Check if all questions in section are answered
            const section = psychologyQuestions[sectionKey];
            if (section && section.questions) {
                const answeredCount = section.questions.filter(q => 
                    state.formData.psychologyAnswers[q.id]
                ).length;
                isCompleted = answeredCount === section.questions.length;
            }
        }
        
        if (isCompleted) {
            navBtn.classList.add('completed');
        } else {
            navBtn.classList.remove('completed');
        }
    });
}

function showPsychologySection(sectionKey) {
    // If leaving Section A, mark it as completed and stop timer
    if (psychologyTestState.currentSection === 'section_a' && sectionKey !== 'section_a') {
        psychologyTestState.sectionACompleted = true;
        stopPsychologyTimer();
        saveSectionAData();
    }
    
    psychologyTestState.currentSection = sectionKey;
    
    // Hide all sections
    document.querySelectorAll('.psychology-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Show selected section
    const targetSection = document.getElementById(`psychology-${sectionKey}`);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Update nav buttons
    document.querySelectorAll('.psych-section-nav-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeBtn = document.querySelector(`.psych-section-nav-btn[data-section="${sectionKey}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    // Show/hide timer for Section A
    const timerBox = document.getElementById('psychology-timer-box');
    if (timerBox) {
        timerBox.style.display = sectionKey === 'section_a' ? 'flex' : 'none';
    }
    
    // Update prev/next buttons
    updatePsychologySectionNavButtons();
    updatePsychologyProgress();
    updatePsychologySectionNav();
    
    // Scroll to top of test container
    const container = document.getElementById('psychology-test-container');
    if (container) {
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function updatePsychologySectionNavButtons() {
    const currentIndex = psychologyTestState.sections.indexOf(psychologyTestState.currentSection);
    const prevBtn = document.getElementById('btn-prev-psych-section');
    const nextBtn = document.getElementById('btn-next-psych-section');
    
    if (prevBtn) {
        prevBtn.style.display = currentIndex > 0 ? 'inline-flex' : 'none';
    }
    
    if (nextBtn) {
        if (currentIndex < psychologyTestState.sections.length - 1) {
            nextBtn.textContent = 'Bagian Selanjutnya →';
            nextBtn.style.display = 'inline-flex';
        } else {
            nextBtn.style.display = 'none';
        }
    }
}

function prevPsychologySection() {
    const currentIndex = psychologyTestState.sections.indexOf(psychologyTestState.currentSection);
    if (currentIndex > 0) {
        showPsychologySection(psychologyTestState.sections[currentIndex - 1]);
    }
}

function nextPsychologySection() {
    const currentIndex = psychologyTestState.sections.indexOf(psychologyTestState.currentSection);
    if (currentIndex < psychologyTestState.sections.length - 1) {
        showPsychologySection(psychologyTestState.sections[currentIndex + 1]);
    }
}

function startPsychologyTimer() {
    if (psychologyTestState.timerStarted) return;
    
    psychologyTestState.timerStarted = true;
    
    // Show timer box
    const timerBox = document.getElementById('psychology-timer-box');
    if (timerBox) {
        timerBox.style.display = 'flex';
    }
    
    psychologyTestState.timerInterval = setInterval(updatePsychologyTimer, 1000);
}

function updatePsychologyTimer() {
    psychologyTestState.timeRemaining--;
    
    const minutes = Math.floor(psychologyTestState.timeRemaining / 60);
    const seconds = psychologyTestState.timeRemaining % 60;
    const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    const timerEl = document.getElementById('psychology-timer');
    if (timerEl) {
        timerEl.textContent = display;
        
        // Add warning classes
        if (psychologyTestState.timeRemaining <= 60) {
            timerEl.classList.add('danger');
            timerEl.classList.remove('warning');
        } else if (psychologyTestState.timeRemaining <= 120) {
            timerEl.classList.add('warning');
            timerEl.classList.remove('danger');
        }
    }
    
    // Time's up for Section A
    if (psychologyTestState.timeRemaining <= 0) {
        stopPsychologyTimer();
        psychologyTestState.sectionACompleted = true;
        saveSectionAData();
        alert('Waktu Bagian A (Ketelitian) telah habis! Jawaban Anda akan disimpan dan dilanjutkan ke bagian berikutnya.');
        nextPsychologySection();
    }
}

function stopPsychologyTimer() {
    if (psychologyTestState.timerInterval) {
        clearInterval(psychologyTestState.timerInterval);
        psychologyTestState.timerInterval = null;
    }
}

// Update goToStep to initialize psychology test
const originalGoToStep = goToStep;
goToStep = function(step, subStep = 'a') {
    // Initialize psychology test when entering step 3
    if (step === 3 && state.currentStep !== 3) {
        initPsychologyTest();
    }
    
    // Stop psychology timer when leaving step 3
    if (state.currentStep === 3 && step !== 3) {
        stopPsychologyTimer();
        if (psychologyTestState.currentSection === 'section_a') {
            psychologyTestState.sectionACompleted = true;
            saveSectionAData();
        }
    }
    
    originalGoToStep(step, subStep);
};

// Update validatePsychologyTest
function validatePsychologyTest() {
    // Check if at least some questions are answered
    const answeredCount = Object.keys(state.formData.psychologyAnswers).filter(key => 
        key !== 'section_a' && state.formData.psychologyAnswers[key]
    ).length;
    
    // Also check Section A
    const sectionAData = state.formData.psychologyAnswers.section_a;
    const hasSectionAData = sectionAData && Object.keys(sectionAData.marks || {}).some(
        subtestId => Object.keys(sectionAData.marks[subtestId]).length > 0
    );
    
    if (answeredCount === 0 && !hasSectionAData) {
        alert('Mohon jawab minimal beberapa soal tes psikologi sebelum melanjutkan.');
        return false;
    }
    return true;
}
