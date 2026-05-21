# 🍽️ Nouri'ZZZ — Restaurant Booking System

> A dynamic PHP/MySQL restaurant reservation and ordering web application.

---

## 🚀 Tech Stack
- **Backend:** PHP (procedural, no framework)
- **Frontend:** HTML5, Bootstrap 5.3, JavaScript
- **Database:** MySQL (via phpMyAdmin)
- **Server:** XAMPP (Apache + MySQL)

---

## ⚙️ Installation (XAMPP)

### Step 1 — Clone/Copy Files
Place the entire `restaurant-booking-system/` folder in:
```
C:\xampp\htdocs\
```

### Step 2 — Import the Database
1. Start XAMPP → Start **Apache** and **MySQL**
2. Open **phpMyAdmin** → http://localhost/phpmyadmin
3. Click **New** → name it `restaurant_db` → click Create
4. Click **Import** → choose `database.sql` → click Go

### Step 3 — Configure Database
Open `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // your MySQL user
define('DB_PASS', '');       // your MySQL password
define('DB_NAME', 'restaurant_db');
```

### Step 4 — Create Uploads Folder
Create an `uploads/` folder at the root of the project. It should already exist but ensure it has write permissions.

### Step 5 — Run
Open your browser: **http://localhost/restaurant-booking-system/**

---

## 🔑 Default Accounts
| Role  | Email                    | Password   |
|-------|--------------------------|------------|
| Admin | admin@restaurant.com     | password   |
| User  | john@example.com         | password   |

---

## 📁 Project Structure
```
restaurant-booking-system/
├── index.php               ← Homepage
├── menu.php                ← Menu page (public)
├── reserve.php             ← Reservation form (logged in)
├── order.php               ← Order food (logged in)
├── database.sql            ← Import this into MySQL
│
├── config/
│   └── database.php        ← DB connection
│
├── includes/
│   ├── auth.php            ← Session helpers
│   ├── header.php          ← Navbar + head
│   └── footer.php          ← Footer + scripts
│
├── auth/
│   ├── login.php           ← Login
│   ├── register.php        ← Registration
│   └── logout.php          ← Logout
│
├── user/
│   ├── profile.php         ← Edit profile + stats
│   ├── reservations.php    ← View/cancel reservations
│   └── orders.php          ← Order history
│
├── admin/
│   ├── dashboard.php       ← Stats + charts
│   ├── products.php        ← CRUD + image upload
│   ├── reservations.php    ← Confirm/cancel reservations
│   ├── orders.php          ← Update order status
│   └── users.php           ← List + delete users
│
└── uploads/                ← Product images (auto-created)
```

---

## ✅ Features
### User Side
- Register / Login / Logout with session management
- Browse menu by category
- Reserve a table (date, time, persons, notes)
- Order food with live cart summary
- View/cancel reservations
- Order history
- Edit profile & change password

### Admin Side
- Dashboard with 6 stat cards + Chart.js bar/line chart
- **Full CRUD** for products with image upload
- Toggle product availability
- Confirm / cancel reservations (filter by status)
- Update order status: pending → preparing → delivered → cancelled
- View all users, search by name/email, delete users

---

## 📊 Database Tables
| Table         | Key Fields                                        |
|---------------|---------------------------------------------------|
| `users`       | id, name, email, password, phone, role            |
| `categories`  | id, name                                          |
| `products`    | id, name, description, price, image, category_id  |
| `reservations`| id, user_id, date, time, persons, status          |
| `orders`      | id, user_id, total_price, status                  |
| `order_items` | id, order_id, product_id, quantity, unit_price    |

---

## 🎤 Presentation Script (French)

> "Notre projet est une application web dynamique de réservation et de commande pour un restaurant, développée en PHP, JavaScript, MySQL, HTML/CSS et Bootstrap."

**Côté utilisateur :** authentification, consultation du menu, réservation de table, commande de plats, profil.

**Côté administration :** gestion des produits avec upload d'image, gestion des réservations et commandes, tableau de bord avec statistiques.

**Base de données :** 6 tables reliées par clés étrangères, opérations CRUD complètes, gestion des sessions, upload de fichiers.
