# Teacher Panel - How Students Are Displayed Explanation

## Overview
Students displayed in the teacher panel's "View Students" section are **filtered based on the teacher's own courses**. Only students enrolled in courses created by that teacher are visible.

## How It Works - The Logic Flow

### Step-by-Step Process:

```
TEACHER VIEWS "View Students" PAGE
    ↓
1. SYSTEM IDENTIFIES CURRENT TEACHER
   - Gets WordPress user ID of logged-in user
   - Checks if user has 'edit_posts' capability (teacher role)
   
    ↓
2. GET TEACHER'S COURSES
   - Query WordPress posts table
   - Filter: post_type = 'courses' 
   - Filter: post_author = current_teacher_id
   - Result: List of course IDs teacher has created
   
    ↓
3. GET STUDENTS IN THOSE COURSES
   - Query custom LMS students table
   - Filter: course_id IN (teacher's course IDs)
   - Result: All students enrolled in teacher's courses
   
    ↓
4. DISPLAY FILTERED STUDENTS
   - Show student name, registration number, course, etc.
   - Format: Table with student details
   - Sort: By student name (alphabetically)
```

## Code Implementation

### The Query Logic (from `class-ica-lms-admin-student.php` lines 1860-1880):

```php
// Step 1: Check if user is teacher/admin
$current_user_id = get_current_user_id();
$is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, $user->roles);
$is_admin = current_user_can('manage_options');

// Step 2: Get teacher's courses (WordPress posts)
$teacher_courses = get_posts(array(
    'post_type' => 'courses',                               // Only courses
    'author' => $is_admin ? 0 : $current_user_id,          // Teacher's own posts
    'posts_per_page' => -1,                                 // All courses
));

// Extract course IDs from the results
$teacher_course_ids = wp_list_pluck($teacher_courses, 'ID');
// Result: [123, 456, 789] ← course IDs

// Step 3: Get students from those courses
global $wpdb;
$db_table = ICA_LMS_DB::table_students();  // wp_ica_lms_students

$query = $wpdb->prepare(
    "SELECT * FROM $db_table WHERE course_id IN ($placeholders) ORDER BY name ASC",
    $teacher_course_ids                     // Pass course IDs
);

$students = $wpdb->get_results($query, ARRAY_A);
```

## Visual Relationship Diagram

```
WORDPRESS DATABASE
================================================================================

Posts Table (wp_posts)
┌────────────────────────────────────────────────────────────────────────────┐
│ ID   │ post_title          │ post_type  │ post_author  │ post_status      │
├────────────────────────────────────────────────────────────────────────────┤
│ 123  │ C++ Programming     │ courses    │ 5 (Teacher1) │ publish          │
│ 456  │ Web Development     │ courses    │ 5 (Teacher1) │ publish          │
│ 789  │ Data Science        │ courses    │ 6 (Teacher2) │ publish          │
│ 1000 │ Machine Learning    │ courses    │ 6 (Teacher2) │ publish          │
└────────────────────────────────────────────────────────────────────────────┘
           ▲                                      ▲
           │ (Teachers only see their own)       │
           │                                     │
    ┌──────┴──────────────────────┐             │
    │                              │             │
    │ TEACHER 1 (ID: 5)           │      TEACHER 2 (ID: 6)
    │ Can see courses:            │      Can see courses:
    │ ✓ 123 ✓ 456                │      ✓ 789 ✓ 1000
    │                              │
    └──────────────────────────────┘


Students Table (wp_ica_lms_students)
┌────────────────────────────────────────────────────────────────────────────┐
│ ID │ reg_no │ name        │ course_id │ batch_id │ fee_status │ status    │
├────────────────────────────────────────────────────────────────────────────┤
│ 1  │ STU001 │ Anil Kumar  │ 123       │ NULL     │ approved   │ active    │
│ 2  │ STU002 │ Bhavna Singh│ 123       │ NULL     │ pending    │ active    │
│ 3  │ STU003 │ Chirag Patel│ 456       │ 1        │ approved   │ active    │
│ 4  │ STU004 │ Diya Gupta  │ 789       │ 2        │ approved   │ active    │
│ 5  │ STU005 │ Ehr Sharma  │ 789       │ 2        │ pending    │ active    │
└────────────────────────────────────────────────────────────────────────────┘
        │                               │
        │ course_id IN (123, 456)       │ course_id IN (789, 1000)
        │                               │
    ┌───┴──────────────────────┐   ┌────┴─────────────────────┐
    │   TEACHER 1 SEES:        │   │  TEACHER 2 SEES:        │
    │ ✓ STU001 (course 123)    │   │  ✓ STU004 (course 789)  │
    │ ✓ STU002 (course 123)    │   │  ✓ STU005 (course 789)  │
    │ ✓ STU003 (course 456)    │   │                         │
    │                          │   │  (Does NOT see STU001,  │
    │ (Does NOT see STU004,    │   │   STU002, STU003)       │
    │  STU005)                 │   │                         │
    └──────────────────────────┘   └────────────────────┬────┘
```

