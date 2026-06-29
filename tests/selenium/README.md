# Selenium Regression Test Suite
## SnackCheck — Kelompok 6 | UAS PPKPL

### Setup

```bash
# Install dependencies
pip install selenium pytest requests

# Pastikan aplikasi Laravel berjalan di port 8000
php artisan serve

# Pastikan ChromeDriver sesuai versi Chrome
# Download: https://chromedriver.chromium.org/downloads
```

### Struktur File

```
tests/selenium/
├── test_regression_snackcheck.py   # File test utama
├── requirements.txt                # Python dependencies
└── README.md                       # Dokumen ini
```

### Menjalankan Test

```bash
# Jalankan semua test
pytest tests/selenium/test_regression_snackcheck.py -v

# Jalankan hanya test Critical
pytest tests/selenium/test_regression_snackcheck.py -v -k "TestSubmitTestValid or TestReviewLulusCoA or TestCoANotGeneratedForNonLulus"

# Jalankan dengan output ringkas
pytest tests/selenium/test_regression_snackcheck.py -q
```

### Mapping Test ke Skenario

| Class | TC | Prioritas |
|---|---|---|
| TestSubmitTestValid | TC-RT-01 | Critical |
| TestReviewLulusCoA | TC-RT-03 | Critical |
| TestReviewValidation | TC-RT-04, TC-RT-10 | High |
| TestReviewDetailEndpoint | TC-RT-07 | High |
| TestEnumCompatibility | TC-RT-09 | High |
| TestCoANotGeneratedForNonLulus | TC-RT-11 | Critical |
| TestReviewPlaygroundUI | TC-RT-UI | High |

### Persiapan Database

Sebelum menjalankan test, pastikan:

```bash
php artisan migrate:fresh --seed
```

Seeder harus menyiapkan:
- Minimal 1 user dengan role `qc_manager`
- Minimal 1 batch dengan status `sedang_diuji` (BATCH_ID = 1)
- Minimal 1 product dan beberapa test_results
