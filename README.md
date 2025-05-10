A secure, PHP-based web application that allows users to register, authenticate, upload and track profile avatars, and participate in a global message board. 

Includes an admin dashboard for message moderation and emphasizes robust security practices.

## Features
User Registration & Login with session-based authentication

Avatar Upload & History: Users can upload new avatars and view their upload history

Global Message Board: Leave and view public messages

Admin Panel: Delete messages individually or by user

Rich Text Support using UEditor

Clean HTML Handling via HTMLPurifier

## Security
Session management for user authentication

CSRF token protection against cross-site request forgery

Parameterized SQL queries to prevent SQL injection

Brute-force attack prevention on login endpoints

Input sanitization using HTMLPurifier

## Tech Stack
Backend: PHP, MySQL, Apache

Frontend: HTML, CSS, JavaScript

Rich Text Editor: UEditor

Sanitization Library: HTMLPurifier
