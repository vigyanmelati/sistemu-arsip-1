# SISTEMU ARSIP
**Sistem Temu dan Penemuan Arsip Terpadu**

SISTEMU ARSIP adalah aplikasi pengelolaan arsip digital yang dirancang untuk
memudahkan pencarian, penelusuran, pengendalian, dan riwayat arsip
dari masa aktif hingga pemusnahan.

## 🎯 Tujuan
- Mempermudah temu kembali arsip
- Mengelola siklus hidup arsip
- Menyediakan riwayat dan histori arsip
- Mendukung pengelolaan arsip yang tertib dan terdokumentasi

## 🏛️ Lingkup
Aplikasi ini dikembangkan untuk kebutuhan **KPU Provinsi**.

## ⚙️ Teknologi
- Laravel
- PHP
- MySQL / MariaDB
- HTML, CSS, JavaScript
- Vite

## 🚀 Instalasi
```bash
git clone https://github.com/vigyanmelati/sistemu-arsip.git
cd sistemu-arsip
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
