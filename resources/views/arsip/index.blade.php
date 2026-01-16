<!DOCTYPE html>
<html>
<head>
    <title>Daftar Arsip</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 6px; font-size: 13px; }
        th { background: #eee; }
    </style>
</head>
<body>

<h3>Daftar Arsip Bagian Umum KPU Provinsi Bali</h3>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Klasifikasi</th>
            <th>Uraian Arsip</th>
            <th>Sub Bagian</th>
            <th>Tahun</th>
            <th>No Rak</th>
            <th>No Box</th>
            <th>Status Arsip</th>
        </tr>
    </thead>
    <tbody>
        @foreach($arsips as $index => $arsip)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $arsip->kode_klasifikasi }}</td>
            <td>{{ $arsip->judul_arsip }}</td>
            <td>{{ $arsip->sub_bagian }}</td>
            <td>{{ $arsip->tahun_arsip }}</td>
            <td>{{ $arsip->nomor_rak }}</td>
            <td>{{ $arsip->nomor_box }}</td>
            <td>{{ strtoupper($arsip->status_arsip) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
