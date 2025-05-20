# PHP Final Project

This is a PHP-based web application that handles user authentication and password management. It includes features such as user signup, login, password generation, and password storage using AES encryption and a MySQL database. The system is built using object-oriented programming (OOP) principles.

## Features

- **User Signup** – Create a new user account with hashed passwords
- **User Login** – Log in using secure credentials
- **Password Generation** – Create strong passwords with custom character options
- **Save and Retrieve Passwords** – Store and access encrypted passwords
- **Password Change** – Update account password and securely re-encrypt the AES key

## Project Structure

- `Signup.php` – Handles user registration and AES key generation
- `Login.php` – Authenticates user credentials
- `passgen.php` – Generates passwords based on user-defined criteria
- `SavePass.php` – Encrypts and saves passwords to the database
- `GetPass.php` – Decrypts and displays stored passwords
- `ChagePas.php` – Updates login password and re-encrypts stored key
- `Html.html` – User interface (dashboard)
- `book.sql` – SQL schema file to set up MySQL database

## Requirements

- PHP 7.x 
- MySQL database
- Web server (e.g., XAMPP, WAMP)

## Setup Instructions

1. Importing `book.sql` into MySQL database.
2. Placeing the project files in server root directory (`htdocs` for XAMPP).
3. Accessing the project via  browser.
