# 📋 Skenario Testing - Sistem Pengajuan Ternak NTB

Dokumen ini berisi semua skenario testing yang tersedia dalam sistem pengajuan ternak NTB.

## 🎯 **Jenis Pengajuan yang Tersedia**

### 1. **Antar Kab/Kota NTB**
- **Deskripsi**: Pengajuan perpindahan ternak antar kabupaten/kota dalam wilayah NTB
- **Workflow**: Pengusaha → Disnak Kab/Kota Asal → Disnak Kab/Kota Tujuan → Disnak Provinsi → DPMPTSP
- **Kuota**: Menggunakan kuota pemasukan (tujuan) dan pengeluaran (asal)

### 2. **Pengeluaran**
- **Deskripsi**: Pengajuan pengeluaran ternak dari NTB ke luar provinsi
- **Workflow**: Pengusaha → Disnak Kab/Kota Asal → Disnak Provinsi → DPMPTSP
- **Kuota**: Menggunakan kuota pengeluaran (asal)

### 3. **Pemasukan**
- **Deskripsi**: Pengajuan pemasukan ternak dari luar NTB ke dalam provinsi
- **Workflow**: Pengusaha → Disnak Kab/Kota Tujuan → Disnak Provinsi → DPMPTSP
- **Kuota**: Menggunakan kuota pemasukan (tujuan)

## 🏝️ **Skenario Khusus Pulau Lombok**

### **Kuota Terintegrasi**
- **Deskripsi**: Kuota pengeluaran sapi pedaging pulau Lombok berlaku untuk semua kab/kota di Lombok
- **Implementasi**: 
  - Field `pulau` di tabel `kuota` diset ke 'Lombok'
  - Logika khusus di model `Pengajuan` untuk handle kuota Lombok
  - Method `getKuotaTersisaLombok()` di model `PenggunaanKuota`

### **Kab/Kota di Pulau Lombok**
- Lombok Barat
- Lombok Tengah  
- Lombok Timur
- Mataram

## 📊 **Status Pengajuan**

### 1. **Menunggu**
- **Deskripsi**: Pengajuan baru yang belum diproses
- **Aksi yang Tersedia**: 
  - Approve (untuk dinas yang berwenang)
  - Tolak (untuk dinas yang berwenang)
  - Edit (untuk pengusaha)

### 2. **Diproses**
- **Deskripsi**: Pengajuan sedang dalam tahap verifikasi
- **Aksi yang Tersedia**:
  - Approve (untuk tahap selanjutnya)
  - Tolak (untuk dinas yang berwenang)
  - Upload Dokumen (setelah disetujui)

### 3. **Disetujui**
- **Deskripsi**: Pengajuan telah disetujui dan siap untuk upload dokumen
- **Aksi yang Tersedia**:
  - Upload Dokumen (untuk dinas)
  - Verifikasi (untuk DPMPTSP)

### 4. **Ditolak**
- **Deskripsi**: Pengajuan ditolak dan dikembalikan ke pengusaha
- **Aksi yang Tersedia**:
  - Ajukan Kembali (untuk pengusaha)

### 5. **Selesai**
- **Deskripsi**: Pengajuan telah selesai diproses
- **Aksi yang Tersedia**:
  - Download Dokumen (untuk semua user)

## 👥 **Role dan Wewenang**

### 1. **Pengusaha (Pengguna)**
- **Wewenang**: 
  - Membuat pengajuan baru
  - Melihat status pengajuan
  - Download dokumen yang sudah diupload dinas
  - Ajukan kembali jika ditolak
- **Menu Akses**: 
  - Pengajuan Antar Kab/Kota
  - Pengajuan Pengeluaran
  - Pengajuan Pemasukan
  - Dokumen Saya

### 2. **Disnak Kab/Kota**
- **Wewenang**:
  - Approve/Tolak pengajuan di wilayahnya
  - Upload dokumen setelah pengajuan disetujui
  - Melihat semua pengajuan di wilayahnya
- **Upload Dokumen**:
  - **Kab/Kota Asal**: Rekomendasi Keswan, SKKH, Surat Keterangan Veteriner, Dokumen Lainnya
  - **Kab/Kota Tujuan**: Rekomendasi Keswan saja

### 3. **Disnak Provinsi**
- **Wewenang**:
  - Approve/Tolak pengajuan di tingkat provinsi
  - Upload dokumen rekomendasi keswan
  - Melihat semua pengajuan di provinsi
- **Upload Dokumen**: Rekomendasi Keswan saja

### 4. **DPMPTSP**
- **Wewenang**:
  - Verifikasi pengajuan final
  - Upload izin pengeluaran/pemasukan
  - Melihat semua pengajuan yang sudah disetujui
- **Upload Dokumen**: Izin Pengeluaran atau Izin Pemasukan

### 5. **Administrator**
- **Wewenang**:
  - Akses penuh ke semua fitur
  - Kelola data master
  - Upload dokumen kapan saja
  - Melihat semua data

## 📄 **Jenis Dokumen**

### **Dari Pengusaha**
- Surat Permohonan Perusahaan
- Hasil Uji Lab
- Dokumen Lainnya (opsional)
- Izin PTSP Daerah (untuk pengeluaran/pemasukan)

### **Dari Dinas Kab/Kota Asal**
- Rekomendasi Keswan
- SKKH (Surat Keterangan Kesehatan Hewan)
- Surat Keterangan Veteriner
- Dokumen Lainnya

### **Dari Dinas Kab/Kota Tujuan**
- Rekomendasi Keswan

