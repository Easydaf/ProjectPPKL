# Panduan Pengembangan SnackCheck (Laravel MVC)

File ini berisi instruksi, konvensi, dan best practices untuk AI Copilot dalam menulis kode pada proyek **SnackCheck**. Proyek ini menggunakan arsitektur MVC pada framework Laravel. Selalu gunakan panduan ini sebagai rujukan utama.

## 1. Konvensi Penulisan Kode (Coding Standards)
- **Standar PSR-12:** Semua penulisan kode PHP wajib mematuhi standar PSR-12 (indentasi 4 spasi, *opening brace* class/method di baris baru).
- **Strict Typing:** Selalu gunakan *type-hinting* pada parameter fungsi/method dan tentukan *return type*. 
  - Contoh: `public function submitDecision(ReviewRequest $request, int $batch_id): RedirectResponse`
- **Clean Code & SRP:** Jaga fungsi-fungsi agar pendek dan jalankan *Single Responsibility Principle*. Pindahkan logika bisnis eksternal ke kelas Service/Action (seperti `CoAService` untuk *generate PDF* atau notifikasi `ReTestNotification`).

## 2. Penggunaan Eloquent ORM
Sistem ini menggunakan struktur database relasional yang terpandu. Terapkan konvensi berikut untuk model-model utama:
- **Daftar Model Berdasarkan Tabel:** `User`, `Product`, `Batch`, `AuditTrail`, `TestParameter`, `TestTicket`, `TestDecision`, dan `TestResult`.
- **Relasi Entitas (Relationships):**
  - Manfaatkan metode relasional bawaan Eloquent. Contoh: `Batch` `hasMany` `AuditTrail`, `Batch` `hasMany` `TestTicket`, atau `Batch` `hasOne` `TestDecision`.
  - Gunakan nama fungsi tabel terkait (relasi tunggal = method singular, relasi jamak = method plural).
- **Penanganan Tipe Enum:** Beberapa kolom menggunakan `ENUM` di database (misal: `role` pada `users`, `status` pada `batches`, `decision_status` pada `test_decisions`, dan `category` pada `test_parameters`). Buatlah kelas PHP Enum murni (Native Enum PHP 8.1+) untuk *casting* properti jenis ini di model guna menjamin akurasi validasi.
- **Mass Assignment:** Wajib menggunakan atribut `$fillable` untuk menentukan rincian data mana yang diizinkan untuk di-*insert* secara massal.
- **Audit Logging:** Sebagaimana diagram Kelas, setiap aksi penentuan status *Batch* wajib memicu pembuatan rekaman (record) pada tabel/model `AuditTrail` untuk menjaga ketertelusuran data.

## 3. Pola Validasi Menggunakan Form Request
- **Pemisahan Logika Validasi:** Jangan pernah melakukan validasi *hard-code* atau *inline* pada Controller (misal, hindari `$request->validate([...])`).
- **Gunakan Class Khusus:** Buat dan gunakan `FormRequest` khusus untuk memisahkan logika validasi aturan (*rules*) dan otorisasi dari fungsi pengontrol.
  - *Contoh Berdasarkan Class Diagram:* Gunakan `ReviewRequest` untuk menangani endpoint `submitDecision`.
  - Class `ReviewRequest` ini harus mendefinisikan rules untuk menerima data: `decision` (lulus/tidak_lulus/uji_ulang/ditahan), `recommendation`, dan `decision_reason` (terkait dengan record di tabel `test_decisions` atau status pada `batches`).
- **Injeksi Dependency:** Lakukakan *type-hinting* langsung (*Dependency Injection*) kelas `ReviewRequest` pada metode Controller supaya validasi dijalankan secara otomatis saat *route* dipanggil.

## 4. Aturan Penamaan Route & Controller
- **Penamaan Controller:**
  - Gunakan `PascalCase` diakhiri dengan sufiks `Controller`.
  - Gunakan pendekatan *Thin Controller*, dengan berfokus kepada penerimaan Request dan pengembalian Response/View. Eksekusi yang berat dioper kepada service seperti `CoAService`.
  - **Contoh pada Class Diagram:** Gunakan `ReviewController` untuk mengatur flow UI manajer QC (seperti method `show()` dan `submitDecision()`).
- **Aturan Route:**
  - Buat rute dengan format nama URL menggunakan *kebab-case* dan harus rapi. (Contoh URL: `/batches/{batch}/review`).
  - Selalu terapkan Route Naming menggunakan *dot notation* untuk referensi yang lebih aman di Views dan Controllers (Contoh nama route: `batches.review` atau `review.submit`).
  - Gunakan pembatas rute dalam grup (contoh: di bawah middleware `auth` dan otorisasi *Role* QC Manager untuk akses ke sistem Review).