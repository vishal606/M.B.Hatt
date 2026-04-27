# MBHaat.com — Digital Product Selling Platform
**Version:** 1.0 | **Stack:** PHP 8+ · MySQL · Bootstrap (mdbootstrap)  
**Brand:** YBT Digital | **Palette:** Beige · Blue · Red · Purple Vintage

---

## 🚀 Quick Install

### Option A — Install Wizard (Recommended)
1. Upload the `mbhaat/` folder to your web server (e.g. `public_html/mbhaat/`)
2. Visit `http://yourdomain.com/mbhaat/install.php`
3. Follow the 3-step wizard
4. **Delete `install.php`** after installation

### Option B — Manual Setup
1. Create a MySQL database named `mbhaat`
2. Import `database/schema.sql`
3. Edit `src/config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'mbhaat');
   define('DB_USER', 'your_user');
   define('DB_PASS', 'your_password');
   define('APP_URL', 'http://yourdomain.com/mbhaat');
   ```
4. Set `DEBUG_MODE` to `false` in production
5. Ensure these directories are **writable** (chmod 755):
   - `uploads/`
   - `uploads/products/`
   - `uploads/avatars/`
   - `uploads/screenshots/`

---

## 🔑 Default Admin Credentials
- **URL:** `/admin/login.php`
- **Email:** `admin@mbhaat.com`
- **Password:** `password`
> ⚠️ Change these immediately after first login via Admin → Settings

---

## 📁 Project Structure
```
mbhaat/
├── index.php              ← Homepage
├── products.php           ← Product listing with filters
├── product.php            ← Product detail page
├── cart.php               ← Shopping cart (AJAX)
├── checkout.php           ← Checkout + coupon + payment
├── orders.php             ← Order history + secure download
├── dashboard.php          ← User dashboard
├── profile.php            ← Edit profile + password
├── login.php              ← User login
├── register.php           ← User registration
├── forgot-password.php    ← Password reset
├── logout.php
├── contact.php            ← Support ticket form
├── tickets.php            ← User ticket view/reply
├── faq.php                ← FAQ accordion
├── search.php             ← Product search
├── download.php           ← Secure file download handler
├── install.php            ← One-time setup wizard
├── maintenance.php        ← Maintenance mode page
├── .htaccess              ← Security rules
│
├── admin/
│   ├── login.php          ← Admin login
│   ├── logout.php
│   ├── dashboard.php      ← Stats & recent activity
│   ├── products.php       ← Add/Edit/Delete products
│   ├── orders.php         ← View & update orders
│   ├── users.php          ← Manage users (block/unblock)
│   ├── coupons.php        ← Coupon CRUD
│   ├── tickets.php        ← Support ticket management
│   ├── faqs.php           ← FAQ management
│   ├── categories.php     ← Category management
│   ├── testimonials.php   ← Testimonial management
│   ├── reports.php        ← Sales reports & analytics
│   ├── settings.php       ← All site settings (payment, email, branding)
│   ├── ajax.php           ← Admin AJAX handler
│   └── partials/
│       ├── header.php     ← Admin layout header + sidebar
│       └── footer.php     ← Admin layout footer
│
├── src/
│   ├── init.php           ← Bootstrap (loads config, DB, helpers)
│   ├── config/
│   │   ├── config.php     ← App configuration constants
│   │   └── database.php   ← PDO database class
│   ├── helpers/
│   │   └── functions.php  ← All helper functions
│   └── views/
│       └── layouts/
│           ├── header.php ← Site header + navbar + mobile appbar
│           └── footer.php ← Site footer + bottom nav (mobile)
│
├── assets/
│   ├── css/
│   │   ├── style.css      ← Main stylesheet (brand colors, responsive)
│   │   └── admin.css      ← Admin panel extras
│   ├── js/
│   │   └── app.js         ← Cart AJAX, dark mode, UI interactions
│   └── images/
│       └── logo.png       ← MBHaat logo
│
├── uploads/
│   ├── products/          ← Uploaded product files (protected)
│   ├── screenshots/       ← Product preview images
│   ├── avatars/           ← User profile photos
│   └── .htaccess          ← Blocks PHP execution in uploads
│
└── database/
    └── schema.sql         ← Full database schema + seed data
```

---

## 💳 Payment Gateways
Configured via **Admin → Settings → Payment**:

| Gateway     | Configuration |
|-------------|---------------|
| **bKash**   | Merchant mobile number |
| **Nagad**   | Merchant mobile number |
| **SSLCommerz** | Store ID + Merchant ID + Password |
| **Bank Transfer** | Bank name + Account + Routing |
| **Visa / Mastercard** | Processed via SSLCommerz |

> Current implementation uses a **manual payment flow**: customer pays externally, enters transaction ID, admin verifies and updates order status.

---

## 📱 Responsive Design
- **Mobile** (< 768px): Native app experience with AppBar + Bottom Navigation (4 tabs)
- **Tablet** (768–1024px): Adaptive grid layout
- **Desktop** (> 1024px): Full navbar, sidebar, 3-4 column product grid

### Dark Mode
- Toggled via 🌙 button in navbar / AppBar
- Persists via localStorage + cookie
- Full dark palette applied to all components

---

## 🔒 Security Features
- CSRF tokens on all forms
- Password hashing with `bcrypt`
- Prepared statements (PDO) — SQL injection safe
- Secure download tokens (64-char random hex)
- Download limits & expiry dates
- PHP execution blocked in uploads via `.htaccess`
- Session-based authentication
- Admin role separation (Super Admin / Editor)
- User blocking capability

---

## ⚙️ Admin Panel Features
- **Dashboard** — Revenue, orders, users, top products
- **Products** — Add/edit/delete, file upload, screenshots, categories, status toggle
- **Orders** — View all orders, update payment/order status
- **Users** — List users, view purchase history, block/unblock
- **Coupons** — Flat & percentage discounts, expiry, usage limits
- **Support Tickets** — View/reply to user messages, close tickets
- **FAQs** — Add/edit/delete/reorder FAQ entries
- **Categories** — Manage product categories
- **Testimonials** — Customer review management
- **Reports** — Revenue by period, top products, by payment method, daily sales
- **Settings** — Payment gateways, tax, downloads, SMTP, branding, maintenance mode

---

## 🛠️ PHP Requirements
- PHP **8.0+** (uses `match`, `named args`, `enum`)
- Extensions: `pdo`, `pdo_mysql`, `fileinfo`, `mbstring`
- Apache with `mod_rewrite` (or Nginx equivalent)

---

## 📧 Email Setup
Configure SMTP in **Admin → Settings → Email**:
- Works with Gmail, Outlook, SendGrid, Mailgun
- For Gmail: use App Password (not your regular password)
- To enable actual email sending, integrate **PHPMailer** or **SwiftMailer**

> The current codebase includes SMTP settings storage. Email sending implementation requires adding PHPMailer: `composer require phpmailer/phpmailer`

---

## 🔧 Customization
- **Brand colors**: Edit CSS variables in `assets/css/style.css` (`:root`)
- **Logo**: Upload via Admin → Settings → Branding
- **Currency**: Set symbol & code in Admin → Settings → General
- **Add payment gateways**: Extend `checkout.php` with gateway SDK calls

---

## 📄 License
Built for — MBHaat.com  
For private/commercial use. All rights reserved.
