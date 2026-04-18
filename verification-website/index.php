<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Certificate Verification</h1>
            <p>Verify the authenticity of your certificate</p>
        </div>
        
        <div class="verification-methods">
            <div class="method-tabs">
                <button class="tab-btn active" data-tab="scan">Scan QR Code</button>
                <button class="tab-btn" data-tab="manual">Enter Certificate Number</button>
            </div>
            
            <!-- QR Scanner Tab -->
            <div class="tab-content active" id="scan-tab">
                <div class="scanner-container">
                    <video id="qr-video" autoplay playsinline></video>
                    <div class="scanner-overlay">
                        <div class="scanner-frame"></div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button id="start-scan-btn" class="btn btn-primary">Start Scanning</button>
                    <button id="stop-scan-btn" class="btn btn-secondary" style="display: none;">Stop Scanning</button>
                </div>
                <p style="text-align: center; margin-top: 10px; font-size: 14px; color: #666;">
                    Point your camera at the QR code on the certificate
                </p>
            </div>
            
            <!-- Manual Entry Tab -->
            <div class="tab-content" id="manual-tab">
                <form id="manual-verify-form">
                    <div class="form-group">
                        <label for="certificate-number">Certificate Number</label>
                        <input 
                            type="text" 
                            id="certificate-number" 
                            name="certificate_number" 
                            placeholder="CERT-20260416223748-3B1D0A"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-primary">Verify Certificate</button>
                </form>
            </div>
        </div>
        
        <!-- Results Display -->
        <div id="result-container" class="result-container hidden">
            <div id="result-content"></div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
