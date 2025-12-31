# Daftar Placeholder Template Dokumen

Dokumen ini menjelaskan placeholder yang tersedia untuk digunakan di template Word (.doc atau .docx) di folder `public/docs/`.

## Cara Menggunakan Placeholder

Di template Word, gunakan format: `${placeholder_name}`

Contoh: `${nama_perusahaan}`, `${jumlah_ternak}`, dll.

## Daftar Placeholder

### Informasi Pemohon/Perusahaan

| Placeholder | Deskripsi | Contoh |
|------------|-----------|--------|
| `nama_perusahaan` | Nama perusahaan/instansi | PT. ABC |
| `nama_pemohon` | Nama pemohon | John Doe |
| `alamat_perusahaan` | Alamat perusahaan | Jl. Raya No. 123 |
| `alamat` | Alias untuk alamat_perusahaan | Jl. Raya No. 123 |
| `desa` | Desa/Kelurahan | Desa ABC |
| `telepon` | Nomor telepon | 081234567890 |
| `no_hp` | Nomor HP (alias telepon) | 081234567890 |
| `email` | Email perusahaan | info@abc.com |
| `no_nib` | Nomor NIB | 1234567890123456 |
| `no_npwp` | Nomor NPWP | 12.345.678.9-012.345 |

### Informasi Pengajuan

| Placeholder | Deskripsi | Contoh |
|------------|-----------|--------|
| `nomor_surat_permohonan` | Nomor surat permohonan | 001/ABC/2024 |
| `nomor_surat` | Alias untuk nomor_surat_permohonan | 001/ABC/2024 |
| `tanggal_surat_permohonan` | Tanggal surat permohonan (format: d F Y) | 15 Januari 2024 |
| `tanggal_surat` | Alias untuk tanggal_surat_permohonan | 15 Januari 2024 |
| `jenis_pengajuan` | Jenis pengajuan | Antar Kabupaten/Kota |
| `tahun_pengajuan` | Tahun pengajuan | 2024 |
| `keterangan` | Keterangan pengajuan | - |
| `status` | Status pengajuan | Disetujui |

### Informasi Ternak

| Placeholder | Deskripsi | Contoh |
|------------|-----------|--------|
| `jenis_ternak` | Jenis ternak | Sapi Pedaging |
| `kategori_ternak` | Kategori ternak | Ternak Besar |
| `jumlah_ternak` | Jumlah ternak (format: 1.000) | 100 |
| `jumlah` | Alias untuk jumlah_ternak | 100 |
| `jenis_kelamin` | Jenis kelamin | Jantan |
| `ras_ternak` | Ras/Strain ternak | Brahman |
| `ras` | Alias untuk ras_ternak | Brahman |
| `satuan` | Satuan jumlah | ekor |
| `jumlah_jantan` | Jumlah ternak jantan | 50 |
| `jantan` | Alias untuk jumlah_jantan | 50 |
| `jumlah_betina` | Jumlah ternak betina | 50 |
| `betina` | Alias untuk jumlah_betina | 50 |

### Lokasi Asal

| Placeholder | Deskripsi | Contoh |
|------------|-----------|--------|
| `provinsi_asal` | Provinsi asal | Nusa Tenggara Barat |
| `kab_kota_asal` | Kabupaten/Kota asal | Kab. Lombok Barat |
| `kabupaten_asal` | Alias untuk kab_kota_asal | Kab. Lombok Barat |
| `pelabuhan_asal` | Pelabuhan asal | Pelabuhan Lembar |

### Lokasi Tujuan

| Placeholder | Deskripsi | Contoh |
|------------|-----------|--------|
| `provinsi_tujuan` | Provinsi tujuan | Nusa Tenggara Timur |
| `kab_kota_tujuan` | Kabupaten/Kota tujuan | Kab. Sumbawa |
| `kabupaten_tujuan` | Alias untuk kab_kota_tujuan | Kab. Sumbawa |
| `pelabuhan_tujuan` | Pelabuhan tujuan | Pelabuhan Bima |

