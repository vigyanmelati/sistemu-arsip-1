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
             font-family: "Times New Roman", serif;
    font-size: 12pt;
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
        \.wrapper{
    width:100%;
}

        .header {
            text-align: center;
            margin-top: 3px;
            line-height: 1.3;
        }
        /* .header div {
            font-size: 13px;
        } */

        .judul {
            text-align: center;
            margin: 15px 0;
            font-size: 1px;
            /* font-weight: bold; */
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
    min-height: 120px;
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
            height: 120px;
        }

        .data-surat {
    width: 100%;
    border-collapse: collapse;
}

.data-surat .label {
    width: 120px;
    white-space: nowrap;
    padding-right: 6px;
}

.data-surat .colon {
    width: 15px;
    text-align: center;
}

.data-surat .label{
    width:120px;
    white-space:nowrap;
    padding-right:6px;
}

.data-surat .colon{
    width:15px;
    text-align:center;
}

.list-checkbox {
    width: 100%;
    border-collapse: collapse;
}

.list-checkbox td {
    padding: 2px 0;
    vertical-align: middle;
}

.label-yth {
    width: 50px;
    vertical-align: top;
    /* text-decoration: underline; */
}

.cb {
    width: 18px;
}

.custom-checkbox {
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 1px solid #000;
}

.line{
    border-top:1px solid #000;
}

.line td{
    vertical-align:top;
    padding-top:6px;
}

.catatan{
    text-decoration:underline;
    text-align:center;
    margin-bottom:8px;
    font-weight:normal;
}

.list-checkbox{
    border-collapse:collapse;
    width:auto;
}

.list-checkbox td{
    padding:2px 0;
    vertical-align:middle;
}

.label-yth{
    width:65px;
    text-align:left;
    white-space:nowrap;
}

.cb{
    width:24px;
}

.custom-checkbox{
    display:inline-block;
    width:13px;
    height:13px;
    border:1px solid #000;
    vertical-align:middle;
}

.info-sign td{
    height:90px;
    vertical-align:top;
    padding:4px;
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
    <table class="data-surat">
    <tr>
        <td class="label">Dari</td>
        <td class="colon">:</td>
        <td>{{ $surat->instansi_satker }}</td>
    </tr>
    <tr>
        <td class="label">No. Surat</td>
        <td class="colon">:</td>
        <td>{{ $surat->nomor_dokumen }}</td>
    </tr>
    <tr>
        <td class="label">Tanggal Surat</td>
        <td class="colon">:</td>
        <td>{{ \Carbon\Carbon::parse($surat->tanggal_dokumen)->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">Perihal</td>
        <td class="colon">:</td>
        <td>{{ $surat->perihal }}</td>
    </tr>
</table>

    <!-- TANGGAL MASUK & AGENDA -->
<table class="data-surat" style="margin-top:4px;">
    <tr>
        <td class="label">Tanggal Masuk</td>
        <td class="colon">:</td>
        <td width="35%">
            {{ \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d F Y') }}
        </td>

        <td class="label" style="text-align:right;">No. Agenda</td>
        <td class="colon">:</td>
        <td>
            {{ $surat->nomor_agenda }}
        </td>
    </tr>
</table>
    <!-- YTH dan CATATAN (sejajar kiri-kanan) -->
  <table class="line">
    <tr>

        <!-- Kolom kiri -->
        <td width="45%" style="vertical-align:top;">

            <table class="list-checkbox">
                <tr>
                    <td class="label-yth">Yth.</td>
                    <td class="cb"><span class="custom-checkbox"></span></td>
                    <td>Ketua</td>
                </tr>

                <tr>
                    <td></td>
                    <td><span class="custom-checkbox"></span></td>
                    <td>Sekretaris</td>
                </tr>

                <tr>
                    <td></td>
                    <td><span class="custom-checkbox"></span></td>
                    <td>Kabag</td>
                </tr>

                <tr>
                    <td></td>
                    <td><span class="custom-checkbox"></span></td>
                    <td>Kasubbag</td>
                </tr>

                <tr>
                    <td></td>
                    <td><span class="custom-checkbox"></span></td>
                    <td>Staf</td>
                </tr>
            </table>

        </td>

        <!-- Kolom kanan -->
        <td style="vertical-align:top;">

           <div class="catatan">
    Catatan:
</div>

            <div style="margin-top:8px;">
                {{ $surat->catatan }}
            </div>

        </td>

    </tr>
</table>

    <!-- SIFAT -->
    <table class="list-checkbox">
    <tr>
        <td class="label-yth">Sifat:</td>
        <td class="cb"><span class="custom-checkbox"></span></td>
        <td>Biasa Mendesak</td>
    </tr>

    <tr>
        <td></td>
        <td><span class="custom-checkbox"></span></td>
        <td>Penting/Segera</td>
    </tr>

    <tr>
        <td></td>
        <td><span class="custom-checkbox"></span></td>
        <td>Perlu Perhatian Khusus</td>
    </tr>

    <tr>
        <td></td>
        <td><span class="custom-checkbox"></span></td>
        <td>Perlu Perhatian Batas Waktu</td>
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
    <table>
        <tr>
            <td class="ttd">Sekretaris:</td>
        </tr>
    </table>
</div>

    <!-- INFORMASI -->
    <div class="center line"><b>INFORMASI</b></div>
   <div class="info-box">
    <table class="info-sign">
        <tr>
            <td>Ketua:</td>
        </tr>
        <tr>
            <td>Sekretaris:</td>
        </tr>
    </table>
</div>
</div>
</body>
</html>