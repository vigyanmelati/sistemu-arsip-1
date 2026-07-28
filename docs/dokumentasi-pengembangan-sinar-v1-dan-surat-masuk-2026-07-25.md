# Dokumentasi Pengembangan SINAR V1 Historis dan Surat Masuk

Tanggal: 25 Juli 2026

Terakhir diperbarui: 27 Juli 2026

Branch: `feature/sinar-v1-dokumen-historis`

Commit dasar fitur historis: `afd6634 feat: add SINAR V1 historical import workflow`

## 1. Tujuan pekerjaan

Pekerjaan ini dilakukan untuk memisahkan data lama SINAR V1 dari arsip aktif SINAR V2, menyediakan proses migrasi yang aman, dan memperbaiki tata kelola Surat Masuk agar sesuai pembagian tugas organisasi.

Sasaran utamanya:

- Data kategori SINAR V1 tetap dikenali sebagai data historis.
- Surat Masuk V1 dapat dipindahkan secara bertahap ke modul Surat Masuk V2.
- TU menjadi petugas utama pencatatan Surat Masuk.
- Admin dan Super Admin tetap dapat melakukan koreksi serta pengelolaan sistem.
- User subbagian dapat melihat seluruh Surat Masuk tanpa dapat mengubahnya.
- Instansi/Satker dan Tujuan Disposisi dikelola sebagai master data.
- Proses import aman dijalankan ulang dan tidak menggandakan dokumen.

## 2. Modul SINAR V1 Historis

Modul historis menyimpan dokumen hasil migrasi SINAR V1 secara terpisah dari tabel arsip aktif. Kategori yang didukung adalah kategori 1–11, Surat Masuk (kategori 12), dan Surat Keluar (kategori 13).

Fitur yang tersedia:

- Daftar dan filter dokumen historis.
- Detail dokumen dan status lampiran.
- Download lampiran dari penyimpanan privat.
- Verifikasi keberadaan hardcopy dan lokasi hardcopy.
- Status integrasi dokumen.
- Persiapan dokumen historis menjadi arsip V2.
- Halaman import dengan input koneksi database V1 dan pemilihan folder lampiran.
- Dry-run sebelum import sebenarnya.
- Staging file agar pemindahan lampiran dapat dilakukan dari browser.
- Import idempoten berdasarkan kombinasi ID data lama dan kategori.

Surat Masuk kategori 12 ditampilkan pada SINAR V1 Historis dan menjadi sumber untuk import ke modul Surat Masuk V2.

## 3. Pembagian hak akses Surat Masuk

### TU

- Melihat seluruh Surat Masuk.
- Menambah Surat Masuk.
- Mengedit dan menghapus Surat Masuk.
- Mencetak lembar disposisi.
- Mengimpor Surat Masuk dari SINAR V1 Historis.
- Mengelola Instansi/Satker.
- Mengelola Tujuan Disposisi.

### Admin dan Super Admin

Memiliki hak pengelolaan yang sama dengan TU untuk kebutuhan koreksi data dan administrasi sistem.

### User subbagian

- Melihat seluruh Surat Masuk tanpa filter subbagian.
- Membuka detail dan mencetak disposisi.
- Tidak dapat menambah, mengedit, menghapus, atau mengimpor Surat Masuk.
- Tidak dapat mengelola master Instansi/Satker dan Tujuan Disposisi.

Pembatasan tidak hanya dilakukan pada tampilan, tetapi juga pada route menggunakan middleware `admin`. Middleware tersebut mengizinkan TU, Admin, dan Super Admin serta menolak user biasa.

## 4. Perubahan form Surat Masuk

Perubahan pada form tambah dan edit:

- Field pilihan Subbagian dihilangkan.
- `sub_bagian_id` dibuat nullable pada database.
- Surat baru tidak lagi wajib dikaitkan dengan subbagian.
- Nilai subbagian pada surat lama tetap dipertahankan sebagai riwayat dan tidak dihapus ketika surat diedit.
- Instansi/Satker wajib dipilih dari master data.
- Tujuan Disposisi bersifat opsional dan dapat memilih lebih dari satu tujuan.
- Tujuan Disposisi ditampilkan pada detail serta lembar disposisi.

### Pencarian Instansi/Satker pada form

Field Instansi/Satker pada Tambah Surat Masuk dibuat sebagai combobox interaktif:

- User dapat mengetik sebagian nama instansi.
- Sistem langsung memberikan maksimal 12 saran yang sesuai.
- User harus memilih salah satu hasil saran agar ID master tercatat.
- Jika tidak ada hasil, muncul aksi `Tambah Instansi/Satker`.
- Form tambah cepat terbuka tanpa meninggalkan halaman Surat Masuk.
- Setelah berhasil disimpan, instansi baru otomatis dipilih.
- Input tambah cepat menggunakan endpoint JSON yang tetap dilindungi middleware hak akses.

## 5. Master Instansi/Satker

Struktur isian disamakan dengan SINAR V1:

- Nama Instansi (wajib).
- Alamat Kantor.
- Nomor Telepon.
- Nomor Fax.
- Alamat Email.
- Alamat Web.
- Status aktif/nonaktif untuk kebutuhan V2.

Validasi yang diterapkan:

- Nama instansi harus unik.
- Telepon dan fax hanya menerima angka, mengikuti perilaku V1.
- Email harus menggunakan format email yang sah.
- Website harus berupa URL lengkap.

Fitur tambahan:

- Pencarian berdasarkan nama, alamat, telepon, fax, email, atau website.
- Pagination mempertahankan parameter pencarian.
- Cek duplikat nama instansi.
- Penggabungan data duplikat.

### Mekanisme cek dan perbaikan duplikat

Nama dibandingkan setelah normalisasi huruf besar-kecil, spasi, tanda baca, dan karakter khusus. Pada kelompok duplikat, petugas memilih satu data utama.

Saat digabungkan:

1. Informasi kontak yang kosong pada data utama dilengkapi dari data duplikat.
2. Seluruh relasi Surat Masuk dialihkan ke data utama.
3. Snapshot nama `instansi_satker` pada Surat Masuk diperbarui.
4. Entri duplikat dihapus.
5. Seluruh proses dijalankan dalam transaksi database.

## 6. Master Tujuan Disposisi

Master Tujuan Disposisi menyediakan:

- Penambahan nama tujuan.
- Perubahan nama tujuan.
- Status aktif/nonaktif.
- Relasi many-to-many dengan Surat Masuk melalui tabel pivot.

Satu Surat Masuk dapat memiliki nol, satu, atau beberapa Tujuan Disposisi.

## 7. Import Surat Masuk dari SINAR V1 Historis

Tombol Import pada halaman Daftar Surat Masuk tidak lagi digunakan untuk upload Excel. Tombol tersebut menjalankan migrasi dari data SINAR V1 Historis.

Urutan proses:

1. Membaca master instansi historis dari `sinar_v1_instansis`.
2. Membuat atau memperbarui master Instansi/Satker V2.
3. Membaca `sinar_v1_documents` kategori 12.
4. Membuat atau memperbarui Surat Masuk V2.
5. Menyalin lampiran yang tersedia dari storage privat historis ke storage publik Surat Masuk.
6. Memberikan label `SINAR V1` pada daftar Surat Masuk.

Modal import menampilkan:

- Jumlah Surat Masuk historis.
- Jumlah instansi historis.
- Jumlah surat yang sudah diimpor.
- Konfirmasi sebelum import dijalankan.

### Perilaku import ulang

Setiap Surat Masuk hasil migrasi menyimpan `sinar_v1_document_id` yang unik. Karena itu:

- Data yang belum ada akan dibuat.
- Data yang sudah ada akan diperbarui.
- Surat tidak digandakan.
- Lampiran menggunakan nama stabil berdasarkan ID historis.

### Import tabel instansi V1

Perintah `sinar-v1:migrate` diperluas agar membaca `t_instansi` dan menyimpannya ke `sinar_v1_instansis`, termasuk alamat dan informasi kontak. Data tersebut kemudian digunakan oleh import Surat Masuk.

Pada verifikasi awal tanggal 25 Juli 2026, koneksi lokal V1 belum tersedia. Migrasi produksi kemudian berhasil dijalankan pada 27 Juli 2026 menggunakan koneksi database V1 di `10.14.4.103` dan akses lampiran melalui bind mount read-only.

### Konfigurasi bind mount produksi

Lokasi aplikasi dan lampiran SINAR V1 pada host:

```text
/media/datahp1/web/siarsip/public
└── uploads/
```

Lokasi proyek Docker SINAR V2:

```text
/media/datahp1/Docker
```

Volume berikut ditambahkan pada service `app4`:

```yaml
volumes:
  - ./app4:/var/www/html
  - ./docker/php/php.ini:/usr/local/etc/php/php.ini
  - /media/datahp1/web/siarsip/public:/mnt/sinar-v1-public:ro
```

Konfigurasi lingkungan SINAR V2:

```env
SINAR_V1_DB_HOST=10.14.4.103
SINAR_V1_DB_PORT=3306
SINAR_V1_DB_DATABASE=siarsip
SINAR_V1_DB_USERNAME=<user-migrasi-read-only>
SINAR_V1_DB_PASSWORD=<password>
SINAR_V1_FILES_ROOT=/mnt/sinar-v1-public
SINAR_V1_COPY_FILES=true
```

Mount menggunakan opsi `:ro` agar container SINAR V2 hanya dapat membaca dan tidak dapat mengubah file asli SINAR V1.

### Hasil migrasi produksi 27 Juli 2026

Perintah yang dijalankan:

```bash
cd /media/datahp1/Docker
docker compose exec app4 php artisan optimize:clear
docker compose exec app4 php artisan sinar-v1:migrate
docker compose exec app4 php artisan sinar-v1:migrate --commit
```

Hasil aktual:

| Komponen | Jumlah |
|---|---:|
| Instansi ditemukan | 1.218 |
| Dokumen ditemukan dan tersimpan | 15.420 |
| Lampiran berhasil disalin | 14.209 |
| Lampiran tercatat hilang | 99 |
| Dokumen tanpa lampiran sumber | 1.112 |

Jumlah dokumen tanpa lampiran dihitung dari `15.420 - 14.209 - 99 = 1.112`. Status ini berbeda dengan file hilang: dokumen tanpa lampiran memang tidak memiliki nama file sumber, sedangkan 99 file hilang memiliki referensi file tetapi file fisiknya belum ditemukan pada lokasi bind mount.

Migrasi dinyatakan berhasil. Tingkat keberhasilan penyalinan terhadap dokumen yang mempunyai referensi lampiran adalah sekitar 99,31% (`14.209 / (14.209 + 99)`). Import dapat dijalankan ulang setelah 99 file ditemukan atau dikembalikan ke struktur folder V1; dokumen tidak akan digandakan dan importer akan mencoba kembali file yang belum tersedia.

## 8. Struktur database baru

### `surat_instansis`

Master Instansi/Satker V2, termasuk informasi alamat dan kontak.

### `tujuan_disposisis`

Master tujuan disposisi.

### `surat_masuk_tujuan_disposisi`

Tabel pivot relasi banyak tujuan untuk satu Surat Masuk.

### `sinar_v1_instansis`

Salinan historis tabel `t_instansi` dari SINAR V1.

### Perubahan `surat_masuk`

- `sub_bagian_id` menjadi nullable.
- Penambahan `instansi_id` sebagai relasi master instansi.
- Penambahan `sinar_v1_document_id` sebagai identitas sumber historis yang unik.
- Kolom lama `instansi_satker` dipertahankan sebagai snapshot nama dan untuk kompatibilitas data lama.

Migration terkait:

- `2026_07_25_000001_add_surat_masuk_master_data.php`
- `2026_07_25_000002_add_contact_fields_to_surat_instansis.php`
- `2026_07_25_000003_create_sinar_v1_instansis_and_link_surat_masuk.php`

## 9. Navigasi sidebar

Menu Surat Masuk dikeluarkan dari Kelola Arsip dan dijadikan menu tersendiri. Posisi akhirnya tepat di atas SINAR V1 Historis.

Untuk TU/Admin/Super Admin:

- Daftar Surat Masuk.
- Instansi/Satker.
- Tujuan Disposisi.

Untuk user subbagian:

- Daftar Surat Masuk.

Menu Kelola Arsip kembali khusus menangani Arsip Internal.

## 10. Penyempurnaan tampilan Daftar Surat Masuk

Tampilan diselaraskan dengan tabel Arsip Internal:

- Card, header, alert informasi, dan kelompok tombol yang konsisten.
- Tombol Import, Export, Cek Duplikasi, dan Tambah Surat menggunakan gaya warna Arsip Internal.
- Search bar penuh dan responsif.
- Lebar minimum kolom agar isi mudah dibaca.
- Horizontal scrollbar di atas dan bawah tabel yang saling tersinkronisasi.
- Area tabel memiliki tinggi maksimum `65vh`.
- Header kolom dibuat sticky/freeze saat isi tabel digulir.
- Hover baris tidak lagi memperbesar dan menggeser tabel.
- Tombol aksi dibuat lebih ringkas.
- Tampilan read-only user subbagian ikut diselaraskan.

Pagination diubah menggunakan Bootstrap 5 secara global melalui `Paginator::useBootstrapFive()`. Perubahan ini memperbaiki ikon Previous/Next yang sebelumnya membesar karena template Tailwind dirender tanpa CSS Tailwind.

## 11. File utama yang berubah

