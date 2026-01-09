# RayCorp Recruitment System

Sistem recruitment untuk IT Staff Developer dengan assessment technical dan psikotes.

---

## 🚀 PANDUAN DEPLOYMENT

### Step 1: Clone Repository
```bash
git clone https://github.com/airaylabs/rkrtit-ryd.git
cd rkrtit-ryd
```

### Step 2: Upload ke Server
Upload semua file ke server hosting (cPanel, VPS, dll) ke folder:
```
/public_html/recruitment/
```
atau sesuai domain `https://recruitment.rayandra.com/`

### Step 3: Setup Database

1. **Buat database baru** di MySQL/phpMyAdmin dengan nama: `rekrutment`

2. **Import SQL file:**
   - Buka phpMyAdmin
   - Pilih database `rekrutment`
   - Klik tab **Import**
   - Upload file: `database/upload rekrutment ini.sql`
   - Klik **Go**

### Step 4: Konfigurasi Environment

1. **Copy file `.env.example` ke `.env`:**
```bash
cp .env.example .env
```

2. **Edit file `.env`** dengan kredensial database:
```env
DB_HOST=localhost
DB_NAME=rekrutment
DB_USER=username_database_kamu
DB_PASS=password_database_kamu

ADMIN_PASSWORD_HASH=
```

3. **Generate password hash untuk admin:**
   - Buka browser: `https://recruitment.rayandra.com/generate-hash.php?pass=PASSWORD_ADMIN_KAMU`
   - Copy hash yang muncul
   - Paste ke `ADMIN_PASSWORD_HASH=` di file `.env`
   - **Hapus file `generate-hash.php` setelah selesai!**

### Step 5: Set Permissions
```bash
chmod 755 -R /path/to/recruitment
chmod 777 uploads/
chmod 600 .env
```

### Step 6: Test
- Form: `https://recruitment.rayandra.com/`
- Admin: `https://recruitment.rayandra.com/admin/`

---

## 📁 Struktur File

```
recruitment-php/
├── admin/              # Admin dashboard
│   └── index.php       # Dashboard & detail modal
├── api/                # API endpoints
│   └── submit.php      # Handle form submission
├── assets/
│   ├── css/
│   │   ├── style.css       # Main styles
│   │   └── anti-cheat.css  # Anti-cheat styles
│   └── js/
│       ├── app.js          # Main JavaScript
│       └── anti-cheat.js   # Anti-cheat protection
├── config/
│   └── database.php    # Database connection
├── database/
│   ├── schema.sql                    # Schema (untuk referensi)
│   └── upload rekrutment ini.sql     # SQL UNTUK IMPORT
├── includes/
│   ├── Applicant.php       # Model applicant
│   ├── questions.php       # Soal-soal test
│   ├── scoring.php         # Logic scoring
│   └── ...
├── uploads/            # Folder CV uploads
├── index.php           # Form recruitment
├── .env.example        # Template environment
├── .env                # Environment (BUAT SENDIRI)
├── .htaccess           # Apache config
└── README.md           # File ini
```

---

## 📊 Struktur Assessment

### Technical Test (70%)
| Soal | Topik | Jumlah |
|------|-------|--------|
| 1 | PHP & Laravel | 2 soal |
| 2 | SQL & Git | 2 soal |
| 3 | Automation/n8n | 1 soal |
| **Total** | | **5 soal** |

### Psikotes (30%)
| Skenario | Aspek |
|----------|-------|
| 1 | Multi-tasking & Prioritas |
| 2 | Adaptability & Learning |
| 3 | Initiative & Problem Solving |

### Scoring
- **8-10**: LULUS → Lanjut interview
- **5-7**: REVIEW → Perlu evaluasi manual
- **<5**: TIDAK LULUS

---

## 🔒 Fitur Anti-Cheating
- ❌ Blokir copy/paste/cut
- ❌ Blokir screenshot (blur content)
- ❌ Blokir Developer Tools (F12)
- ❌ Blokir right-click
- ❌ Blokir View Source
- 👁️ Deteksi tab switching

---

## 🔄 Auto-Deploy dari GitHub

Jika server mendukung webhook/auto-deploy:

1. Setup webhook di GitHub → Settings → Webhooks
2. Payload URL: `https://recruitment.rayandra.com/deploy-hook.php`
3. Setiap push ke `main` branch akan auto-update

**Manual update:**
```bash
cd /path/to/recruitment
git pull origin main
```

---

## ⚠️ PENTING

1. **JANGAN commit file `.env`** - berisi kredensial sensitif
2. **Backup database** sebelum update
3. **Test di staging** sebelum production
4. **Hapus `generate-hash.php`** setelah setup password admin

---

## 📞 Kontak

Jika ada masalah, hubungi tim development.
