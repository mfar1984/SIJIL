<?php
/**
 * Generate self-signed SSL certificate for HTTPS server
 * This script uses PHP's OpenSSL extension (built-in)
 */

echo "Generating self-signed SSL certificate...\n\n";

// Certificate configuration
$dn = [
    "countryName" => "MY",
    "stateOrProvinceName" => "Selangor",
    "localityName" => "Kuala Lumpur",
    "organizationName" => "eSijil Certificate Verification",
    "commonName" => "192.168.1.33", // Change this to your IP address
];

// Generate private key
$privateKey = openssl_pkey_new([
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
]);

if (!$privateKey) {
    die("Error: Failed to generate private key\n");
}

// Generate certificate signing request
$csr = openssl_csr_new($dn, $privateKey, ['digest_alg' => 'sha256']);

if (!$csr) {
    die("Error: Failed to generate CSR\n");
}

// Generate self-signed certificate (valid for 365 days)
$x509 = openssl_csr_sign($csr, null, $privateKey, 365, ['digest_alg' => 'sha256']);

if (!$x509) {
    die("Error: Failed to generate certificate\n");
}

// Export certificate to file
openssl_x509_export_to_file($x509, 'cert.pem');
echo "✓ Certificate saved to: cert.pem\n";

// Export private key to file
openssl_pkey_export_to_file($privateKey, 'key.pem');
echo "✓ Private key saved to: key.pem\n";

echo "\nCertificate generated successfully!\n\n";
echo "Next steps:\n";
echo "1. Run HTTPS server: php server-https.php\n";
echo "2. Access via: https://192.168.1.33:8443\n";
echo "3. Accept security warning in browser\n";
echo "4. Camera access will now work!\n";