- `app/Console/Commands/MigrateSinarV1Documents.php`
- `app/Http/Controllers/SuratMasukController.php`
- `app/Http/Controllers/SubBagianSuratMasukController.php`
- `app/Http/Controllers/SuratInstansiController.php`
- `app/Http/Controllers/TujuanDisposisiController.php`
- `app/Models/SuratMasuk.php`
- `app/Models/SuratInstansi.php`
- `app/Models/TujuanDisposisi.php`
- `app/Models/SinarV1Document.php`
- `app/Models/SinarV1Instansi.php`
- `app/Providers/AppServiceProvider.php`
- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/surat_masuk/*`
- `resources/views/subbagian/surat_masuk/*`
- `resources/views/sinar_v1/index.blade.php`
- Tiga migration baru yang disebutkan pada bagian struktur database.

## 12. Verifikasi yang telah dilakukan

- Pemeriksaan sintaks PHP menggunakan `php -l` pada controller, model, provider, command, dan migration terkait.
- Menjalankan migration pada database lokal; seluruh migration baru berstatus `Ran`.
- Kompilasi seluruh Blade menggunakan `php artisan view:cache` berhasil.
- Pemeriksaan route menggunakan `php artisan route:list -vv`.
- Route pengelolaan Surat Masuk dan master data terverifikasi menggunakan middleware `auth`, `nocache`, dan `admin`.
- Pemeriksaan whitespace/error patch menggunakan `git diff --check` berhasil.
- Cache aplikasi dibersihkan dengan `php artisan optimize:clear`.
- Bind mount read-only dari folder `public` SINAR V1 berhasil dibaca oleh container `laravel-app4`.
- Koneksi database sumber pada `10.14.4.103` berhasil digunakan oleh command migrasi.
- Migrasi produksi berhasil menyimpan 1.218 instansi dan 15.420 dokumen.
- Sebanyak 14.209 lampiran berhasil disalin dan 99 referensi lampiran perlu ditindaklanjuti.

Catatan pengujian:

- Browser interaktif tidak tersedia pada sesi pengembangan, sehingga verifikasi UI dilakukan melalui kompilasi Blade, inspeksi struktur HTML/JavaScript, dan pemeriksaan route.
- Test suite bawaan proyek masih gagal pada migration lama ketika menggunakan SQLite in-memory karena migration mencoba mengubah tabel `arsips` sebelum tabel tersebut tersedia. Kegagalan ini sudah ada pada fondasi migration proyek dan tidak berasal dari fitur Surat Masuk ini.

## 13. Langkah deployment

Setelah perubahan diambil pada lingkungan tujuan:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan view:cache
```

`npm run build` tidak diperlukan karena perubahan tampilan menggunakan Blade serta CSS/JavaScript inline dan tidak mengubah aset Vite pada `resources/css` atau `resources/js`.

Sebelum import data produksi:

1. Cadangkan database SINAR V1 dan SINAR V2.
2. Cadangkan folder lampiran kedua aplikasi.
3. Jalankan Import SINAR V1 menggunakan kredensial sumber yang benar.
4. Periksa jumlah Surat Masuk dan instansi historis pada modal Import Surat Masuk.
5. Jalankan import dan periksa beberapa sampel data serta lampirannya.
6. Gunakan fitur Cek Duplikat Instansi sebelum melakukan penggabungan.

Untuk deployment Docker produksi yang digunakan saat migrasi:

```bash
cd /media/datahp1/Docker
docker compose config
docker compose up -d --force-recreate app4
docker compose exec app4 ls -lah /mnt/sinar-v1-public/uploads
docker compose exec app4 php artisan optimize:clear
docker compose exec app4 php artisan sinar-v1:migrate
docker compose exec app4 php artisan sinar-v1:migrate --commit
```

Metode bind mount dipilih menggantikan upload folder lewat browser karena jumlah lampiran sangat besar. Staging browser dibatasi 5 GB dan file individual maksimal 20 MB, sedangkan bind mount tidak menggandakan folder sumber ke staging serta lebih stabil untuk proses migrasi satu server.

## 14. Riwayat commit dan kondisi repository

- Branch aktif sudah benar: `feature/sinar-v1-dokumen-historis`.
- Migration baru sudah dijalankan pada database lokal.
- Commit `afd6634` menambahkan alur import historis SINAR V1.
- Commit `72e7539` menyempurnakan workflow Surat Masuk dan import V1.
- Commit `fef61a3` menambahkan validasi Surat Masuk dan alur pratinjau disposisi.
- Folder `docs/` dan `tools/` sebelumnya sudah berstatus untracked; commit harus memilih file secara eksplisit agar berkas lain milik pengguna tidak ikut masuk tanpa sengaja.
