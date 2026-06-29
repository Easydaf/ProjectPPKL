# Post-Mortem Insiden Keamanan SnackCheck

## 1. Identitas Insiden

Nama insiden: Penerbitan CoA oleh Pengguna Tanpa Otorisasi

Tanggal insiden asumsi: 29 Juni 2026

Sistem terdampak: SnackCheck

Tingkat keparahan: Critical

Status insiden: Selesai diinvestigasi

Metode Root Cause Analysis: 5 Why

Dokumen ini menggunakan skenario insiden asumsi berdasarkan hasil security testing pada sistem SnackCheck. Pengujian aktual menunjukkan bahwa pengguna tanpa login dapat mengakses endpoint kritis, mengubah keputusan batch menjadi lulus, dan memicu penerbitan Certificate of Analysis.

## 2. Ringkasan Insiden

Pengguna tanpa autentikasi berhasil membuka halaman SnackCheck melalui browser Incognito. Pengguna tersebut dapat membaca detail BATCH-001, mengirim hasil pengujian, dan mengubah keputusan akhir menjadi lulus.

Endpoint perubahan keputusan menerima request PUT dengan status HTTP 200. Setelah keputusan tersimpan, sistem menghasilkan nomor CoA dan file PDF CoA.

Sistem tidak meminta pengguna untuk login dan tidak memverifikasi bahwa pengguna memiliki role QC Manager. Akibatnya, tindakan yang seharusnya hanya dapat dilakukan oleh petugas berwenang dapat dilakukan oleh pengguna anonim.

Pada skenario insiden perusahaan, CoA yang tidak sah tersebut digunakan sebagai dasar pelepasan produk dari area penyimpanan menuju proses distribusi.

## 3. Bug yang Menyebabkan Insiden

Bug utama terdapat pada kontrol autentikasi dan otorisasi.

Bentuk bug yang ditemukan meliputi:

1. Route kritis dapat diakses tanpa login.
2. Endpoint detail batch memberikan data kepada pengguna anonim.
3. Endpoint submit hasil uji menerima request pengguna anonim.
4. Endpoint keputusan akhir menerima request pengguna anonim.
5. Authorization memiliki bypass pada environment local.
6. Sistem tidak memverifikasi role QC Manager secara konsisten.
7. Penerbitan CoA hanya memeriksa status lulus, tetapi tidak memeriksa identitas pemberi keputusan.
8. Audit trail belum mencatat identitas pengguna yang valid pada tindakan anonim.

Bug tersebut dikategorikan sebagai Broken Access Control.

## 4. Dampak terhadap Pengguna

Dampak terhadap pengguna sistem meliputi:

1. QC Manager tidak dapat menjamin bahwa keputusan berasal dari petugas berwenang.
2. Analis laboratorium dapat menerima status batch yang tidak valid.
3. Staff gudang dapat melepaskan produk berdasarkan CoA yang tidak sah.
4. Manajemen menerima data kualitas yang tidak dapat dipercaya.
5. Auditor kesulitan menentukan pelaku perubahan karena aktivitas dilakukan secara anonim.
6. Pelanggan berpotensi menerima produk yang belum melalui persetujuan resmi.

## 5. Dampak terhadap Perusahaan

Dampak bisnis yang dapat terjadi meliputi:

1. Produk yang tidak memenuhi standar berpotensi didistribusikan.
2. Perusahaan harus menghentikan distribusi.
3. Produk harus ditarik dari gudang atau pelanggan.
4. Perusahaan harus melakukan pengujian ulang.
5. Dokumen CoA harus dibatalkan.
6. Biaya operasional meningkat.
7. Kepercayaan pelanggan menurun.
8. Perusahaan menghadapi risiko komplain dan sanksi.
9. Reputasi proses quality control menurun.
10. Perusahaan kehilangan peluang penjualan selama proses investigasi.

## 6. Estimasi Kerugian Perusahaan

Nilai berikut merupakan asumsi untuk kebutuhan analisis insiden.

