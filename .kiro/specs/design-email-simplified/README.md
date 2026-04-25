# Email Design Documentation - Simplified Participant Registration

## Overview

Dokumentasi ini menerangkan design email untuk simplified participant registration feature. Terdapat 2 jenis email yang berbeza untuk verified vs simplified participants.

## Email Templates

### 1. Certificate Generated - Simplified Participant
**File**: `certificate-generated-simplified.html`

**Penerima**: Simplified participants (tanpa IC/Passport)

**Perbezaan dengan Verified**:
- ✅ **Direct Download Button** - Link terus ke PDF certificate
- ✅ **30 Days Validity Notice** - Warning bahawa link valid 30 hari sahaja
- ❌ **TIADA PWA Portal Link** - Simplified participants tidak boleh access PWA
- ❌ **TIADA Login Instructions** - Tidak perlu login ke portal

**Key Features**:
- Green download icon dengan arrow pointing down
- Big green button "Download Certificate"
- Yellow warning box dengan 30 days notice
- Signed URL yang valid selama 30 hari

**Design Elements**:
- Header: Dark background dengan green checkmark icon
- Certificate Info: Grey box dengan certificate details
- Download Section: Green background dengan white button
- Warning Section: Yellow background dengan orange border
- Contact Section: Standard contact info

---

### 2. Event Registration Confirmation - Simplified
**File**: `event-registration-simplified.html`

**Penerima**: Simplified participants (tanpa IC/Passport)

**Perbezaan dengan Verified**:
- ✅ **Certificate Notice** - Mention certificate akan dihantar via email
- ❌ **TIADA PWA Portal Section** - Tidak mention tentang portal
- ❌ **TIADA Login Instructions** - Tidak perlu create account

**Key Features**:
- Blue info box dengan certificate information
- Simple event details table
- Contact information section
- No mention of PWA portal anywhere

**Design Elements**:
- Header: Dark background dengan green checkmark icon
- Event Info: Grey box dengan event details
- Certificate Notice: Blue background dengan scroll icon
- Contact Section: Standard contact info

---

## Comparison: Verified vs Simplified

### Certificate Email

| Feature | Verified Participants | Simplified Participants |
|---------|----------------------|------------------------|
| Download Method | PWA Portal Login | Direct Download Link |
| Link Validity | Permanent (via account) | 30 days (signed URL) |
| Portal Access | ✅ Yes | ❌ No |
| Login Required | ✅ Yes | ❌ No |
| Instructions | 4-step portal guide | 1-click download |

### Registration Email

| Feature | Verified Participants | Simplified Participants |
|---------|----------------------|------------------------|
| Portal Section | ✅ Yes (blue box) | ❌ No |
| Certificate Info | Portal access after event | Email after event |
| Account Creation | Mentioned | Not mentioned |
| Login Instructions | Included | Not included |

---

## Technical Implementation

### Signed URL Generation
```php
// In CertificateGeneratedSimplified.php
$this->downloadUrl = URL::temporarySignedRoute(
    'certificates.download.simplified',
    now()->addDays(30),
    ['certificate' => $certificate->id]
);
```

### Route Configuration
```php
// Public route with signed middleware
Route::get('/certificates/{certificate}/download-simplified', 
    [CertificateController::class, 'downloadSimplified'])
    ->middleware('signed')
    ->name('certificates.download.simplified');
```

### Controller Logic
```php
// CertificateController@store
if ($participant->registration_type === 'simplified') {
    $mailable = new CertificateGeneratedSimplified($event, $participant, $certificate);
} else {
    $mailable = new CertificateGeneratedNotification($event, $participant, $certificate);
}
```

---

## Preview Instructions

1. Buka file HTML dalam browser untuk preview design
2. File boleh dibuka terus tanpa server (static HTML)
3. Semua styling adalah inline untuk email compatibility

### Preview Files:
- `certificate-generated-simplified.html` - Certificate email design
- `event-registration-simplified.html` - Registration email design

---

## Design Guidelines

### Colors Used:
- **Primary Green**: `#10b981` - Success, download buttons
- **Dark Background**: `#111827` - Email header
- **Warning Yellow**: `#fef3c7` - 30 days notice background
- **Warning Orange**: `#f59e0b` - Warning border
- **Info Blue**: `#eff6ff` - Certificate notice background
- **Text Dark**: `#111827` - Main text
- **Text Grey**: `#6b7280` - Secondary text

### Typography:
- **Font Family**: 'Poppins', Arial, sans-serif
- **Header**: 26px, bold
- **Body**: 14px, regular
- **Labels**: 13px, medium weight

### Spacing:
- **Section Padding**: 40px
- **Element Margin**: 25px bottom
- **Inner Padding**: 20-30px

---

## Email Client Compatibility

Design ini compatible dengan:
- ✅ Gmail (Desktop & Mobile)
- ✅ Outlook (Desktop & Web)
- ✅ Apple Mail (iOS & macOS)
- ✅ Yahoo Mail
- ✅ Thunderbird

**Note**: Semua styling menggunakan inline CSS untuk maximum compatibility.

---

## Testing Checklist

Sebelum deploy, pastikan:
- [ ] Download link berfungsi dengan betul
- [ ] Signed URL valid selama 30 hari
- [ ] Email display correctly di mobile devices
- [ ] Semua placeholder data diganti dengan data sebenar
- [ ] Contact information betul
- [ ] Certificate number format betul
- [ ] Date format betul (d F Y)

---

## Maintenance Notes

### Bila nak update design:
1. Edit file Blade template di `resources/views/emails/`
2. Update preview HTML di folder ini
3. Test dengan send test email
4. Verify di multiple email clients

### Bila nak tambah field baru:
1. Tambah dalam certificate details table
2. Maintain left-right layout (label: value)
3. Keep consistent spacing (10px padding)
4. Update preview HTML juga

---

## Support

Untuk sebarang issue atau pertanyaan tentang email design:
1. Check preview HTML files dulu
2. Test dengan send test email
3. Verify signed URL expiry
4. Check logs untuk email sending errors

---

**Last Updated**: 26 April 2026
**Version**: 1.0.0
