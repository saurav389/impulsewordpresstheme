# QR Code & ID Card Feature Implementation

## Overview
Successfully implemented a complete QR code generation and ID card creation system for the LMS student management module.

## Features Implemented

### 1. **QR Code Generation** (`class-ica-lms-qr-code.php`)
- **Automatic QR Generation**: QR codes are automatically generated when a new student is created
- **Data Encoded**: Registration number (e.g., ICAL-2026-00001) is encoded in the QR code
- **Storage**: QR code images are stored in `/wp-content/uploads/ica-lms-qr-codes/` directory
- **Fallback Methods**:
  - Uses `chillerlan/php-qrcode` if available
  - Falls back to external QR API (qrserver.com) for reliable generation
  - All generated QR codes are 200x200 pixels PNG images

**Key Methods:**
- `generate_qr_code($student_reg_no, $student_id)` - Generate QR code for a student
- `get_student_qr_url($student_id)` - Retrieve QR code URL (with auto-regeneration if missing)
- `delete_student_qr($student_id, $reg_no)` - Clean up QR code when student is deleted

### 2. **ID Card Generation** (`class-ica-lms-id-card.php`)
- **Professional Design**: Beautiful gradient ID card with modern styling
- **Contains**:
  - Student photograph (if available)
  - Registration number
  - Student name
  - Roll number
  - Course name
  - Batch name
  - QR code (for verification/scanning)
  - Admission date
  - Authorized signature space

**Output Formats:**
- HTML (for browser printing) - fallback method
- PDF (if Dompdf is available)

**Key Methods:**
- `generate_id_card_pdf($student_id)` - Generate ID card for individual student
- `generate_bulk_id_cards($student_ids)` - Generate multiple ID cards as ZIP file

### 3. **Database Integration**
- **Auto QR Generation**: Modified `create_student()` to automatically generate QR codes
- **Cleanup on Delete**: Modified `delete_student()` to remove QR codes when student is deleted

### 4. **Admin Interface Enhancements**

#### Individual ID Card Download
- **Location**: Student list - Actions column
- **Access**: Click "ID Card" button next to each student
- **Security**: Nonce-verified endpoint (`admin_post_ica_lms_download_id_card`)
- **Output**: Downloads as HTML/PDF file named `ID_Card_[REGISTRATION_NUMBER]`

#### Bulk ID Card Download
- **Location**: Student list - Filter section
- **Access**: Click "Bulk ID Cards" button
- **Security**: AJAX nonce verification
- **Output**: Downloads ZIP file containing all ID cards as HTML files
- **Filter Support**: Can filter by course before bulk download

### 5. **File Structure**

```
lms/includes/
├── class-ica-lms-qr-code.php      (NEW - 153 lines)
├── class-ica-lms-id-card.php      (NEW - 334 lines)
├── class-ica-lms-db.php           (MODIFIED - added QR generation in create_student, cleanup in delete_student)
├── class-ica-lms-admin-student.php (MODIFIED - added ID card download handlers and UI)
└── bootstrap.php                   (MODIFIED - included new classes)
```

### 6. **How It Works**

#### Student Creation Flow:
1. Admin adds new student via "Add New Student" form
2. Student record created in database
3. **Automatic**: QR code generated with registration number
4. QR image stored in `/wp-content/uploads/ica-lms-qr-codes/qr_student_[ID]_[REG_NO].png`

#### ID Card Download:
1. Admin clicks "ID Card" button next to student name
2. System retrieves:
   - Student data (name, reg no, roll no)
   - Course name
   - Batch name
   - Student photo (if available)
   - Generated QR code image
3. ID card generated with all information
4. Downloaded as HTML or PDF

#### Bulk Download:
1. Admin clicks "Bulk ID Cards" button
2. System generates ID cards for:
   - All students (if no filter)
   - Students in selected course (if filtered)
3. All HTML files packaged in ZIP
4. ZIP downloaded with timestamp: `ica-lms-id-cards-2026-04-11-HHMMSS.zip`

## Technical Details

### Security Features
- ✅ Nonce verification on all download endpoints
- ✅ `manage_options` capability check
- ✅ Proper sanitization of user input
- ✅ Safe file operations

### Styling
- Professional gradient background (purple theme)
- Responsive design
- Print-friendly CSS
- High-quality image handling

### Performance
- QR codes generated only once and reused
- Bulk generation uses temporary directory
- Cleanup of temporary files after ZIP creation

## Usage Instructions

### For Admin Users:

**Single ID Card Download:**
1. Go to LMS → Students
2. Find student in list
3. Click "ID Card" button in Actions column
4. File downloads automatically

**Bulk ID Card Download:**
1. Go to LMS → Students
2. (Optional) Filter by course using "Filter by Course" dropdown
3. Click "Bulk ID Cards" button
4. Wait for processing (shows alert)
5. ZIP file downloads containing all ID cards

### For Developers:

**Generate QR Code Programmatically:**
```php
ICA_LMS_QR_Code::generate_qr_code('ICAL-2026-00001', 1);
$qr_url = ICA_LMS_QR_Code::get_student_qr_url(1);
```

**Generate ID Card Programmatically:**
```php
ICA_LMS_ID_Card::generate_id_card_pdf(1); // Downloads PDF
$zip_file = ICA_LMS_ID_Card::generate_bulk_id_cards([1, 2, 3]); // Returns ZIP path
```

## Configuration

No configuration needed! The system automatically:
- Creates QR directory on first use
- Detects available QR library
- Uses external API as fallback
- Creates temporary directory for batch operations

## Dependencies

### Required
- WordPress core functions
- PHP 5.6+ (for file operations)

### Optional (Auto-detected)
- Dompdf (for PDF output) - if not available, uses HTML
- chillerlan/php-qrcode (for local QR generation) - if not available, uses external API

## Testing

To test the feature:

1. **Create a new student**: Go to LMS → Students → Add New
   - Fill all fields
   - Submit form
   - Check `/wp-content/uploads/ica-lms-qr-codes/` - QR file should exist

2. **Download ID Card**:
   - Go to LMS → Students
   - Click "ID Card" button for any student
   - File should download

3. **Bulk Download**:
   - Go to LMS → Students
   - Click "Bulk ID Cards" button
   - Wait for processing
   - ZIP file should download with multiple ID card files

## Future Enhancements

Potential improvements:
- [ ] Add ID card template customization
- [ ] Add background image for ID cards
- [ ] Support for digital signature
- [ ] Email ID card to student
- [ ] Print preview before download
- [ ] Add barcode in addition to QR code
- [ ] Support for multiple language ID cards
- [ ] Custom QR code styling (colors, size)

## Files Modified

1. **bootstrap.php**
   - Added: `require_once ICA_LMS_PATH . '/includes/class-ica-lms-qr-code.php';`
   - Added: `require_once ICA_LMS_PATH . '/includes/class-ica-lms-id-card.php';`

2. **class-ica-lms-db.php**
   - Modified: `create_student()` - added auto QR generation
   - Modified: `delete_student()` - added QR cleanup

3. **class-ica-lms-admin-student.php**
   - Modified: `init()` - added new action hooks
   - Modified: `render_students_list()` - added ID Card button and bulk download button
   - Added: `handle_download_id_card()` - handles individual ID card downloads
   - Added: `ajax_bulk_download_id_cards()` - handles bulk download

## Version Info

- **Implementation Date**: April 11, 2026
- **Feature Version**: 1.0.0
- **LMS Version**: 1.4.0+
- **Compatible With**: WordPress 4.7+, PHP 5.6+
