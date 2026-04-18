// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        
        // Update active states
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        btn.classList.add('active');
        document.getElementById(`${tab}-tab`).classList.add('active');
    });
});

// QR Scanner
let videoStream = null;
let scanning = false;
const video = document.getElementById('qr-video');
const startScanBtn = document.getElementById('start-scan-btn');
const stopScanBtn = document.getElementById('stop-scan-btn');

startScanBtn.addEventListener('click', async () => {
    console.log('Start scan button clicked');
    console.log('navigator.mediaDevices:', navigator.mediaDevices);
    console.log('getUserMedia:', navigator.mediaDevices ? navigator.mediaDevices.getUserMedia : 'undefined');
    
    try {
        console.log('Requesting camera access...');
        
        // Try to get camera access - let browser handle compatibility
        videoStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        });
        
        console.log('Camera access granted');
        
        video.srcObject = videoStream;
        await video.play();
        
        console.log('Video playing');
        
        startScanBtn.style.display = 'none';
        stopScanBtn.style.display = 'inline-block';
        scanning = true;
        
        // Start scanning
        scanQRCode();
    } catch (err) {
        console.error('Camera error:', err);
        console.error('Error name:', err.name);
        console.error('Error message:', err.message);
        
        let errorMessage = 'Camera access failed.';
        
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            errorMessage = 'Camera permission denied. Please allow camera access and try again.';
        } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
            errorMessage = 'No camera found. Please use a device with a camera or enter the certificate number manually.';
        } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
            errorMessage = 'Camera is in use by another application. Please close other apps and try again.';
        } else if (err.name === 'OverconstrainedError' || err.name === 'ConstraintNotSatisfiedError') {
            console.log('Trying with simpler camera constraints...');
            
            // Retry with simpler constraints
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = videoStream;
                await video.play();
                startScanBtn.style.display = 'none';
                stopScanBtn.style.display = 'inline-block';
                scanning = true;
                scanQRCode();
                return;
            } catch (retryErr) {
                console.error('Retry failed:', retryErr);
                errorMessage = 'Failed to access camera with any settings.';
            }
        } else if (err.name === 'TypeError' || err.message.includes('mediaDevices')) {
            errorMessage = 'Camera API not available. Please use HTTPS or localhost, and ensure your browser supports camera access.';
        } else if (err.name === 'NotSupportedError' || err.name === 'SecurityError') {
            errorMessage = 'Camera access not supported. Please ensure you are using HTTPS or localhost.';
        }
        
        alert(errorMessage + '\n\nDetails: ' + err.name + ' - ' + err.message + '\n\nPlease check browser console for more information.');
    }
});

stopScanBtn.addEventListener('click', () => {
    stopScanning();
});

function scanQRCode() {
    if (!scanning) return;
    
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    
    function tick() {
        if (!scanning) return;
        
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            
            try {
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });
                
                if (code && code.data) {
                    // QR code detected
                    console.log('QR Code detected:', code.data);
                    stopScanning();
                    verifyQRCode(code.data);
                    return;
                }
            } catch (err) {
                console.error('QR scanning error:', err);
            }
        }
        
        requestAnimationFrame(tick);
    }
    
    tick();
}

function stopScanning() {
    scanning = false;
    
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    
    video.srcObject = null;
    startScanBtn.style.display = 'inline-block';
    stopScanBtn.style.display = 'none';
}

// Verify QR code
async function verifyQRCode(qrData) {
    try {
        const response = await fetch('verify.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ qr_data: qrData })
        });
        
        const result = await response.json();
        displayResult(result);
    } catch (err) {
        displayResult({ status: 'ERROR', message: 'Verification failed' });
    }
}

// Manual verification form
document.getElementById('manual-verify-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const certificateNumber = document.getElementById('certificate-number').value;
    
    try {
        const response = await fetch('verify.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ certificate_number: certificateNumber })
        });
        
        const result = await response.json();
        displayResult(result);
    } catch (err) {
        displayResult({ status: 'ERROR', message: 'Verification failed' });
    }
});

// Display verification result
function displayResult(result) {
    const container = document.getElementById('result-container');
    const content = document.getElementById('result-content');
    
    container.classList.remove('hidden');
    
    if (result.status === 'VALID') {
        content.innerHTML = `
            <div class="result-valid">
                <div class="status-badge valid">✓ VALID</div>
                <div class="certificate-details">
                    <div class="detail-row">
                        <span class="label">Certificate Number:</span>
                        <span class="value">${result.data.certificate_number}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Participant Name:</span>
                        <span class="value">${result.data.participant_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Event Name:</span>
                        <span class="value">${result.data.event_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Event Date:</span>
                        <span class="value">${result.data.event_date}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Event Time:</span>
                        <span class="value">${result.data.event_time}</span>
                    </div>
                </div>
            </div>
        `;
    } else {
        let debugInfo = '';
        if (result.debug) {
            debugInfo = `<p class="text-xs text-gray-600 mt-2">Debug: ${result.debug}</p>`;
        }
        
        content.innerHTML = `
            <div class="result-invalid">
                <div class="status-badge invalid">✗ INVALID</div>
                <p class="message">${result.message || 'Certificate not found or invalid'}</p>
                ${debugInfo}
            </div>
        `;
    }
    
    // Scroll to result
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
