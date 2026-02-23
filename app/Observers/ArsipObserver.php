<?php

namespace App\Observers;

use App\Models\Arsip;
use App\Models\HistoryPindah;

class ArsipObserver
{
    //  public function updating(Arsip $arsip)
    // {
    //     // Cek jika nomor_rak atau nomor_box berubah
    //     if ($arsip->isDirty(['nomor_rak', 'nomor_box'])) {
    //         $original = $arsip->getOriginal();
            
    //         // Catat history pindah
    //         HistoryPindah::create([
    //             'arsip_id' => $arsip->id,
    //             'dari_rak' => $original['nomor_rak'],
    //             'dari_box' => $original['nomor_box'],
    //             'ke_rak' => $arsip->nomor_rak,
    //             'ke_box' => $arsip->nomor_box,
    //             'tanggal_pindah' => now(),
    //             'alasan_pindah' => 'Update lokasi manual',
    //             'user_id' => auth()->id() ?? 1,
    //         ]);
    //     }
    // }


public function updating(Arsip $arsip)
{
    if ($arsip->skipHistory === true) {
        return;
    }

    if ($arsip->isDirty(['nomor_rak', 'nomor_box'])) {
        HistoryPindah::create([
            'arsip_id' => $arsip->id,
            'dari_rak' => $arsip->getOriginal('nomor_rak'),
            'dari_box' => $arsip->getOriginal('nomor_box'),
            'ke_rak'   => $arsip->nomor_rak,
            'ke_box'   => $arsip->nomor_box,
            'tanggal_pindah' => now(),
            'user_id' => auth()->id(),
        ]);
    }
}


}
