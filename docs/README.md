# Dokumentasi API Sistem Kuota Ternak NTB

Folder ini berisi dokumentasi dan file-file untuk import ke Hoppscotch.

## File-file yang Tersedia

### 1. `hoppscotch-collection.json` ⭐ **DISARANKAN**
- Format: Hoppscotch Native Collection (v10)
- **Format native Hoppscotch, paling kompatibel!**
- Sudah termasuk struktur lengkap dengan auth, body, scripts
- Menggunakan `<<base_url>>` untuk environment variables
- Import sebagai **Hoppscotch Collection** di Hoppscotch

### 2. `postman-collection.json` 
- Format: Postman Collection v2.1.0
- Alternatif yang juga kompatibel dengan Hoppscotch
- Sudah termasuk environment variables
- Import sebagai **Postman Collection** di Hoppscotch

### 3. `openapi.yaml`
- Format: OpenAPI 3.0.3
- Import sebagai **OpenAPI** atau **Swagger** di Hoppscotch
- Dokumentasi lengkap dengan schema

### 4. `API_DOCUMENTATION.md`
- Dokumentasi lengkap dalam format Markdown
- Berisi semua endpoint, parameter, dan contoh response

### 5. `HOPPSCOTCH_IMPORT_GUIDE.md`
- Panduan lengkap cara import ke Hoppscotch
- Troubleshooting tips

## Cara Import ke Hoppscotch

### Metode 1: Hoppscotch Collection (Paling Mudah) ⭐

1. Buka [Hoppscotch](https://hoppscotch.io)
2. Klik **Collections** → **Import**
3. Pilih **Hoppscotch Collection**
4. Upload file `hoppscotch-collection.json`
5. Set environment variable `base_url`:
   - Klik ikon **Environment** (atau `Ctrl/Cmd + E`)
   - Buat environment baru atau edit yang ada
   - Tambahkan variable:
     - Key: `base_url`
     - Value: `http://localhost/api` (atau URL API Anda)
   - Aktifkan environment tersebut
   - Semua request akan menggunakan `<<base_url>>` dari environment

### Metode 2: Postman Collection

1. Buka [Hoppscotch](https://hoppscotch.io)
2. Klik **Collections** → **Import**
3. Pilih **Postman Collection**
4. Upload file `postman-collection.json`
5. Set environment variable `base_url` (sama seperti di atas)

### Metode 3: OpenAPI

1. Buka [Hoppscotch](https://hoppscotch.io)
2. Klik **Collections** → **Import**
3. Pilih **OpenAPI** atau **Swagger**
4. Upload file `openapi.yaml`
5. Set base URL di settings jika diperlukan

## Troubleshooting

### Error "format not recognized"

**Solusi:**
1. **Gunakan `hoppscotch-collection.json`** (format native Hoppscotch, paling kompatibel)
2. Pastikan file JSON/YAML valid (tidak ada syntax error)
3. Validasi OpenAPI di [Swagger Editor](https://editor.swagger.io/) sebelum import
4. Pastikan menggunakan versi terbaru Hoppscotch
5. Cek console browser untuk error detail

### Environment variable tidak berfungsi

**Solusi:**
1. Pastikan environment sudah diaktifkan (ada tanda centang)
2. Pastikan variable `base_url` sudah ditambahkan
3. Format variable di Hoppscotch adalah: `<<base_url>>` (dengan double angle brackets)
4. Untuk Postman Collection, format: `{{base_url}}` (dengan double curly braces)
5. Reload halaman Hoppscotch setelah set environment

### Endpoint tidak bisa diakses

**Solusi:**
1. Pastikan server Laravel sudah berjalan
2. Cek base URL di environment setting
3. Pastikan route API sudah terdaftar di `routes/api.php`
4. Cek CORS settings jika ada masalah dari browser

## Validasi File

### Validasi OpenAPI
- Buka [Swagger Editor](https://editor.swagger.io/)
- Paste konten `openapi.yaml`
- Cek apakah ada error

### Validasi JSON
- Gunakan [JSONLint](https://jsonlint.com/)
- Paste konten file JSON
- Cek apakah valid

## Catatan

- Semua endpoint menggunakan method **GET**
- Response format adalah **JSON**
- Tidak ada autentikasi saat ini (untuk produksi, disarankan menambahkan)
- Default tahun adalah tahun saat ini jika tidak ditentukan

