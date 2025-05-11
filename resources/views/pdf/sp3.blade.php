<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SP3 - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            margin-bottom: 30px;
        }
        .footer {
            margin-top: 50px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SURAT PERNYATAAN PERSETUJUAN PENGELUARAN TERNAK</h2>
        <h3>Nomor: SP3/{{ date('Y') }}/{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</h3>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>
        
        <table>
            <tr>
                <th width="30%">Nama</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>Jabatan</th>
                <td>Pemilik/Penanggung Jawab</td>
            </tr>
            <tr>
                <th>Perusahaan</th>
                <td>{{ $user->company_name }}</td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td>{{ $user->company_address }}</td>
            </tr>
            <tr>
                <th>NPWP</th>
                <td>{{ $user->npwp }}</td>
            </tr>
        </table>

        <p>Dengan ini menyatakan bahwa:</p>
        <ol>
            <li>Saya telah membaca dan memahami seluruh ketentuan dan persyaratan yang berlaku dalam sistem pengajuan pengeluaran ternak.</li>
            <li>Saya bersedia mematuhi semua ketentuan dan persyaratan yang telah ditetapkan.</li>
            <li>Saya bertanggung jawab penuh atas kebenaran data dan dokumen yang saya berikan.</li>
            <li>Saya bersedia menerima sanksi sesuai ketentuan yang berlaku jika terdapat ketidaksesuaian data.</li>
        </ol>
    </div>

    <div class="footer">
        <p>Dibuat di: {{ $user->kabKota->nama }}</p>
        <p>Pada tanggal: {{ date('d F Y') }}</p>
        <br><br><br>
        <p>( {{ $user->name }} )</p>
    </div>
</body>
</html> 