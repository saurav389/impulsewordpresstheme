# ICA LMS REST API

A comprehensive REST API for the ICA LMS (Impulse Academy Clone) that allows external applications to integrate with the student management system.

## 📚 Documentation Files

### Quick Start (5 minutes)
- **File**: [API-QUICKSTART.md](./API-QUICKSTART.md)
- **For**: Developers who want to quickly test and start using the API
- **Contains**: Setup instructions, common use cases, code examples in JavaScript, Python, Postman & React

### Complete API Documentation
- **File**: [API-DOCUMENTATION.md](./API-DOCUMENTATION.md)
- **For**: Reference documentation for all endpoints
- **Contains**: Detailed endpoint descriptions, request/response examples, error codes, integration guides

### Postman Collection
- **File**: [postman_collection.json](./postman_collection.json)
- **For**: Testing API in Postman GUI
- **How to use**:
  1. Open Postman
  2. Click "Import"
  3. Select this JSON file
  4. Update variables (base_url, username, password)
  5. Start making requests

## 🚀 API Features

### Core Endpoints (19 total)

**Students Management**
- `GET /students` - List students with pagination & filtering
- `GET /students/{id}` - Get single student
- `POST /students` - Create new student
- `PUT /students/{id}` - Update student
- `DELETE /students/{id}` - Delete student

**Batch Management**
- `GET /batches` - List batches
- `GET /batches/{id}` - Get single batch
- `POST /batches` - Create new batch
- `PUT /batches/{id}` - Update batch
- `DELETE /batches/{id}` - Delete batch

**Course & Category**
- `GET /courses` - List courses
- `GET /courses/{id}` - Get single course
- `GET /categories` - List categories

**Documents & IDs**
- `GET /id-card/{student_id}` - Download ID card (HTML/PDF)
- `GET /qr-code/{student_id}` - Get QR code

**Payments**
- `GET /payments/student/{student_id}` - Get student payments
- `POST /payments/record` - Record payment

**Info**
- `GET /info` - API information (no auth required)

## 🔐 Authentication

All endpoints require:
- WordPress Administrator account credentials
- Application Password (recommended for production)

```bash
# Using Application Password
curl -u username:app_password https://your-site.com/wp-json/ica-lms/v1/students
```

## ✨ Key Capabilities

✅ Full CRUD operations on students and batches  
✅ Course and category browsing  
✅ Payment tracking and recording  
✅ Student ID card generation  
✅ QR code generation  
✅ Pagination support  
✅ Advanced filtering and search  
✅ JSON responses with proper error handling  
✅ RESTful API standards  

## 📊 API Namespace

```
/wp-json/ica-lms/v1
```

## 🛠️ Implementation Files

- **[class-ica-lms-api.php](./includes/class-ica-lms-api.php)** - Main API class (19 endpoints)
- **[bootstrap.php](./bootstrap.php)** - Includes API class initialization
- **[class-ica-lms.php](./includes/class-ica-lms.php)** - Initializes API

## 🔧 Setup Instructions

### 1. Verify API is Running
```bash
curl https://your-site.com/wp-json/ica-lms/v1/info
```

### 2. Create Application Password
1. WordPress Admin → Users → Your Profile
2. Scroll to "Application Passwords"
3. Enter app name and click "Create Application Password"
4. Save the generated password

### 3. Make Your First Request
```bash
curl -u username:app_password \
  https://your-site.com/wp-json/ica-lms/v1/students
```

## 📖 Example Requests

### Get Students (Paginated)
```bash
curl -u username:app_password \
  "https://your-site.com/wp-json/ica-lms/v1/students?page=1&per_page=50"
```

### Create Student
```bash
curl -X POST -u username:app_password \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "mobile_no": "9876543210",
    "course_id": 5,
    "batch_id": 1,
    "fee_amount": 10000
  }' \
  https://your-site.com/wp-json/ica-lms/v1/students
```

