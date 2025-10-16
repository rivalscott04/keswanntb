# 🔍 Searchable Jenis Ternak dengan Grouping

## 📋 **Ringkasan Perubahan**

Field "Jenis Ternak" di semua form telah diupdate untuk menjadi **searchable** dengan **grouping berdasarkan kategori ternak**. Perubahan ini memudahkan user untuk mencari jenis ternak yang diinginkan tanpa perlu server-side loading.

---

## ✅ **Resource yang Diupdate**

### **1. KuotaResource**
- **File**: `app/Filament/Resources/KuotaResource.php`
- **Perubahan**: Field jenis ternak sekarang searchable dengan grouping
- **Fitur**: 
  - Searchable dropdown
  - Preload data
  - Grouping berdasarkan kategori ternak

### **2. PengajuanResource**
- **File**: `app/Filament/Resources/PengajuanResource.php`
- **Perubahan**: Field jenis ternak dengan dependency pada kategori ternak
- **Fitur**:
  - Searchable dropdown
  - Preload data
  - Grouping berdasarkan kategori ternak
  - Live update saat kategori ternak berubah

### **3. PengajuanPemasukanResource**
- **File**: `app/Filament/Resources/PengajuanPemasukanResource.php`
- **Perubahan**: Field jenis ternak dengan dependency pada kategori ternak
- **Fitur**: Sama dengan PengajuanResource

### **4. PengajuanPengeluaranResource**
- **File**: `app/Filament/Resources/PengajuanPengeluaranResource.php`
- **Perubahan**: Field jenis ternak dengan dependency pada kategori ternak
- **Fitur**: Sama dengan PengajuanResource

---

## 🏗️ **Implementasi Teknis**

### **Kode yang Digunakan**

```php
Forms\Components\Select::make('jenis_ternak_id')
    ->label('Jenis Ternak')
    ->options(function () {
        return \App\Models\JenisTernak::with('kategoriTernak')
            ->get()
            ->groupBy('kategoriTernak.nama')
            ->map(function ($jenisTernakGroup, $kategoriNama) {
                return $jenisTernakGroup->pluck('nama', 'id');
            })
            ->toArray();
    })
    ->searchable()
    ->preload()
    ->required(),
```

### **Untuk Resource dengan Dependency Kategori**

```php
Forms\Components\Select::make('jenis_ternak_id')
    ->label('Jenis Ternak')
    ->options(function (callable $get) {
        $kategoriTernakId = $get('kategori_ternak_id');
        if (!$kategoriTernakId) {
            return [];
        }
        
        return \App\Models\JenisTernak::with('kategoriTernak')
            ->where('kategori_ternak_id', $kategoriTernakId)
            ->get()
            ->groupBy('kategoriTernak.nama')
            ->map(function ($jenisTernakGroup, $kategoriNama) {
                return $jenisTernakGroup->pluck('nama', 'id');
            })
            ->toArray();
    })
    ->searchable()
    ->preload()
    ->required()
    ->live(),
```

---

## 📊 **Struktur Data**

### **Kategori Ternak yang Tersedia**
1. **Ruminansia Besar** (4 jenis)
   - Sapi
   - Kerbau
   - Kuda
   - Sapi Eksotik

2. **Ruminansia Kecil** (3 jenis)
   - Kambing
   - Babi
   - Domba

3. **Unggas & Telur** (6 jenis)
   - Bibit Ayam
   - Bibit Bebek/Itik
   - Bibit Puyuh
   - Telur Tetas
   - Ayam Dara / Potong
   - Bibit Sapi

4. **Produk Ternak** (4 jenis)
   - Daging Ayam Beku
   - Daging Sapi Beku
   - Daging Ayam Olahan
   - Telur Konsumsi dan Susu

5. **Hewan Kesayangan** (3 jenis)
   - Anjing
   - Kuda
   - Kucing

---

## 🎯 **Fitur yang Tersedia**

### **1. Searchable**
- User dapat mengetik untuk mencari jenis ternak
- Pencarian dilakukan di client-side (tidak perlu server request)
- Pencarian berdasarkan nama jenis ternak

### **2. Grouping**
- Jenis ternak dikelompokkan berdasarkan kategori
- Interface yang lebih terorganisir
- Mudah untuk menemukan jenis ternak yang diinginkan

### **3. Preload**
- Data dimuat saat form dibuka
- Tidak ada loading delay saat user berinteraksi
- Performa yang lebih baik

### **4. Live Update** (untuk resource dengan dependency)
- Field jenis ternak otomatis update saat kategori ternak berubah
- Reset pilihan jenis ternak saat kategori berubah
- UX yang lebih smooth

---

## 🚀 **Cara Penggunaan**

### **Untuk KuotaResource**
1. Buka halaman "Buat Kuota"
2. Klik field "Jenis Ternak"
3. Ketik nama jenis ternak untuk mencari
4. Pilih dari dropdown yang sudah dikelompokkan

### **Untuk Resource Pengajuan**
1. Pilih "Kategori Ternak" terlebih dahulu
2. Field "Jenis Ternak" akan otomatis terisi dengan jenis ternak dari kategori tersebut
3. Klik field "Jenis Ternak"
4. Ketik untuk mencari atau pilih langsung dari dropdown

---

## 🔧 **Keuntungan Implementasi**

### **1. User Experience**
- ✅ Interface yang lebih user-friendly
- ✅ Pencarian yang cepat dan responsif
- ✅ Organisasi data yang lebih baik

### **2. Performance**
- ✅ Tidak ada server request saat searching
- ✅ Data preload untuk performa optimal
- ✅ Client-side filtering yang cepat

### **3. Maintainability**
- ✅ Kode yang konsisten di semua resource
- ✅ Mudah untuk diupdate atau dimodifikasi
- ✅ Menggunakan relationship Eloquent yang sudah ada

---

## 📝 **Testing**

### **Data yang Tersedia untuk Testing**
- **Total Jenis Ternak**: 20
- **Total Kategori**: 5
- **Distribusi**: Merata di semua kategori

### **Cara Testing**
1. Buka form "Buat Kuota"
2. Klik field "Jenis Ternak"
3. Ketik "sapi" untuk mencari
4. Verifikasi hasil pencarian muncul dengan grouping
5. Pilih salah satu jenis ternak
6. Verifikasi form berfungsi normal

---

## 🎉 **Kesimpulan**

Implementasi searchable jenis ternak dengan grouping telah berhasil dilakukan di semua resource yang relevan. Fitur ini memberikan:

- **UX yang lebih baik** dengan pencarian yang responsif
- **Organisasi data yang jelas** dengan grouping berdasarkan kategori
- **Performance yang optimal** dengan client-side filtering
- **Konsistensi** di seluruh aplikasi

**Sistem siap digunakan dengan fitur searchable yang baru!** 🚀
