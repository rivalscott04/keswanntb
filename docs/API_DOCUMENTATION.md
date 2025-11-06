# API Documentation - Sistem Kuota Ternak NTB

## Base URL
```
http://localhost/api
```
*Ganti `http://localhost` dengan URL aplikasi Anda*

## Authentication
Saat ini API tidak memerlukan autentikasi. Untuk produksi, disarankan menambahkan autentikasi.

---

## Endpoint Kuota

### 1. Get All Kuota
**GET** `/api/kuota`

Mendapatkan semua data kuota dengan filter opsional.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `tahun` | integer | No | Filter berdasarkan tahun (default: tahun saat ini) |
| `jenis_kuota` | string | No | Filter: `pemasukan` atau `pengeluaran` |
| `jenis_ternak_id` | integer | No | Filter berdasarkan ID jenis ternak |
| `kab_kota_id` | integer | No | Filter berdasarkan ID kabupaten/kota |
| `pulau` | string | No | Filter berdasarkan pulau |
| `jenis_kelamin` | string | No | Filter berdasarkan jenis kelamin |

**Example Request:**
```
GET /api/kuota?tahun=2025&jenis_kuota=pemasukan
GET /api/kuota?tahun=2025&jenis_ternak_id=1&kab_kota_id=5
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "jenis_ternak": {
        "id": 1,
        "nama": "Sapi"
      },
      "wilayah": {
        "kab_kota_id": 5,
        "kab_kota_nama": "Mataram",
        "pulau": null,
        "lokasi_display": "Mataram"
      },
      "tahun": 2025,
      "jenis_kuota": "pemasukan",
      "jenis_kelamin": "Jantan",
      "kuota_total": 1000,
      "kuota_terpakai": 250,
      "kuota_tersisa": 750
    }
  ],
  "meta": {
    "total": 10,
    "tahun": 2025
  }
}
```

---

### 2. Get Kuota Pemasukan
**GET** `/api/kuota/pemasukan`

Mendapatkan kuota pemasukan, dikelompokkan berdasarkan jenis ternak dan wilayah.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `tahun` | integer | No | Filter berdasarkan tahun (default: tahun saat ini) |
| `jenis_ternak_id` | integer | No | Filter berdasarkan ID jenis ternak |
| `kab_kota_id` | integer | No | Filter berdasarkan ID kabupaten/kota |
| `pulau` | string | No | Filter berdasarkan pulau |
| `jenis_kelamin` | string | No | Filter berdasarkan jenis kelamin |

**Example Request:**
```
GET /api/kuota/pemasukan?tahun=2025
GET /api/kuota/pemasukan?tahun=2025&jenis_ternak_id=1
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "jenis_ternak": {
        "id": 1,
        "nama": "Sapi"
      },
      "wilayah": {
        "kab_kota_id": 5,
        "kab_kota_nama": "Mataram",
        "pulau": null,
        "lokasi_display": "Mataram"
      },
      "tahun": 2025,
      "jenis_kelamin": "Jantan",
      "kuota_total": 1000,
      "kuota_terpakai": 250,
      "kuota_tersisa": 750,
      "detail": [
        {
          "id": 1,
          "kuota_total": 1000,
          "kuota_terpakai": 250,
          "kuota_tersisa": 750
        }
      ]
    }
  ],
  "meta": {
    "total": 10,
    "tahun": 2025,
    "jenis_kuota": "pemasukan"
  }
}
```

---

### 3. Get Kuota Pengeluaran
**GET** `/api/kuota/pengeluaran`

Mendapatkan kuota pengeluaran, dikelompokkan berdasarkan jenis ternak dan wilayah.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `tahun` | integer | No | Filter berdasarkan tahun (default: tahun saat ini) |
| `jenis_ternak_id` | integer | No | Filter berdasarkan ID jenis ternak |
| `kab_kota_id` | integer | No | Filter berdasarkan ID kabupaten/kota |
| `pulau` | string | No | Filter berdasarkan pulau |
| `jenis_kelamin` | string | No | Filter berdasarkan jenis kelamin |

**Example Request:**
```
GET /api/kuota/pengeluaran?tahun=2025
GET /api/kuota/pengeluaran?tahun=2025&pulau=Lombok
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "jenis_ternak": {
        "id": 1,
        "nama": "Sapi"
      },
      "wilayah": {
        "kab_kota_id": null,
        "kab_kota_nama": null,
        "pulau": "Lombok",
        "lokasi_display": "Pulau Lombok"
      },
      "tahun": 2025,
      "jenis_kelamin": "Jantan",
      "kuota_total": 500,
      "kuota_terpakai": 100,
      "kuota_tersisa": 400,
      "detail": [
        {
          "id": 2,
          "kuota_total": 500,
          "kuota_terpakai": 100,
          "kuota_tersisa": 400
        }
      ]
    }
  ],
  "meta": {
    "total": 5,
    "tahun": 2025,
    "jenis_kuota": "pengeluaran"
  }
}
```

