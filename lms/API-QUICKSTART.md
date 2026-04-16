# ICA LMS REST API - Quick Start Guide

## 5-Minute Setup

### Step 1: Verify API is Enabled

First, verify the API is running by visiting:

```
https://your-site.com/wp-json/ica-lms/v1/info
```

You should see API information without needing to log in.

### Step 2: Create an Application Password

1. Login to WordPress Admin
2. Go to **Users** → **Your Profile**
3. Scroll to **Application Passwords** section
4. Enter an app name (e.g., "Mobile App")
5. Click **Create Application Password**
6. Copy the generated password and save it securely

### Step 3: Make Your First API Call

Replace `username`, `app_password`, and `your-site.com` with your actual values:

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/students"
```

You should receive a JSON response with student data.

---

## Common Use Cases

### Get All Students (with pagination)

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/students?page=1&per_page=50"
```

### Get Students from Specific Batch

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/students?batch_id=1"
```

### Search for Student

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/students?search=john"
```

### Get Student Details

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/students/1"
```

### Create New Student

```bash
curl -X POST -u username:app_password \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Student",
    "mobile_no": "9876543210",
    "course_id": 5,
    "batch_id": 1,
    "fee_amount": 10000
  }' \
  "https://your-site.com/wp-json/ica-lms/v1/students"
```

### Update Student

```bash
curl -X PUT -u username:app_password \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "mobile_no": "9876543211"
  }' \
  "https://your-site.com/wp-json/ica-lms/v1/students/1"
```

### Delete Student

```bash
curl -X DELETE -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/students/1"
```

### Get All Batches

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/batches"
```

### Get Active Batches Only

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/batches?status=active"
```

### Get All Courses

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/courses"
```

### Download Student ID Card

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/id-card/1?format=pdf" \
  -o student_id_card.pdf
```

### Get Student QR Code

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/qr-code/1"
```

### Record Payment

```bash
curl -X POST -u username:app_password \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "installment_number": 1,
    "amount": 5000,
    "payment_method": "online",
    "transaction_id": "TXN123456"
  }' \
  "https://your-site.com/wp-json/ica-lms/v1/payments/record"
```

### Get Payment History for Student

```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/payments/student/1"
```

---

## Using with Postman

### 1. Create a new Postman Collection

### 2. Set Base URL Variable
- Click **Variables** (top menu)
- Create variable: `base_url` = `https://your-site.com/wp-json/ica-lms/v1`

### 3. Configure Authentication
- Create request with URL: `{{base_url}}/students`
- Go to **Authorization** tab
- Select "Basic Auth"
- Username: your WordPress username
- Password: your application password

### 4. Start Making Requests

#### GET Students
```
GET {{base_url}}/students
```

#### POST Create Student
```
POST {{base_url}}/students
Body (raw JSON):
{
  "name": "John Doe",
  "mobile_no": "9876543210",
  "course_id": 5,
  "batch_id": 1
}
```

#### PUT Update Student
```
PUT {{base_url}}/students/1
Body (raw JSON):
{
  "name": "John Smith"
}
```

#### DELETE Student
```
DELETE {{base_url}}/students/1
```

---

## Using with JavaScript (Frontend)

### Setup Request Helper

```javascript
const API_BASE = 'https://your-site.com/wp-json/ica-lms/v1';

// For browser requests (if API is on same domain)
async function apiRequest(endpoint, options = {}) {
  const url = `${API_BASE}${endpoint}`;
  
  const response = await fetch(url, {
    credentials: 'include', // Include WordPress cookies
    headers: {
      'Content-Type': 'application/json',
      ...options.headers
    },
    ...options
  });
  
  if (!response.ok) {
    throw new Error(`API Error: ${response.status}`);
  }
  
  return response.json();
}
```

### Usage Examples

```javascript
// Get students
const students = await apiRequest('/students?page=1&per_page=20');
console.log(students);

// Get single student
const student = await apiRequest('/students/1');
console.log(student);

// Create student
const newStudent = await apiRequest('/students', {
  method: 'POST',
  body: JSON.stringify({
    name: 'Jane Doe',
    mobile_no: '9876543210',
    course_id: 5,
    batch_id: 1,
    fee_amount: 10000
  })
});
console.log(newStudent);

// Update student
const updated = await apiRequest('/students/1', {
  method: 'PUT',
  body: JSON.stringify({
    name: 'Jane Smith'
  })
});
console.log(updated);

// Delete student
await apiRequest('/students/1', { method: 'DELETE' });
console.log('Student deleted');

// Get QR code
const qrCode = await apiRequest('/qr-code/1');
console.log(qrCode.data.qr_code_url);
```

---

## Using with React

### Custom Hook

