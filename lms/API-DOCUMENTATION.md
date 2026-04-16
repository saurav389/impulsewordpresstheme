# ICA LMS REST API Documentation

## Overview

The ICA LMS REST API allows external applications to integrate with the LMS system. All endpoints follow RESTful standards and return JSON responses.

**API Base URL**: `https://your-site.com/wp-json/ica-lms/v1`

**Current API Version**: 1.0.0

## Authentication

All API endpoints (except `/info`) require:
1. **User Authentication**: Must be logged in with WordPress user account
2. **Capability**: User must have `manage_options` capability (Administrator role)
3. **Method**: WordPress REST API authentication (cookies or token-based)

### Authentication Methods

#### Using Basic Authentication (for development)
```bash
curl -u username:password https://your-site.com/wp-json/ica-lms/v1/students
```

#### Using Application Passwords (WordPress 5.6+)
1. Navigate to WordPress Admin → Users → Your Profile
2. Scroll down to "Application Passwords"
3. Create a new application password
4. Use it for authentication:

```bash
curl -u username:app_password https://your-site.com/wp-json/ica-lms/v1/students
```

#### Using Cookies (Browser-based)
If making requests from JavaScript in WordPress admin, authentication is automatic.

## API Endpoints

### 1. Students Endpoints

#### GET `/students` - Get list of students

**Query Parameters:**
- `page` (integer, default: 1) - Page number for pagination
- `per_page` (integer, default: 20, max: 100) - Items per page
- `course_id` (integer, optional) - Filter by course
- `batch_id` (integer, optional) - Filter by batch
- `search` (string, optional) - Search by name, reg_no, mobile_no, or aadhar_no

**Request:**
```bash
curl -u username:password "https://your-site.com/wp-json/ica-lms/v1/students?course_id=5&page=1&per_page=20"
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "reg_no": "ICAL-2026-00001",
      "roll_no": "Batch-A-0001",
      "name": "John Doe",
      "mobile_no": "9876543210",
      "aadhar_no": "1234-5678-9012",
      "father_name": "James Doe",
      "mother_name": "Jane Doe",
      "date_of_birth": "2000-05-15",
      "gender": "Male",
      "category_id": 1,
      "qualification": "B.Tech",
      "address": "123 Baker Street",
      "course_id": 5,
      "batch_id": 1,
      "fee_status": "pending",
      "fee_type": "installment",
      "fee_amount": 10000,
      "discount_amount": 500,
      "status": "active",
      "student_photo_url": "https://...",
      "student_signature_url": "https://...",
      "aadhar_photo_url": "https://...",
      "qualification_cert_url": "https://...",
      "admission_date": "2026-04-01 10:30:00",
      "created_at": "2026-04-01 10:30:00",
      "updated_at": "2026-04-01 10:30:00"
    }
  ],
  "pagination": {
    "total": 150,
    "page": 1,
    "per_page": 20,
    "total_pages": 8
  }
}
```

---

#### GET `/students/{id}` - Get single student

**Request:**
```bash
curl -u username:password "https://your-site.com/wp-json/ica-lms/v1/students/1"
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "reg_no": "ICAL-2026-00001",
    "name": "John Doe",
    ...
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "code": "student_not_found",
  "message": "Student not found",
  "data": {
    "status": 404
  }
}
```

---

#### POST `/students` - Create new student

**Request:**
```bash
curl -X POST -u username:password \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "mobile_no": "9876543211",
    "father_name": "David Smith",
    "mother_name": "Mary Smith",
    "date_of_birth": "2000-06-20",
    "gender": "Female",
    "category_id": 1,
    "qualification": "B.Tech",
    "aadhar_no": "1234-5678-9013",
    "address": "456 Main Street",
    "course_id": 5,
    "batch_id": 1,
    "fee_amount": 10000,
    "fee_type": "one_time"
  }' \
  "https://your-site.com/wp-json/ica-lms/v1/students"
```

