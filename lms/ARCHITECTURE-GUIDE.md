# LMS System Architecture & Implementation Gaps

**Document:** System Architecture Overview  
**Date:** April 7, 2026  
**Version:** 1.0

---

## CURRENT ARCHITECTURE (v3.1 - Implemented)

```
┌─────────────────────────────────────────────────────────────────┐
│                      IMPULSE LMS v3.1                          │
│                   (40% Complete - Production)                  │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────────────┐
│   WORDPRESS FOUNDATION       │
│  (User/Post/Meta System)     │
└──────────────────────────────┘
           ▲
           │
┌──────────┴────────────────────────────────────────────────────────┐
│                    LMS MODULE (/lms/)                            │
│                                                                    │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │              CORE CLASSES (10 implemented)                 │ │
│  │                                                             │ │
│  │  ICA_LMS (Orchestrator)                                    │ │
│  │    ├── ICA_LMS_CPTs (courses, lessons, quizzes)          │ │
│  │    ├── ICA_LMS_DB (6 tables: enrollments, progress...)   │ │
│  │    ├── ICA_LMS_Student (enrollment, lesson, quiz logic) │ │
│  │    ├── ICA_LMS_Pages (dashboard, catalog views)         │ │
│  │    ├── ICA_LMS_Certificates (issue & verify certs)      │ │
│  │    ├── ICA_LMS_Automation (cron: at-risk reminders)     │ │
│  │    ├── ICA_LMS_Admin (KPI dashboard, payments)          │ │
│  │    ├── ICA_LMS_Instructor (instructor dashboard)        │ │
│  │    └── ICA_LMS_Utils (quiz parsing, helpers)            │ │
│  │                                                             │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                    │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │          FRONTEND FEATURES (Implemented)                   │ │
│  │                                                             │ │
│  │  ✅ Student Dashboard (/lms-dashboard/)                    │ │
│  │     • Enrolled courses                                      │ │
│  │     • Progress bars                                         │ │
│  │     • Certificate download                                 │ │
│  │                                                             │ │
│  │  ✅ Course Catalog (/lms-catalog/)                         │ │
│  │     • Browse courses                                        │ │
│  │     • Free/paid enrollment                                 │ │
│  │                                                             │ │
│  │  ✅ Lesson Player                                          │ │
│  │     • Mark complete                                         │ │
│  │     • Prerequisite checking                                │ │
│  │                                                             │ │
│  │  ✅ Quiz Engine                                            │ │
│  │     • Multiple choice questions                            │ │
│  │     • Randomization option                                 │ │
│  │     • Instant scoring                                      │ │
│  │                                                             │ │
│  │  ✅ Certificate Verification (/certificate-verification/) │ │
│  │     • Public lookup by code                                │ │
│  │                                                             │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                    │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │          ADMIN FEATURES (Implemented)                      │ │
│  │                                                             │ │
│  │  ✅ KPI Dashboard                                          │ │
│  │     • Total enrollments                                     │ │
│  │     • Active learners                                       │ │
│  │     • Avg quiz score                                        │ │
│  │                                                             │ │
│  │  ✅ At-Risk Learner Automation                             │ │
│  │     • Daily cron detection (7+ days, <20% progress)       │ │
│  │     • Email reminders sent                                 │ │
│  │                                                             │ │
│  │  ✅ Payment Queue                                          │ │
│  │     • Manual "Mark Paid & Enroll" action                  │ │
│  │                                                             │ │
│  │  ✅ CSV Exports                                            │ │
│  │     • Enrollments export                                    │ │
│  │     • Progress export                                       │ │
│  │                                                             │ │
│  │  ✅ Instructor Dashboard                                   │ │
│  │     • Per-course analytics                                 │ │
│  │     • Student pass rates                                    │ │
│  │                                                             │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                    │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │         DATABASE SCHEMA (6 tables, v1.3.0)                 │ │
│  │                                                             │ │
│  │  ✅ wp_ica_lms_enrollments                                │ │
│  │     (user_id, course_id, enrolled_at, status)             │ │
│  │                                                             │ │
│  │  ✅ wp_ica_lms_progress                                   │ │
│  │     (user_id, course_id, lesson_id, completed_at)         │ │
│  │                                                             │ │
│  │  ✅ wp_ica_lms_quiz_attempts                              │ │
│  │     (user_id, course_id, quiz_id, score, passed)          │ │
│  │                                                             │ │
│  │  ✅ wp_ica_lms_certificates                               │ │
│  │     (user_id, course_id, certificate_code, issued_at)     │ │
│  │                                                             │ │
│  │  ✅ wp_ica_lms_notifications                              │ │
│  │     (user_id, course_id, type, channel, status)           │ │
│  │                                                             │ │
│  │  ✅ wp_ica_lms_transactions                               │ │
│  │     (user_id, course_id, amount, currency, status)        │ │
│  │                                                             │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘

```

