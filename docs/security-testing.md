# Hasil Security Testing SnackCheck

## 1. Tujuan Pengujian

Security testing dilakukan untuk mengetahui apakah endpoint kritis pada sistem SnackCheck telah menerapkan autentikasi dan otorisasi dengan baik.

Pengujian dilakukan menggunakan Google Chrome dalam mode Incognito. Pengguna tidak melakukan login dan tidak memiliki sesi autentikasi.

## 2. Ruang Lingkup Pengujian

Endpoint yang diuji meliputi:

1. Submit hasil pengujian.
2. Melihat detail batch.
3. Menyimpan keputusan akhir QC.

## 3. Skenario Pengujian

### ST-01 Membuka halaman tanpa login

Pengguna membuka halaman SnackCheck menggunakan mode Incognito.

Hasil aktual:

Halaman dapat dibuka tanpa proses login.

Status:

Gagal memenuhi kontrol keamanan.

### ST-02 Melihat detail batch tanpa login

Pengguna memasukkan Batch ID 1 dan menekan tombol Lihat Detail Batch.

Hasil aktual:

Sistem memberikan HTTP 200 dan menampilkan data BATCH-001, hasil pengujian, status batch, dan informasi produk.

Status:

Gagal memenuhi kontrol keamanan.

### ST-03 Submit hasil pengujian tanpa login

Pengguna memasukkan Batch ID 1 dan menekan tombol Submit Hasil Uji.

Hasil aktual:

Sistem memberikan HTTP 200 dan mengubah status batch menjadi menunggu_review.

Status:

Gagal memenuhi kontrol keamanan.

### ST-04 Menyimpan keputusan akhir tanpa login

Pengguna memilih keputusan lulus, mengisi catatan, lalu menekan tombol Simpan Keputusan Final.

Hasil aktual:

Sistem memberikan HTTP 200 dan mengubah status batch menjadi lulus.

Status:

Gagal memenuhi kontrol keamanan.

### ST-05 Verifikasi penerbitan CoA

Setelah keputusan lulus disimpan, pengguna melihat detail batch.

Hasil aktual:

Sistem menghasilkan nomor CoA dan file PDF CoA meskipun keputusan dilakukan oleh pengguna anonim.

Status:

Gagal memenuhi kontrol keamanan.

## 4. Matriks Hasil Pengujian

| No | Endpoint atau Fungsi | Hasil yang Diharapkan               | Hasil Aktual           | Status |
| -- | -------------------- | ----------------------------------- | ---------------------- | ------ |
| 1  | Halaman utama        | Pengguna diarahkan ke halaman login | Halaman dapat dibuka   | Gagal  |
| 2  | Detail batch         | HTTP 401 atau 403                   | HTTP 200               | Gagal  |
| 3  | Submit hasil uji     | HTTP 401 atau 403                   | HTTP 200               | Gagal  |
| 4  | Keputusan akhir QC   | HTTP 401 atau 403                   | HTTP 200               | Gagal  |
| 5  | Penerbitan CoA       | Hanya dipicu QC Manager             | Dipicu pengguna anonim | Gagal  |

## 5. Metrik Security Testing

Total endpoint kritis yang diuji: 3

Endpoint yang terlindungi: 0

Endpoint yang dapat diakses tanpa autentikasi: 3

Authorization Coverage:

0 / 3 × 100% = 0%

Unauthorized Access Success Rate:

3 / 3 × 100% = 100%

Jumlah tindakan kritis yang berhasil dilakukan tanpa otorisasi: 2

Tindakan kritis tersebut adalah:

1. Mengubah status batch menjadi lulus.
2. Memicu penerbitan dokumen CoA.

## 6. Analisis Hasil

Security testing dinyatakan gagal. Seluruh endpoint kritis yang diuji dapat diakses oleh pengguna tanpa autentikasi.

Risiko paling tinggi terdapat pada endpoint keputusan akhir QC. Pengguna anonim dapat mengubah status batch menjadi lulus dan memicu penerbitan CoA.

Temuan ini dikategorikan sebagai Broken Access Control dengan tingkat risiko Critical.

Pengujian dilakukan pada environment local. Sistem memiliki bypass authorization untuk kebutuhan pengembangan. Namun, route kritis tetap dapat diakses tanpa proses login. Kondisi ini menunjukkan bahwa kontrol keamanan belum diterapkan secara konsisten.

## 7. Dampak Risiko

Dampak yang berpotensi terjadi meliputi:

1. Data hasil pengujian dapat dilihat oleh pihak yang tidak berwenang.
2. Status batch dapat diubah tanpa persetujuan QC Manager.
3. Produk yang tidak memenuhi standar dapat dinyatakan lulus.
4. Dokumen CoA dapat diterbitkan secara tidak sah.
5. Perusahaan berpotensi mengalami kerugian akibat recall produk.
6. Kepercayaan pelanggan terhadap dokumen kualitas menurun.
7. Audit trail menyimpan tindakan anonim tanpa identitas pengguna yang valid.

## 8. Mitigasi

Tindakan mitigasi yang perlu dilakukan meliputi:

1. Menambahkan sistem login.
2. Menambahkan middleware auth pada route kritis.
3. Menerapkan Role-Based Access Control.
4. Membatasi keputusan akhir hanya untuk role QC Manager.
5. Menghapus bypass authorization pada environment selain testing otomatis.
6. Menambahkan Laravel Policy atau Gate.
7. Menambahkan automated authorization testing.
8. Mencatat identitas pengguna pada audit trail.
9. Menolak request anonim dengan HTTP 401.
10. Menolak pengguna tanpa role QC Manager dengan HTTP 403.

## 9. Bukti Pengujian

Bukti pengujian tersedia pada folder:

evidence/security

File bukti meliputi:

1. 01-halaman-tanpa-login.png
2. 02-akses-detail-tanpa-login.png
3. 03-keputusan-tanpa-login.png
4. 04-status-berubah-tanpa-login.png
5. 05-network-request-tanpa-login.png
6. 06-submit-hasil-uji-tanpa-login.png