**Required Fields:**
- `name` (string) - Student name
- `mobile_no` (string) - Mobile number
- `course_id` (integer) - Course ID
- `batch_id` (integer) - Batch ID

**Optional Fields:**
- `father_name`, `mother_name`, `date_of_birth`, `gender`, `category_id`
- `qualification`, `aadhar_no`, `address`
- `fee_amount`, `fee_type`, `discount_amount`, `installment_count`
- `student_photo_url`, `student_signature_url`, `aadhar_photo_url`, `qualification_cert_url`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Student created successfully",
  "data": {
    "id": 150,
    "reg_no": "ICAL-2026-00150",
    "roll_no": "Batch-A-0050",
    ...
  }
}
```

**Error Response (400 Bad Request):**
```json
{
  "code": "invalid_input",
  "message": "Name and Mobile No are required",
  "data": {
    "status": 400
  }
}
```

---

#### PUT `/students/{id}` - Update student

**Request:**
```bash
curl -X PUT -u username:password \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith Updated",
    "mobile_no": "9876543211",
    "fee_amount": 11000
  }' \
  "https://your-site.com/wp-json/ica-lms/v1/students/1"
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Student updated successfully",
  "data": { ... }
}
```

---

#### DELETE `/students/{id}` - Delete student

**Request:**
```bash
curl -X DELETE -u username:password \
  "https://your-site.com/wp-json/ica-lms/v1/students/1"
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Student deleted successfully"
}
```

---

### 2. Batches Endpoints

#### GET `/batches` - Get list of batches

**Query Parameters:**
- `course_id` (integer, optional) - Filter by course
- `status` (string, optional) - Filter by status: `active`, `inactive`, `completed`

**Request:**
```bash
curl -u username:password "https://your-site.com/wp-json/ica-lms/v1/batches?course_id=5&status=active"
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "batch_name": "Batch A 2026",
      "course_id": 5,
      "total_students": 30,
      "batch_start_date": "2026-04-01",
      "batch_end_date": "2026-09-30",
      "description": "Spring batch for 2026",
      "status": "active",
      "created_at": "2026-04-01 10:00:00",
      "updated_at": "2026-04-01 10:00:00"
    }
  ]
}
```

---

#### GET `/batches/{id}` - Get single batch

**Request:**
```bash
curl -u username:password "https://your-site.com/wp-json/ica-lms/v1/batches/1"
```

---

#### POST `/batches` - Create new batch

**Request:**
```bash
curl -X POST -u username:password \
  -H "Content-Type: application/json" \
  -d '{
    "batch_name": "Batch B 2026",
    "course_id": 5,
    "total_students": 25,
    "batch_start_date": "2026-05-01",
    "batch_end_date": "2026-10-31",
    "description": "Summer batch for 2026"
  }' \
  "https://your-site.com/wp-json/ica-lms/v1/batches"
```

**Required Fields:**
- `batch_name` (string) - Batch name
- `course_id` (integer) - Course ID
- `total_students` (integer) - Total student capacity (must be > 0)

**Optional Fields:**
- `batch_start_date`, `batch_end_date`, `description`

---

#### PUT `/batches/{id}` - Update batch

**Request:**
```bash
curl -X PUT -u username:password \
  -H "Content-Type: application/json" \
  -d '{
    "batch_name": "Batch A 2026 Updated",
    "total_students": 32,
    "status": "completed"
  }' \
  "https://your-site.com/wp-json/ica-lms/v1/batches/1"
```

**Allowed Fields:**
- `batch_name`, `total_students`, `description`
- `batch_start_date`, `batch_end_date`
- `status` (`active`, `inactive`, `completed`)

---

#### DELETE `/batches/{id}` - Delete batch

**Request:**
```bash
curl -X DELETE -u username:password \
  "https://your-site.com/wp-json/ica-lms/v1/batches/1"