---

## MISSING ARCHITECTURE (What Needs to be Built)

```
┌─────────────────────────────────────────────────────────────────┐
│        MISSING FEATURES (60% to Complete)                       │
└─────────────────────────────────────────────────────────────────┘

STUDENT IDENTITY LAYER (❌ Not Implemented)
┌──────────────────────────────────────────────────────┐
│  ICA_LMS_Registration                               │ NEW
│  ├── Auto-generate registration numbers             │
│  ├── Format: ICA-2026-XXXXX                         │
│  └── Store in wp_ica_lms_student_profiles           │ NEW TABLE
│                                                      │
│  ICA_LMS_Student_Profiles                          │ NEW
│  ├── DOB, Phone, Address, Batch                    │
│  ├── Student metadata storage                       │
│  └── Extend user dashboard display                  │
│                                                      │
│  ICA_LMS_Birthdays                                 │ NEW
│  ├── Birthday detection cron                        │
│  ├── Automated email notifications                  │
│  └── Birthday log tracking                          │
└──────────────────────────────────────────────────────┘

FEE MANAGEMENT LAYER (⚠️ Partially Implemented - 20%)
┌──────────────────────────────────────────────────────┐
│  ICA_LMS_Fees                                       │ NEW
│  ├── Course fee structure config                    │
│  ├── One-time vs installment options                │
│  └── Fee schedule builder (wp_ica_lms_fee_...)      │ NEW TABLE
│                                                      │
│  ICA_LMS_Installments                               │ NEW
│  ├── Installment breakdown (e.g., 3 EMI)           │
│  ├── Due date calculation                           │
│  └── Installment status tracking                    │
│  └── Partial payment logic                          │
│                                                      │
│  ICA_LMS_Fee_Automation                             │ NEW
│  ├── Overdue fee detection cron                     │
│  ├── Upcoming installment reminders                 │
│  └── Fee status notifications                       │
│                                                      │
│  ✅ wp_ica_lms_transactions (EXTEND)                │
│     Add: payment_type, installment_#, due_date      │
│                                                      │
│  NEW: wp_ica_lms_fee_schedules                      │ NEW TABLE
│     (course_id, type, installment_count, interval)  │
└──────────────────────────────────────────────────────┘

EXAM MANAGEMENT LAYER (⚠️ Partially Implemented - 50%)
┌──────────────────────────────────────────────────────┐
│  ICA_LMS_Exams                                      │ NEW
│  ├── Exam vs Quiz distinction                       │
│  ├── Online exam execution                          │
│  ├── Offline exam scheduling                        │
│  └── Exam result workflow                           │
│                                                      │
│  ICA_LMS_ExamCenters                                │ NEW
│  ├── Venue/center management                        │
│  ├── Proctor assignment                             │
│  └── Seating arrangement                            │
│                                                      │
│  ICA_LMS_OfflineResults                             │ NEW
│  ├── Offline result upload form                     │
│  ├── File validation & scanning                     │
│  ├── Approval workflow (pending → approved)         │
│  └── Scoring & certificate eligibility              │
│                                                      │
│  ICA_LMS_MockTests                                  │ NEW
│  ├── Mock test designation flag                     │
│  ├── Separate analytics tracking                    │
│  ├── Performance comparison tools                   │
│  └── Improvement suggestions                        │
│                                                      │
│  ✅ wp_ica_lms_quiz_attempts (EXTEND)               │
│     Add: exam_type, is_mock, offline_result_id      │
│                                                      │
│  NEW: wp_ica_lms_exam_sessions                      │ NEW TABLE
│  NEW: wp_ica_lms_exam_centers                       │ NEW TABLE
│  NEW: wp_ica_lms_offline_results                    │ NEW TABLE
└──────────────────────────────────────────────────────┘

```

---

## DATA FLOW MAPS