---

## Endpoint Master Data

### 1. Get All Jenis Ternak
**GET** `/api/master/jenis-ternak`

Mendapatkan semua jenis ternak.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `kategori_ternak_id` | integer | No | Filter berdasarkan kategori ternak ID |
| `bidang_id` | integer | No | Filter berdasarkan bidang ID |

**Example Request:**
```
GET /api/master/jenis-ternak
GET /api/master/jenis-ternak?kategori_ternak_id=1
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama": "Sapi",
      "kategori_ternak": {
        "id": 1,
        "nama": "Ternak Besar"
      },
      "bidang": {
        "id": 1,
        "nama": "Bidang Peternakan"
      }
    }
  ],
  "meta": {
    "total": 10
  }
}
```

---

### 2. Get Jenis Ternak by ID
**GET** `/api/master/jenis-ternak/{id}`

Mendapatkan detail jenis ternak berdasarkan ID.

**Example Request:**
```
GET /api/master/jenis-ternak/1
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nama": "Sapi",
    "kategori_ternak": {
      "id": 1,
      "nama": "Ternak Besar"
    },
    "bidang": {
      "id": 1,
      "nama": "Bidang Peternakan"
    }
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Jenis ternak tidak ditemukan"
}
```

---

### 3. Get All Kabupaten/Kota
**GET** `/api/master/kab-kota`

Mendapatkan semua kabupaten/kota.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `provinsi_id` | integer | No | Filter berdasarkan provinsi ID |

**Example Request:**
```
GET /api/master/kab-kota
GET /api/master/kab-kota?provinsi_id=1
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama": "Mataram",
      "provinsi": {
        "id": 1,
        "nama": "Nusa Tenggara Barat"
      }
    }
  ],
  "meta": {
    "total": 10
  }
}
```

---

### 4. Get Kabupaten/Kota by ID
**GET** `/api/master/kab-kota/{id}`

Mendapatkan detail kabupaten/kota berdasarkan ID.

**Example Request:**
```
GET /api/master/kab-kota/1
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nama": "Mataram",
    "provinsi": {
      "id": 1,
      "nama": "Nusa Tenggara Barat"
    }
  }
}
```

---

### 5. Get All Provinsi
**GET** `/api/master/provinsi`

Mendapatkan semua provinsi.

**Example Request:**
```
GET /api/master/provinsi
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama": "Nusa Tenggara Barat"
    }
  ],
  "meta": {
    "total": 1
  }
}
```

---

### 6. Get Provinsi by ID
**GET** `/api/master/provinsi/{id}`

Mendapatkan detail provinsi dengan list kabupaten/kota di dalamnya.

**Example Request:**
```
GET /api/master/provinsi/1
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nama": "Nusa Tenggara Barat",
    "kab_kota": [
      {
        "id": 1,
        "nama": "Mataram"
      },
      {
        "id": 2,
        "nama": "Sumbawa"
      }
    ]
  }
}
```

---

### 7. Get All Kategori Ternak
**GET** `/api/master/kategori-ternak`

Mendapatkan semua kategori ternak.

**Example Request:**
```
GET /api/master/kategori-ternak
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama": "Ternak Besar"
    },
    {
      "id": 2,
      "nama": "Ternak Kecil"
    }
  ],
  "meta": {
    "total": 2
  }
}
```

---

### 8. Get Kategori Ternak by ID
**GET** `/api/master/kategori-ternak/{id}`

Mendapatkan detail kategori ternak dengan list jenis ternak di dalamnya.

**Example Request:**
```
GET /api/master/kategori-ternak/1
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nama": "Ternak Besar",
    "jenis_ternak": [
      {
        "id": 1,
        "nama": "Sapi"
      },
      {
        "id": 2,
        "nama": "Kerbau"
      }
    ]
  }
}
```

---

## Response Format

Semua endpoint mengembalikan response dalam format JSON dengan struktur:

### Success Response
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "total": 10,
    // ... metadata lainnya
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message"
}
```

## HTTP Status Codes

- `200` - Success
- `404` - Not Found
- `500` - Server Error

---

## Tips Penggunaan

1. **Default Tahun**: Jika parameter `tahun` tidak disertakan, API akan menggunakan tahun saat ini secara default
2. **Filter Multiple**: Anda bisa menggabungkan beberapa filter dalam satu request
3. **Pagination**: Saat ini tidak ada pagination, semua data dikembalikan sekaligus. Untuk data besar, pertimbangkan menambahkan pagination
4. **Caching**: Untuk performa yang lebih baik, pertimbangkan implementasi caching di sisi client

---

## Changelog

### Version 1.0.0 (2025-01-XX)
- Initial release
- API Kuota (semua, pemasukan, pengeluaran)
- API Master Data (jenis ternak, kab/kota, provinsi, kategori ternak)

