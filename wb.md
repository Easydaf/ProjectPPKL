# White-Box Testing Note - SnackCheck Journey 3

## Kasus yang Diuji

Konteks pengujian: melakukan testing pada logika else-if.

Target white-box:
- Fungsi: NotificationService::broadcastDecision
- Cabang yang diuji: else-if decision_status == tidak_lulus
- Tujuan: memastikan jalur tidak_lulus men-trigger notifikasi yang tepat (ke Manajer Produksi), membawa alasan dan rekomendasi, serta tidak memanggil generator CoA.

## Klasifikasi Metode Testing

Metode yang digunakan pada kasus ini adalah **Sanity Testing** dengan pendekatan **white-box branch testing**.

### Kenapa termasuk Sanity Testing

1. **Cakupan sempit dan spesifik**
   - Pengujian hanya fokus pada satu bagian logika penting, yaitu cabang else-if `decision_status == tidak_lulus`.
2. **Tujuan validasi cepat setelah implementasi/perubahan**
   - Test dilakukan untuk memastikan alur utama yang baru/diubah berjalan benar sebelum melanjutkan pengujian yang lebih luas.
3. **Bukan pengujian menyeluruh seluruh modul**
   - Tidak menguji semua endpoint, semua status keputusan, dan semua kombinasi data.

### Beda dengan metode lain

1. **Bukan Smoke Testing**
   - Smoke testing memeriksa apakah fungsi dasar aplikasi hidup secara umum.
   - Kasus ini lebih dalam pada satu branch logika tertentu.
2. **Bukan Regression Testing penuh**
   - Regression testing memverifikasi dampak perubahan terhadap banyak fitur lama.
   - Kasus ini tidak mencakup keseluruhan fitur terkait, hanya satu jalur kritis.
3. **Bukan UAT (User Acceptance Testing)**
   - UAT berfokus pada validasi kebutuhan bisnis dari sudut pandang user/stakeholder.
   - Kasus ini bersifat teknis-internal untuk memverifikasi percabangan kode.

## Alur Internal yang Diuji

1. Request masuk ke endpoint PUT /batches/{batch_id}/review.
2. ReviewController::update memvalidasi input menggunakan ReviewBatchRequest.
3. Dalam DB transaction:
   - status batch diupdate.
   - test_decisions diupdate/insert.
   - audit_trails dibuat dengan action final_review_updated.
4. Setelah transaction:
   - if status lulus: CoAService::generate dipanggil.
   - untuk tidak_lulus: blok if lulus dilewati.
5. NotificationService::broadcastDecision dipanggil.
6. Di dalam broadcastDecision:
   - if decision_status == lulus -> branch lulus.
   - else-if decision_status == tidak_lulus -> branch ini yang diuji.
   - else-if decision_status == uji_ulang -> branch uji_ulang.
   - fallback -> generic log.
7. Untuk tidak_lulus, sistem menulis log notifikasi berisi:
   - batch_id
   - batch_number
   - reason (notes)
   - recommendation (action_recommendation)

## Data Uji

Gunakan data berikut:
- batch_id: 1 (atau batch valid lain)
- keputusan_akhir: tidak_lulus
- tindakan_rekomendasi: hold
- catatan: White-box test branch tidak_lulus

## Langkah-Langkah Testing

### Metode A - Via UI Playground

1. Pastikan environment siap:
   - php artisan migrate
   - php artisan db:seed
   - php artisan storage:link
2. Jalankan aplikasi:
   - php artisan serve
3. Buka halaman root project di browser.
4. Pada section kontrol demo:
   - isi Batch ID valid.
   - pilih keputusan_akhir = tidak_lulus.
   - pilih tindakan_rekomendasi = hold.
   - isi catatan.
5. Klik tombol Simpan Keputusan Final.
6. Verifikasi response JSON sukses di panel output.
7. Verifikasi isi log di storage/logs/laravel.log:
   - harus ada message notifikasi keputusan tidak lulus.
   - context log memuat reason dan recommendation.
8. Verifikasi CoA tidak dibuat baru untuk keputusan tidak_lulus:
   - cek folder storage/app/public/coa.

### Metode B - Via API Client (Postman/Insomnia)

1. Method: PUT
2. URL: /batches/{batch_id}/review
3. Header:
   - Accept: application/json
   - Content-Type: application/json
   - X-CSRF-TOKEN (jika diperlukan untuk sesi web)
4. Body JSON:
   {
     "keputusan_akhir": "tidak_lulus",
     "tindakan_rekomendasi": "hold",
     "catatan": "White-box test branch tidak_lulus"
   }
5. Kirim request, lalu lakukan verifikasi yang sama pada log dan folder CoA.

## Expected Result

1. HTTP response sukses (200) dengan message keputusan akhir berhasil diperbarui.
2. Record test_decisions untuk batch tersebut berisi:
   - decision_status: tidak_lulus
   - action_recommendation: hold
   - notes sesuai input
3. Record audit_trails bertambah dengan action final_review_updated.
4. Log aplikasi memuat notifikasi tidak_lulus dengan reason dan recommendation.
5. Tidak ada file CoA baru yang dihasilkan dari aksi tidak_lulus.

## Hasil Pengujian (Isi Setelah Eksekusi)

Template pencatatan hasil:
- Tanggal uji:
- Penguji:
- Batch ID:
- Status HTTP:
- Message response:
- Log notifikasi ditemukan (Ya/Tidak):
- CoA baru terbentuk (Ya/Tidak):
- Kesimpulan:

Contoh kesimpulan lulus uji:
Cabang else-if untuk status tidak_lulus berjalan sesuai desain. Notifikasi menuju pihak yang benar dengan payload reason dan recommendation, serta CoA tidak digenerate.