```

---

### 3. Courses Endpoints

#### GET `/courses` - Get list of courses

**Query Parameters:**
- `page` (integer, default: 1)
- `per_page` (integer, default: 20, max: 100)

**Request:**
```bash
curl -u username:password "https://your-site.com/wp-json/ica-lms/v1/courses?page=1"
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "title": "Advanced PHP Programming",
      "description": "Learn advanced PHP concepts...",
      "price": 10000,
      "currency": "INR",
      "status": "publish",
      "created_at": "2026-03-15 10:00:00",
      "updated_at": "2026-04-01 15:30:00"
    }
  ],
  "pagination": {
    "total": 10,
    "page": 1,
    "per_page": 20,
    "total_pages": 1
  }
}
```

---

#### GET `/courses/{id}` - Get single course

**Request:**
```bash
curl -u username:password "https://your-site.com/wp-json/ica-lms/v1/courses/5"
```

---

### 4. Categories Endpoints

#### GET `/categories` - Get list of categories

**Request:**
```bash
curl -u username:password "https://your-site.com/wp-json/ica-lms/v1/categories"
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "General",
      "description": "General category",
      "status": "active",
      "created_at": "2026-04-01 10:00:00"
    }
  ]
}
```

---

### 5. ID Card Endpoints

#### GET `/id-card/{student_id}` - Get ID card

**Query Parameters:**
- `format` (string, enum: `html`, `pdf`, default: `pdf`) - Output format

**Request (PDF):**
```bash
curl -u username:password \
  "https://your-site.com/wp-json/ica-lms/v1/id-card/1?format=pdf" \
  -o id_card.pdf
```

**Request (HTML):**
```bash
curl -u username:password \
  "https://your-site.com/wp-json/ica-lms/v1/id-card/1?format=html"
```

**Response (200 OK for HTML):**
```json
{
  "success": true,
  "format": "html",
  "html": "<html>...</html>",
  "student_name": "John Doe",
  "reg_no": "ICAL-2026-00001"
}
```

---

### 6. QR Code Endpoints

#### GET `/qr-code/{student_id}` - Get QR code

**Request:**
```bash
curl -u username:password \
  "https://your-site.com/wp-json/ica-lms/v1/qr-code/1"
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "student_id": 1,
    "reg_no": "ICAL-2026-00001",
    "qr_code_url": "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=ICAL-2026-00001",
    "qr_data": "ICAL-2026-00001"
  }
}
```

---

### 7. Payment Endpoints

#### GET `/payments/student/{student_id}` - Get student payments

**Request:**
```bash
curl -u username:password \
  "https://your-site.com/wp-json/ica-lms/v1/payments/student/1"
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "student_id": 1,
    "reg_no": "ICAL-2026-00001",
    "name": "John Doe",
    "summary": {
      "total_fee": 10000,
      "paid_amount": 5000,
      "balance_amount": 5000,
      "installment_count": 2,
      "per_installment": 5000
    },
    "payments": [
      {
        "id": 1,
        "student_id": 1,
        "installment_number": 1,
        "amount": 5000,
        "currency": "INR",
        "payment_method": "online",
        "transaction_id": "TXN123456",
        "status": "completed",
        "payment_date": "2026-04-05 14:30:00",
        "notes": "First installment",
        "created_at": "2026-04-05 14:30:00"
      }
    ]
  }
}
```

---

#### POST `/payments/record` - Record payment

**Request:**
```bash
curl -X POST -u username:password \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "installment_number": 2,
    "amount": 5000,
    "payment_method": "online",
    "transaction_id": "TXN789123",
    "notes": "Second installment"
  }' \
  "https://your-site.com/wp-json/ica-lms/v1/payments/record"
```

**Required Fields:**
- `student_id` (integer)
- `installment_number` (integer)
- `amount` (float)

**Optional Fields:**
- `payment_method`, `transaction_id`, `notes`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Payment recorded successfully",
  "data": {
    "id": 2,
    "student_id": 1,
    "installment_number": 2,
    "amount": 5000,
    "status": "completed",
    ...
  }
}
```

