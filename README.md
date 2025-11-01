Job Awareness (WAMP demo)

Overview
- Simple demo of a job awareness system using PHP + MySQL (WAMP) backend and HTML/CSS/JS frontend.
- Polls the backend for new jobs and shows browser notifications (or in-page alerts if notifications are unavailable).

Setup (WAMP)
1. Place the folder `job-awareness` inside your WAMP www folder. In this workspace it's at:
   `c:/wamp64/www/my project/job-awareness`
   Note: If you prefer a simpler URL, move the `job-awareness` folder directly to `c:/wamp64/www/job-awareness`.

2. Start WAMP (Apache + MySQL).

3. Create the database/tables and sample data:
   - Open phpMyAdmin (usually http://localhost/phpmyadmin).
   - Create/import the SQL file `sql/schema.sql` from the project or run the SQL manually.

4. Edit database credentials if needed:
   - Open `api/config.php` and adjust DB_HOST, DB_NAME, DB_USER, DB_PASS. By default it uses `root` with empty password.

5. Visit the app in your browser:
   - If folder is at `c:/wamp64/www/my project/job-awareness` then use:
     http://localhost/my%20project/job-awareness/
   - If you moved it to `c:/wamp64/www/job-awareness` use:
     http://localhost/job-awareness/

Usage
- The page shows existing jobs.
- Use "Add a job" to insert a job (demo; no auth). New jobs are visible immediately and trigger browser notifications when the page polls.
- "Subscribe" stores an email and keywords (demo only; no outgoing mail is sent).

Notes and next steps
- This is a demo/prototype. For production you should:
  - Add authentication for adding jobs/admin actions.
  - Add server-side push (WebSockets) or push notifications instead of polling.
  - Implement real email delivery when subscribers match job keywords (use a queue and transactional email API).
  - Harden input validation and CSRF protections.

Troubleshooting
- Blank page or PHP errors: ensure Apache/PHP is running and error display is enabled (or check Apache error logs).
- DB connection error: edit `api/config.php` with correct credentials and ensure the `job_awareness` database exists.

Enjoy — open issues or ask for features!
