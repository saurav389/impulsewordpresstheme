# Impulse LMS Delivery Status

## Shipped (v3.1 - 2026-03-31)
- Separate LMS module in `theme/lms/` with isolated bootstrap.
- Student enrollment workflow on existing `courses` single pages.
- Lesson engine:
  - `lms_lesson` post type.
  - Course mapping + lesson order + completion tracking.
  - Added lesson duration, prerequisite lesson, and downloadable resource metadata.
- Quiz engine:
  - `lms_quiz` post type.
  - Scoring, pass/fail logic, attempt persistence.
  - Added max-attempt policies and optional question randomization.
- Student dashboard:
  - Auto-created page: `/lms-dashboard/`
  - Shows enrolled courses and progress bars.
  - Shows certificate download action when earned.
- Catalog page:
  - Auto-created page: `/lms-catalog/`
- Certificates:
  - Auto-issue on completion criteria (all lessons complete + passed quiz).
  - Secure download endpoint (`admin-post`) for enrolled learners.
  - Public verification page auto-created at `/certificate-verification/`.
- Automation:
  - Daily cron reminder for at-risk learners (7+ days enrolled, <20% progress).
  - Notification logs persisted to database to avoid duplicate reminders.
- Admin analytics:
  - Admin menu: `Impulse LMS`
  - KPIs: total enrollments, active learners, lesson completions, avg quiz score.
  - Operational table: at-risk learners (7+ days, <20% progress).
  - CSV exports for enrollments and progress.
  - Manual reminder run button for operations team.
- Instructor operations:
  - New role: `LMS Instructor` (`ica_instructor`).
  - Instructor dashboard with per-course enrollments, average progress, and pass rate.
- Commerce operations:
  - Course-level pricing/currency/access-duration settings.
  - Enrollment gating for paid courses with transaction creation.
  - Admin payment queue with `Mark Paid & Enroll` workflow.
- Hardening and production fixes (March 31, 2026):
  - Removed duplicate CPT/menu registrations from theme `functions.php` to avoid conflicts.
  - Added guarded `courses` CPT registration in LMS module (`register only if missing`).
  - Fixed randomized quiz scoring with per-attempt tokenized question order.
  - Enforced course access duration by expiring outdated enrollments and requiring re-enrollment.
  - Restricted instructor dashboard access to `LMS Instructor` + Administrator capability only.
  - Added publish-state validation in AJAX flows for courses, lessons, and quizzes.
  - Added autosave/revision guards on LMS meta-save handlers for data integrity.
  - Switched LMS bootstrap paths to stylesheet directory for safer separated-theme operation.
  - Added launch runbook at `lms/LAUNCH-RUNBOOK.md` for repeatable deployment/UAT.

## Measurable Outcomes Enabled
- Enrollment conversion from course detail page.
- Engagement via lesson completion events.
- Learning efficacy via quiz score distribution and pass rate.
- Retention risk detection and outreach via daily/manual reminder execution.
- Certification throughput and external verification using unique certificate codes.
- Management reporting with direct CSV exports for enrollment/progress operations.

## Data Model
- `wp_ica_lms_enrollments`
- `wp_ica_lms_progress`
- `wp_ica_lms_quiz_attempts`
- `wp_ica_lms_certificates`
- `wp_ica_lms_notifications`
- `wp_ica_lms_transactions`

## Immediate 72-Hour Execution Tasks
1. Seed each live course with production lesson and quiz content.
2. Create dedicated SMTP setup and verify reminder deliverability.
3. Integrate online gateway webhook flow to auto-mark transactions paid.
4. Add cohort and batch management for group operations.
5. Run UAT with admin + instructor + student personas before cutover.

## Blockers
- Content dependency: live curriculum assets still need to be populated course-by-course.
- Infrastructure dependency: outbound email provider configuration required for reliable reminder delivery.
- Commercial dependency: payment queue is live, but automated gateway/webhook integration is still pending.
- Environment note: `php` is not on PATH in this terminal session; linting was executed successfully via `C:\xampp\php\php.exe -l`.