```javascript
import { useState, useEffect } from 'react';

const useAPI = () => {
  const API_BASE = 'https://your-site.com/wp-json/ica-lms/v1';

  const request = async (endpoint, options = {}) => {
    const response = await fetch(`${API_BASE}${endpoint}`, {
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        ...options.headers
      },
      ...options
    });

    if (!response.ok) throw new Error(`Error: ${response.status}`);
    return response.json();
  };

  return { request };
};

// Usage in component
function StudentsList() {
  const [students, setStudents] = useState([]);
  const { request } = useAPI();

  useEffect(() => {
    request('/students?page=1')
      .then(data => setStudents(data.data))
      .catch(err => console.error(err));
  }, []);

  return (
    <ul>
      {students.map(student => (
        <li key={student.id}>{student.name}</li>
      ))}
    </ul>
  );
}
```

---

## Using with Python Requests

### Setup

```python
import requests
from requests.auth import HTTPBasicAuth
import json

API_BASE = 'https://your-site.com/wp-json/ica-lms/v1'
USERNAME = 'your_username'
PASSWORD = 'your_app_password'

def api_request(endpoint, method='GET', data=None):
    url = f'{API_BASE}{endpoint}'
    auth = HTTPBasicAuth(USERNAME, PASSWORD)
    
    if method == 'GET':
        response = requests.get(url, auth=auth)
    elif method == 'POST':
        response = requests.post(url, json=data, auth=auth)
    elif method == 'PUT':
        response = requests.put(url, json=data, auth=auth)
    elif method == 'DELETE':
        response = requests.delete(url, auth=auth)
    
    return response.json()
```

### Usage Examples

```python
# Get all students
students = api_request('/students')
print(json.dumps(students, indent=2))

# Create student
new_student = api_request('/students', method='POST', data={
    'name': 'John Doe',
    'mobile_no': '9876543210',
    'course_id': 5,
    'batch_id': 1,
    'fee_amount': 10000
})
print(f"Created student: {new_student['data']['id']}")

# Update student
updated = api_request('/students/1', method='PUT', data={
    'name': 'Jane Doe'
})
print(updated)

# Delete student
api_request('/students/1', method='DELETE')
print("Student deleted")

# Get payments
payments = api_request('/payments/student/1')
print(json.dumps(payments, indent=2))
```

---

## Error Handling

### Check for Success

```bash
# All successful responses have "success": true
curl -u username:app_password "https://your-site.com/wp-json/ica-lms/v1/students" | jq '.success'
```

### Handle Errors

```javascript
async function apiRequest(endpoint) {
  try {
    const response = await fetch(`https://your-site.com/wp-json/ica-lms/v1${endpoint}`, {
      credentials: 'include'
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      console.error(`Error: ${data.code} - ${data.message}`);
      return null;
    }
    
    return data;
  } catch (error) {
    console.error('Request failed:', error);
    return null;
  }
}
```

---

## Testing the API

### Using cURL Script

Save this as `test_api.sh`:

```bash
#!/bin/bash

API="https://your-site.com/wp-json/ica-lms/v1"
USERNAME="your_username"
PASSWORD="your_app_password"

echo "=== Testing ICA LMS API ==="

echo "\n1. Get API Info (no auth required)"
curl "$API/info"

echo "\n2. Get Students"
curl -u "$USERNAME:$PASSWORD" "$API/students"

echo "\n3. Get Batches"
curl -u "$USERNAME:$PASSWORD" "$API/batches"

echo "\n4. Get Courses"
curl -u "$USERNAME:$PASSWORD" "$API/courses"

echo "\nDone!"
```

Run with:
```bash
chmod +x test_api.sh
./test_api.sh
```

---

## Rate Limits

Currently no rate limiting is implemented. However, it's recommended to:
- Batch requests when possible
- Cache responses with appropriate TTL
- Implement exponential backoff for retries

---

## Security Best Practices

1. **Never expose credentials in code** - Use environment variables
2. **Use HTTPS always** - Never use HTTP
3. **Limit app passwords** - Use different passwords for different apps
4. **Rotate passwords** - Change application passwords regularly
5. **Monitor access** - Check WordPress authentication logs

---

## Troubleshooting

### 401 Unauthorized
- Check username and password
- Verify application password is correct
- Make sure user has `manage_options` capability

### 404 Not Found
- Verify student/batch/course exists
- Check endpoint spelling
- Ensure base URL is correct

### 403 Forbidden
- User doesn't have `manage_options` capability
- Promote user to Administrator role

### 500 Internal Server Error
- Check WordPress error logs at `/wp-content/debug.log`
- Verify all required classes are loaded
- Check for PHP syntax errors

---

## Support Resources

- **Full API Documentation**: See `API-DOCUMENTATION.md`
- **WordPress REST API Guide**: https://developer.wordpress.org/rest-api/
- **Postman Collection**: Download from GitHub/repository

---

**Last Updated**: April 13, 2026  
**API Version**: 1.0.0
