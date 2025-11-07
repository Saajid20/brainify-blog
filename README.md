# Brainify Blog Application

Brainify is a full-stack web application that provides a tranquil, high-contrast space for users to share and explore thoughts. Built from scratch using PHP, MySQL, and Tailwind CSS.

## 🚀 Features
- **User Authentication:** Secure registration and login.
- **CRUD Operations:** Create, Read, Update, and Delete posts.
- **Interactive Ratings:** AJAX-based Like/Dislike system.
- **Tagging System:** Organization with a native autocomplete dropdown.
- **Responsive Design:** Modern "Dark Tech" UI built with Tailwind CSS.
- **Security:** Uses prepared statements and `.env` for credentials.

## 🛠️ Setup Instructions
1. Clone the repository.
2. Import `blog.sql` into your MySQL database.
3. Create a `.env` file in the root directory with your credentials:
   ```env
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=
   DB_NAME=your_database_name