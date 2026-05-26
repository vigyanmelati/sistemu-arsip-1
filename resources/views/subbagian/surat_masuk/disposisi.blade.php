<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Disposisi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", "Segoe UI", "Times New Roman", serif;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            background: #fff;
        }

        /* Ukuran halaman A4 portrait */
        @page {
            size: A4;
            margin: 1.5cm;
        }

        .wrapper {
            border: 1px solid #000;
            padding: 8px 12px 12px 12px;
            max-width: 100%;
        }

        .logo {
            text-align: center;
            margin-top: 5px;
        }
        .logo img {
            width: 55px;
        }

        .header {
            text-align: center;
            margin-top: 3px;
            line-height: 1.3;
        }
        .header div {
            font-size: 13px;
        }

        .judul {
            text-align: center;
            margin: 10px 0;
            font-size: 12px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            vertical-align: top;
            padding: 2px 4px;
        }

        .line {
            border-top: 1px solid #000;
        }
        .box {
            border-top: 1px solid #000;
            min-height: 80px;
        }
        .info-box {
            border-top: 1px solid #000;
            min-height: 120px;
        }
        .center {
            text-align: center;
        }
        .underline {
            text-decoration: underline;
        }
        .small-gap {
            margin-top: 6px;
        }

        /* Perbaikan utama checkbox sejajar */
        .checkbox-list div {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
            white-space: nowrap;
        }
        .custom-checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            background: #fff;
            margin-right: 6px;
            flex-shrink: 0;
        }

        .ttd {
            height: 70px;
        }
    </style>
</head>
<body onload="window.print()">
<div class="wrapper">
    <!-- LOGO -->
    <div class="logo">
        <img src="{{ public_path('logo_kpu.png') }}" alt="Logo KPU">
    </div>

    <!-- HEADER -->
    <div class="header">
        <div>KOMISI PEMILIHAN UMUM</div>
        <div>PROVINSI BALI</div>
    </div>

    <!-- JUDUL -->
    <div class="judul">LEMBAR DISPOSISI</div>

    <!-- DATA SURAT -->
    <table>
        <tr><td width="13%">Dari</td><td width="2%">:</td><td>{{ $surat->instansi_satker }}</td></tr>
        <tr><td>No. Surat</td><td>:</td><td>{{ $surat->nomor_dokumen }}</td></tr>
        <tr><td>Tanggal Surat</td><td>:</td><td>{{ \Carbon\Carbon::parse($surat->tanggal_dokumen)->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Perihal</td><td>:</td><td>{{ $surat->perihal }}</td></tr>
    </table>

    <!-- TANGGAL MASUK & AGENDA -->
    <table style="margin-top: 4px;">
        <tr>
            <td width="45%">Tanggal Masuk : {{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d F Y') }}</td>
            <td width="10%"></td>
            <td>No. Agenda : {{ $surat->nomor_agenda }}</td>
        </tr>
    </table>

    <!-- YTH dan CATATAN (sejajar kiri-kanan) -->
    <table class="line">
        <tr>
            <td width="45%">
                <span class="underline">Yth.</span>
                <div class="checkbox-list small-gap">
                    <div><span class="custom-checkbox"></span> Ketua</div>
                    <div><span class="custom-checkbox"></span> Sekretaris</div>
                    <div><span class="custom-checkbox"></span> Kabag</div>
                    <div><span class="custom-checkbox"></span> Kasubbag</div>
                    <div><span class="custom-checkbox"></span> Staf</div>
                </div>
            </td>
            <td>
                <div class="center underline">Catatan:</div>
                <div style="margin-top: 5px;">{{ $surat->catatan }}</div>
            </td>
        </tr>
    </table>

    <!-- SIFAT -->
    <table>
        <tr>
            <td width="45%">
                <span class="underline">Sifat:</span>
                <div class="checkbox-list small-gap">
                    <div><span class="custom-checkbox"></span> Biasa Mendesak</div>
                    <div><span class="custom-checkbox"></span> Penting/Segera</div>
                    <div><span class="custom-checkbox"></span> Perlu Perhatian Khusus</div>
                    <div><span class="custom-checkbox"></span> Perlu Perhatian Batas Waktu</div>
                </div>
            </td>
            <td></td>
        </tr>
    </table>

    <!-- BANTUAN (2 kolom) -->
    <table>
        <tr><td colspan="2">Mohon bantuan Saudara untuk:</td></tr>
        <tr>
            <td width="50%">
                <div class="checkbox-list small-gap">
                    <div><span class="custom-checkbox"></span> Dokumentasi/File</div>
                    <div><span class="custom-checkbox"></span> Mohon hadir mewakili saya</div>
                    <div><span class="custom-checkbox"></span> Membicarakan dengan saya</div>
                    <div><span class="custom-checkbox"></span> Membuat jawaban/tanggapan</div>
                    <div><span class="custom-checkbox"></span> Ikut hadir</div>
                    <div><span class="custom-checkbox"></span> Memonitor</div>
                    <div><span class="custom-checkbox"></span> Menyiapkan konsep</div>
                </div>
            </td>
            <td>
                <div class="checkbox-list small-gap">
                    <div><span class="custom-checkbox"></span> Diketahui/sbg. Informasi</div>
                    <div><span class="custom-checkbox"></span> Mempelajari dan memberikan saran</div>
                    <div><span class="custom-checkbox"></span> Melaksanakan/menindaklanjuti</div>
                    <div><span class="custom-checkbox"></span> Memproses sesuai prosedur</div>
                    <div><span class="custom-checkbox"></span> Menyelesaikan sebelum batas waktu</div>
                    <div><span class="custom-checkbox"></span> Mengkoordinasikan</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- TTD SEKRETARIS -->
    <div class="box">
        <table><tr><td width="50%" class="ttd">Sekretaris:</td></tr></table>
    </div>

    <!-- INFORMASI -->
    <div class="center line"><b>INFORMASI</b></div>
    <div class="info-box">
        <table><tr><td>Ketua:</td><td width="50%" style="height:110px;"></td><td></td></tr></table>
    </div>
</div>
</body>
</html>