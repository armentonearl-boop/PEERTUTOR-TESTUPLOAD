# PeerTutor - Test Checklist

Write **Pass** or **Fail** in the last column while you test. Fix every Fail before the defense.

| # | What to test | What should happen | Result |
|---|---|---|---|
| 1 | Register a tutee with all boxes filled | "Account created! You can log in now." | |
| 2 | Register with a password shorter than 6 characters | Red message, account is not created | |
| 3 | Register with two different passwords | Red message, account is not created | |
| 4 | Register with a student ID that already exists | "That student ID is already registered." | |
| 5 | Register a tutor and try to log in right away | "Your tutor account is still waiting for the teacher's approval." | |
| 6 | Log in with a wrong password | "Wrong student ID or password." | |
| 7 | Log in as admin (`admin` / `admin123`) | Goes to the dashboard | |
| 8 | Check `users` table in phpMyAdmin | The password column shows a long scrambled text, not the real password | |
| 9 | Open `localhost/peertutor/reports.php` while logged in as a tutee | "Sorry, you are not allowed to open this page." | |
| 10 | Open `localhost/peertutor/dashboard.php` after logging out | Goes back to the login page | |
| 11 | Stay inactive for 15 minutes, then click something | Logged out with the inactivity message | |
| 12 | Admin approves the tutor | Tutor can log in now | |
| 13 | Admin adds, renames and deletes a subject | The list changes each time (full CRUD) | |
| 14 | Admin adds a subject that already exists | "That subject name already exists." | |
| 15 | Tutor adds a subject and a free time | Both show in the lists | |
| 16 | Tutor adds a free time where end is earlier than start | "The end time must be later than the start time." | |
| 17 | Tutee searches a subject | The tutor shows with free time and rating | |
| 18 | Tutee books a time inside the tutor's free time | Session appears as **pending** | |
| 19 | Tutee books a day the tutor is not free | "The tutor is not free at that day and time." | |
| 20 | Tutee books the same time again | "That time is already booked." | |
| 21 | Tutor accepts the request | Status becomes **accepted** for both | |
| 22 | Tutor writes notes and clicks Mark as completed | Status becomes **completed**, the tutee can see the notes | |
| 23 | Tutee rates the completed session | Rating shows on the session and on the tutor card | |
| 24 | Tutee cancels a pending session | Status becomes **cancelled** | |
| 25 | Tutor clicks "Tutee did not show up" | Status becomes **no_show**, counted in reports | |
| 26 | Dashboard reminder | An accepted session for today or tomorrow shows in yellow | |
| 27 | Open Reports as admin | Summary numbers, 2 charts, tables, history and activity log all show | |
| 28 | Click "Download all sessions (CSV)" | A CSV file downloads and opens in Excel | |
| 29 | Tutee clicks "Download my progress (CSV)" | CSV contains only that tutee's sessions | |
| 30 | Reports > Print / Save as PDF | Preview has no menu or buttons | |
| 31 | Open the site on a phone-size window | Layout still readable, menu collapses | |
| 32 | Activity log after the tests | Login, booking, accept, complete, rating, approval are all listed | |
