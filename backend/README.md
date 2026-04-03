# MetaV Scheduling Backend Setup

## Prerequisites
- XAMPP (Apache + MySQL + PHP 8+)
- Point htdocs to d:/Balicudiong/MetaV/MetaV

## Quick Start
1. **Start XAMPP** (Apache & MySQL)
2. **Import DB**: phpMyAdmin → Import `backend/schema.sql`
3. **Test Backend**: Open `http://localhost/MetaV/backend/index.php`
4. **Test APIs**: Use Postman or curl on `/backend/api/*`
5. **Update Frontend**: JS forms POST to `/backend/api/`

## API Endpoints
```
POST /backend/api/auth.php {"action":"login/register", ...}
GET/POST/PUT/DELETE /backend/api/users.php (admin only)
GET/POST/PUT/DELETE /backend/api/schedules.php
GET/POST/PUT /backend/api/requests.php
```

## Security
- Passwords bcrypt hashed
- Role-based access
- Session auth
- PDO prepared statements

## Next: Frontend Integration
Update JS in HTML files to AJAX call backend APIs instead of local forms.

Backend ready! 🚀
