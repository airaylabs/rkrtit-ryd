/**
 * Anti-Cheating Protection Script - MAXIMUM SECURITY
 * 
 * Proteksi agresif untuk mencegah kecurangan:
 * - Blur/hide content saat screenshot attempt
 * - Watermark overlay
 * - Visibility API detection
 * - Multiple layer protection
 */

(function() {
    'use strict';

    // ============================================
    // CONFIGURATION
    // ============================================
    const CONFIG = {
        blurDuration: 3000,      // Durasi blur saat screenshot (ms)
        warningDuration: 3000,   // Durasi warning message (ms)
        maxTabSwitches: 3,       // Max tab switch sebelum warning keras
        enableWatermark: true,   // Enable watermark overlay
        enableBlurOnCapture: true // Blur saat capture attempt
    };

    let tabSwitchCount = 0;
    let isBlurred = false;

    // ============================================
    // CONTENT PROTECTION - BLUR ON SCREENSHOT
    // ============================================
    
    function blurContent() {
        if (isBlurred) return;
        isBlurred = true;
        
        const mainContent = document.getElementById('main-form') || document.body;
        mainContent.style.filter = 'blur(20px)';
        mainContent.style.transition = 'filter 0.1s';
        
        // Show warning overlay
        showScreenshotWarning();
        
        setTimeout(() => {
            mainContent.style.filter = 'none';
            isBlurred = false;
            hideScreenshotWarning();
        }, CONFIG.blurDuration);
    }

    function showScreenshotWarning() {
        let overlay = document.getElementById('screenshot-warning-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'screenshot-warning-overlay';
            overlay.innerHTML = `
                <div style="text-align:center;">
                    <div style="font-size:80px;margin-bottom:20px;">🚫</div>
                    <h2 style="font-size:28px;margin-bottom:15px;color:#ff4444;">SCREENSHOT TERDETEKSI!</h2>
                    <p style="font-size:16px;color:#ccc;">Aktivitas ini telah dicatat dan dilaporkan.</p>
                    <p style="font-size:14px;color:#888;margin-top:10px;">Konten disembunyikan untuk keamanan.</p>
                </div>
            `;
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.95);
                z-index: 999999;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-family: 'Segoe UI', Arial, sans-serif;
            `;
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    }

    function hideScreenshotWarning() {
        const overlay = document.getElementById('screenshot-warning-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    // ============================================
    // KEYBOARD PROTECTION - COMPREHENSIVE
    // ============================================
    
    document.addEventListener('keyup', function(e) {
        // Detect Print Screen key release (keyup karena keydown tidak selalu catch)
        if (e.key === 'PrintScreen' || e.keyCode === 44) {
            blurContent();
            // Clear clipboard
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText('Screenshot tidak diizinkan - RayCorp Recruitment').catch(() => {});
            }
        }
    });

    document.addEventListener('keydown', function(e) {
        // Print Screen
        if (e.key === 'PrintScreen' || e.keyCode === 44) {
            e.preventDefault();
            blurContent();
            return false;
        }

        // Windows + Shift + S (Snipping Tool)
        if ((e.metaKey || e.key === 'Meta') && e.shiftKey && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            blurContent();
            return false;
        }

        // Windows + PrtSc
        if ((e.metaKey || e.key === 'Meta') && (e.key === 'PrintScreen' || e.keyCode === 44)) {
            e.preventDefault();
            blurContent();
            return false;
        }

        // Alt + Print Screen
        if (e.altKey && (e.key === 'PrintScreen' || e.keyCode === 44)) {
            e.preventDefault();
            blurContent();
            return false;
        }

        // Ctrl combinations
        if (e.ctrlKey) {
            // Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+A
            if (['c', 'C', 'v', 'V', 'x', 'X', 'a', 'A'].includes(e.key)) {
                // Allow in input fields for personal data only
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                    if (!e.target.name || (!e.target.name.includes('tech') && !e.target.name.includes('psi'))) {
                        return true;
                    }
                }
                e.preventDefault();
                showWarning('Copy/Paste dinonaktifkan selama assessment.');
                return false;
            }
            // Ctrl+U (view source)
            if (e.key === 'u' || e.key === 'U') {
                e.preventDefault();
                showWarning('View Source dinonaktifkan.');
                return false;
            }
            // Ctrl+S (save)
            if (e.key === 's' || e.key === 'S') {
                e.preventDefault();
                return false;
            }
            // Ctrl+P (print)
            if (e.key === 'p' || e.key === 'P') {
                e.preventDefault();
                showWarning('Print dinonaktifkan selama assessment.');
                return false;
            }
        }

        // Ctrl+Shift combinations (DevTools)
        if (e.ctrlKey && e.shiftKey) {
            if (['I', 'i', 'J', 'j', 'C', 'c', 'K', 'k'].includes(e.key)) {
                e.preventDefault();
                showWarning('Developer Tools dinonaktifkan.');
                return false;
            }
        }

        // F12
        if (e.key === 'F12' || e.keyCode === 123) {
            e.preventDefault();
            showWarning('Developer Tools dinonaktifkan.');
            return false;
        }

        // F5 (optional - prevent refresh during test)
        // if (e.key === 'F5') {
        //     e.preventDefault();
        //     return false;
        // }
    });

    // ============================================
    // RIGHT-CLICK PROTECTION
    // ============================================
    
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        showWarning('Klik kanan dinonaktifkan.');
        return false;
    });

    // ============================================
    // SELECTION & DRAG PROTECTION
    // ============================================
    
    document.addEventListener('selectstart', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return true;
        }
        e.preventDefault();
        return false;
    });

    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });

    document.addEventListener('copy', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            if (!e.target.name || (!e.target.name.includes('tech') && !e.target.name.includes('psi'))) {
                return true;
            }
        }
        e.preventDefault();
        showWarning('Copy dinonaktifkan.');
        return false;
    });

    document.addEventListener('cut', function(e) {
        e.preventDefault();
        return false;
    });

    // ============================================
    // VISIBILITY API - TAB SWITCH DETECTION
    // ============================================
    
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            tabSwitchCount++;
            console.log('[Security] Tab switch detected:', tabSwitchCount);
            
            // Blur content when tab is hidden (prevents some screenshot tools)
            const mainContent = document.getElementById('main-form') || document.body;
            mainContent.dataset.originalFilter = mainContent.style.filter;
            mainContent.style.filter = 'blur(30px)';
            
            if (tabSwitchCount >= CONFIG.maxTabSwitches) {
                showWarning(`Peringatan: Anda sudah ${tabSwitchCount}x meninggalkan halaman. Aktivitas tercatat!`);
            }
        } else {
            // Restore content when tab is visible
            const mainContent = document.getElementById('main-form') || document.body;
            mainContent.style.filter = mainContent.dataset.originalFilter || 'none';
        }
    });

    // Window blur (additional detection)
    window.addEventListener('blur', function() {
        // Some screenshot tools cause window blur
        const mainContent = document.getElementById('main-form') || document.body;
        mainContent.style.filter = 'blur(15px)';
    });

    window.addEventListener('focus', function() {
        const mainContent = document.getElementById('main-form') || document.body;
        if (!isBlurred) {
            mainContent.style.filter = 'none';
        }
    });

    // ============================================
    // DEVTOOLS DETECTION
    // ============================================
    
    let devToolsOpen = false;

    // Method 1: Window size detection
    function checkDevTools() {
        const threshold = 160;
        const widthDiff = window.outerWidth - window.innerWidth;
        const heightDiff = window.outerHeight - window.innerHeight;
        
        if (widthDiff > threshold || heightDiff > threshold) {
            if (!devToolsOpen) {
                devToolsOpen = true;
                handleDevToolsOpen();
            }
        } else {
            devToolsOpen = false;
        }
    }

    function handleDevToolsOpen() {
        showWarning('Developer Tools terdeteksi! Harap tutup untuk melanjutkan.');
        // Blur content
        const mainContent = document.getElementById('main-form') || document.body;
        mainContent.style.filter = 'blur(20px)';
    }

    setInterval(checkDevTools, 500);

    // Method 2: Console detection
    const element = new Image();
    Object.defineProperty(element, 'id', {
        get: function() {
            devToolsOpen = true;
            handleDevToolsOpen();
        }
    });
    // Uncomment to enable (may affect performance)
    // setInterval(() => console.log('%c', element), 1000);

    // ============================================
    // PRINT PROTECTION
    // ============================================
    
    window.addEventListener('beforeprint', function(e) {
        e.preventDefault();
        document.body.innerHTML = '<h1 style="text-align:center;padding:100px;">Print tidak diizinkan</h1>';
        return false;
    });

    window.matchMedia('print').addEventListener('change', function(e) {
        if (e.matches) {
            blurContent();
        }
    });

    // ============================================
    // MOBILE SCREENSHOT DETECTION (Limited)
    // ============================================
    
    // Detect volume button press (common screenshot method on mobile)
    let volumeKeyPressed = false;
    let powerKeyPressed = false;

    document.addEventListener('keydown', function(e) {
        if (e.key === 'AudioVolumeDown' || e.key === 'AudioVolumeUp') {
            volumeKeyPressed = true;
            setTimeout(() => { volumeKeyPressed = false; }, 500);
        }
        // Power button is harder to detect
    });

    // Touch event monitoring for gesture-based screenshots
    let touchStartTime = 0;
    document.addEventListener('touchstart', function(e) {
        touchStartTime = Date.now();
        if (e.touches.length >= 3) {
            // 3+ finger touch might be screenshot gesture on some devices
            blurContent();
        }
    });

    // ============================================
    // WATERMARK OVERLAY (Visual Deterrent)
    // ============================================
    
    if (CONFIG.enableWatermark) {
        function createWatermark() {
            const watermark = document.createElement('div');
            watermark.id = 'security-watermark';
            watermark.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                pointer-events: none;
                z-index: 99999;
                background: repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 100px,
                    rgba(255,255,255,0.01) 100px,
                    rgba(255,255,255,0.01) 200px
                );
                opacity: 0.3;
            `;
            
            // Add timestamp watermark text
            const text = document.createElement('div');
            text.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-30deg);
                font-size: 60px;
                color: rgba(255,255,255,0.03);
                white-space: nowrap;
                pointer-events: none;
                user-select: none;
            `;
            text.textContent = 'RAYCORP CONFIDENTIAL';
            watermark.appendChild(text);
            
            document.body.appendChild(watermark);
        }
        
        // Create watermark when test starts
        document.addEventListener('DOMContentLoaded', function() {
            const startBtn = document.querySelector('.btn-start');
            if (startBtn) {
                startBtn.addEventListener('click', createWatermark);
            }
        });
    }

    // ============================================
    // WARNING MESSAGE DISPLAY
    // ============================================
    
    let warningTimeout = null;
    
    function showWarning(message) {
        let warning = document.getElementById('anti-cheat-warning');
        
        if (!warning) {
            warning = document.createElement('div');
            warning.id = 'anti-cheat-warning';
            document.body.appendChild(warning);
        }
        
        warning.innerHTML = `⚠️ ${message}`;
        warning.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            z-index: 9999999;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(220, 53, 69, 0.5);
            animation: slideIn 0.3s ease;
            max-width: 90%;
            text-align: center;
        `;
        
        if (warningTimeout) clearTimeout(warningTimeout);
        
        warningTimeout = setTimeout(() => {
            if (warning) {
                warning.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    if (warning.parentNode) warning.remove();
                }, 300);
            }
        }, CONFIG.warningDuration);
    }

    // ============================================
    // CSS ANIMATIONS
    // ============================================
    
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        @keyframes slideOut {
            from { opacity: 1; transform: translateX(-50%) translateY(0); }
            to { opacity: 0; transform: translateX(-50%) translateY(-20px); }
        }
        
        /* Prevent text selection globally */
        .test-content, .question-card, .question-container, .no-select {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
            -webkit-touch-callout: none !important;
        }
        
        /* Hide content when printing */
        @media print {
            body * { display: none !important; visibility: hidden !important; }
            body::before {
                content: "⚠️ PRINT TIDAK DIIZINKAN - RAYCORP RECRUITMENT";
                display: block !important;
                visibility: visible !important;
                font-size: 30px;
                text-align: center;
                padding: 200px 50px;
                color: #dc3545;
            }
        }
    `;
    document.head.appendChild(style);

    // ============================================
    // INITIALIZATION LOG
    // ============================================
    
    console.log('%c⚠️ SECURITY NOTICE', 'color: red; font-size: 20px; font-weight: bold;');
    console.log('%cThis assessment is protected. Any attempt to cheat will be logged and reported.', 'color: orange; font-size: 14px;');
    console.log('%c[Anti-Cheat] Maximum security protection initialized', 'color: green;');

})();
