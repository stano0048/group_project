# KarU Marketplace

**Campus Student Marketplace System**
Karatina University — School of Computing and Informatics
Web Design & Development — Group Project

---

## Project Description

KarU Marketplace is a web-based e-commerce platform where Karatina University students can buy and sell items within the campus community. Unlike normal e-commerce websites, this system is designed specifically for students. Sellers post items, buyers place orders, and payment happens only after physical delivery.

---

## Tech Stack

- **Backend:** PHP 8+
- **Database:** MySQL
- **Frontend:** Vanilla HTML, CSS, JavaScript (no frameworks)
- **Fonts:** Plus Jakarta Sans, DM Mono (Google Fonts)
- **File Uploads:** PHP move_uploaded_file()

---

## Folder Structure

```
karu-marketplace/
├── index.php
├── products.php
├── product-details.php
├── cart.php
├── checkout.php
├── login.php
├── register.php
├── logout.php
├── safety.php
├── about.php
├── contact.php
├── terms.php
├── members.php
├── database.sql
├── .htaccess
│
├── admin/
│   ├── dashboard.php
│   ├── users.php
│   ├── products.php
│   ├── orders.php
│   ├── seller-applications.php
│   ├── categories.php
│   ├── reports.php
│   ├── feedback.php
│   ├── cms.php
│   └── settings.php
│
├── moderator/
│   ├── dashboard.php
│   ├── add-user.php
│   ├── users.php
│   ├── seller-applications.php
│   ├── products.php
│   └── reports.php
│
├── seller/
│   ├── dashboard.php
│   ├── post-product.php
│   ├── my-products.php
│   ├── orders.php
│   ├── sold-items.php
│   ├── feedback.php
│   └── profile.php
│
├── user/
│   ├── dashboard.php
│   ├── my-orders.php
│   ├── bought-items.php
│   ├── apply-seller.php
│   ├── feedback.php
│   └── profile.php
│
├── includes/
│   ├── db.php
│   ├── auth.php
│   ├── header.php
│   └── footer.php
│
├── ajax/
│   ├── add-to-cart.php
│   └── cart-count.php
│
└── assets/
    ├── css/main.css
    ├── js/main.js
    ├── images/
    └── uploads/
        ├── products/
        └── applications/
```

---

## Installation & Setup

### Requirements
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- XAMPP, WAMP, LAMP, or any PHP server

### Step 1 — Clone or Extract Files
Place the project folder in your server's web root:
- XAMPP: `C:/xampp/htdocs/karu-marketplace/`
- WAMP: `C:/wamp/www/karu-marketplace/`
- Linux: `/var/www/html/karu-marketplace/`

### Step 2 — Create the Database
1. Open phpMyAdmin or MySQL CLI
2. Run the SQL file:
```sql
SOURCE /path/to/karu-marketplace/database.sql;
```
Or import `database.sql` via phpMyAdmin Import tab.

### Step 3 — Configure Database Connection
Edit `includes/db.php` and update these constants:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'karu_marketplace');
```

### Step 4 — Create Upload Directories
Ensure these folders exist and are writable:
```
assets/uploads/products/
assets/uploads/applications/
```
On Linux run:
```bash
chmod -R 755 assets/uploads/
```

### Step 5 — Access the Website
Open your browser and go to:
```
http://localhost/karu-marketplace/
```

---

## Default Admin Login

| Field    | Value               |
|----------|---------------------|
| Email    |     |
| Password |     |

**Important:** Change the admin password immediately after first login via Admin > Settings.

---

## User Roles

| Role      | Description                                                      |
|-----------|------------------------------------------------------------------|
| Admin     | Full control of the entire system                                |
| Moderator | Can approve sellers, hide products, manage reports               |
| Seller    | Approved student who can post and manage products                |
| User      | Normal student buyer who can browse, cart, and order             |

---

## Key Features

- Student registration with admission number
- Role-based dashboards (Admin, Moderator, Seller, Buyer)
- Seller verification and approval system
- Product posting with multiple image uploads
- Product search and category filtering
- Cart with negotiable price offer system
- Order tracking with status stages
- Pay-after-delivery model enforced site-wide
- Buyer feedback system (positive, neutral, negative)
- Product reporting system
- Admin CMS for About, Contact, Safety, Terms pages
- Activity logging
- Notification system
- Wishlist / Save item feature
- Safety guidelines and marketplace rules

---

## Payment Model

KarU Marketplace does not process online payments.
All payments are made in person after physical delivery.
This protects students from scams and fake sellers.

---


Karatina University — Web Design & Development — Group Project — 2026
