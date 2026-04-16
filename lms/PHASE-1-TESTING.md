# Phase 1 Testing & Verification Guide

**Test Date:** April 7, 2026  
**Phase:** 1 - Student Registration Numbers  
**Status:** Ready for Testing

---

## Pre-Testing Checklist

Before running tests, verify:

- [ ] WordPress is running (XAMPP Apache started)
- [ ] Database permissions enabled
- [ ] Currently logged in as WordPress admin
- [ ] Theme "impulse-academy-clone" is active
- [ ] At least one course exists (create one if needed)

---

## Test 1: Basic Registration Number Generation

**Objective:** Verify that a student gets a unique registration number on enrollment

### Steps:

1. **Create a test user (or use existing user)**
   - Go to: `WordPress Admin → Users → Add New`
   - Username: `test_student_001`
   - Email: `test1@example.com`
   - Password: Generate random
   - Role: Subscriber
   - Save

2. **Navigate to course catalog**
   - Visit: `/lms-catalog/` (or `/courses/`)
   - Select a course or go to any course page

3. **Enroll in the course**
   - Click "Enroll Now" button
   - Confirm enrollment message appears

4. **Check Dashboard for Registration Number**
   - Log in as `test_student_001`
   - Go to: `/lms-dashboard/`
   - **Expected Result:** 
     - Registration # should display: `ICA-2026-00001`
     - Format: `ICA-YYYY-XXXXX`

5. **Verify in Database**
   - PHPMyAdmin → Select WordPress DB
   - Table: `wp_ica_lms_student_profiles`
   - Should see record with:
     - user_id: Matches test user ID
     - registration_number: ICA-2026-00001
     - student_status: active
     - created_at: Today's date

**Test Result:** ✅ PASS / ❌ FAIL

---

## Test 2: Sequential Registration Numbers

**Objective:** Verify that multiple students get unique, sequential registration numbers

### Steps:

1. **Create second test user**
   - Username: `test_student_002`
   - Email: `test2@example.com`

2. **Enroll in course**
   - Same course as Test 1
   - Confirm enrollment

3. **Check Dashboard**
   - **Expected Result:** Registration # shows `ICA-2026-00002`

4. **Create third test user with sequential number**
   - Username: `test_student_003`
   - Email: `test3@example.com`
   - Enroll in course
   - **Expected Result:** Registration # shows `ICA-2026-00003`

5. **Verify Uniqueness in Database**
   - Query: `SELECT user_id, registration_number FROM wp_ica_lms_student_profiles ORDER BY registration_number;`
   - **Expected Result:**
     ```
     user_id  registration_number
     123      ICA-2026-00001
     124      ICA-2026-00002
     125      ICA-2026-00003
     ```

**Test Result:** ✅ PASS / ❌ FAIL

---

## Test 3: Admin Profile Editing

**Objective:** Verify admin can view and edit student profiles

### Steps:

1. **Go to Users → Edit User Profile**
   - Edit: `test_student_001`
   - Scroll down in user edit page

2. **Verify Student Profile Section**
   - **Expected:** "LMS Student Profile" section appears
   - **Fields visible:**
     - Registration Number (read-only): `ICA-2026-00001`
     - Date of Birth: [Date picker]
     - Phone Number: [Text field]
     - Address: [Textarea]
     - Batch/Section: [Text field]
     - Student Status: [Dropdown]

3. **Edit Profile Data**
   - Date of Birth: `1995-06-15`
   - Phone Number: `+91-9876543210`
   - Address: `123 Main St, Mumbai`
   - Batch/Section: `Batch A - 2026`
   - Status: `active`
   - Click "Update Profile"

4. **Verify Changes**
   - Reload page
   - Check fields still contain entered data
   - Check database table updated

5. **Test Status Change**
   - Change status to: `graduated`
   - Save
   - Verify in database: `student_status = 'graduated'`

**Test Result:** ✅ PASS / ❌ FAIL

---

## Test 4: User Listing Column

**Objective:** Verify registration numbers appear in user list

### Steps:

1. **Navigate to Users List**
   - Go to: `WordPress Admin → Users`

2. **Check for Registration Column**
   - **Expected:** "Registration #" column present (after Name, Email columns)