---

### 8. API Info Endpoint

#### GET `/info` - Get API information

**Note:** This endpoint does NOT require authentication

**Request:**
```bash
curl "https://your-site.com/wp-json/ica-lms/v1/info"
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "api_version": "1.0.0",
    "api_namespace": "ica-lms/v1",
    "endpoints": {
      "students": "/wp-json/ica-lms/v1/students",
      "batches": "/wp-json/ica-lms/v1/batches",
      "courses": "/wp-json/ica-lms/v1/courses",
      "categories": "/wp-json/ica-lms/v1/categories",
      "id_card": "/wp-json/ica-lms/v1/id-card/{student_id}",
      "qr_code": "/wp-json/ica-lms/v1/qr-code/{student_id}",
      "payments": "/wp-json/ica-lms/v1/payments/student/{student_id}"
    },
    "authentication": "WordPress user login required with manage_options capability",
    "description": "ICA LMS REST API for integration with external applications"
  }
}
```

---

## Error Handling

### Error Response Format

All error responses follow this format:

```json
{
  "code": "error_code",
  "message": "Human-readable error message",
  "data": {
    "status": 400
  }
}
```

### Common Error Codes

| Code | Status | Description |
|------|--------|-------------|
| `student_not_found` | 404 | Student does not exist |
| `batch_not_found` | 404 | Batch does not exist |
| `course_not_found` | 404 | Course does not exist |
| `invalid_input` | 400 | Required fields missing or invalid |
| `create_failed` | 400 | Creation failed |
| `update_failed` | 400 | Update failed |
| `delete_failed` | 400 | Deletion failed |
| `payment_failed` | 400 | Payment recording failed |

---

## Integration Examples

### JavaScript/Node.js

```javascript
// Using Fetch API
async function getStudents() {
  const response = await fetch('https://your-site.com/wp-json/ica-lms/v1/students', {
    method: 'GET',
    credentials: 'include', // Include cookies for authentication
    headers: {
      'Authorization': 'Basic ' + btoa('username:app_password')
    }
  });
  
  const data = await response.json();
  console.log(data);
}

// Create student
async function createStudent(studentData) {
  const response = await fetch('https://your-site.com/wp-json/ica-lms/v1/students', {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Basic ' + btoa('username:app_password')
    },
    body: JSON.stringify(studentData)
  });
  
  return response.json();
}
```

### Python

```python
import requests
import json
from requests.auth import HTTPBasicAuth

BASE_URL = 'https://your-site.com/wp-json/ica-lms/v1'
AUTH = HTTPBasicAuth('username', 'app_password')

# Get students
response = requests.get(f'{BASE_URL}/students', auth=AUTH)
students = response.json()

# Create student
student_data = {
    'name': 'Jane Smith',
    'mobile_no': '9876543211',
    'course_id': 5,
    'batch_id': 1
}

response = requests.post(f'{BASE_URL}/students', json=student_data, auth=AUTH)
new_student = response.json()
```

### PHP (cURL)

```php
<?php
$username = 'username';
$password = 'app_password';
$api_url = 'https://your-site.com/wp-json/ica-lms/v1';

// Get students
$ch = curl_init("$api_url/students");
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$students = json_decode($response, true);
?>
```

---

## Rate Limiting

Currently, there is no rate limiting implemented. Future versions may include rate limiting.

---

## Versioning

API uses semantic versioning. Current version: **1.0.0**

Future versions will be available at different namespaces:
- `/wp-json/ica-lms/v2`
- `/wp-json/ica-lms/v3`
- etc.

---

## Support

For issues, bugs, or feature requests, contact your LMS administrator.

---

**Last Updated**: April 13, 2026  
**API Version**: 1.0.0  
**LMS Version**: 1.4.0+
