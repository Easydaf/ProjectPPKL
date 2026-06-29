# UAS Pengujian dan Penjaminan Kualitas Perangkat Lunak

## Identitas

Nama: Muhammad Adh-Dhiya’Us Salim

NIM: 2310817210022

Program Studi: Teknologi Informasi

Studi Kasus: SnackCheck

## Deskripsi Sistem

SnackCheck merupakan sistem pengelolaan pengujian kualitas produk makanan. Sistem mencakup proses submit hasil pengujian, melihat detail batch, memberikan keputusan akhir QC, menerbitkan Certificate of Analysis, mengirim notifikasi, dan mencatat audit trail.

## Teknologi

1. Laravel 13
2. PHP 8.3
3. MySQL
4. Blade
5. JavaScript
6. Selenium
7. Python
8. Pytest
9. Pytest HTML

## Regression Testing

Regression testing dilakukan menggunakan Selenium dan pytest.

Jumlah skenario yang diterapkan: 12 test case.

Hasil akhir:

12 test case lulus.

Skenario yang diuji:

1. Membuka halaman utama.
2. Melihat detail batch valid.
3. Menangani batch yang tidak ditemukan.
4. Submit hasil pengujian.
5. Menyimpan keputusan lulus.
6. Membuat dokumen CoA.
7. Menolak keputusan tidak lulus tanpa rekomendasi.
8. Menyimpan keputusan tidak lulus dengan rework.
9. Menyimpan keputusan uji ulang tanpa CoA.
10. Menyimpan keputusan ditahan tanpa CoA.
11. Memastikan audit trail tercatat.
12. Memastikan CoA tidak terduplikasi.

## Menjalankan Selenium

Pastikan aplikasi Laravel sudah berjalan:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Aktifkan virtual environment:

```powershell
.\.venv\Scripts\Activate.ps1
```

Pasang dependency Python:

```bash
pip install -r selenium_tests/requirements.txt
```

Jalankan seluruh regression test:

```bash
python -m pytest selenium_tests/test_regression.py -v
```

Buat laporan HTML:

```bash
python -m pytest selenium_tests/test_regression.py -v --html=evidence/selenium/regression-report.html --self-contained-html
```

## Struktur Pengujian

```text
selenium_tests/
├── conftest.py
├── requirements.txt
└── test_regression.py
```

## Dokumentasi

Dokumentasi UAS tersedia pada:

```text
docs/
├── security-testing.md
├── iso-9001-2000.md
└── post-mortem-rca.md
```

## Bukti Pengujian

Bukti regression testing tersedia pada:

```text
evidence/selenium
```

Bukti security testing tersedia pada:

```text
evidence/security
```

## Hasil Security Testing

Security testing dilakukan melalui browser Incognito tanpa login.

Hasil pengujian menunjukkan:

1. Halaman dapat dibuka tanpa autentikasi.
2. Detail batch dapat diakses dengan HTTP 200.
3. Submit hasil pengujian dapat dilakukan dengan HTTP 200.
4. Keputusan akhir dapat diubah menjadi lulus dengan HTTP 200.
5. Pengguna anonim dapat memicu penerbitan CoA.

Authorization Coverage: 0 persen.

Unauthorized Access Success Rate: 100 persen.

Temuan dikategorikan sebagai Broken Access Control dengan tingkat risiko Critical.

## Catatan

Security testing dilakukan pada environment local. Hasil tersebut digunakan untuk mengidentifikasi kebutuhan penerapan autentikasi, Role-Based Access Control, middleware, dan Laravel Policy sebelum sistem digunakan pada environment produksi.