3. **Verify Values**
   - test_student_001: `ICA-2026-00001`
   - test_student_002: `ICA-2026-00002`
   - test_student_003: `ICA-2026-00003`
   - Other users: `—` (if no registration)

4. **Sort by Registration Number** (if WordPress supports it)
   - Click "Registration #" column header
   - Should sort by registration number

**Test Result:** ✅ PASS / ❌ FAIL

---

## Test 5: Dashboard Display

**Objective:** Verify registration number displays correctly on student dashboard

### Steps:

1. **Log in as test_student_001**
   - Use credentials from earlier test

2. **Navigate to Dashboard**
   - Go to: `/lms-dashboard/`

3. **Check Header Display**
   - **Expected:**
     ```
     My Learning Dashboard
     Track your enrolled courses...
     
     [Styled info box]
     Registration #: ICA-2026-00001
     Student ID • Name: [Student Name]
     ```

4. **Test with Different Student**
   - Log out → Log in as test_student_002
   - Verify shows `ICA-2026-00002` instead

5. **Check Mobile View** (if possible)
   - Ensure registration number still displays properly on mobile screens

**Test Result:** ✅ PASS / ❌ FAIL

---

## Test 6: Multiple Courses Enrollment

**Objective:** Verify enrollment in multiple courses doesn't create duplicate registrations

### Steps:

1. **Create second course** (if needed)
   - Create a new course: "Advanced Python"
   - Mark as published

2. **Enroll test_student_001 in second course**
   - Go to course page
   - Click enroll
   - Confirm enrollment

3. **Check Registration Number**
   - Go to dashboard
   - Should still show: `ICA-2026-00001` (same number)
   - Both courses visible on dashboard

4. **Verify Database**
   - Query: `SELECT * FROM wp_ica_lms_student_profiles WHERE user_id = 123;`
   - **Expected:** Only ONE record (not duplicated)

5. **Check Enrollments**
   - Query: `SELECT * FROM wp_ica_lms_enrollments WHERE user_id = 123;`
   - **Expected:** TWO records (one per course)

**Test Result:** ✅ PASS / ❌ FAIL

---

## Test 7: Read-Only Registration Number

**Objective:** Verify registration number cannot be manually edited

### Steps:

1. **Go to User Edit Page**
   - Edit: `test_student_001`
   - Scroll to "LMS Student Profile"

2. **Try to Edit Registration Number**
   - Registration Number field shows: `ICA-2026-00001`
   - **Expected:** Field is DISABLED/READ-ONLY
   - Cannot type or change value
   - Background color should appear grayed out

3. **Attempt Direct Edit (Advanced)**
   - Open browser developer console (F12)
   - Try to modify field via JavaScript
   - Submit form
   - **Expected:** Value reverts (not saved in database)

**Test Result:** ✅ PASS / ❌ FAIL

---

## Test 8: Existing User Migration

**Objective:** Verify registration numbers auto-created for existing users on enrollment

### Steps:

1. **Find or create existing user WITHOUT registration**
   - Manual SQL: `DELETE FROM wp_ica_lms_student_profiles WHERE user_id = [ID];`
   - Or: Create a user that enrolled BEFORE this feature

2. **Enroll that user in a course**
   - Manually or via enrollment button
   - Trigger: `do_action('ica_lms_after_enroll')`

3. **Check Dashboard**
   - Log in as user
   - Go to `/lms-dashboard/`
   - **Expected:** Registration # newly generated (e.g., `ICA-2026-00004`)
   - Should be unique and sequential

4. **Verify Database**
   - New record created in `wp_ica_lms_student_profiles`
   - No duplicates

**Test Result:** ✅ PASS / ❌ FAIL

---

## Test 9: Emergency Test Cases

### Edge Case 1: Year Rollover (Simulated)
```sql
-- Test next year numbers (for 2027)
INSERT INTO wp_ica_lms_student_profiles (user_id, registration_number, student_status, created_at, updated_at)
VALUES (999, 'ICA-2027-00001', 'active', NOW(), NOW());

-- Verify 2027 numbers generate separately from 2026
```

### Edge Case 2: Concurrent Enrollments
- Have two users enroll simultaneously
- Verify both get unique registration numbers (no race condition)

### Edge Case 3: Database Constraint Test
```sql
-- Try to insert duplicate registration number
INSERT INTO wp_ica_lms_student_profiles (...) VALUES (..., 'ICA-2026-00001', ...);
-- Expected: MySQL Error (duplicate unique key) ✓
```

