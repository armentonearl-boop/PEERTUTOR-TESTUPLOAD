# PeerTutor

A web-based peer tutoring system for our school. Students who need help (tutees) can find and book classmates who are good at a subject (tutors). A teacher approves tutors and watches the program through reports.

## Features

- **Accounts:** sign up, log in and log out for three roles (tutee, tutor, teacher/admin)
- **Security:** passwords are encrypted with `password_hash` (bcrypt), session-based login, access control per role, auto-logout after 15 minutes, front-end and back-end form validation
- **Tutors:** choose the subjects they can teach, set their free time, accept or decline requests, write session notes
- **Tutees:** search tutors by subject, book a session (the system checks the tutor's free time and double booking), cancel, rate the tutor, download their own progress as CSV
- **Teacher:** approve tutor accounts, add / edit / delete subjects
- **Transactions:** booking a session and changing a session status save the session and an activity log together (all or nothing)
- **Reports:** summary numbers, charts (Chart.js), tutor ratings, transaction history, user activity log, CSV export, print / save as PDF

## Technologies

HTML, CSS, JavaScript, Bootstrap 5, Chart.js, PHP (PDO), MySQL / MariaDB, XAMPP, VS Code, Git and GitHub

## How to run it on your computer

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL** in the control panel.
2. Copy this folder to `C:\xampp\htdocs\peertutor`.
3. Open `http://localhost/phpmyadmin`, click **Import**, choose `database.sql` and click **Import**.
4. Open `http://localhost/peertutor`.

Default teacher account: student ID `admin`, password `admin123` (change it after the first login).

If your database password is not empty, change `$dbpass` in `config.php`.

If you imported an older `database.sql` before, run `update_activity_logs.sql` once to add the `activity_logs` table.

## Database tables

| Table | What it keeps |
|---|---|
| `users` | all accounts (admin, tutor, tutee) |
| `subjects` | list of subjects |
| `tutor_subjects` | which subjects each tutor teaches, and the skill level |
| `availability` | free time of each tutor |
| `sessions` | tutoring sessions (tutee, tutor, subject, date, time, status, notes) |
| `ratings` | rating of a completed session (1 to 5) |
| `activity_logs` | what each user did and when |

## Project files

| File | Purpose |
|---|---|
| `config.php` | database connection, auto-logout, helper functions |
| `logger.php` | function that writes to the activity log |
| `login.php`, `register.php`, `logout.php` | authentication |
| `dashboard.php` | home page and reminders |
| `tutor_setup.php`, `requests.php` | tutor pages |
| `find_tutors.php`, `book.php`, `my_sessions.php` | tutee pages |
| `approve_tutors.php`, `subjects.php`, `reports.php`, `export_csv.php` | teacher pages |
| `header.php`, `footer.php`, `style.css`, `script.js` | shared layout, design and small scripts |

## Testing

See `TEST_CHECKLIST.md` for the list of things we tested.

## Author

Capstone project - add your name, section and school year here.
