# SnackCheck AI Coding Instructions (Laravel MVC)

Dokumen ini adalah panduan system instructions untuk AI yang men-generate kode proyek SnackCheck.
Seluruh implementasi wajib mengikuti standar Laravel modern, berorientasi maintainability, testability, dan keamanan data.

## 1. Tujuan dan Ruang Lingkup

- Proyek menggunakan Laravel (PHP 8+) sebagai backend API.
- Arsitektur utama yang wajib dipakai adalah MVC (Model, View, Controller) standar Laravel.
- Semua perubahan kode harus konsisten dengan struktur folder Laravel bawaan.

## 2. Project Structure (Wajib)

- Gunakan arsitektur MVC standar Laravel.
- Controller hanya menangani alur HTTP: menerima request, memanggil service, mengembalikan response.
- Model hanya menangani representasi data, relasi Eloquent, casting, dan aturan mass assignment.
- View dipakai jika dibutuhkan halaman web; untuk endpoint API backend fokus utama adalah JSON response.
- Logika lintas domain dan pekerjaan berat tidak boleh ditempatkan di Controller.

## 3. Validation (Wajib)

- Selalu pisahkan validasi input dari Controller menggunakan Form Request Validation.
- Dilarang memakai validasi inline di Controller.
- Semua endpoint yang menerima input harus memiliki Form Request khusus.
- Form Request minimal berisi:
    - authorize
    - rules
    - messages (jika perlu memperjelas error)
- Controller hanya menggunakan data tervalidasi dari request.

## 4. Business Logic dengan Service Pattern (Wajib)

- Gunakan Service Pattern untuk proses berat atau reusable, misalnya:
    - generate PDF
    - pengiriman notifikasi
    - integrasi eksternal
    - orkestrasi proses multi-langkah
- Controller harus tetap tipis (thin controller).
- Service dipanggil melalui Dependency Injection, bukan inisialisasi manual di dalam method.
- Proses yang melibatkan banyak penulisan database dianjurkan berada dalam transaksi.

## 5. Data Integrity dan Mass Assignment (Wajib)

- Setiap Model wajib mendefinisikan proteksi mass assignment melalui properti fillable.
- Jangan gunakan input mentah untuk create atau update tanpa whitelist field.
- Pastikan field sensitif tidak dapat diisi mass assignment secara tidak sengaja.
- Untuk kolom enum di database, gunakan enum PHP native dan casting di model jika relevan.

## 6. API Response Standard (Wajib)

- Karena ini backend API, semua Controller harus mengembalikan JSON menggunakan response json.
- Format response harus konsisten:
    - sukses: message, data
    - gagal validasi: mengikuti standar Laravel validation error
    - gagal proses: message error yang jelas dan status code tepat
- Gunakan HTTP status code yang sesuai, misalnya:
    - 200 atau 201 untuk sukses
    - 404 untuk resource tidak ditemukan
    - 422 untuk validasi gagal
    - 500 untuk error server

## 7. Routing dan Naming Convention

- Definisikan route secara eksplisit dengan nama route yang konsisten.
- Gunakan parameter route yang deskriptif dan validasi tipe jika dibutuhkan.
- Penamaan Controller menggunakan PascalCase dengan suffix Controller.
- Penamaan method mengikuti aksi yang jelas, misalnya show, store, update, destroy, submitDecision.

## 8. Eloquent dan Relasi Data

- Definisikan relasi Eloquent secara lengkap dan benar (hasOne, hasMany, belongsTo, dll).
- Lakukan eager loading untuk mencegah N+1 query pada endpoint yang menampilkan relasi.
- Gunakan casting pada atribut tanggal, boolean, integer, dan enum sesuai kebutuhan.

## 9. Error Handling dan Logging

- Tangani exception di lapisan yang tepat dan kembalikan JSON error yang aman.
- Gunakan logging untuk kejadian penting seperti kegagalan proses service.
- Hindari mengembalikan detail internal exception ke client produksi.

## 10. Kualitas Kode

- Wajib mengikuti PSR-12.
- Gunakan strict types jika file sudah menerapkannya.
- Terapkan single responsibility principle pada class dan method.
- Hindari duplikasi logic; ekstrak ke service, helper, atau action class bila diperlukan.

## 11. Checklist Implementasi AI

Sebelum menyelesaikan output, AI wajib memastikan:

- Sudah memakai struktur MVC standar Laravel.
- Validasi sudah dipindah ke Form Request.
- Business logic berat sudah dipindah ke Service Pattern.
- Model yang diubah sudah memiliki fillable yang aman.
- Controller mengembalikan JSON response yang konsisten.
- Route endpoint sudah didefinisikan dan siap diuji.

## 12. Prinsip Eksekusi untuk AI

- Prioritaskan keterbacaan dan maintainability dibanding solusi cepat yang rapuh.
- Jangan membuat perubahan di luar scope task.
- Jika ada ambiguitas requirement, gunakan asumsi paling aman dan dokumentasikan secara singkat.
