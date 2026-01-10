# Setup Localhost - Recruitment System

## Langkah 1: Setup Database MySQL

### Buka phpMyAdmin atau MySQL CLI

**Opsi A: Via phpMyAdmin**
1. Buka `http://localhost/phpmyadmin`
2. Klik tab "SQL"
3. Copy-paste isi file `database/schema_v2.sql`
4. Klik "Go" / "Execute"

**Opsi B: Via MySQL CLI**
```bash
mysql -u root -p < database/schema_v2.sql
```

**Opsi C: Via XAMPP/Laragon**
1. Start Apache dan MySQL
2. Buka phpMyAdmin
3. Import file `database/schema_v2.sql`

---

## Langkah 2: Konfigurasi .env

1. Copy file `.env.example` ke `.env`:
```bash
copy .env.example .env
```

2. Edit file `.env` sesuai konfigurasi MySQL kamu:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=recruitment
DB_USER=root
DB_PASS=

# Admin Password Hash (untuk login admin panel)
# Default password: admin123
ADMIN_PASSWORD_HASH=$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

# Application Settings
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/recruitment-php
```

**Catatan:**
- Jika MySQL kamu pakai password, isi `DB_PASS=password_kamu`
- Jika pakai XAMPP default, biasanya `DB_PASS=` (kosong)

---

## Langkah 3: Setup Web Server

### Opsi A: XAMPP
1. Copy folder `recruitment-php` ke `C:\xampp\htdocs\`
2. Akses via `http://localhost/recruitment-php/`

### Opsi B: Laragon
1. Copy folder `recruitment-php` ke `C:\laragon\www\`
2. Akses via `http://recruitment-php.test/` atau `http://localhost/recruitment-php/`

### Opsi C: PHP Built-in Server
```bash
cd recruitment-php
php -S localhost:8000
```
Akses via `http://localhost:8000/`

---

## Langkah 4: Verifikasi Setup

### Test Database Connection
Buka browser dan akses:
- Form aplikasi: `http://localhost/recruitment-php/`
- Admin panel: `http://localhost/recruitment-php/admin/`

### Login Admin
- Password default: `admin123`

### Test API
```bash
# Test submit endpoint
curl -X POST http://localhost/recruitment-php/api/submit.php

# Test applicants list
curl http://localhost/recruitment-php/api/applicants.php
```

---

## Langkah 5: Run Tests (Optional)

```bash
cd recruitment-php
php tests/run_all_tests.php
```

Hasil yang diharapkan: **343 tests passed**

---

## Troubleshooting

### Error: "Database connection failed"
1. Pastikan MySQL sudah running
2. Cek kredensial di `.env`
3. Pastikan database `recruitment` sudah dibuat

### Error: "Table doesn't exist"
1. Import ulang `database/schema_v2.sql`
2. Pastikan database yang dipilih adalah `recruitment`

### Error: "Access denied for user"
1. Cek username dan password MySQL
2. Pastikan user punya akses ke database `recruitment`

### Admin login tidak bisa
1. Pastikan `ADMIN_PASSWORD_HASH` di `.env` sudah benar
2. Atau generate hash baru:
```bash
php -r "echo password_hash('password_baru', PASSWORD_DEFAULT);"
```

### Upload CV gagal
1. Pastikan folder `uploads/` writable
2. Cek permission: `chmod 755 uploads/`

---

## Struktur URL

| URL | Fungsi |
|-----|--------|
| `/` | Form aplikasi kandidat |
| `/admin/` | Admin dashboard |
| `/admin/detail.php?id=XXX` | Detail kandidat |
| `/api/submit.php` | API submit aplikasi |
| `/api/applicants.php` | API list kandidat |
| `/api/assess.php` | API save HR assessment |

---

## Quick Start Commands

```bash
# 1. Setup database
mysql -u root -p < database/schema_v2.sql

# 2. Copy env
copy .env.example .env

# 3. Start PHP server
php -S localhost:8000

# 4. Open browser
start http://localhost:8000
```

Selesai! 🎉
