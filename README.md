# MBHaat.com - Digital Product Selling Platform

A fully responsive digital product selling platform built with PHP, MySQL, and Material Design Bootstrap.

## Features

### User Side (Frontend)
- **Responsive Landing Page** - Hero section, featured products, testimonials, FAQ
- **Product Listing** - Grid/scrollable cards with filters (category, price, popularity) and search
- **Product Details** - Title, description, screenshots, price, related products
- **Shopping Cart** - Add/remove/update quantities
- **Checkout** - Multiple payment methods, coupon codes, order summary
- **User Dashboard** - Profile management, order history, secure downloads
- **Authentication** - Login, registration, forgot password, profile editing
- **Dark/Light Mode** - Full theme support

### Admin Side (Backend)
- **Dashboard** - Sales stats, recent orders, top products
- **Product Management** - Add/edit/delete products, file uploads
- **Order Management** - View and update order statuses
- **User Management** - View users, block/unblock
- **Category Management** - Manage product categories
- **Coupon Management** - Create flat/percentage discounts
- **Reports & Analytics** - Daily/monthly sales reports
- **Settings** - Payment gateway config, tax settings, branding

### Payment Gateways (Configurable)
- bKash
- Nagad
- SSLCommerz
- Bank Transfer
- Visa Card
- Master Card

## Tech Stack

- **Frontend**: PHP, HTML5, CSS3, JavaScript
- **UI Framework**: Material Design Bootstrap (MDB UI Kit)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Icons**: Font Awesome 6

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled

### Step 1: Database Setup

```bash
# Create database in MySQL
mysql -u root -p
CREATE DATABASE mbhaat_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit

# Import schema
mysql -u root -p mbhaat_db < database/schema.sql
```

### Step 2: Configuration

1. Edit `includes/config.php` and update database credentials:
```php
$DB_HOST = 'localhost';
$DB_NAME = 'mbhaat_db';
$DB_USER = 'root';
DB_PASS = 'your_password';
```

2. Update `APP_URL` in config.php to match your server:
```php
define('APP_URL', 'http://localhost/M.B>Hatt');
```

### Step 3: Upload Directories

Ensure these directories are writable (755 permissions):
- `assets/uploads/products/`
- `assets/uploads/thumbnails/`
- `assets/uploads/screenshots/`

### Step 4: Access the Application

- **User Site**: `http://localhost/M.B>Hatt/`
- **Admin Panel**: Login with admin credentials

### Default Admin Account
```
Email: admin@mbhaat.com
Password: admin123
```

## Directory Structure

```
M.B>Hatt/
├── admin/              # Admin dashboard
│   ├── assets/         # Admin CSS/JS
│   ├── includes/       # Admin header/footer
│   ├── index.php       # Admin dashboard
│   ├── products.php    # Product management
│   ├── orders.php      # Order management
│   ├── users.php       # User management
│   ├── categories.php  # Category management
│   ├── coupons.php     # Coupon management
│   ├── reports.php     # Reports & analytics
│   └── settings.php    # Site settings
├── assets/             # Public assets
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   ├── images/         # Image assets
│   └── uploads/        # File uploads
├── database/
│   └── schema.sql      # Database schema
├── includes/
│   ├── config.php      # Configuration & helpers
│   ├── header.php      # Site header
│   └── footer.php      # Site footer
├── pages/              # Public pages
│   ├── login.php
│   ├── register.php
│   ├── forgot-password.php
│   ├── products.php
│   ├── product.php
│   ├── cart.php
│   ├── checkout.php
│   ├── faq.php
│   └── order-success.php
├── user/               # User dashboard
│   ├── dashboard.php
│   ├── orders.php
│   ├── order-detail.php
│   ├── downloads.php
│   ├── profile.php
│   ├── change-password.php
│   └── download.php
├── payment/            # Payment processing
├── secure_downloads/   # Protected downloads
├── index.php           # Homepage
└── .htaccess           # Security & URL rules
```

## Brand Colors

| Color    | Hex       | Usage              |
|----------|-----------|-------------------|
| Beige    | #F7F6E5   | Background        |
| Blue     | #76D2DB   | Accent / CTA      |
| Red      | #DA4848   | Highlight / Alert |
| Purple   | #36064D   | Primary / Brand   |

## Security Features

- Password hashing with bcrypt
- CSRF protection on forms
- Session-based authentication
- Secure file downloads with token-based access
- SQL injection prevention (prepared statements)
- XSS prevention (output escaping)
- File upload restrictions
- Role-based admin access

## Responsive Design

- Mobile-first approach with Bootstrap 5
- Material Design components
- Mobile bottom navigation (Home, Products, Cart, Profile)
- Desktop sidebar navigation
- Adaptive product grid/list views
- Touch-friendly buttons and cards

## License

This project is proprietary software for MBHaat.com.

## Support

For support, contact: support@mbhaat.com