**Test Result:** ✅ PASS / ❌ FAIL

---

## Test 10: Performance Test

**Objective:** Verify system performs well with large number of students

### Steps (Optional - Advanced Testing):

1. **Create 100+ test users**
   - Use WordPress CLI or script
   - Enroll all in course
   - Monitor system performance
   - Should complete in <10 seconds

2. **Dashboard Load Time**
   - For student with registration number
   - Should load in <2 seconds
   - Monitor for database query delays

3. **Admin Users List**
   - Load /wp-admin/users.php
   - With 100+ users
   - Should load in <3 seconds

**Test Result:** ✅ PASS / ❌ FAIL

---

## Troubleshooting During Testing

### Issue: Registration number not showing on dashboard

**Possible Causes:**
1. User not enrolled (check enrollments table)
2. Student profile not created (check student_profiles table)
3. Template caching (clear browser cache: Ctrl+F5)
4. PHP error (check WordPress debug log)

**Solutions:**
```php
// Force profile creation
ICA_LMS_DB::create_student_profile($user_id);

// Check if profile exists
$profile = ICA_LMS_DB::get_student_profile($user_id);
if (empty($profile)) {
    echo "Profile missing!";
} else {
    echo "Registration: " . $profile['registration_number'];
}
```

### Issue: Duplicate registration numbers

**Possible Causes:**
1. Database constraint not applied
2. Manual insertion of duplicates
3. Race condition on concurrent enrollments

**Solutions:**
```sql
-- Check constraint exists
SHOW CREATE TABLE wp_ica_lms_student_profiles;
-- Should show: UNIQUE KEY reg_number (registration_number)

-- Find duplicates (should be none)
SELECT registration_number, COUNT(*) 
FROM wp_ica_lms_student_profiles 
GROUP BY registration_number HAVING COUNT(*) > 1;
```

### Issue: Admin profile section not showing

**Possible Causes:**
1. User not logged in as admin
2. ICA_LMS_Registration::init() not called
3. Capability check failing

**Solutions:**
```php
// Check if current user is admin
if (current_user_can('manage_options')) {
    echo "User is admin";
} else {
    echo "User is not admin";
}

// Verify class initialized
if (class_exists('ICA_LMS_Registration')) {
    echo "Class exists";
} else {
    echo "Class not found - check bootstrap";
}
```

---

## Test Summary Report Template

```
DATE: _____________
TESTER: _____________
BUILD: Phase 1 Implementation
WORDPRESS VERSION: _____________
PHP VERSION: _____________
MYSQL VERSION: _____________

TEST RESULTS:
[ ] Test 1: Basic Registration - ✅PASS / ❌FAIL
[ ] Test 2: Sequential Numbers - ✅PASS / ❌FAIL
[ ] Test 3: Admin Profile Edit - ✅PASS / ❌FAIL
[ ] Test 4: User List Column - ✅PASS / ❌FAIL
[ ] Test 5: Dashboard Display - ✅PASS / ❌FAIL
[ ] Test 6: Multiple Enrollments - ✅PASS / ❌FAIL
[ ] Test 7: Read-Only Field - ✅PASS / ❌FAIL
[ ] Test 8: Existing User Migration - ✅PASS / ❌FAIL
[ ] Test 9: Edge Cases - ✅PASS / ❌FAIL
[ ] Test 10: Performance - ✅PASS / ❌FAIL

OVERALL RESULT: ✅ READY / ⚠️ NEEDS FIXES / ❌ BLOCKING

ISSUES FOUND:
1. _________________________________________
2. _________________________________________
3. _________________________________________

NOTES:
_________________________________________
_________________________________________

SIGNED: _________________ DATE: _________
```

---

## Next Steps After Testing

### If All Tests Pass ✅
- Proceed to Phase 2: Birthday Reminders
- Update CEO-STATUS.md
- Documentation is complete

### If Some Tests Fail ⚠️
- Document the failures
- Review code changes
- Fix issues
- Re-test affected areas

### If Critical Issues Found ❌
- Do NOT proceed to next phase
- Rollback if in production
- Review implementation
- Fix and re-test

---

**Testing Complete!**

All tests documented. Ready for implementation validation.