### Tanggal Dokumen

| Placeholder | Deskripsi | Contoh |
|------------|-----------|--------|
| `tanggal_dokumen` | Tanggal dokumen (format: d F Y) | 20 Januari 2024 |
| `tanggal` | Alias untuk tanggal_dokumen | 20 Januari 2024 |
| `tanggal_ttd` | Tanggal tanda tangan | 20 Januari 2024 |
| `tanggal_sekarang` | Alias untuk tanggal dokumen | 20 Januari 2024 |

### Biodata Kadis (Kepala Dinas)

| Placeholder | Deskripsi | Contoh |
|------------|-----------|--------|
| `nama_kadis` | Nama Kepala Dinas | Dr. John Doe, S.Pt., M.Si |
| `pangkat_kadis` | Pangkat/Jabatan Kadis | Kepala Dinas |
| `jabatan_kadis` | Alias untuk pangkat_kadis | Kepala Dinas |
| `nip_kadis` | NIP Kepala Dinas | 196001011985031001 |
| `nip` | Alias untuk nip_kadis | 196001011985031001 |

### Nomor Dokumen (Khusus)

| Placeholder | Deskripsi | Contoh | Keterangan |
|------------|-----------|--------|------------|
| `nomor_dokumen` | Nomor dokumen rekomendasi | REKOM/LOM/2024/0001 | Hanya untuk jenis dokumen rekomendasi_keswan |
| `nomor_skkh` | Nomor SKKH | 001/SKKH/2024 | Hanya untuk jenis dokumen skkh |
| `nomor_izin` | Nomor izin | IZIN/LOM/2024/0001 | Hanya untuk jenis dokumen izin_pengeluaran/izin_pemasukan |

## Catatan Penting

1. **Format Tanggal**: Semua placeholder tanggal menggunakan format Indonesia: "d F Y" (contoh: "20 Januari 2024")
2. **Format Angka**: Jumlah ternak menggunakan format dengan titik sebagai pemisah ribuan (contoh: "1.000")
3. **Default Value**: Jika data tidak tersedia, placeholder akan diisi dengan "-"
4. **Alias**: Beberapa placeholder memiliki alias untuk fleksibilitas penamaan di template
5. **Case Sensitive**: Placeholder bersifat case-sensitive, pastikan penulisan sesuai dengan daftar di atas

## Contoh Template

```
SURAT REKOMENDASI

Nomor: ${nomor_dokumen}
Tanggal: ${tanggal_dokumen}

Kepada Yth.
${nama_perusahaan}
${alamat_perusahaan}
${desa}

Dengan hormat,

Berdasarkan permohonan dari ${nama_pemohon} dengan nomor surat ${nomor_surat_permohonan} 
tanggal ${tanggal_surat_permohonan}, dengan ini kami rekomendasikan untuk:

Jenis Ternak: ${jenis_ternak}
Jumlah: ${jumlah_ternak} ${satuan}
Jenis Kelamin: ${jenis_kelamin}
Asal: ${kab_kota_asal}, ${provinsi_asal}
Tujuan: ${kab_kota_tujuan}, ${provinsi_tujuan}

Demikian surat rekomendasi ini dibuat untuk dapat dipergunakan sebagaimana mestinya.

${kab_kota_asal}, ${tanggal_ttd}

${nama_kadis}
${pangkat_kadis}
NIP. ${nip_kadis}
```

## Troubleshooting

Jika placeholder tidak terisi:
1. Pastikan format placeholder benar: `${nama_placeholder}` (dengan tanda dolar dan kurung kurawal)
2. Pastikan nama placeholder sesuai dengan daftar di atas (case-sensitive)
3. Cek apakah data tersedia di database untuk field yang bersangkutan
4. Cek log error untuk melihat pesan error yang lebih detail

