
# Multi-Vendor E-Commerce System

## 1. Executive Summary

This project serves as a comprehensive simulation of a modern, multi-vendor e-commerce marketplace. Developed using the **Laravel PHP Framework**, the system is designed to facilitate interaction between three distinct user groups: **Customers, Vendors, and Administrators.**

The platform employs a robust **Role-Based Access Control (RBAC)** system to ensure security and logical separation of concerns. While Customers enjoy a seamless shopping experience with cart management and checkout capabilities, Vendors are provided with tools to manage inventory and visualize sales data. Administrators maintain global oversight, ensuring platform integrity. The system bridges the gap between complex backend logic and a user-friendly frontend using the **Model-View-Controller (MVC)** architectural pattern.

---

## 2. Functional Modules and System Working

The application is divided into three core panels, each tailored to a specific user role.

### A. Admin Panel (System Oversight)

* **User Management:** Admins can view a paginated list of all registered users, with color-coded badges distinguishing between Customers, Vendors, and other Admins.
* **Global Product Management:** Admins possess "Absolute control" of the inventory. They can see every product listed by every vendor and have the authority to force-delete inappropriate items.
* **System Statistics:** A high-level dashboard displays aggregate metrics, such as total registered users and total active product listings.

### B. Vendor Panel (Inventory Management)

* **Dynamic Dashboard:** Upon login, vendors see a real-time **Stock Level Chart** (powered by Chart.js) and a calculation of their Total Inventory Value (Price × Stock).
* **Product CRUD:** Vendors can **C**reate new listings with image uploads, **R**ead their own product list, **U**pdate prices or stock levels, and **D**elete their own items.
* **Secure Isolation:** Vendors are strictly prevented from editing or deleting products that belong to other vendors.

### C. Customer Panel (Shopping Experience)

* **Catalog & Search:** Customers can browse products and use a search bar to filter items by name or description.
* **Shopping Cart:** A session-persistent cart allows users to add items, update quantities, or remove products. The system prevents duplicate rows, instead incrementing the quantity of existing items.
* **Checkout & Order:** A transactional workflow that validates shipping details, checks real-time stock availability, and decrements inventory from the database upon successful order placement.

---

## 3. Technical Architecture and Tech Stack

### Backend

* **Framework:** Laravel 12.x (PHP 8.4)
* **Architecture:** MVC (Model-View-Controller)
* **API:** RESTful API implementation for external connectivity.

#### Key Backend Features

* **Eloquent ORM:** Handles database interactions (e.g., `$user->products` fetches all products owned by a user).
* **Server-Side Validation:** All inputs are validated to prevent SQL injection and data corruption.
* **Storage Management:** Utilizes Laravel's Filesystem to securely store and retrieve user-uploaded product images.

#### Application Controllers Logic

| Controller | Role | Key Logic & Routes |
| --- | --- | --- |
| **AuthController** | Manual Authentication | Handles Login/Registration/Logout. Redirects users based on role (`Auth::user()->role`). |
| **ProductController** | Public Access | Manages catalog index (pagination) and search logic using `LIKE %keyword%`. |
| **VendorController** | Vendor Protection | Dashboard stats (SQL inventory value) and Product CRUD with file uploads. |
| **CartController** | Customer Protection | Logic for incrementing quantities, stock validation, and inventory decrementing. |
| **AdminController** | System Oversight | User listing and "Force Delete" functionality for products and associated files. |

---

### Frontend

* **Templating Engine:** Laravel Blade for server-side rendering and layout inheritance.
* **CSS Framework:** Bootstrap 5 for responsive grid systems and pre-styled components.
* **Data Visualization:** Chart.js integrated into the Vendor Dashboard for stock visualization.

#### UI Structure

1. **Layout Files:** `app.blade.php` (Master template with Navbar, Footer, and Flash Messages).
2. **Public Views:** Product grids, detailed views, and the cart/checkout forms.
3. **Vendor Views:** Dashboard with `<canvas>` for Chart.js and forms with `enctype="multipart/form-data"`.
4. **Admin Views:** High-level stats cards and management tables with JavaScript delete confirmations.

---

## 4. API Implementation

A stateless REST API built for external connectivity (e.g., mobile apps).

* **Controller:** `Api/ProductApiController.php`
* **Format:** JSON response with standard HTTP status codes (200, 201, 404).
* **Routes (`routes/api.php`):**
```php
Route::apiResource('products', ProductApiController::class);

```


* `GET /api/products` - Index
* `POST /api/products` - Store
* `PUT /api/products/{id}` - Update
* `DELETE /api/products/{id}` - Destroy



---

## 5. Database Design

* **Users Table:** Uses `ENUM('customer', 'vendor', 'admin')` to determine permissions.
* **Products Table:** Stores inventory data with a foreign key (`user_id`) linking to the Vendor.
* **Carts Table:** Links a User to a potential purchase for persistence.
* **Cart Items Table:** Junction table connecting Carts and Products to store specific quantities.

---

## 6. Installation and Setup Guide

### Prerequisites

* PHP 8.4
* Laravel 12.x
* Composer
* MySQL

### Backend Setup

1. **Clone the Repository:**
```bash
git clone https://github.com/masharyousaf-prog/Multi-VendorE-Commerce-Inventory-OrdersSystem

```


2. **Install Dependencies:**
```bash
composer install

```


3. **Configure `.env`:**
```text
DB_DATABASE=laravel_shop
DB_USERNAME=root
DB_PASSWORD=

```


4. **Finalize Setup:**
```bash
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve

```



### Frontend Setup

Bootstrap 5 is loaded via CDN; no NPM/Node.js setup is required for basic functionality.

---

## 7. User Access and Credentials

### Creating an Admin

Admins must be created via **Laravel Tinker**:

1. Run `php artisan tinker`.
2. Execute:
```php
\App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@shop.com',
    'password' => bcrypt('password123'),
    'role' => 'admin'
]);

```



### Vendors & Customers

* **Vendors:** Register at `/register` and select "Vendor" from the role dropdown.
* **Customers:** Register at `/register`. Default logic assigns the "Customer" role.

---

## 8. Conclusion

This project successfully demonstrates the implementation of a scalable, multi-role e-commerce application. By adhering to the MVC pattern and leveraging Laravel's advanced features—such as Middleware, Eloquent Relationships, and Resource Controllers—we have built a secure and maintainable system.


