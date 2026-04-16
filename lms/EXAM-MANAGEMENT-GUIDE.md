# Exam Management System - User Guide

## Overview
The Exam Management System allows teachers to create exams/tests, add questions with options, and map them to courses for student assessment.

## Features

### 1. **Exam Creation**
Teachers can create exams with:
- **Exam Title**: Name of the exam
- **Description**: Optional details about the exam
- **Exam Type**: Choose between "Test" or "Final Exam"
- **Start Date**: When the exam becomes available to students
- **Expiry Date**: Deadline for exam submission
- **Duration**: Total hours allowed for the exam
- **Total Marks**: Maximum marks possible for the exam
- **Pass Marks**: Minimum marks required to pass

### 2. **Question Management**
Teachers have two options:

#### **Option A: Add Questions One by One**
- Navigate to exam → Click "Questions"
- Click "Add Single Question"
- Enter question text
- Add 4 options and select the correct one
- Set marks for the question
- Click "Add Question"

#### **Option B: Bulk Import Questions**
- Click "Bulk Import" tab
- Upload a CSV file with the following format:

```
Question Text | Option 1 | Option 2 | Option 3 | Option 4 | Correct Option (1-4) | Marks
```

Example CSV:
```
What is the capital of France? | London | Paris | Berlin | Madrid | 2 | 1
What is 2 + 2? | 3 | 4 | 5 | 6 | 2 | 1
```

**CSV Format Guidelines:**
- Use pipe (`|`) as separator
- Correct option should be number 1-4 indicating which position is correct
- Each row = one question
- All 7 fields are required

### 3. **Map Exams to Courses**
- Go to exam → Click "Map Courses"
- Select one or more courses
- Save mapping
- Students enrolled in mapped courses will see the exam

## Access Control

### Teachers
- Can only create and manage their own exams
- Can add questions and edit exam details
- Can map exams to courses they manage

### Admins
- Can view and manage all exams
- Have full access to all features

### Students
- Cannot access exam management system
- Will see mapped exams in their student dashboard

## Database Structure

The system creates 4 database tables:

1. **wp_ica_lms_exams**
   - Stores exam master data (title, dates, duration, marks)
   - `id`, `created_by`, `exam_title`, `exam_type`, `start_date`, `end_date`, `duration_hours`, `total_marks`, `pass_marks`

2. **wp_ica_lms_exam_questions**
   - Stores individual questions
   - `id`, `exam_id`, `question_text`, `marks`, `question_order`

3. **wp_ica_lms_exam_question_options**
   - Stores answer options for each question
   - `id`, `question_id`, `option_text`, `is_correct`, `option_order`

4. **wp_ica_lms_exam_course_mapping**
   - Maps exams to courses
   - `id`, `exam_id`, `course_id`, `is_mandatory`

## Workflow Example

1. **Teacher creates exam**
   - Go to LMS → Exams → Create New Exam
   - Fill in exam details (title, type, dates, duration, marks)
   - Click "Create Exam"

2. **Teacher adds questions**
   - From exam list, click "Questions"
   - Either add single questions or bulk import from CSV
   - View all questions in the list

3. **Teacher maps to courses**
   - From exam list, click "Map Courses"
   - Select courses where students should take this exam
   - Save mapping

4. **Students see exam**
   - In their dashboard, students enrolled in mapped courses see the exam
   - Can view exam details and start taking the exam (when start date is reached)

## Tips

- **Mark questions properly**: Make sure to select the correct option when creating questions
- **Bulk import verification**: Test CSV format with a few questions before importing many
- **Exam scheduling**: Set start dates in future so students can't see exam until scheduled
- **Pass marks**: Set pass marks appropriately (usually 40% for standard exams)
- **Duration**: Consider exam complexity when setting hours
- **Course mapping**: You can map one exam to multiple courses and vice versa

## Status Options

- **Draft**: Exam is being prepared, not visible to students
- **Published**: Exam is live and visible to students (when start date is reached)

## Future Enhancements (Roadmap)

- Student exam attempts and submissions
- Automatic grading of exams
- Exam results and analytics
- Progress reports
- Randomize question order for students
- Question difficulty levels
- Negative marking support
