# Employee Monitoring System

## Deskripsi Proyek

Sistem Employee Monitoring adalah aplikasi untuk memantau aktivitas karyawan di browser menggunakan ekstensi Chrome yang terintegrasi dengan backend Laravel. Sistem ini mencatat aktivitas browsing, keystroke, dan interaksi pengguna untuk tujuan evaluasi produktivitas karyawan.

## Arsitektur Sistem

### Komponen Utama

1. **Ekstensi Chrome** (`EmployeeTrack/`)
   - Frontend monitoring yang berjalan di browser
   - Mengumpulkan data aktivitas pengguna secara real-time
   - Menyimpan data lokal dan mengirim ke server

2. **Laravel API** (`employee-track-laravel/`)
   - Backend server untuk menerima dan menyimpan data
   - Sistem autentikasi menggunakan Laravel Sanctum
   - Admin panel menggunakan Filament dengan dashboard widgets

### Logika Sistem

#### 1. Pengumpulan Data (Chrome Extension)
- **Content Script** (`content.js`) berjalan di setiap halaman web
- Menangkap keystroke, klik mouse, scroll, dan form submission
- Buffer data sebelum dikirim untuk efisiensi
- Filter data sensitif (password, kartu kredit, dll)

#### 2. Pemrosesan Data (Background Script)
- **Background Script** (`background.js`) mengelola komunikasi
- Menerima data dari content script
- Menambahkan metadata (timestamp, URL, domain)
- Mengirim ke server dengan autentikasi token
- Menyimpan backup lokal (maksimal 1000 entri)

#### 3. Penyimpanan Data (Laravel API)
- **API Controller** menerima data dari ekstensi
- Validasi data menggunakan Laravel Validator
- Identifikasi pengguna melalui Sanctum token
- Simpan ke database dengan timestamp yang tepat

## Struktur File

### Ekstensi Chrome (`EmployeeTrack/`)

```
EmployeeTrack/
├── manifest.json          # Konfigurasi ekstensi Chrome
├── background.js          # Service worker untuk mengelola data
├── content.js             # Script yang berjalan di setiap halaman
├── popup.html             # Interface popup ekstensi
├── popup.js               # Logic untuk popup
├── config.html            # Halaman konfigurasi
├── config.js              # Logic konfigurasi
├── logs.html              # Halaman untuk melihat log lokal
├── logs.js                # Logic untuk menampilkan log
└── letter-*.png           # Icon ekstensi
```

#### File Utama:

- **`manifest.json`**: Konfigurasi ekstensi, permissions, dan entry points
- **`background.js`**: Mengelola komunikasi, penyimpanan, dan pengiriman data
- **`content.js`**: Monitoring aktivitas di halaman web (keylogger, activity tracker)
- **`popup.js`**: Interface status dan navigasi ekstensi
- **`config.js`**: Pengaturan server URL dan auth token
- **`logs.js`**: Tampilan dan filter log aktivitas lokal

### Laravel API (`employee-track-laravel/`)

```
employee-track-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   └── EmployeeLogController.php    # API endpoint untuk log
│   │   └── Responses/
│   │       └── LoginWithTokenResponse.php   # Custom login response dengan token
│   ├── Models/
│   │   ├── User.php                         # Model pengguna
│   │   └── EmployeeLog.php                  # Model log aktivitas
│   ├── Filament/
│   │   ├── Resources/
│   │   │   └── EmployeeLogResource.php      # Admin interface untuk log
│   │   └── Widgets/                         # Dashboard widgets
│   │       ├── EmployeeLogStatsWidget.php   # Widget statistik aktivitas
│   │       ├── DailyActivityChart.php       # Chart aktivitas harian
│   │       ├── TokenWidget.php              # Widget token pengguna
│   │       ├── TopDomainsWidget.php         # Widget top domain
│   │       └── UserTokensWidget.php         # Widget manajemen user & token
│   └── Providers/
│       └── Filament/
│           └── AdminPanelProvider.php       # Konfigurasi admin panel
├── resources/
│   └── views/
│       └── filament/
│           └── widgets/                     # View files untuk widgets
│               ├── token-widget.blade.php  # Template widget token
│               ├── user-tokens-modal.blade.php # Modal detail token user
│               └── no-tokens.blade.php     # Template jika tidak ada token
├── routes/
│   └── api.php                              # API routes
└── config/
    └── sanctum.php                          # Konfigurasi autentikasi
```

#### File Utama:

**Controllers & API:**
- **`EmployeeLogController.php`**: API endpoint untuk menerima data dari ekstensi
- **`LoginWithTokenResponse.php`**: Generate dan return token Sanctum saat login

**Models:**
- **`EmployeeLog.php`**: Model database untuk menyimpan log aktivitas
- **`User.php`**: Model pengguna dengan relasi ke token Sanctum

**Filament Resources:**
- **`EmployeeLogResource.php`**: Interface admin Filament untuk melihat dan mengelola log
- **`AdminPanelProvider.php`**: Konfigurasi admin panel dan registrasi widgets

**Dashboard Widgets:**
- **`EmployeeLogStatsWidget.php`**: Widget statistik aktivitas harian (total logs, website visits, keystrokes, dll)
- **`DailyActivityChart.php`**: Chart tren aktivitas 7 hari terakhir dengan multiple datasets
- **`TokenWidget.php`**: Widget untuk menampilkan dan menyalin token API pengguna
- **`TopDomainsWidget.php`**: Widget top 10 domain yang dikunjungi hari ini
- **`UserTokensWidget.php`**: Widget manajemen user dan token API dengan modal detail