| Komponen Kerugian                       |     Estimasi |
| --------------------------------------- | -----------: |
| Penahanan dan penarikan produk          | Rp50.000.000 |
| Biaya transportasi dan distribusi ulang | Rp15.000.000 |
| Pengujian ulang laboratorium            |  Rp5.000.000 |
| Kompensasi kepada pelanggan             | Rp10.000.000 |
| Investigasi dan pemulihan sistem        |  Rp5.000.000 |
| Total estimasi kerugian                 | Rp85.000.000 |

Kerugian dapat meningkat jika produk telah tersebar luas atau menyebabkan masalah pada pelanggan.

## 7. Timeline Kejadian

09.00 WITA

Pengguna tanpa login membuka halaman SnackCheck melalui browser Incognito.

09.03 WITA

Pengguna memasukkan Batch ID 1 dan membuka detail BATCH-001.

09.05 WITA

Sistem memberikan HTTP 200 dan menampilkan data hasil pengujian.

09.08 WITA

Pengguna memilih keputusan lulus dan mengirim request PUT ke endpoint `/batches/1/review`.

09.08 WITA

Sistem menerima request dengan HTTP 200 dan mengubah status batch menjadi lulus.

09.09 WITA

Sistem menghasilkan nomor CoA dan file PDF CoA.

09.20 WITA

Staff gudang melihat status lulus dan menyiapkan produk untuk distribusi.

10.00 WITA

QC Manager menemukan bahwa tidak pernah memberikan persetujuan terhadap batch tersebut.

10.05 WITA

Proses distribusi dihentikan.

10.15 WITA

Batch dipindahkan kembali ke status penahanan secara administratif.

10.30 WITA

Tim pengembang memeriksa audit trail, log Laravel, dan request endpoint.

11.00 WITA

Tim menemukan bahwa endpoint kritis dapat diakses tanpa autentikasi.

11.30 WITA

Sistem dinonaktifkan sementara untuk tindakan kritis.

13.00 WITA

Investigasi Root Cause Analysis dimulai.

15.00 WITA

Rencana Corrective Action dan Preventive Action disusun.

## 8. Tindakan yang Dilakukan

Tindakan penanganan langsung meliputi:

1. Menghentikan distribusi BATCH-001.
2. Menahan produk pada area gudang.
3. Membatalkan penggunaan CoA yang tidak sah.
4. Menginformasikan insiden kepada QC Manager.
5. Memeriksa audit trail dan log aplikasi.
6. Memeriksa seluruh keputusan batch pada periode terdampak.
7. Mengidentifikasi route yang dapat diakses tanpa autentikasi.
8. Menonaktifkan akses terhadap fungsi keputusan akhir.
9. Melakukan backup database dan log.
10. Menjalankan regression testing setelah investigasi.

## 9. Corrective Action

Corrective Action dilakukan untuk memperbaiki penyebab insiden yang sudah terjadi.

Tindakan yang perlu diterapkan meliputi:

1. Menambahkan sistem login.
2. Menambahkan middleware `auth` pada route kritis.
3. Membatasi endpoint keputusan akhir hanya untuk role QC Manager.
4. Menambahkan Laravel Policy atau Gate.
5. Menghapus bypass authorization pada penggunaan normal.
6. Menolak pengguna anonim dengan HTTP 401.
7. Menolak pengguna tanpa role QC Manager dengan HTTP 403.
8. Mencatat user ID pada audit trail.
9. Menambahkan mekanisme pembatalan CoA.
10. Menambahkan automated authorization testing.
11. Melakukan security retesting.
12. Memeriksa batch lain yang mungkin terdampak.

## 10. Lessons Learned

Pelajaran yang diperoleh dari insiden meliputi:

1. CSRF protection tidak menggantikan autentikasi.
2. Endpoint yang menggunakan metode PUT tetap harus dilindungi authorization.
3. Status lulus tidak cukup untuk menjadi dasar penerbitan CoA.
4. Sistem harus memverifikasi identitas pemberi keputusan.
5. Environment local tetap harus memiliki pengujian keamanan.
6. Bypass authorization dapat menjadi risiko jika tidak dikendalikan.
7. Audit trail harus mencatat identitas pengguna.
8. Security testing harus dilakukan sebelum deployment.
9. Test case fungsional saja tidak cukup untuk menjamin keamanan.
10. CoA harus memiliki status aktif, dibatalkan, atau dicabut.

