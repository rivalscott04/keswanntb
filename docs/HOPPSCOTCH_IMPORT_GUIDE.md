# Cara Import API ke Hoppscotch

Hoppscotch mendukung beberapa format untuk import API. Berikut cara mengimport API Sistem Kuota Ternak NTB:

## Opsi 1: Import Hoppscotch Collection (Recommended) ⭐

1. Buka [Hoppscotch](https://hoppscotch.io)
2. Klik menu **Collections** di sidebar
3. Klik tombol **Import** atau **+**
4. Pilih **Hoppscotch Collection**
5. Upload file `hoppscotch-collection.json` atau paste kontennya
6. Klik **Import**

**Format ini adalah format native Hoppscotch dan paling kompatibel!**

## Opsi 2: Import Postman Collection

1. Buka [Hoppscotch](https://hoppscotch.io)
2. Klik menu **Collections** di sidebar
3. Klik tombol **Import** atau **+**
4. Pilih **Postman Collection**
5. Upload file `postman-collection.json` atau paste kontennya
6. Klik **Import**

Format ini juga kompatibel dengan Hoppscotch dan mendukung environment variables.

## Opsi 3: Import OpenAPI Specification

1. Buka [Hoppscotch](https://hoppscotch.io)
2. Klik menu **Collections** di sidebar
3. Klik tombol **Import** atau **+**
4. Pilih **OpenAPI** atau **Swagger**
5. Upload file `openapi.yaml` atau paste kontennya
6. Klik **Import**

Setelah import, semua endpoint akan tersedia dengan dokumentasi lengkap.

Format ini sudah termasuk:
- Semua endpoint yang sudah dikonfigurasi
- Parameter query yang dapat diaktifkan/nonaktifkan
- Environment variables untuk development dan production
- Folder organization (Kuota dan Master Data)

## Konfigurasi Environment

Setelah import, pastikan untuk mengatur environment:

1. Klik ikon **Environment** di sidebar
2. Pilih environment yang sesuai:
   - **Development**: `http://localhost/api`
   - **Production**: `https://api.example.com/api` (ubah sesuai URL production Anda)
3. Aktifkan environment yang diinginkan

## Menggunakan Environment Variables

File `hoppscotch-collection.json` sudah menggunakan variable `<<base_url>>` yang akan otomatis terisi sesuai environment yang aktif.

**Cara set environment di Hoppscotch:**
1. Klik ikon **Environment** di sidebar (atau tekan `Ctrl/Cmd + E`)
2. Klik **+** untuk membuat environment baru
3. Tambahkan variable:
   - Key: `base_url`
   - Value: `http://localhost/api` (untuk development)
4. Aktifkan environment tersebut
5. Semua request akan otomatis menggunakan `<<base_url>>` dari environment yang aktif

**Catatan:** Format variable di Hoppscotch adalah `<<variable_name>>` (double angle brackets), bukan `{{variable_name}}`.

## Catatan

- Pastikan base URL sesuai dengan URL aplikasi Anda
- Untuk development, pastikan server Laravel sudah berjalan
- Semua endpoint menggunakan method GET
- Response format adalah JSON

## Troubleshooting

### Import tidak berhasil
- Pastikan format file valid (YAML atau JSON)
- Cek console browser untuk error message
- Pastikan versi Hoppscotch mendukung format yang digunakan

### Endpoint tidak bisa diakses
- Pastikan server Laravel sudah berjalan
- Cek base URL di environment setting
- Pastikan route API sudah terdaftar di `routes/api.php`

### Response error
- Pastikan database sudah terisi dengan data
- Cek log Laravel untuk error detail
- Pastikan semua model dan relasi sudah benar