### Get Student QR Code
```bash
curl -u username:app_password \
  https://your-site.com/wp-json/ica-lms/v1/qr-code/1
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
  https://your-site.com/wp-json/ica-lms/v1/payments/record
```

## 🧪 Testing Tools

### Postman
1. Import `postman_collection.json`
2. Set variables in Postman
3. Start making requests

### cURL
```bash
curl -u username:app_password \
  https://your-site.com/wp-json/ica-lms/v1/students
```

### JavaScript/Fetch
```javascript
const response = await fetch('https://your-site.com/wp-json/ica-lms/v1/students', {
  credentials: 'include'
});
const data = await response.json();
```

### Python
```python
import requests
from requests.auth import HTTPBasicAuth

response = requests.get(
  'https://your-site.com/wp-json/ica-lms/v1/students',
  auth=HTTPBasicAuth('username', 'app_password')
)
print(response.json())
```

## 📱 Use Cases

- **Mobile Apps** - Integrate LMS with your mobile application
- **Third-party Systems** - Sync student data with other systems
- **Custom Dashboards** - Build custom admin dashboards
- **Automation Tools** - Automate student creation, payments, etc.
- **Data Analysis** - Export data for analytics
- **External Portals** - Allow students to check progress via external portal

## ⚙️ Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "pagination": { ... }
}
```

### Error Response
```json
{
  "code": "error_code",
  "message": "Human-readable message",
  "data": { "status": 400 }
}
```

## 🔗 Endpoints by Category

| Category | Endpoints | Read | Write |
|----------|-----------|------|-------|
| Students | 5 | ✅ | ✅ |
| Batches | 5 | ✅ | ✅ |
| Courses | 2 | ✅ | ❌ |
| Categories | 1 | ✅ | ❌ |
| Documents | 2 | ✅ | ❌ |
| Payments | 2 | ✅ | ✅ |
| **Total** | **19** | | |

## 🔒 Security

- Requires WordPress authentication
- Uses nonce verification
- Respects user capabilities
- HTTPS recommended for production
- Application passwords support in WordPress 5.6+

## 🐛 Error Handling

All errors follow standard HTTP status codes:

- `200` OK - Request successful
- `400` Bad Request - Invalid input
- `401` Unauthorized - Auth failed
- `403` Forbidden - Permission denied
- `404` Not Found - Resource not found
- `500` Server Error - Internal error

## 📝 API Versioning

Current version: `1.0.0`

Namespace: `/wp-json/ica-lms/v1`

Future versions will be available at `/wp-json/ica-lms/v2`, etc.

## 🚀 Performance Tips

1. Use pagination for large datasets
2. Cache responses appropriately
3. Use filters to reduce data transfer
4. Batch multiple operations when possible
5. Delete unused application passwords

## 📞 Support

For issues or questions:
1. Check [API-DOCUMENTATION.md](./API-DOCUMENTATION.md) for endpoint details
2. Review [API-QUICKSTART.md](./API-QUICKSTART.md) for examples
3. Check WordPress error logs: `/wp-content/debug.log`

## 📄 Version History

### 1.0.0 (April 13, 2026)
- Initial API release
- 19 endpoints
- Full CRUD for students and batches
- Payment management
- ID card generation
- QR code integration

## 📦 Files Modified

1. `lms/includes/class-ica-lms-api.php` - NEW
2. `lms/bootstrap.php` - Updated to include API class
3. `lms/includes/class-ica-lms.php` - Updated to initialize API

## 🔗 Related Documentation

- [QR Code & ID Card Feature Guide](./QR-CODE-ID-CARD-FEATURE-GUIDE.md)
- [Batch Management Guide](./ARCHITECTURE-GUIDE.md)
- [Student Management](./FEATURE-ANALYSIS-REPORT.md)

---

**API Version**: 1.0.0  
**LMS Version**: 1.4.0+  
**Last Updated**: April 13, 2026  
**Documentation Status**: Complete
