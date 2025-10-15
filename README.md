# BookStore - Full Stack E-commerce Web Application

Welcome to BookStore, a complete e-commerce web application built from the ground up using PHP and MySQL. This project demonstrates a full-featured online bookstore with a seamless user experience for customers and a powerful, secure dashboard for administrators.

This application was developed as the final project for my 60-day full-stack internship, showcasing skills in both front-end and back-end development.


![BookStore Screenshot](uploads/Bookstore%20Banner.jpg) 

***

## ## Key Features 🚀

The application is split into two main parts: the customer-facing storefront and a secure admin panel.

### ### 👤 Customer & User Features

* **Secure User Authentication:**
    * **Registration:** New users can create an account with securely **hashed passwords** using `password_hash()`.
    * **Login:** A robust login system that supports different user roles (e.g., customer, admin).
    * **Password Reset:** A fully secure "Forgot Password" feature that generates one-time-use, expiring tokens.

* **Dynamic Product Catalog:**
    * **Browse & Search:** Users can browse all books, search for specific titles or authors, and sort the results.
    * **AJAX-Powered Filtering:** The book catalog can be filtered by category and sorted by price or date **without any page reloads**, providing a fast, modern user experience.

* **Complete Shopping Cart & Ordering System:**
    * **Add to Cart:** Users can add books to their shopping cart. The system intelligently updates the quantity if the item already exists.
    * **Dynamic Cart Management:** Cart quantities can be updated in real-time using AJAX, with totals recalculating instantly.
    * **Secure Checkout:** Users can "buy now" directly from the product page or place an order for all items in their cart.
    * **Order History:** A dedicated "My Orders" page where users can view their past purchases and their status.

***

### ### 🔐 Admin Panel Features

The admin panel is a protected area that gives the administrator full control over the store's data.

* **At-a-Glance Dashboard:** A central dashboard that displays key statistics like **total books, total users, total orders, and total revenue**.
* **Book Management (CRUD):**
    * Full **Create, Read, Update, and Delete** functionality for all books in the store.
    * Features include file uploads for book covers and category management.
* **Order Management:**
    * View all orders placed by all users in a single, comprehensive table.
    * Search and filter orders to quickly find specific information.
* **User Management (CRUD):**
    * Admins can view, add, edit, and delete user accounts.

***

## ## Core Technical Features & Security 🛡️

This application was built with security and modern web standards as a top priority.

* **SQL Injection Prevention:** All database queries are executed using **Prepared Statements**, making the application immune to SQL injection attacks.
* **Cross-Site Scripting (XSS) Prevention:** All user-provided data is sanitized using `htmlspecialchars()` before being displayed, preventing XSS vulnerabilities.
* **Cross-Site Request Forgery (CSRF) Protection:** All actions that modify data (like deleting an order or updating a book) are handled exclusively through `POST` requests, protecting against CSRF attacks.
* **Role-Based Access Control (RBAC):** The application has a clear distinction between `admin` and `user` roles, ensuring that sensitive admin pages are inaccessible to regular users.

***

## ## Technology Stack 🛠️

* **Backend:** **PHP**
* **Database:** **MySQL**
* **Frontend:**
    * HTML5
    * CSS3
    * **Bootstrap 5** (for responsive design and components)
    * **JavaScript (ES6)** (for dynamic features like AJAX)
    * **jQuery** (for simplified DOM manipulation and AJAX)
* **Server:** XAMPP / WAMP (Apache)

***

## ## Setup and Installation Instructions 💻

To run this project locally, follow these steps:

1.  **Clone the Repository**
    ```bash
    git clone (https://github.com/Arijit-Podder636/bookstore.git)
    ```

2.  **Database Setup**
    * Start your Apache and MySQL services using XAMPP or a similar tool.
    * Open phpMyAdmin and create a new database named `bookstore`.
    * Import the `database.sql` file (you should provide this file) into the `bookstore` database. This will create all the necessary tables.

3.  **Configuration**
    * In the root directory, find the `config.php` file. This file reads database credentials from `config.ini`.
    * Create a new file named `config.ini` in the root directory.
    * Copy the following structure into `config.ini` and fill in your database details (for a default XAMPP setup, the password is often blank).

    ```ini
    [database]
    DB_HOST = 127.0.0.1
    DB_PORT = 3306 
    DB_USER = root
    DB_PASS = 
    DB_NAME = bookstore
    ```

4.  **Run the Application**
    * Place the entire project folder inside your server's root directory (e.g., `C:/xampp/htdocs/`).
    * Open your web browser and navigate to `http://localhost/your-project-folder-name/`.

You should now be able to see the BookStore homepage!
