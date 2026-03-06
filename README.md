# 📊 Sales Dashboard

ระบบ Web Application สำหรับวิเคราะห์และจัดการข้อมูลยอดขาย พัฒนาด้วย PHP, MySQL, JavaScript และ Chart.js

![Dashboard Preview](https://img.shields.io/badge/Status-Active-brightgreen) ![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php) ![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql) ![Chart.js](https://img.shields.io/badge/Chart.js-4.x-FF6384?logo=chartdotjs)

---

## ✨ ฟีเจอร์หลัก

- **📈 Dashboard** — แสดงยอดขายรวม, ยอดขายวันนี้, จำนวนออเดอร์ และสินค้าในระบบแบบ Real-time
- **📅 กราฟยอดขายรายวัน** — ดูแนวโน้มย้อนหลัง 14 วัน (Line Chart)
- **📆 กราฟยอดขายรายเดือน** — สรุปรายเดือนย้อนหลัง 6 เดือน (Bar Chart)
- **🏆 สินค้าขายดี** — จัดอันดับสินค้าตามยอดขาย (Doughnut Chart)
- **📦 จัดการสินค้า** — เพิ่ม / แก้ไข / ลบ / ค้นหาสินค้า
- **🛒 จัดการคำสั่งซื้อ** — สร้างออเดอร์, คำนวณยอดอัตโนมัติ, กรองตามวันที่
- **🔄 Auto Refresh** — อัพเดทข้อมูลอัตโนมัติทุก 30 วินาที

---

## 🛠️ เทคโนโลยีที่ใช้

| Layer    | Technology          |
|----------|---------------------|
| Frontend | HTML, CSS, JavaScript |
| Charts   | Chart.js 4.x        |
| Backend  | PHP 8.x (REST API)  |
| Database | MySQL 8.x           |
| Server   | Apache (XAMPP)      |

---

## 📁 โครงสร้างโปรเจกต์

```
sales-dashboard/
├── index.php                  # หน้าหลัก Dashboard
├── config/
│   └── db.php                 # การเชื่อมต่อ Database
├── api/
│   ├── sales.php              # GET ยอดขาย + ข้อมูลกราฟ
│   ├── products.php           # CRUD สินค้า (GET/POST/PUT/DELETE)
│   └── orders.php             # GET/POST คำสั่งซื้อ
├── assets/
│   ├── css/
│   │   └── style.css          # Dark theme UI
│   └── js/
│       └── app.js             # JavaScript หลัก
└── database/
    └── sales_dashboard.sql    # SQL สำหรับสร้าง Database
```

---

## 🚀 วิธีติดตั้งและใช้งาน

### ความต้องการของระบบ
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.x)

### ขั้นตอนการติดตั้ง

**1. Clone หรือดาวน์โหลดโปรเจกต์**
```bash
git clone https://github.com/thaweesakmos56-netizen/sales-dashboard.git
```

**2. วางโฟลเดอร์ใน htdocs**
```
C:\xampp\htdocs\sales-dashboard\
```

**3. เริ่มต้น XAMPP**
- เปิด XAMPP Control Panel
- Start **Apache** และ **MySQL**

**4. Import Database**

เปิด [http://localhost/phpmyadmin](http://localhost/phpmyadmin) แล้วรัน SQL นี้:
```sql
CREATE DATABASE IF NOT EXISTS sales_dashboard 
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
จากนั้น Import ไฟล์ `database/sales_dashboard.sql`

**5. เปิดใช้งาน**
```
http://localhost/sales-dashboard/
```

---

## 🔌 API Endpoints

| Method   | Endpoint                      | คำอธิบาย                          |
|----------|-------------------------------|-----------------------------------|
| `GET`    | `/api/sales.php?type=summary` | ยอดขายรวม, จำนวนออเดอร์, สินค้า  |
| `GET`    | `/api/sales.php?type=daily`   | ยอดขายรายวัน 14 วันย้อนหลัง      |
| `GET`    | `/api/sales.php?type=monthly` | ยอดขายรายเดือน 6 เดือนย้อนหลัง  |
| `GET`    | `/api/sales.php?type=top_products` | สินค้าขายดี Top 6           |
| `GET`    | `/api/products.php`           | ดึงรายการสินค้าทั้งหมด           |
| `POST`   | `/api/products.php`           | เพิ่มสินค้าใหม่                  |
| `PUT`    | `/api/products.php`           | แก้ไขสินค้า                      |
| `DELETE` | `/api/products.php?id={id}`   | ลบสินค้า                         |
| `GET`    | `/api/orders.php`             | ดึงรายการคำสั่งซื้อ              |
| `POST`   | `/api/orders.php`             | สร้างคำสั่งซื้อใหม่              |

---

## 🗄️ Database Schema

```sql
-- ตาราง products
CREATE TABLE products (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(150) NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    stock        INT NOT NULL DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตาราง orders
CREATE TABLE orders (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    product_id  INT NOT NULL,
    quantity    INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

---

## ⚙️ การตั้งค่า

แก้ไขไฟล์ `config/db.php` ให้ตรงกับ environment ของคุณ:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // MySQL username
define('DB_PASS', '');           // MySQL password (ค่าเริ่มต้น XAMPP = ว่าง)
define('DB_NAME', 'sales_dashboard');
```

---

## 📸 Screenshots

> Dashboard หน้าหลักแสดงกราฟและสถิติยอดขาย

---

## 👨‍💻 พัฒนาโดย

thaweesak seeangrat

---

## 📄 License

MIT License — ใช้สำหรับการศึกษา
