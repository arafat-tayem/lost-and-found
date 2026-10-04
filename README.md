# Lost & Found Web App

A web application for Southeast University students to report lost items, post found items, and help reunite belongings with their owners. Built for CSE471 (Web and Internet Programming).

**Live site:** [PASTE YOUR RENDER URL HERE]
(The site runs on a free plan and sleeps when idle, so the first visit can take up to a minute.)

## Features

- User registration, login and logout with hashed passwords
- Post, edit and delete your own lost or found items
- Photo upload (JPG, PNG, GIF, WEBP, max 2 MB), stored in the database
- Public browse page with search and category/status filters
- Personal dashboard showing only your own items
- Contact link to email the person who posted an item

## Tech Stack

- Frontend: HTML, CSS, JavaScript
- Backend: PHP 8.2 (no framework), PDO
- Database: PostgreSQL
- Hosting: Render (Docker) and Neon (PostgreSQL)

## Security

- Passwords hashed with `password_hash` and checked with `password_verify`
- Prepared statements everywhere to prevent SQL injection
- Output escaped with `htmlspecialchars` to prevent XSS
- Access control: protected pages require login, and edit/delete only work on your own items
- Uploads checked by real file type and size, not just file name
- Database credentials kept in environment variables, not in the code

## Run Locally (Windows + XAMPP)

1. Install XAMPP and PostgreSQL.
2. Enable the `pdo_pgsql` extension in XAMPP's `php.ini` and restart Apache.
3. Copy this project into `C:\xampp\htdocs\lost_and_found\`.
4. In PostgreSQL, create a database named `lost_and_found`.
5. Run `database/schema.sql` in that database to create the tables.
6. Create `config/db.local.php` with your local database settings (host, port, dbname, user, password). This file is ignored by Git.
7. Start Apache in XAMPP and open `http://localhost/lost_and_found/`.

## Deployment

- The app is built from the `Dockerfile` and runs as a Render Web Service.
- The database is a Neon PostgreSQL instance.
- Render has an environment variable `DATABASE_URL` holding the Neon connection string. If it is set, the app uses it. Otherwise it falls back to `config/db.local.php`.
- Pushing to the `main` branch redeploys the site automatically.

## Author

Yasir Arafat, Southeast University, Barishal