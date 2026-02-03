<form method="POST" action="{{ route('pemusnahan.finalisasi', $pemusnahan) }}">
@csrf

<table class="table table-bordered">
<thead>
<tr>
    <th>No</th>
    <th>Arsip</th>
    <th>Tahun</th>
    <th>Keputusan</th>
    <th>Catatan</th>
</tr>
</thead>
<tbody>
@foreach ($pemusnahan->details as $i => $detail)
<tr>
    <td>{{ $i+1 }}</td>
    <td>{{ $detail->arsip->uraian_arsip }}</td>
    <td>{{ $detail->arsip->tahun_arsip }}</td>
    <td>
        <select name="keputusan[{{ $detail->id }}]" class="form-select">
            <option value="musnah">Musnah</option>
            <option value="tidak_musnah">Tidak Dimusnahkan</option>
        </select>
    </td>
    <td>
        <input type="text"
            name="catatan[{{ $detail->id }}]"
            class="form-control">
    </td>
</tr>
@endforeach
</tbody>
</table>

<button class="btn btn-danger">
    Finalisasi Pemusnahan
</button>
</form>