## Example Scenario

### Setup:
- **Teacher A** creates: "Python Course" (ID: 123), "Java Course" (ID: 456)
- **Teacher B** creates: "JavaScript Course" (ID: 789)

### Students:
- **Student 1** enrolled in Python (123)
- **Student 2** enrolled in Python (123)
- **Student 3** enrolled in Java (456)
- **Student 4** enrolled in JavaScript (789)

### When Teacher A Views "View Students":
1. System finds courses by Teacher A → [123, 456]
2. System queries: SELECT * FROM students WHERE course_id IN (123, 456)
3. **Results**: Student 1, Student 2, Student 3
4. **NOT shown**: Student 4 (belongs to JavaScript course created by Teacher B)

### When Teacher B Views "View Students":
1. System finds courses by Teacher B → [789]
2. System queries: SELECT * FROM students WHERE course_id IN (789)
3. **Results**: Student 4
4. **NOT shown**: Student 1, 2, 3 (belong to courses created by Teacher A)

## File Location in Code

**File**: [lms/includes/class-ica-lms-admin-student.php](../includes/class-ica-lms-admin-student.php#L1830)

**Function**: `render_teacher_students_page()` (Lines 1830-1920)

**Key Query Logic** (Lines 1860-1880):
```php
// Get teacher's courses
$teacher_courses = get_posts(array(
    'post_type' => 'courses',
    'author' => $is_admin ? 0 : $current_user_id,
    'posts_per_page' => -1,
));
$teacher_course_ids = wp_list_pluck($teacher_courses, 'ID');

// Get students from those courses
$query = $wpdb->prepare(
    "SELECT * FROM $db_table WHERE course_id IN ($placeholders) ORDER BY name ASC",
    $teacher_course_ids
);
$students = $wpdb->get_results($query, ARRAY_A);
```

## Database Tables Involved

### 1. **wp_posts** (WordPress Core)
Stores courses created by teachers
```sql
- post_type = 'courses'
- post_author = teacher's WordPress user ID
- ID = course identifier
```

### 2. **wp_ica_lms_students** (Custom LMS Table)
Stores student enrollment records
```sql
- course_id = which course student is enrolled in
- reg_no = student registration number
- name = student name
- fee_status = payment status
- status = active/inactive
```

## Special Cases

### Admin vs Teacher:

**ADMIN**:
- Can see ALL students from ALL courses
- Query: `WHERE author = 0` (shows all courses)
- Access all students

**TEACHER**:
- Can see only students from their own courses
- Query: `WHERE author = current_teacher_id`
- Isolated view of their students

### Student with Student Role:
- **BLOCKED** from accessing "View Students" page
- Error message: "You are not allowed to perform this action"

## Features Displayed

When students are shown, the table displays:
- **RegNo** - Registration number (STU001, STU002)
- **Name** - Student full name
- **Course** - Course enrolled in
- **Roll No** - Course-specific roll number
- **Mobile** - Contact number
- **Fee Amount** - Total fees charged
- **Payment Status** - pending/submitted/approved
- **Status** - active/inactive

## Security Logic

```php
// Only teachers and admins can access View Students
if (!current_user_can('edit_posts')) {
    wp_die('Unauthorized');
}

// Even teachers can't see OTHER teachers' students
// Only admin can see all students
if ($is_teacher && !$is_admin) {
    $teacher_courses = get_posts([
        'author' => $current_user_id  // ← Only THIS teacher's courses
    ]);
}
```

## Summary

| Aspect | Details |
|--------|---------|
| **Who can view** | Teachers (edit_posts cap) + Admins |
| **What they see** | Students in courses THEY created |
| **Filtering method** | Course author → course ID → students in those courses |
| **Query type** | WordPress post query + Custom DB query |
| **Special access** | Admins see ALL students; Teachers see ONLY their own |
| **Performance** | Optimized with SQL IN clause + indexes |

---

**Last Updated**: April 18, 2026
