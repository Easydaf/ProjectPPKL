# ProjectALL - Dokumentasi Track Record Prompting

Dokumen ini merekam jejak prompting dari proses pembuatan project SnackCheck Journey 3.
Format setiap item:

- Prompt User
- Context Prompt
- Hasil dari Prompt

## Ringkasan Project

- Nama project: SnackCheck
- Fokus implementasi: Journey 3 (US 3.1 sampai US 3.5)
- Stack utama: Laravel 13, PHP 8.3, MySQL
- Tujuan akhir: Sistem review batch end-to-end yang bisa didemokan

---

## 1) Penyusunan Instruksi Coding

### Prompt User

Minta dibuatkan isi file skills.md sebagai panduan best practice Laravel MVC.

### Context Prompt

Project masih basic dan butuh standar implementasi agar output code konsisten.

### Hasil dari Prompt

- File skills.md ditulis ulang jadi panduan coding Laravel.
- Isi mencakup MVC, Form Request, Service Pattern, fillable, JSON response, dan routing convention.

---

## 2) Pembuatan Dokumen User Story (Template Prompt)

### Prompt User

Minta dibuatkan file markdown user story dengan format ketat untuk Journey 3.

### Context Prompt

Butuh template prompt-driven development sebagai dasar implementasi.

### Hasil dari Prompt

Dibuat file markdown:

- us_3_1_notifikasi_review.md
- us_3_2_detail_hasil_uji.md
- us_3_4_penerbitan_coa.md
- us_3_5_notifikasi_keputusan.md

---

## 3) Perubahan Arah: Dari Dokumen ke Implementasi Nyata

### Prompt User

Minta agar bukan hanya markdown, tapi langsung dibuatkan code agar jadi satu sistem sesuai Journey 3.

### Context Prompt

Dokumen spesifikasi sudah ada, namun aplikasi belum benar-benar bisa dijalankan end-to-end.

### Hasil dari Prompt

Mulai implementasi backend nyata:

- Penambahan route review dan submit test.
- Implementasi controller utama review flow.
- Integrasi service untuk CoA dan notifikasi.
- Perbaikan alur status batch dan audit trail.

---

## 4) Pondasi Domain Journey 3

### Prompt User

Lanjutkan pembuatan code sesuai semua user story yang sudah dibuat.

### Context Prompt

Sistem butuh model dan tabel tambahan agar data Journey 3 lengkap.

### Hasil dari Prompt

Penambahan domain model dan schema:

- Model: Product, TestParameter, TestResult, CoaDocument.
- Enum: UserRole, ParameterCategory, TestResultStatus.
- Migrations: products, test_parameters, test_results, coa_documents.
- Relasi model batch, decision, audit trail diperluas.

---

## 5) Implementasi User Story 3.1

### Prompt User

Fokus submit hasil tes analis dan notifikasi ke QC Manager.

### Context Prompt

US 3.1 menuntut status batch berubah ke menunggu review setelah submit.

### Hasil dari Prompt

- Dibuat SubmitTestController untuk endpoint submit.
- Status batch diupdate ke menunggu_review.
- Audit trail action submitted_for_review dicatat.
- NotificationService mengirim notifikasi review-ready ke QC Manager (melalui logging).

---

## 6) Implementasi User Story 3.2

### Prompt User

Minta detail hasil uji lengkap, indikator lulus/tidak, dan lampiran.

### Context Prompt

QC Manager perlu melihat ringkasan data uji sebelum memutuskan.

### Hasil dari Prompt

- Endpoint GET detail batch pada ReviewController.
- Response JSON memuat:
    - Data batch
    - Data test results
    - Dokumen CoA (jika ada)
    - Test decision
    - Audit trail
- Eager loading dipakai agar query efisien.

---

## 7) Implementasi User Story 3.3

### Prompt User

Minta proses review keputusan akhir QC dapat disimpan.

### Context Prompt

Perlu validasi keputusan akhir dan rekomendasi tindakan.

### Hasil dari Prompt