## 11. Root Cause Analysis Menggunakan 5 Why

### Why 1

Mengapa produk yang belum memperoleh persetujuan resmi berpotensi didistribusikan?

Karena sistem menunjukkan status batch lulus dan menghasilkan CoA.

### Why 2

Mengapa sistem menghasilkan status lulus dan CoA?

Karena endpoint keputusan akhir menerima request perubahan status menjadi lulus.

### Why 3

Mengapa request tersebut diterima dari pengguna yang tidak berwenang?

Karena endpoint dapat diakses tanpa login dan tidak melakukan pemeriksaan role QC Manager secara konsisten.

### Why 4

Mengapa kelemahan autentikasi dan authorization tidak ditemukan lebih awal?

Karena pengujian sebelumnya lebih berfokus pada fungsi submit, review, keputusan, dan CoA. Pengujian belum memprioritaskan akses pengguna anonim.

### Why 5

Mengapa security testing belum menjadi bagian utama proses pengembangan?

Karena belum terdapat security checklist, quality gate, automated authorization testing, dan standar pemeriksaan route sebelum release.

## 12. Akar Masalah

Akar masalah insiden adalah tidak adanya kontrol autentikasi dan authorization yang konsisten pada endpoint kritis.

Masalah tersebut diperburuk oleh:

1. Bypass authorization pada environment local.
2. Tidak adanya middleware auth pada route kritis.
3. Tidak adanya pemeriksaan role yang menyeluruh.
4. Tidak adanya automated security regression testing.
5. Tidak adanya quality gate keamanan sebelum deployment.

## 13. Tindakan Pencegahan

Tindakan pencegahan agar insiden serupa tidak terulang meliputi:

1. Menerapkan autentikasi pada seluruh halaman sistem.
2. Menerapkan Role-Based Access Control.
3. Menambahkan middleware auth pada route submit, detail, dan review.
4. Membatasi keputusan akhir hanya untuk QC Manager.
5. Menambahkan Laravel Policy.
6. Menghapus bypass authorization dari alur normal.
7. Menambahkan security test untuk pengguna anonim.
8. Menambahkan security test untuk role analis.
9. Menambahkan security test untuk role QC Manager.
10. Menjalankan automated testing sebelum merge.
11. Menambahkan code review untuk perubahan route.
12. Menambahkan monitoring aktivitas keputusan akhir.
13. Mengirim peringatan ketika keputusan dilakukan dari sesi tidak dikenal.
14. Mencatat user ID, alamat IP, dan waktu perubahan pada audit trail.
15. Menambahkan status pencabutan CoA.
16. Melakukan backup database dan log secara berkala.
17. Melakukan security audit sebelum deployment.
18. Menetapkan zero critical defect sebagai syarat release.

## 14. Verifikasi Perbaikan

Perbaikan dinyatakan berhasil jika memenuhi kriteria berikut:

1. Pengguna anonim menerima HTTP 401.
2. Pengguna tanpa role QC Manager menerima HTTP 403.
3. QC Manager dapat menyimpan keputusan dengan HTTP 200.
4. CoA hanya dibuat setelah keputusan sah.
5. Audit trail mencatat identitas QC Manager.
6. Seluruh regression test tetap lulus.
7. Seluruh security test akses anonim dinyatakan lulus.
8. Tidak terdapat defect berlevel Critical atau High.

## 15. Kesimpulan

Insiden terjadi karena kontrol akses pada endpoint kritis belum diterapkan secara konsisten. Pengguna tanpa login dapat mengubah status batch menjadi lulus dan memicu penerbitan CoA.

Security testing membuktikan bahwa authorization coverage pada endpoint yang diuji adalah 0 persen dan unauthorized access success rate mencapai 100 persen.

Akar masalah tidak hanya berada pada kode authorization, tetapi juga pada proses pengembangan yang belum memiliki quality gate keamanan. Perusahaan perlu menerapkan autentikasi, Role-Based Access Control, automated security testing, audit trail yang lengkap, dan pemeriksaan keamanan sebelum deployment.
