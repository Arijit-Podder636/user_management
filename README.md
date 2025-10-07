**User Management System**

A comprehensive and secure User Management System built with PHP and MySQL. This project includes features like role-based authentication (Admin/User), complete CRUD operations, and user profile management with profile picture uploads.
This project was developed as part of the ApexPlanet Software Pvt. Ltd. Backend Development & Database Integration Task (Task - 3).

**Table of Contents**

Key Features

Technology Stack

Prerequisites

Installation & Setup

Database Schema

Usage

Project Structure

Security Measures

**Key Features**

User Authentication: Secure user registration with hashed passwords, login, and logout functionality using PHP sessions.

Role-Based Access Control (RBAC): Differentiates between Admin and User roles.

Admin: Can view, create, update, and delete all user records.

User: Can view and edit their own profile.

CRUD Operations: Full Create, Read, Update, and Delete functionality for user management (Admin-only).

Profile Management: Users can update their personal information and upload a profile picture.

Secure File Uploads: Profile picture uploads include server-side validation for file size and type (e.g., jpeg, png).

Dynamic Data Display: User information is fetched from the database and displayed dynamically on the dashboard and profile pages.

Security: Implements prepared statements to prevent SQL injection and server-side validation for all user inputs.

**Technology Stack**

Backend: PHP

Database: MySQL

Frontend: HTML, CSS, JavaScript (for confirmation popups and dynamic elements)

Server: Apache (via XAMPP/WAMP/MAMP)

**Prerequisites**

Before you begin, ensure you have the following installed on your local machine:

A local server environment like XAMPP, WAMP, or MAMP.

A web browser (e.g., Google Chrome, Firefox).

A code editor (e.g., VS Code).

Git (for cloning the repository).

**Installation & Setup**

Follow these steps to get the project up and running on your local machine.

1) Clone the repository:

Bash

git clone https://github.com/Arijit-Podder636/user_management.git

2) Move the project to your server directory:

Place the cloned project folder inside your server's web root directory.

For XAMPP, this is typically C:/xampp/htdocs/.

For WAMP, this is C:/wamp/www/.

3) Start your local server:

Open your XAMPP/WAMP control panel and start the Apache and MySQL services.

4) Set up the database:

Open your web browser and navigate to http://localhost/phpmyadmin/.

Create a new database. Let's name it user_management_db.

Select the new database and go to the Import tab.

Click on "Choose File" and select the database.sql file located in the root of the project folder.

Click "Go" to import the schema and tables.

5) Configure the Application
This is the most crucial step. You need to tell the application how to connect to your database.

Navigate to the config/ folder and open the database.php file in your code editor.

Replace its contents with the code below.

Update the defined constants to match your local server setup:

DB_USER: Your MySQL username (the default for XAMPP is 'root').

DB_PASS: Your MySQL password (the default for XAMPP is '' (empty)).

DB_NAME: The exact name of the database you created (e.g., 'user_management_db').

DB_PORT: The port number your MySQL server is using. Check your XAMPP/WAMP control panel. The default is 3306.

PHP

<?php
// Start the session, making session variables available on every page
session_start();

// --- DATABASE CREDENTIALS ---
// Adjust these constants to match your local environment
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default for XAMPP is an empty password
define('DB_NAME', 'user_management_db');
define('DB_PORT', 3306); // The default MySQL port is 3306. Change if yours is different.

// --- ESTABLISH DATABASE CONNECTION ---
// Using the Object-Oriented mysqli approach
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check for a connection error
if ($mysqli->connect_errno) {
    // Terminate the script and show an error message
    die("Database connection failed: " . $mysqli->connect_error);
}

// --- SET TIMEZONE ---
// Ensures that all date and time functions use the correct timezone for your location
date_default_timezone_set('Asia/Kolkata');

?>

**Database Schema**

The database is normalized to 3NF and consists of two main tables:

roles table:

id (INT, Primary Key)

role_name (VARCHAR) - e.g., 'admin', 'user'

users table:

id (INT, Primary Key, AUTO_INCREMENT)

full_name (VARCHAR)

email (VARCHAR, UNIQUE)

password (VARCHAR) - Stores hashed passwords

profile_picture (VARCHAR) - Stores the file path

role_id (INT, Foreign Key referencing roles.id)

**Usage**

Open your web browser and navigate to the project: http://localhost/your-repo-name/

You will be redirected to the login page.

Register a new account or use the default admin credentials to log in.

Default Admin Credentials:

Email: admin@example.com

Password: Admin@123

Once logged in, you will be redirected to the dashboard, where functionality will vary based on your role.

**Security Measures**

This project prioritizes security by implementing the following:

SQL Injection Prevention: All database queries are executed using Prepared Statements (mysqli_prepare, mysqli_stmt_bind_param, etc.).

Password Hashing: User passwords are never stored as plain text. They are securely hashed using PHP's password_hash() function and verified with password_verify().

Server-Side Validation: All user inputs (forms, file uploads) are validated on the server to ensure data integrity and security.

Session Management: Secure sessions are used to manage user login states.
