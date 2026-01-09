# 📧 n8n Email Notification Setup Guide

## 🔗 Koneksi PHP ke n8n

### 1. Konfigurasi Webhook URL

Edit file `.env` dan isi `N8N_WEBHOOK_URL`:

```properties
# Production mode
N8N_WEBHOOK_URL=https://your-n8n-domain.com/webhook/raycorp-recruitment

# Test mode (untuk testing)
N8N_WEBHOOK_URL=https://your-n8n-domain.com/webhook-test/raycorp-recruitment
```

### 2. Data yang Dikirim PHP ke n8n

Setiap kali ada submission, PHP akan mengirim data berikut:

```json
{
  "event": "new_application",
  "timestamp": "2026-01-09T10:30:00+07:00",
  "applicant": {
    "id": 123,
    "nama": "John Doe",
    "email": "john@example.com",
    "whatsapp": "081234567890",
    "status": "LULUS",
    "statusLabel": "Lulus"
  },
  "scores": {
    "overall": 8.5,
    "technical": 9.0,
    "psikotes": 7.5
  },
  "timer": {
    "personal": 120,
    "technical": 600,
    "psikotes": 480,
    "total": 1200
  },
  "details": {
    "technicalCorrect": 4,
    "technicalTotal": 5,
    "recommendation": "...",
    "technicalContribution": 6.3,
    "psikotesContribution": 2.25
  }
}
```

---

## 🔧 Setup n8n Workflow

### Langkah 1: Import Workflow

1. Buka n8n dashboard
2. Klik **Import from File**
3. Pilih file `n8n-workflow-v2.json`

### Langkah 2: Tambahkan Send Email Node

Karena HTML template terlalu panjang untuk JSON, tambahkan manual:

1. Tambahkan node **Send Email** setelah **Transform Data**
2. Konfigurasi:
   - **From Email**: `it@rayandra.com`
   - **To Email**: `it@rayandra.com`
   - **Subject**: 
     ```
     =[IT-RCT] {{ $json.kategoriEmoji }} {{ $json.kategori }} | {{ $json.nama }} | Score: {{ $json.overallScore }}/10 | {{ $json.priority }}
     ```
   - **HTML**: Copy seluruh isi file `n8n-email-template.html`

3. Setup SMTP credentials

### Langkah 3: Aktifkan Workflow

1. Klik toggle **Active** di pojok kanan atas
2. Copy webhook URL yang muncul
3. Paste ke file `.env` di PHP

---

## 📊 Fitur Email Template v2

### Status Kategori
| Score | Kategori | Emoji | Warna | Priority |
|-------|----------|-------|-------|----------|
| 8-10 | LULUS | ✅ | Hijau | HIGH |
| 5-7.9 | REVIEW | ⚠️ | Kuning | MEDIUM |
| 0-4.9 | BELUM LULUS | ❌ | Merah | LOW |

### Speed Evaluation
| Waktu | Evaluasi | Icon |
|-------|----------|------|
| < 15 menit | Sangat Cepat | 🚀 |
| 15-25 menit | Cepat & Ideal | ✅ |
| 25-40 menit | Normal | ⏱️ |
| > 40 menit | Lambat | 🐢 |

### Score Analysis
- **Technical**: Excellent (8+), Good (6+), Fair (4+), Needs Improvement (<4)
- **Psikotes**: Excellent (8+), Good (6+), Fair (4+), Concern (<4)

### Email Sections
1. **Header** - Nama, kontak, score besar dengan badge status
2. **Score Cards** - Technical & Psikotes dengan breakdown detail
3. **Timer Section** - Waktu per section dengan speed evaluation
4. **Recommendation** - Rekomendasi tindak lanjut berdasarkan score
5. **Action Buttons** - WhatsApp, Email, Admin Panel
6. **Footer** - Legend warna & timestamp

---

## 🧪 Testing

### Test dengan cURL

```bash
curl -X POST https://your-n8n-domain.com/webhook-test/raycorp-recruitment \
  -H "Content-Type: application/json" \
  -d '{
    "applicant": {
      "id": 999,
      "nama": "Test User",
      "email": "test@example.com",
      "whatsapp": "081234567890",
      "status": "LULUS",
      "statusLabel": "Lulus"
    },
    "scores": {
      "overall": 8.5,
      "technical": 9.0,
      "psikotes": 7.5
    },
    "timer": {
      "personal": 120,
      "technical": 600,
      "psikotes": 480,
      "total": 1200
    },
    "details": {
      "technicalCorrect": 4,
      "technicalTotal": 5,
      "technicalContribution": 6.3,
      "psikotesContribution": 2.25
    }
  }'
```

---

## 📁 File Structure

```
recruitment-php/
├── .env                      # Konfigurasi webhook URL
├── api/
│   └── submit.php            # Mengirim notifikasi ke n8n
├── n8n-workflow-v2.json      # Workflow untuk import ke n8n
├── n8n-email-template.html   # HTML template untuk email
└── N8N-SETUP-GUIDE.md        # Panduan ini
```

---

## ⚠️ Troubleshooting

### Email tidak terkirim
1. Cek `N8N_WEBHOOK_URL` di `.env` sudah benar
2. Pastikan workflow n8n sudah **Active**
3. Cek SMTP credentials di n8n

### Data tidak lengkap di email
1. Pastikan PHP mengirim semua field yang diperlukan
2. Cek Transform Data node di n8n untuk error

### WhatsApp link tidak berfungsi
- Format nomor harus valid (08xxx atau 628xxx)
- Link akan otomatis diformat ke format internasional (62xxx)
