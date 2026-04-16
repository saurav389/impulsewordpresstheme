# Impulse LMS Launch Runbook

## Scope
This launch package lives in `wp-content/themes/impulse-academy-clone/lms` and includes:
- Student enrollment, lesson progress, quiz attempts, and certificates.
- Admin KPI dashboard with CSV exports.
- Instructor dashboard with course-level performance metrics.
- At-risk learner reminder automation.
- Paid-course approval queue with manual `Mark Paid & Enroll` flow.

## Pre-Launch Checklist
1. Activate theme: **Appearance -> Themes -> impulse-academy-clone**.
2. Visit **Settings -> Permalinks** and click **Save Changes** once.
3. Verify LMS pages exist:
   - `/lms-dashboard/`
   - `/lms-catalog/`
   - `/certificate-verification/`
4. Configure SMTP transport for transactional mail (required for reminders).
5. For each course (`courses` CPT):
   - Set commerce fields (price/currency/access duration).
   - Add lessons (`lms_lesson`) and course-linked quiz (`lms_quiz`).
6. Create at least one instructor account with role `LMS Instructor`.

## UAT Flow (must pass)
1. Student enrolls in a free course and appears in LMS admin KPI counts.
2. Student completes lessons in order (including prerequisite checks).
3. Student attempts quiz; score and pass/fail stored.
4. On full completion + pass, certificate can be downloaded.
5. Certificate code verifies successfully on `/certificate-verification/`.
6. Paid course enrollment creates pending transaction.
7. Admin uses **Mark Paid & Enroll** and student receives enrollment.

## Operations (Daily)
1. Review **Impulse LMS** admin dashboard for KPI movement.
2. Export enrollment/progress CSV for weekly reporting.
3. Review pending payments and clear queue.
4. Trigger **Run At-Risk Reminders Now** when intervention needed.

## Known Dependencies
- Reminder delivery depends on valid SMTP/mail transport.
- Payment flow is currently manual-approval unless external gateway/webhooks are added.
- WP-Cron must run reliably for automated reminders.