**Widget Views:**
- **`token-widget.blade.php`**: Template untuk menampilkan token dengan fitur copy
- **`user-tokens-modal.blade.php`**: Modal untuk menampilkan detail semua token user
- **`no-tokens.blade.php`**: Template ketika user tidak memiliki token

## Fitur Dashboard Admin

### Widget Dashboard:
1. **📊 Employee Log Stats**: Statistik aktivitas harian dengan perbandingan hari sebelumnya
2. **📈 Daily Activity Chart**: Grafik tren aktivitas 7 hari terakhir (line chart)
3. **🎫 Token Widget**: Menampilkan token API pengguna dengan fitur copy
4. **🏆 Top Domains**: Top 10 domain yang paling sering dikunjungi
5. **👥 User Tokens Management**: Manajemen user dan token API mereka

### Fitur Widget:
- **Real-time Statistics**: Data statistik yang update secara real-time
- **Interactive Charts**: Chart interaktif dengan multiple datasets
- **Token Management**: Copy token, lihat detail, dan revoke token
- **User Management**: Melihat semua user dan token mereka
- **Responsive Design**: Layout yang responsif untuk berbagai ukuran layar

## Cara Instalasi dan Penggunaan

### 1. Setup Laravel API

#### Instalasi Dependencies
```bash
cd employee-track-laravel
composer install
npm install
```

#### Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```

#### Setup Database
```bash
# Edit .env untuk konfigurasi database
php artisan migrate
php artisan db:seed
```

#### Jalankan Server
```bash
php artisan serve --port=8001
```

#### Akses Admin Panel
- URL: `http://localhost:8001/admin`
- Buat user admin: `php artisan make:filament-user`
- Dashboard akan menampilkan 5 widget utama untuk monitoring

### 2. Setup Chrome Extension

#### Install Extension
1. Buka Chrome dan akses `chrome://extensions/`
2. Aktifkan "Developer mode"
3. Klik "Load unpacked"
4. Pilih folder `EmployeeTrack/`

#### Konfigurasi Extension
1. Klik icon ekstensi di toolbar
2. Klik "Configuration"
3. Isi:
   - **Server URL**: `http://localhost:8001/api` 
   - **Auth Token**: Token dari widget Token di admin panel
   - Centang persetujuan monitoring
4. Klik "Save Configuration"

### 3. Cara Penggunaan

#### Untuk Admin:
1. Login ke admin panel Laravel
2. Token akan otomatis dibuat dan ditampilkan di Token Widget
3. Copy token dari widget untuk konfigurasi ekstensi
4. Monitor aktivitas melalui dashboard widgets dan menu "Employee Logs"
5. Kelola user dan token melalui User Tokens Widget

#### Untuk Karyawan:
1. Install ekstensi Chrome
2. Konfigurasikan dengan token yang diberikan admin
3. Ekstensi akan otomatis memantau aktivitas
4. Data tersimpan lokal dan dikirim ke server

#### Melihat Log:
- **Dashboard**: Lihat statistik dan chart di halaman utama admin
- **Detail Logs**: Akses menu "Employee Logs" untuk detail lengkap
- **Lokal**: Klik "View Logs" di popup ekstensi

## Fitur Monitoring

### Data yang Dikumpulkan:
1. **Website Visits**: URL, title, domain, timestamp
2. **Keystrokes**: Teks yang diketik (dengan filter sensitif)
3. **Activities**: Klik, scroll, focus, blur, form submission

### Fitur Keamanan:
- Filter field sensitif (password, kartu kredit)
- Enkripsi komunikasi HTTPS
- Autentikasi token Sanctum
- Penyimpanan lokal terbatas (1000 entri)
- Token management dengan revoke capability

### Fitur Admin:
- **Dashboard Widgets**: 5 widget untuk monitoring real-time
- **Filter dan Pencarian**: Filter log berdasarkan tipe, tanggal, user
- **User Management**: Kelola user dan token API
- **Export Data**: Export log untuk analisis lebih lanjut

## API Endpoints

### Authentication
- `POST /api/login` - Login filament dan dapatkan token (dengan custom response)

### Logging
- `POST /api/log` - Kirim data aktivitas
  - Headers: `Authorization: Bearer {token}`
  - Body: JSON dengan data aktivitas

### Contoh Request:
```bash

# Kirim log aktivitas
curl -X POST http://localhost:8001/api/log \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {your-token}" \
  -d '{
    "type": "keystroke",
    "content": "hello world",
    "url": "https://example.com",
    "timestamp": "2024-01-01T12:00:00Z"
  }'

```

## Teknologi yang Digunakan

### Backend:
- **Laravel 12**: Framework PHP untuk API
- **Filament 3**: Admin panel dengan dashboard widgets
- **Laravel Sanctum**: Autentikasi API token
- **MySQL/PostgreSQL**: Database untuk menyimpan log

### Frontend:
- **Vanilla JavaScript**: Content script dan background script
- **Chart.js**: Library untuk dashboard charts

### Fitur Lainnya:
- **Real-time Dashboard**: Widget yang update secara real-time
- **Responsive Design**: Interface yang responsif
- **Token Management**: Sistem manajemen token yang aman
- **Data Visualization**: Chart yang informatif

---

**Catatan**: Sistem ini dirancang untuk tujuan monitoring produktivitas karyawan. Pastikan penggunaan sesuai dengan kebijakan perusahaan dan regulasi privasi yang berlaku.
Dashboard admin menyediakan interface yang user-friendly untuk monitoring dan analisis aktivitas. Dihalaman dashboard siapapun hanya dapat melihat dan mengakses data aktivitas miliknya sendiri.

Nantikan update fitur-fitur yang akan datang. Terima kasih!