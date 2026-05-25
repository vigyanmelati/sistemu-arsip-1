<!DOCTYPE html>
<html>
<head>
    <title>Disposisi Surat</title>

    <style>

        body{
            font-family: Arial;
            font-size:14px;
        }

        table{
            width:100%;
            border-collapse: collapse;
        }

        table, td, th{
            border:1px solid black;
        }

        td{
            padding:8px;
            vertical-align: top;
        }

    </style>

</head>
<body onload="window.print()">

    <center>
        <h2>KOMISI PEMILIHAN UMUM PROVINSI BALI</h2>
        <h3>LEMBAR DISPOSISI</h3>
    </center>

    <table>

        <tr>
            <td width="50%">
                Dari : {{ $surat->instansi_satker }}
            </td>

            <td>
                No Surat : {{ $surat->nomor_dokumen }}
            </td>
        </tr>

        <tr>
            <td>
                Tanggal Surat :
                {{ $surat->tanggal_dokumen }}
            </td>

            <td>
                No Agenda :
                {{ $surat->nomor_agenda }}
            </td>
        </tr>

        <tr>
            <td colspan="2">
                Perihal :
                {{ $surat->perihal }}
            </td>
        </tr>

        <tr style="height:150px">
            <td>
                Kepada :
                {{ $surat->kepada }}
            </td>

            <td>
                Catatan :
                {{ $surat->catatan }}
            </td>
        </tr>

    </table>

</body>
</html>