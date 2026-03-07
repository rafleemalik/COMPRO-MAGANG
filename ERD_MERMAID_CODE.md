# ERD Mermaid Code - Siap Copy Paste
## Sistem Penerimaan Magang - PT Telkom Indonesia

File ini berisi kode Mermaid yang sudah siap untuk di-copy dan digunakan di:
- Mermaid Live Editor (https://mermaid.live/)
- VS Code dengan Mermaid extension
- GitHub/GitLab markdown
- Dokumentasi lainnya

---

## Kode Mermaid ERD Lengkap

Copy seluruh kode di bawah ini ke Mermaid Live Editor untuk generate gambar:

```mermaid
erDiagram
    USERS ||--o{ INTERNSHIP_APPLICATIONS : "mengajukan"
    USERS ||--o{ ASSIGNMENTS : "menerima"
    USERS ||--o{ ASSIGNMENT_SUBMISSIONS : "mengumpulkan"
    USERS ||--o{ ATTENDANCES : "melakukan"
    USERS ||--o{ LOGBOOKS : "menulis"
    USERS ||--o{ CERTIFICATES : "menerima"
    USERS }o--|| DIVISIS : "terhubung_dengan"
    
    DIVISIS }o--|| SUB_DIREKTORATS : "bagian_dari"
    SUB_DIREKTORATS }o--|| DIREKTORATS : "bagian_dari"
    
    DIVISIONS ||--o{ DIVISION_MENTORS : "memiliki"
    DIVISION_MENTORS ||--o{ INTERNSHIP_APPLICATIONS : "membimbing"
    DIVISIONS ||--o{ INTERNSHIP_APPLICATIONS : "menampung"
    
    FIELD_OF_INTERESTS ||--o{ INTERNSHIP_APPLICATIONS : "dipilih"
    
    INTERNSHIP_APPLICATIONS ||--o{ CERTIFICATES : "menghasilkan"
    
    ASSIGNMENTS ||--o{ ASSIGNMENT_SUBMISSIONS : "memiliki"

    USERS {
        bigint id PK
        string username UK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string nim
        string university
        string major
        string phone
        string ktp_number
        string ktm
        enum role "admin, peserta, pembimbing"
        bigint divisi_id FK "nullable"
        string two_factor_secret "nullable"
        timestamp two_factor_verified_at "nullable"
        boolean tour_completed "default false"
        timestamp created_at
        timestamp updated_at
    }

    DIREKTORATS {
        bigint id PK
        string name
        timestamp created_at
        timestamp updated_at
    }

    SUB_DIREKTORATS {
        bigint id PK
        string name
        bigint direktorat_id FK
        timestamp created_at
        timestamp updated_at
    }

    DIVISIS {
        bigint id PK
        string name
        bigint sub_direktorat_id FK
        string vp
        string nippos
        timestamp created_at
        timestamp updated_at
    }

    DIVISIONS {
        bigint id PK
        string division_name
        string mentor_name
        string nik_number UK
        boolean is_active "default true"
        integer sort_order "default 0"
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at "nullable"
    }

    DIVISION_MENTORS {
        bigint id PK
        bigint division_id FK
        string mentor_name
        string nik_number
        timestamp created_at
        timestamp updated_at
    }

    FIELD_OF_INTERESTS {
        bigint id PK
        string name
        text description
        string icon "nullable"
        string color "default #EE2E24"
        boolean is_active "default true"
        integer sort_order "default 0"
        integer division_count "default 0"
        integer position_count "default 0"
        integer duration_months "default 6"
        timestamp created_at
        timestamp updated_at
    }

    INTERNSHIP_APPLICATIONS {
        bigint id PK
        bigint user_id FK
        bigint divisi_id FK "nullable"
        bigint division_admin_id FK "nullable"
        bigint division_mentor_id FK "nullable"
        bigint field_of_interest_id FK "nullable"
        enum status "pending, accepted, rejected, finished, postponed"
        string cover_letter_path "nullable"
        string ktm_path "nullable"
        string surat_permohonan_path "nullable"
        string cv_path "nullable"
        string good_behavior_path "nullable"
        string acceptance_letter_path "nullable"
        string assessment_report_path "nullable"
        string completion_letter_path "nullable"
        date start_date "nullable"
        date end_date "nullable"
        text notes "nullable"
        timestamp acceptance_letter_downloaded_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    ASSIGNMENTS {
        bigint id PK
        bigint user_id FK
        string title "nullable"
        enum assignment_type "tugas_harian, tugas_proyek"
        text description
        date deadline "nullable"
        date presentation_date "nullable"
        string file_path "nullable"
        string submission_file_path "nullable"
        integer grade "nullable"
        boolean is_revision "nullable"
        text feedback "nullable"
        timestamp submitted_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    ASSIGNMENT_SUBMISSIONS {
        bigint id PK
        bigint assignment_id FK
        bigint user_id FK
        string file_path
        timestamp submitted_at
        string keterangan "nullable"
        timestamp created_at
        timestamp updated_at
    }

    ATTENDANCES {
        bigint id PK
        bigint user_id FK
        date date
        enum status "Hadir, Absen, Terlambat"
        time check_in_time "nullable"
        string photo_path "nullable"
        text absence_reason "nullable"
        string absence_proof_path "nullable"
        timestamp created_at
        timestamp updated_at
    }

    LOGBOOKS {
        bigint id PK
        bigint user_id FK
        date date
        text content
        timestamp created_at
        timestamp updated_at
    }

    CERTIFICATES {
        bigint id PK
        bigint user_id FK
        bigint internship_application_id FK "nullable"
        string certificate_path
        string nomor_sertifikat "nullable"
        string predikat "nullable"
        timestamp issued_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    RULES {
        bigint id PK
        text content
        timestamp created_at
        timestamp updated_at
    }
```

---

## Cara Menggunakan

### 1. Convert ke PNG/SVG menggunakan Mermaid Live Editor

**Langkah-langkah:**
1. Buka browser dan kunjungi: **https://mermaid.live/**
2. Copy seluruh kode di atas (mulai dari `erDiagram` sampai `}` terakhir)
3. Paste ke editor di mermaid.live
4. Diagram akan otomatis ter-render
5. Klik tombol **"Actions"** (di pojok kanan atas)
6. Pilih:
   - **"Download PNG"** untuk format gambar PNG
   - **"Download SVG"** untuk format vektor (recommended)
   - **"Copy PNG"** untuk copy ke clipboard

**Keuntungan:**
- ✅ Gratis dan online
- ✅ Tidak perlu install software
- ✅ Export langsung ke PNG/SVG
- ✅ Bisa edit sebelum export

---

### 2. Convert menggunakan Mermaid CLI (Command Line)

**Install Mermaid CLI:**
```bash
npm install -g @mermaid-js/mermaid-cli
```

**Buat file `erd.mmd`:**
Copy kode di atas dan simpan sebagai `erd.mmd`

**Convert ke PNG:**
```bash
mmdc -i erd.mmd -o ERD_PENERIMAAN_MAGANG.png -b transparent -w 2400
```

**Convert ke SVG:**
```bash
mmdc -i erd.mmd -o ERD_PENERIMAAN_MAGANG.svg
```

**Convert ke PDF:**
```bash
mmdc -i erd.mmd -o ERD_PENERIMAAN_MAGANG.pdf
```

**Options:**
- `-i` = input file
- `-o` = output file
- `-b` = background color (transparent, white, dll)
- `-w` = width (default 1200)
- `-H` = height (optional)

---

### 3. Menggunakan VS Code

**Install Extension:**
1. Install extension **"Markdown Preview Mermaid Support"** di VS Code
2. Buat file markdown baru
3. Paste kode di atas dengan format:
   ` ```mermaid ` ... ` ``` `
4. Buka preview dengan `Ctrl+Shift+V` (Windows/Linux) atau `Cmd+Shift+V` (Mac)
5. Klik kanan pada diagram → "Save Image As"

---

### 4. Menggunakan GitHub/GitLab

Jika file ini di-upload ke GitHub/GitLab:
1. GitHub/GitLab akan otomatis render diagram Mermaid
2. Screenshot diagram untuk mendapatkan gambar
3. Atau gunakan GitHub API untuk export

---

## Tips untuk Hasil Terbaik

### 1. Resolusi Tinggi
Saat export, gunakan resolusi tinggi untuk kualitas terbaik:
- PNG: Minimal 2400px width
- SVG: Selalu vektor (infinite resolution)

### 2. Background Transparent
Gunakan background transparent jika ingin memasukkan ke dokumen:
```bash
mmdc -i erd.mmd -o erd.png -b transparent
```

### 3. Custom Styling (Advanced)
Untuk styling custom, buat file `config.json`:
```json
{
  "theme": "default",
  "themeVariables": {
    "primaryColor": "#dae8fc",
    "primaryTextColor": "#000",
    "primaryBorderColor": "#6c8ebf",
    "lineColor": "#333",
    "secondaryColor": "#d5e8d4",
    "tertiaryColor": "#fff2cc"
  }
}
```

Gunakan dengan:
```bash
mmdc -i erd.mmd -o erd.png -c config.json
```

---

## Troubleshooting

### Problem: Diagram terlalu besar/kecil
**Solusi:**
- Adjust width di Mermaid CLI: `-w 3000` untuk lebih besar
- Atau gunakan zoom di Mermaid Live Editor sebelum export

### Problem: Beberapa relasi tidak muncul
**Solusi:**
- Pastikan semua entity didefinisikan sebelum relasi
- Check syntax Mermaid (tidak ada typo)

### Problem: Teks terpotong
**Solusi:**
- Export sebagai SVG untuk text yang tetap editable
- Atau increase width saat export

---

## Links Berguna

- **Mermaid Live Editor**: https://mermaid.live/
- **Mermaid Documentation**: https://mermaid.js.org/
- **Mermaid CLI**: https://github.com/mermaid-js/mermaid-cli
- **Mermaid Examples**: https://mermaid.js.org/ecosystem/tutorials.html

---

**Selamat menggunakan!** 🚀

Jika ada pertanyaan atau issue, pastikan syntax Mermaid sudah benar dan semua entity sudah didefinisikan.

















