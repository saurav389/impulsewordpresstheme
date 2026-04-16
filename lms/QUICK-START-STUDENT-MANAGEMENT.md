# Phase 1: Student Management System - Quick Start Guide

**Status:** ✅ Ready to Use  
**Date:** April 10, 2026

---

## 🚀 Quick Access

**Go to:** WordPress Admin → Settings → LMS Students

---

## 📋 Student Fields Overview

| Field | Type | Required | Auto-Generated | Notes |
|-------|------|----------|-----------------|-------|
| RegNO | Text | No | ✅ Yes | Format: ICAL-YYYY-XXXXX |
| Course | Dropdown | ✅ Yes | No | Search for course |
| Roll No | Text | No | ✅ Yes | Format: Batch-1-XXXX |
| Name | Text | ✅ Yes | No | Full name of student |
| Father Name | Text | No | No | Father's full name |
| Mother Name | Text | No | No | Mother's full name |
| Date of Birth | Date | No | No | Student's DOB |
| Gender | Select | No | No | Male, Female, Other |
| Category | Dropdown | No | No | General, OBC, SC, ST + more |
| Qualification | Text | No | No | Highest qualification |
| Mobile No | Phone | ✅ Yes | No | Contact number |
| Aadhar No | Text | No | No | 12-digit Aadhar |
| Address | Textarea | No | No | Complete address |
| Photo | File | No | No | Student's profile photo |
| Signature | File | No | No | Digital signature |
| Aadhar Photo | File | No | No | Aadhar copy |
| Qualification Cert | File | No | No | Degree/Mark sheet |
| Fee Amount | Number | ✅ Yes | No | Enrollment fee |
| Fee Type | Select | No | No | One-time or Installment |

---

## 🎯 Common Tasks

### ✅ Add a New Student

**Step 1:** Click "Add New Student" button  
**Step 2:** Fill in the form
```
REQUIRED:
□ Course (select from dropdown)
□ Name (student's full name)
□ Mobile No (contact number)

RECOMMENDED:
□ Father Name
□ Mother Name
□ Aadhar No
□ Mobile (should match mobile_no)
```

**Step 3:** Upload Documents (Optional but recommended)
```
□ Student Photo (JPG, PNG)
□ Student Signature (JPG, PNG)
□ Aadhar Photo (JPG, PNG)
□ Qualification Certificate (PDF, DOC, JPG)
```

**Step 4:** Set Fee Details
```
□ Course Fee Amount (required)
□ Fee Payment Type (select One-time or Installment)
```

**Step 5:** Click "Add Student"

**Result:** 
- ✅ Student record created
- ✅ RegNO auto-generated (e.g., ICAL-2026-00001)
- ✅ RollNo auto-generated (e.g., Batch-1-0001)

---

### ✏️ Edit a Student

**Step 1:** Find student in the list  
**Step 2:** Click "Edit" button on that row  
**Step 3:** Modify any fields
```
CAN EDIT:
✅ Name, Father Name, Mother Name
✅ Date of Birth, Gender
✅ Category, Qualification
✅ Mobile No, Address
✅ Fee Amount & Type
✅ Fee Status (Pending/Submitted/Approved)
✅ Student Status (Active/Inactive)

CANNOT EDIT:
❌ RegNo (auto-generated)
❌ Roll No (auto-generated)
❌ Course (admission course)
```

**Step 4:** Click "Update Student"

---

### 🗑️ Delete a Student

**Step 1:** Find student in list  
**Step 2:** Click "Delete" button  
**Step 3:** Confirm deletion  
**Result:** Student record removed  

---

### 🔍 Search & Filter Students

**Search by (any of these):**
```
□ Student Name: "Raj Kumar"
□ Registration No: "ICAL-2026-00001"
□ Mobile Number: "9876543210"
□ Aadhar Number: "1234"
```

**Filter by:**
```
□ Course: Select course from dropdown
```

**To Reset:**
Click "Clear" button to remove filters

---

### ➕ Add New Category

**While adding/editing student:**

1. Click **"+" button** next to Category dropdown
2. A modal popup appears
3. Enter:
   - Category Name (required)
   - Description (optional)
4. Click "Add Category"
5. New category appears in dropdown
6. Select it immediately

**Built-in Categories:**
- General
- OBC (Other Backward Class)
- SC (Scheduled Caste)
- ST (Scheduled Tribe)

---

## 📊 Student List View

**Columns Displayed:**
```
RegNo        → Registration Number
Name         → Student's Full Name
Course       → Course Name
Roll No      → Auto-generated Roll Number
Mobile       → Contact Number
Aadhar       → Last 4 digits of Aadhar
Status       → Active/Inactive (badge)
Actions      → Edit, Delete buttons
```

**Pagination:**
- 20 students per page
- Navigate using page numbers at bottom

**Total Count:**
Shows total number of students

---

## 💡 Tips & Tricks

### Tip 1: Auto-Generated Values
When you add a student:
- RegNO: Automatically created in format ICAL-YYYY-XXXXX
- RollNO: Automatically created based on course and batch
- You cannot edit these values

### Tip 2: Using Categories
- Categories help classify students (SC, ST, OBC, etc.)
- Click "+" to add new category on-the-fly
- Categories appear in dropdown for all future selections

### Tip 3: Fee Management
- Set fee amount during admission
- Later change fee status to: Pending → Submitted → Approved
- Supports both one-time and installment options

### Tip 4: File Uploads
- Keep file sizes under 25MB
- Recommended formats:
  - Photos: JPG, PNG, WebP
  - Certificates: PDF, DOC, DOCX
- URLs are stored for later access

### Tip 5: Searching
- Search works across multiple fields simultaneously
- Partial matches work (e.g., "Raj" finds "Raj Kumar")
- Combine search + filter for precise results

---

## ⚠️ Important Notes

1. **Mobile Number & Aadhar**: Must be unique (duplicate entries not allowed after 2nd entry)
2. **Course**: Cannot be changed after admission (select carefully)
3. **Registration Number**: Is unique per student and cannot be modified
4. **File Uploads**: Only admin can upload; files stored in WordPress uploads
5. **Status**: Can change between Active/Inactive on edit
6. **Fee Status**: Track admission fee collection (Pending → Submitted → Approved)

---

## 🔐 Security

- All data is password-protected
- Only administrators can access student management
- Mobile numbers and Aadhar numbers are masked in lists
- All file uploads are validated

---

## 📞 Support

For issues or questions:
1. Check if all required fields (*) are filled
2. Verify file upload sizes
3. Ensure course is selected
4. Check for duplicate mobile/aadhar numbers

---

## 📈 Next Features Coming in Phase 2+

- Student Portal (view own profile)
- Fee Payment Gateway
- Admission Confirmation
- Certificate Generation
- Exam Management
- Attendance Tracking
- Result Management

---

**Need help?** All fields have clear labels and validation. Required fields are marked with *.

**Update Date:** April 10, 2026  
**System Version:** LMS 3.0.0