### Current Flow: Student Enrollment (Implemented)
```
┌────────────┐
│   Student  │
└─────┬──────┘
      │
      │ 1. Browse courses
      ▼
┌────────────────────┐
│  Course Catalog    │
│ (/lms-catalog/)    │
└─────┬──────────────┘
      │
      │ 2. Click Enroll
      ▼
┌──────────────────────────┐
│ Check: Is Paid?          │
└─────┬──────────┬─────────┘
  YES │          │ NO
      │          │
      ▼          ▼
   ┌──────────────────────────┐
   │ Create transaction        │
   │ (pending status)          │
   │ Shows: "Awaiting approval"│
   └─────┬────────────────────┘
         │
         │ 3. Admin approval
         ▼
      ┌────────────────┐
      │ Mark Paid      │
      │ & Enroll       │
      └─────┬──────────┘
            │
            ▼
      ┌──────────────────────────┐
      │ Create enrollment record  │
      │ wp_ica_lms_enrollments    │
      └─────┬────────────────────┘
            │
            │ 4. Access courses
            ▼
      ┌──────────────────┐
      │ Student Dashboard│
      │ Shows: courses   │
      └────────┬─────────┘
```

### Missing Flow: Student Registration Number Generation
```
┌────────────┐
│ User       │
│ Created    │
└─────┬──────┘
      │
      │ WordPress user_id generated
      ▼
┌────────────────────────────────────────┐
│ ICA_LMS_Registration::assign_number()   │ ← NEW CLASS
└─────┬────────────────────────────────┬──┘
      │                                │
      │ 1. Generate unique number      │
      │    Format: ICA-2026-00001      │
      │                                │
      ▼                                │
┌────────────────────────────────┐    │
│ Check uniqueness via DB         │    │
│ (wp_ica_lms_student_profiles)   │    │
└─────┬──────────────────────────┘    │
      │                                │
      │ Unique? YES                    │
      ▼                                │
┌────────────────────────────────┐    │
│ Store registration number       │    │
│ wp_ica_lms_student_profiles     │    │ ← NEW TABLE
│ (user_id, reg_number, DoB...)   │    │
└─────┬──────────────────────────┘    │
      │                                │
      │ Enrollment          Assign     │
      ├────────────────────────────────┼────→ Dashboard
      │                                │
      ▼                                ▼
┌──────────────────────────────────────┐
│ Student dashboard displays:          │
│ • Registration: ICA-2026-00001       │
│ • Name, DOB, Batch, Email            │
└──────────────────────────────────────┘
```

### Missing Flow: Fee Installment Payment
```
┌────────────┐
│   Student  │
│ Enrolls    │
└─────┬──────┘
      │
      │ Paid course?
      ▼
┌──────────────────────────────┐
│ Check Fee Schedule            │
│ (wp_ica_lms_fee_schedules)    │ ← NEW TABLE
│ Payment type?                 │
└─────┬──────┬──────────────────┘
  ONE │      │ INSTALLMENT
   TIME     4 EMI
      │      │
      ▼      ▼
   ┌──────┐ ┌──────────────────────────┐
   │ 1 txn│ │ 4 transactions created   │
   │Pend. │ │ Due: Imm, +30d, +60d... │
   └──┬───┘ │ Each in notifications    │
      │     │ queue (reminders)        │
      │     └──┬───────────────────────┘
      │        │
      │        │ ICA_LMS_Fee_Automation
      │        │ Daily cron checks:
      │        │ • Due date < today?
      │        │   → Send reminder
      │        │ • All paid?
      │        │   → Auto-enroll
      │        │
      └────┬───┴────────────────────────┐
           │   Admin marks paid          │
           │   (all or individual)       │
           ▼                             ▼
      ┌─────────────────────────────────────┐
      │ Create enrollment record            │
      │ wp_ica_lms_enrollments              │
      │ (when last installment paid)        │
      └──────────────┬──────────────────────┘
                     │
                     ▼
              ┌────────────────┐
              │ Student access │
              │ lessons/quizzes│
              └────────────────┘
```