### **Dari Disnak Provinsi**
- Rekomendasi Keswan

### **Dari DPMPTSP**
- Izin Pengeluaran (untuk pengeluaran)
- Izin Pemasukan (untuk pemasukan)

## 🔄 **Workflow Lengkap**

### **Skenario 1: Pengajuan Antar Kab/Kota (Berhasil)**
1. **Pengusaha** membuat pengajuan antar kab/kota
2. **Disnak Kab/Kota Asal** approve pengajuan
3. **Disnak Kab/Kota Tujuan** approve pengajuan
4. **Disnak Provinsi** approve pengajuan
5. **DPMPTSP** verifikasi dan upload izin
6. **Sistem** catat penggunaan kuota
7. **Dinas** upload dokumen pendukung
8. **Pengusaha** download dokumen final

### **Skenario 2: Pengajuan Pengeluaran (Berhasil)**
1. **Pengusaha** membuat pengajuan pengeluaran
2. **Disnak Kab/Kota Asal** approve pengajuan
3. **Disnak Provinsi** approve pengajuan (skip kab/kota tujuan)
4. **DPMPTSP** verifikasi dan upload izin pengeluaran
5. **Sistem** catat penggunaan kuota pengeluaran
6. **Dinas** upload dokumen pendukung
7. **Pengusaha** download dokumen final

### **Skenario 3: Pengajuan Pemasukan (Berhasil)**
1. **Pengusaha** membuat pengajuan pemasukan
2. **Disnak Kab/Kota Tujuan** approve pengajuan
3. **Disnak Provinsi** approve pengajuan (skip kab/kota asal)
4. **DPMPTSP** verifikasi dan upload izin pemasukan
5. **Sistem** catat penggunaan kuota pemasukan
6. **Dinas** upload dokumen pendukung
7. **Pengusaha** download dokumen final

### **Skenario 4: Pengajuan Ditolak**
1. **Pengusaha** membuat pengajuan
2. **Dinas** tolak pengajuan dengan alasan
3. **Sistem** kembalikan ke status "ditolak"
4. **Pengusaha** dapat ajukan kembali dengan perbaikan

## 🧪 **Data Testing yang Tersedia**

### **Pengajuan Contoh**
1. **Antar Kab/Kota Lombok** - Status: Menunggu
2. **Pengeluaran** - Status: Diproses
3. **Pemasukan** - Status: Disetujui
4. **Antar Kab/Kota Sumbawa** - Status: Ditolak
5. **Pengeluaran Lombok** - Status: Selesai

### **Kuota Contoh**
- **Pulau Lombok**: 100 pemasukan, 150 pengeluaran per kab/kota
- **Pulau Sumbawa**: 80 pemasukan, 120 pengeluaran per kab/kota

### **Dokumen Contoh**
- Setiap pengajuan yang disetujui/selesai memiliki dokumen lengkap
- File dummy dengan konten yang sesuai jenis dokumen
- Ukuran file realistis (1-2MB)

## 🚀 **Cara Menjalankan Testing**

### **1. Jalankan Seeder**
```bash
php artisan db:seed --class=ContohDataSeeder
php artisan db:seed --class=DokumenDummySeeder
```

### **2. Login dengan User Berbeda**
- **Admin**: admin@example.com / password
- **Disnak Provinsi**: disnakprovinsi@example.com / password
- **Disnak Kab/Kota**: [nama_kabkota]@example.com / password
- **DPMPTSP**: dpmptsp@example.com / password
- **Pengusaha**: [user yang sudah diverifikasi]

### **3. Test Skenario**
1. **Login sebagai pengusaha** → Lihat pengajuan dan download dokumen
2. **Login sebagai dinas kab/kota** → Approve/tolak dan upload dokumen
3. **Login sebagai disnak provinsi** → Approve dan upload rekomendasi
4. **Login sebagai DPMPTSP** → Verifikasi dan upload izin
5. **Login sebagai admin** → Akses semua fitur

## 📝 **Checklist Testing**

### **Fitur Utama**
- [ ] Buat pengajuan baru (semua jenis)
- [ ] Approve/tolak pengajuan
- [ ] Upload dokumen sesuai wewenang
- [ ] Download dokumen
- [ ] Tracking kuota otomatis
- [ ] Workflow sesuai jenis pengajuan

### **Skenario Khusus**
- [ ] Kuota Lombok terintegrasi
- [ ] Skip tahap untuk pengeluaran/pemasukan
- [ ] Upload dokumen sesuai role
- [ ] Download dokumen untuk pengusaha
- [ ] Pengurangan kuota otomatis

### **Validasi**
- [ ] Kuota tidak boleh melebihi batas
- [ ] File upload sesuai format dan ukuran
- [ ] Workflow sesuai wewenang user
- [ ] Dokumen tersedia sesuai status pengajuan

## 🎯 **Expected Results**

Setelah menjalankan semua skenario, sistem harus:
1. ✅ Menangani semua jenis pengajuan dengan benar
2. ✅ Mengelola kuota Lombok secara terintegrasi
3. ✅ Mengurangi kuota otomatis saat disetujui
4. ✅ Memungkinkan upload dokumen sesuai wewenang
5. ✅ Menyediakan download dokumen untuk pengusaha
6. ✅ Menjalankan workflow sesuai jenis pengajuan
7. ✅ Menampilkan data yang konsisten di semua interface

---

**Catatan**: Semua data testing menggunakan bahasa Indonesia dan mengikuti konvensi penamaan yang telah ditetapkan.
