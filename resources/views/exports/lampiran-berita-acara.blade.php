<table>

    {{-- Row 1: spacer kosong --}}
    <tr>
        <td></td>
    </tr>

    {{-- Row 2: Judul --}}
    <tr>
        <td></td>
        <td colspan="7">
            DAFTAR ARSIP YANG DIPINDAHKAN
        </td>
    </tr>

    {{-- Row 3: NAMA UNIT PENGOLAH (dari user login) --}}
    <tr>
        <td colspan="4">
            NAMA UNIT PENGOLAH :
            {{ strtoupper($namaSubBagian) }}
        </td>
        <td></td>
        <td></td>
        <td colspan="2"></td>
    </tr>

    {{-- Row 4: Lampiran (kolom G:H) --}}
    <tr>
        <td colspan="4"></td>
        <td></td>
        <td></td>
        <td colspan="2">
            Lampiran Berita Acara Pemindahan Arsip
        </td>
    </tr>

    {{-- Row 5: Nomor (kolom G:H) --}}
    <tr>
        <td colspan="4"></td>
        <td></td>
        <td></td>
        <td colspan="2">
            Nomor : {{ $beritaAcara->nomor_bap }}
        </td>
    </tr>

    {{-- Row 6: Tanggal (kolom G:H) --}}
    <tr>
        <td colspan="4"></td>
        <td></td>
        <td></td>
        <td colspan="2">
            Tanggal :
            {{ \Carbon\Carbon::parse($beritaAcara->tanggal_bap)->translatedFormat('d F Y') }}
        </td>
    </tr>

    {{-- Row 7: spacer kosong --}}
    <tr></tr>

    {{-- Row 8: Header tabel --}}
    <tr>
        <th>NO</th>
        <th>KODE KLASIFIKASI</th>
        <th>JENIS ARSIP</th>
        <th>KURUN WAKTU</th>
        <th>TINGKAT PERKEMBANGAN</th>
        <th>JUMLAH</th>
        <th>KETERANGAN</th>
        <th>NOMOR BOKS/NAMA FOLDER (OPTIONAL)</th>
    </tr>

    {{-- Row 9: Nomor urut kolom --}}
    <tr>
        <td>1</td>
        <td>2</td>
        <td>3</td>
        <td>4</td>
        <td>5</td>
        <td>6</td>
        <td>7</td>
        <td>8</td>
    </tr>

    {{-- Row 10 dst: Data --}}
    @foreach($beritaAcara->details as $detail)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $detail->arsip->kodeKlasifikasi->kode ?? '-' }}</td>
        <td>{{ $detail->arsip->uraian_arsip }}</td>
        <td>{{ $detail->arsip->tahun_arsip }}</td>
        <td>{{ $detail->arsip->tingkat_perkembangan }}</td>
        <td>{{ $detail->arsip->jumlah_berkas }} {{ $detail->arsip->satuan_arsip }}</td>
        <td>{{ $detail->arsip->media_arsip }} / {{ $detail->arsip->keterangan }}</td>
        <td>{{ $detail->arsip->nomor_box ?? '-' }}</td>
    </tr>
    @endforeach

</table>