### Missing Flow: Offline Exam Result Submission
```
┌──────────┐
│   Exam   │
│Scheduled │
│ Offline  │
└────┬─────┘
     │
     │ ICA_LMS_Exams::create_offline_exam()  ← NEW CLASS
     ▼
┌──────────────────────────────────┐
│ Student sees:                    │
│ "Offline Exam"                   │
│ "Awaiting Result Upload"         │
│ Button: "Upload Result" DISABLED │
└────┬─────────────────────────────┘
     │
     │ Exam center admin
     │ uploads result file
     ▼
┌───────────────────────────────────────┐
│ ICA_LMS_OfflineResults::upload()        │ ← NEW CLASS
│                                         │
│ 1. File upload                          │
│ 2. Antivirus scan (ClamAV)             │
│ 3. Store in wp_ica_lms_offline_results │ ← NEW TABLE
│ 4. Status: pending                     │
└────┬────────────────────────────────────┘
     │
     │ Admin approves
     ▼
┌──────────────────────────────┐
│ Mark approved                │
│ Enter score/result           │
│ Populate quiz_attempts       │
│ Status: approved + scored    │
└────┬─────────────────────────┘
     │
     │ Check if passed
     ▼
┌──────────────────────┐
│ Passed? YES          │
│ → Issue certificate  │
│                      │
│ Passed? NO           │
│ → Show failed status │
└──────────────────────┘
```

### Missing Flow: Birthday Reminder Automation
```
┌─────────────────────────────────┐
│ Daily Cron (00:05 AM UTC)       │
│ ica_lms_daily_birthday_check    │ ← NEW CRON
└────┬────────────────────────────┘
     │
     │ ICA_LMS_Birthdays::send_birthday_reminders()
     │ (Added to ICA_LMS_Automation)
     │
     ▼
┌──────────────────────────────────────────────────┐
│ 1. Query students with birthday today            │
│    SELECT FROM wp_ica_lms_student_profiles       │ ← NEW TABLE
│    WHERE DOB = TODAY()                           │
└────┬─────────────────────────────────────────────┘
     │
     │ For each student:
     ▼
┌──────────────────────────────────┐
│ 2. Check if already reminded     │
│    (24-hour dedup logic)         │
└────┬─────────────────────────────┘
     │
     ├─ Already sent? SKIP
     │
     └─ Not sent yet? CONTINUE
            │
            ▼
     ┌────────────────────────────┐
     │ 3. Compose email           │
     │    Subject: Happy Birthday,│
     │            {name}!         │
     │                            │
     │    Body: Personalized      │
     │    greeting + dashboard    │
     │    link                    │
     └────┬──────────────────────┘
          │
          │ wp_mail() sends via SMTP
          ▼
     ┌────────────────────────────┐
     │ 4. Log notification        │
     │    wp_ica_lms_notifications│
     │    type: birthday_reminder │
     │    status: sent/failed     │
     │    error_message (if any)  │
     └────────────────────────────┘
          │
          ├─→ Sent? Log success
          │
          └─→ Failed? Log error
                     (admin can retry)
```

---

## IMPLEMENTATION DEPENDENCIES

### Dependency Tree (Phases must complete in order)

```
PHASE 1: Registration Numbers (Week 1, Day 1-2)
│
├─ Prerequisite: None (standalone)
├─ Creates: wp_ica_lms_student_profiles table
├─ Adds: ICA_LMS_Registration class
└─ Impact: Student dashboard, admin reports

    ↓ (can run parallel with Phase 2)

PHASE 2: Birthday Reminders (Week 1, Day 2-3)
│
├─ Prerequisite: Phase 1 (DOB stored in student profiles)
├─ Extends: ICA_LMS_Automation class
├─ Extends: wp_ica_lms_notifications table
└─ Impact: Daily automation, email traffic

    ↓ (hard dependency)

PHASE 3: Fee Management (Week 2, Day 4-6)
│
├─ Prerequisite: None (extends transactions)
├─ Creates: wp_ica_lms_fee_schedules table
├─ Extends: wp_ica_lms_transactions table
├─ Adds: ICA_LMS_Fees, ICA_LMS_Installments classes
└─ Impact: Enrollment flow, payment workflow

    ↓ (hard dependency)

PHASE 4: Fee Reminders (Week 2, Day 7-8)
│
├─ Prerequisite: Phase 3 (fee data required)
├─ Adds: ICA_LMS_Fee_Automation class
├─ Extends: ICA_LMS_Automation class
├─ Extends: wp_ica_lms_notifications table
└─ Impact: Daily automation, email traffic

    ↓ (can run parallel with Phase 5)

PHASE 5: Exam Management (Week 3, Day 9-15)
│
├─ Prerequisite: None (extends quiz system)
├─ Creates: wp_ica_lms_exam_sessions table
├─ Creates: wp_ica_lms_exam_centers table
├─ Creates: wp_ica_lms_offline_results table
├─ Adds: ICA_LMS_Exams, ICA_LMS_ExamCenters, ICA_LMS_OfflineResults
├─ Modifies: ICA_LMS_CPTs (new meta boxes)
└─ Impact: Major enrollment/exam flow changes

    ↓ (soft dependency on Phase 5)

PHASE 6: Mock Tests (Week 4, Day 16-19)
│
├─ Prerequisite: Phase 5 (exam differentiation)
├─ Adds: ICA_LMS_MockTests class
├─ Extends: wp_ica_lms_quiz_attempts table
└─ Impact: Dashboard display, analytics

```

