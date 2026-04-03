# Backend Implementation Plan

## Database Schema
```
users: id, email, password, full_name, role (admin/teacher/student), created_at
schedules: id, teacher_id, day, time_start, time_end, description, created_at
requests: id, student_id, teacher_id, status (pending/approved/rejected), message, created_at
```

## Files to Create
- [ ] config/database.php
- [ ] api/auth.php (login/register)
- [ ] api/users.php (admin CRUD)
- [ ] api/schedules.php (CRUD)
- [ ] api/requests.php (CRUD)
- [ ] Updated frontend forms to POST /api/

## Setup
1. Create MySQL DB `metav_scheduling`
2. Run schema.sql
3. PHP 8+

**Status: Ready to implement**
