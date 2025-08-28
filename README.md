# PHP Assignment - E-commerce API

A RESTful API built with PHP for managing an e-commerce system with user authentication, product management, and order processing.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Project Structure](#project-structure)
- [API Endpoints](#api-endpoints)
- [Authentication & Authorization](#authentication--authorization)
- [Database Schema](#database-schema)

## Features

- **User Management**: Registration, login/logout with session management
- **Role-based Access Control**: Admin and User roles with different permissions
- **Product Management**: CRUD operations for products with image upload
- **Order Management**: Create and manage orders with order items
- **Session Security**: Automatic session timeout and secure authentication
- **File Upload**: Image upload for products with proper file handling

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/LAMPP (recommended for development)

## Installation

1. Clone or download the project to your web server directory:
   ```bash
   cd /opt/lampp/htdocs/projects/
   git clone <repository-url> php_assignment
   ```

2. Create the database by running the SQL script:
   ```bash
   mysql -u root -p < database.sql
   ```

3. Configure the database connection in `config/config.php`

4. Ensure the `uploads/` directory has write permissions:
   ```bash
   chmod 755 uploads/
   ```

5. Start your web server and access the API at:
   ```
   http://localhost/projects/php_assignment/
   ```

## Configuration

Edit `config/config.php` to match your environment:

```php
define('DB_SERVERNAME', 'localhost');
define('DB_USERNAME', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'assignment');
define('PEPPER', 'your_secret_pepper_string');
define('SESSION_TIMEOUT', 1800); // 30 minutes
```

## Project Structure

```
php_assignment/
├── classes/
│   ├── User.php           # User entity class
│   ├── Product.php        # Product entity class
│   ├── Order.php          # Order entity class
│   └── OrderItem.php      # OrderItem entity class
├── config/
│   └── config.php         # Database and application configuration
├── controllers/
│   ├── UserController.php     # User-related operations
│   ├── ProductController.php  # Product management
│   ├── OrderController.php    # Order management
│   └── OrderItemController.php # Order item management
├── repositories/
│   ├── UserRepository.php     # User data access
│   ├── ProductRepository.php  # Product data access
│   ├── OrderRepository.php    # Order data access
│   └── OrderItemRepository.php # Order item data access
├── enums/
│   └── UserRole.php       # User role constants
├── uploads/               # Product images directory
├── database.sql           # Database schema
├── index.php             # Main entry point and router
└── README.md             # This documentation
```

## API Endpoints

### User Management

| Method | Endpoint | Description | Access Level |
|--------|----------|-------------|--------------|
| POST | `/user` | Register new user | Public |
| POST | `/admin` | Create admin user | Admin only |
| POST | `/login` | User login | Public |
| GET | `/logout` | User logout | Authenticated |

### Product Management

| Method | Endpoint | Description | Access Level |
|--------|----------|-------------|--------------|
| POST | `/product` | Create product | Admin only |
| GET | `/product` | Get all products | Public |
| PUT | `/product?id={id}` | Update product | Admin only |
| DELETE | `/product?id={id}` | Delete product | Admin only |

### Order Management

| Method | Endpoint | Description | Access Level |
|--------|----------|-------------|--------------|
| POST | `/order` | Create order | Authenticated |
| GET | `/order` | Get all orders | Admin only |
| PUT | `/order?id={id}` | Update order | Admin only |
| DELETE | `/order?id={id}` | Delete order | Admin only |

### Order Item Management

| Method | Endpoint | Description | Access Level |
|--------|----------|-------------|--------------|
| POST | `/order-item` | Add item to order | Authenticated |
| GET | `/order-item` | Get order items | Authenticated |
| PUT | `/order-item?id={id}` | Update order item | Authenticated |
| DELETE | `/order-item?id={id}` | Delete order item | Authenticated |

## Authentication & Authorization

### User Roles

- **User**: Can create orders and manage their order items
- **Admin**: Full access to all endpoints including user and product management

### Session Management

- Sessions automatically expire after 30 minutes of inactivity
- Session timeout is configurable via `SESSION_TIMEOUT` in config
- Users must be logged in to access protected endpoints

### Password Security

- Passwords are hashed using bcrypt with a configurable pepper
- Cost factor of 11 for bcrypt hashing

## Database Schema

### Users Table
- `id` (INT, Primary Key, Auto Increment)
- `name` (VARCHAR 255, Not Null)
- `email` (VARCHAR 255, Unique, Not Null)
- `password` (VARCHAR 255, Not Null)
- `role` (ENUM 'Admin'/'User', Default 'User')
- `created_at`, `updated_at` (DateTime)

### Products Table
- `id` (INT, Primary Key, Auto Increment)
- `name` (VARCHAR 255, Not Null)
- `image` (VARCHAR 500)
- `price` (DECIMAL 10,2, Not Null)
- `stock` (INT, Default 0)
- `created_at`, `updated_at` (DateTime)

### Orders Table
- `id` (INT, Primary Key, Auto Increment)
- `user_id` (INT, Foreign Key to Users)
- `total` (DECIMAL 10,2, Not Null)
- `created_at`, `updated_at` (DateTime)

### OrderItem Table
- `id` (INT, Primary Key, Auto Increment)
- `order_id` (INT, Foreign Key to Orders)
- `product_id` (INT, Foreign Key to Products)
- `amount` (INT, Not Null)
- `created_at`, `updated_at` (DateTime)
- Unique constraint on (order_id, product_id)

## Security Features

- Password hashing with bcrypt and pepper
- Session-based authentication
- Role-based access control
- Input validation and sanitization
- SQL injection prevention through prepared statements
- File upload validation
- Session timeout for security