---

## SCHEMA EXTENSION SUMMARY

### Tables to CREATE (New)
```sql
/* Student profile & registration */
CREATE TABLE wp_ica_lms_student_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    registration_number VARCHAR(50) UNIQUE NOT NULL,
    date_of_birth DATE,
    phone_number VARCHAR(20),
    address TEXT,
    batch_name VARCHAR(100),
    enrollment_date DATETIME,
    student_status VARCHAR(20) DEFAULT 'active'
);

/* Fee schedule configuration */
CREATE TABLE wp_ica_lms_fee_schedules (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    course_id BIGINT NOT NULL,
    payment_type ENUM('one_time', 'installment'),
    installment_count INT DEFAULT 1,
    installment_interval_days INT,
    created_at DATETIME
);

/* Exam sessions & scheduling */
CREATE TABLE wp_ica_lms_exam_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    exam_id BIGINT NOT NULL,
    scheduled_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    exam_center_id BIGINT,
    created_at DATETIME
);

/* Physical exam centers */
CREATE TABLE wp_ica_lms_exam_centers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    capacity INT,
    city VARCHAR(100),
    invigilators LONGTEXT /* JSON array of user IDs */
);

/* Offline exam results */
CREATE TABLE wp_ica_lms_offline_results (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    quiz_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    result_file_path VARCHAR(500),
    score DECIMAL(5,2),
    total_marks DECIMAL(5,2),
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at DATETIME,
    approved_by BIGINT,
    approved_at DATETIME
);
```

### Tables to EXTEND (Modify)
```sql
/* Add to wp_ica_lms_transactions */
ALTER TABLE wp_ica_lms_transactions ADD COLUMN (
  payment_type VARCHAR(30) DEFAULT 'one_time',
  installment_number INT,
  total_installments INT,
  due_date DATE,
  parent_transaction_id BIGINT
);

/* Add to wp_ica_lms_quiz_attempts */
ALTER TABLE wp_ica_lms_quiz_attempts ADD COLUMN (
  exam_type ENUM('online', 'offline') DEFAULT 'online',
  is_mock TINYINT(1) DEFAULT 0,
  offline_result_id BIGINT
);

/* Add to wp_ica_lms_notifications */
ALTER TABLE wp_ica_lms_notifications MODIFY COLUMN (
  type VARCHAR(100) /* expand from 50 */
  /* New types: fee_due_reminder, fee_overdue, birthday_greeting, etc. */
);
```

---

## SUCCESS METRICS

### After Phase 1 (Registration Numbers)
- Every student has unique registration number
- Roll numbers visible on student dashboard
- Admin can search/filter by registration number

### After Phase 2 (Birthdays)
- Birthday emails sent automatically
- Zero duplicate emails (24h dedup working)
- Admin can view birthday calendar

### After Phase 3 (Fee Structure)
- Courses support installment option
- Fee schedule stored and enforced
- Transaction records show installment metadata

### After Phase 4 (Fee Reminders)
- Overdue fees trigger automatic reminders
- Upcoming installment reminders sent 1 day before
- Admin fee dashboard shows collection status

### After Phase 5 (Exam Management)
- Exams can be marked online or offline
- Offline results uploadable by exam center
- Scores automatically calculated
- Certificates issued based on results

### After Phase 6 (Mock Tests)
- Mock tests marked separately
- Mock test analytics tracked
- Student can compare mock vs real performance

---

## RISK ASSESSMENT

| Phase | Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| 1 | Duplicate reg numbers generated | Low | Critical | Database unique constraint |
| 2 | Birthday cron doesn't run | Medium | Medium | Logging + admin manual trigger |
| 3 | Fee calculation precision | Low | Critical | Decimal type + rounding tests |
| 4 | Spam of fee reminders | Medium | High | Dedup logic + admin controls |
| 5 | Offline result file contamination | Low | Critical | File scanning + admin review |
| 6 | Mock test confusion | High | Low | Clear UI/UX labeling |

---

*Generated: April 7, 2026*  
*System: Impulse Academy Clone - LMS Module*