- ReviewBatchRequest dipakai untuk validasi.
- Endpoint PUT/PATCH review update aktif.
- TestDecision update/create saat keputusan disimpan.
- Audit trail final_review_updated dicatat.

---

## 8) Implementasi User Story 3.4

### Prompt User

Minta CoA otomatis saat keputusan lulus.

### Context Prompt

Setelah keputusan lulus, sistem harus menerbitkan CoA.

### Hasil dari Prompt

- CoAService dipanggil saat keputusan lulus.
- Nomor CoA unik format COA-YYYYMMDD-ID dibuat.
- Data CoA disimpan ke tabel coa_documents.
- Path CoA disimpan juga di test_decisions.coa_path.

---

## 9) Implementasi User Story 3.5

### Prompt User

Minta notifikasi keputusan akhir ke pihak terkait.

### Context Prompt

Distribusi info keputusan berbeda per status akhir.

### Hasil dari Prompt

NotificationService broadcastDecision ditambahkan:

- lulus: notifikasi ke manajer produksi dan staff gudang + path CoA.
- tidak_lulus: notifikasi ke manajer produksi + alasan dan rekomendasi.
- uji_ulang: notifikasi ke analis.

---

## 10) Perbaikan Runtime dan Demo Readiness

### Prompt User

Minta project bisa dipakai otak-atik karena sebelumnya tidak ada view yang bisa dipakai demo.

### Context Prompt

Backend sudah ada, tapi belum ada UI kerja yang praktis untuk jalankan semua flow.

### Hasil dari Prompt

- Dibuat halaman playground review yang interaktif.
- User dapat trigger endpoint US 3.1 sampai US 3.5 dari satu halaman.
- Output JSON dan ringkasan data ditampilkan langsung di UI.
- authorize ReviewBatchRequest disesuaikan untuk local/testing agar demo tidak mentok 403 saat belum ada auth flow lengkap.

---

## 11) Perbaikan PDF Menggunakan DOMPDF

### Prompt User

Minta PDF CoA tidak kosong dan diganti menggunakan DOMPDF.

### Context Prompt

File CoA sebelumnya berupa file teks dengan ekstensi PDF sehingga tidak valid untuk dokumen resmi.

### Hasil dari Prompt

- Package barryvdh/laravel-dompdf diinstall via Composer.
- CoAService diubah untuk render view ke PDF binary asli.
- Template PDF dibuat di resources/views/coa/document.blade.php.
- Hasil verifikasi: file PDF terbentuk dengan ukuran nyata (bukan kosong).

---

## Daftar File Utama yang Berubah

- app/Http/Controllers/ReviewController.php
- app/Http/Controllers/SubmitTestController.php
- app/Http/Requests/ReviewBatchRequest.php
- app/Services/NotificationService.php
- app/Services/CoAService.php
- app/Models/Batch.php
- app/Models/TestDecision.php
- app/Models/AuditTrail.php
- app/Models/Product.php
- app/Models/TestParameter.php
- app/Models/TestResult.php
- app/Models/CoaDocument.php
- app/Enums/UserRole.php
- app/Enums/ParameterCategory.php
- app/Enums/TestResultStatus.php
- database/migrations/2026_04_19_000007_create_products_table.php
- database/migrations/2026_04_19_000008_create_test_parameters_table.php
- database/migrations/2026_04_19_000009_create_test_results_table.php
- database/migrations/2026_04_19_000010_create_coa_documents_table.php
- database/seeders/DatabaseSeeder.php
- resources/views/reviews/show.blade.php
- resources/views/coa/document.blade.php
- routes/web.php
- skills.md

---

## Penutup

Dokumen ini bisa dijadikan:

- Log histori prompting
- Bahan presentasi progres pengembangan
- Baseline dokumentasi teknis Journey 3

Jika diperlukan, versi berikutnya bisa ditambah:

- Tanggal dan jam per prompt
- Screenshot hasil UI per user story
- Mapping prompt ke commit hash git
