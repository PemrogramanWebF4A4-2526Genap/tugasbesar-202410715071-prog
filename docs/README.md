# UMKM Marketplace

Aplikasi marketplace UMKM berbasis PHP Native dan MySQL yang dibuat untuk memenuhi tugas besar mata kuliah Pemrograman Web Universitas Bhayangkara Jakarta Raya.

## Fitur Utama

### Pembeli

- Register & Login
- Browse produk
- Add to cart
- Checkout
- Upload bukti pembayaran
- Tracking pesanan
- Review & rating produk
- Notification system

### Penjual

- CRUD produk
- Upload gambar produk
- Kelola pesanan
- Update status pengiriman
- Dashboard penjualan
- Statistik penjualan

### Admin

- Dashboard admin
- Kelola user
- Verifikasi seller
- Kelola kategori
- Kelola seluruh pesanan
- Konfirmasi pembayaran
- Analytics marketplace

### System Automation

- Auto generate invoice
- Auto update stock
- Auto email notification
- Auto update order status
- Auto calculate shipping

---

# Teknologi

## Frontend

- HTML5
- Tailwind CSS v3
- JavaScript

## Backend

- PHP Native 8.x

## Database

- MySQL

## Library

- PHPMailer

---

# Struktur Folder

```bash
src/
├── admin/
├── assets/
├── auth/
├── buyer/
├── config/
├── helpers/
├── middleware/
├── seller/
├── uploads/
├── views/
```

---

# Cara Install

## 1. Clone / Extract Project

Pindahkan project ke folder:

```bash
htdocs/
```

atau

```bash
www/
```

---

## 2. Import Database

1. Buka phpMyAdmin
2. Buat database baru
3. Import file `.sql`

---

## 3. Konfigurasi Database

Edit file:

```bash
src/config/database.php
```

Sesuaikan:

```php
$host
$user
$password
$database
```

---

## 4. Install Composer

```bash
composer install
```

---

## 5. Jalankan Project

Contoh:

```bash
http://localhost/umkm_marketplace
```

---

# Akun Demo

## Admin

Email:

```text
admin@gmail.com
```

Password:

```text
admin
```

## Seller

Email:

```text
dika@gmail.com
```

Password:

```text
12345678
```

## Buyer

Email:

```text
abdika@gmail.com
```

Password:

```text
12345678
```

---

# Security Features

- Password Hashing
- Session Authentication
- SQL Injection Prevention
- XSS Prevention
- File Upload Validation
- Role Middleware Protection

---

# Responsive Design

Aplikasi mendukung:

- Desktop
- Tablet
- Mobile

---

# Developer

Nama:
Muhammad Abdika

Universitas Bhayangkara Jakarta Raya
Fakultas Ilmu Komputer
Program Studi Informatika
