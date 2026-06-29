"""
Selenium & API Regression Test Suite -- SnackCheck System
Kelompok 6 | UAS PPKPL

Tujuan: Memastikan modul yang ada tidak terdampak oleh penambahan
Epic Modul Penerimaan Bahan Baku & Persetujuan RA Officer.

Dependencies:
    pip install selenium pytest requests

Run:
    pytest test_regression_snackcheck.py -v
"""

import pytest
import requests
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options


# ---------------------------------------------------------------------------
# Konfigurasi
# ---------------------------------------------------------------------------

BASE_URL = "http://localhost:8000"
REVIEW_UI_URL = f"{BASE_URL}/"
BATCH_ID = 1  # ID batch yang disiapkan oleh seeder


# ---------------------------------------------------------------------------
# Fixtures
# ---------------------------------------------------------------------------

@pytest.fixture(scope="module")
def browser():
    """Setup Chrome WebDriver untuk UI testing."""
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--window-size=1280,800")
    driver = webdriver.Chrome(options=options)
    driver.implicitly_wait(5)
    yield driver
    driver.quit()


@pytest.fixture(scope="module")
def api():
    """Session requests untuk API testing."""
    session = requests.Session()
    session.headers.update({
        "Accept": "application/json",
        "Content-Type": "application/json",
    })
    return session


# ---------------------------------------------------------------------------
# TC-RT-01: Submit Test dari Status Valid
# Non-regression: alur ini tidak boleh berubah akibat epic baru.
# ---------------------------------------------------------------------------

class TestSubmitTestValid:
    """TC-RT-01 -- Submit test dari batch sedang_diuji."""

    def test_submit_returns_200(self, api):
        response = api.post(f"{BASE_URL}/batches/{BATCH_ID}/submit-test")
        assert response.status_code == 200, (
            f"Expected 200, got {response.status_code}. Body: {response.text}"
        )

    def test_submit_changes_status_to_menunggu_review(self, api):
        response = api.post(f"{BASE_URL}/batches/{BATCH_ID}/submit-test")
        if response.status_code == 200:
            data = response.json()
            assert data["data"]["status"] == "menunggu_review", (
                f"Status harus menunggu_review, tapi: {data['data']['status']}"
            )

    def test_submit_creates_audit_trail(self, api):
        """Setiap submit harus mencatat AuditTrail."""
        get_resp = api.get(f"{BASE_URL}/batches/{BATCH_ID}/review")
        if get_resp.status_code != 200:
            pytest.skip("Batch tidak bisa diakses")
        audit_trails = get_resp.json()["data"]["audit_trails"]
        actions = [t["action"] for t in audit_trails]
        assert any("submitted" in a for a in actions), (
            "AuditTrail harus memiliki action yang berkaitan dengan submit"
        )


# ---------------------------------------------------------------------------
# TC-RT-03: Review "lulus" Menghasilkan CoA
# Non-regression (Critical): inti bisnis -- dokumen legal.
# ---------------------------------------------------------------------------

class TestReviewLulusCoA:
    """TC-RT-03 -- Keputusan lulus harus menghasilkan CoA."""

    def test_lulus_returns_200(self, api):
        payload = {
            "keputusan_akhir": "lulus",
            "catatan": "Semua parameter memenuhi standar SNI"
        }
        response = api.put(
            f"{BASE_URL}/batches/{BATCH_ID}/review",
            data=json.dumps(payload)
        )
        assert response.status_code == 200, (
            f"Expected 200, got {response.status_code}. Body: {response.text}"
        )

    def test_lulus_creates_coa_document(self, api):
        """Setelah lulus, data CoA harus ada di response."""
        get_resp = api.get(f"{BASE_URL}/batches/{BATCH_ID}/review")
        if get_resp.status_code != 200:
            pytest.skip("Batch tidak bisa diakses")
        documents = get_resp.json()["data"]["documents"]
        assert documents.get("coa_number") is not None, (
            "coa_number tidak boleh null setelah keputusan lulus"
        )

    def test_coa_number_format(self, api):
        """Nomor CoA harus format COA-YYYYMMDD-{batch_id}."""
        get_resp = api.get(f"{BASE_URL}/batches/{BATCH_ID}/review")
        if get_resp.status_code != 200:
            pytest.skip("Batch tidak bisa diakses")
        coa_number = get_resp.json()["data"]["documents"].get("coa_number")
        if coa_number is None:
            pytest.skip("CoA belum dibuat -- jalankan lulus decision dulu")
        parts = coa_number.split("-")
        assert parts[0] == "COA", f"Prefix harus COA, dapat: {parts[0]}"
        assert len(parts[1]) == 8, f"Tanggal harus 8 digit, dapat: {parts[1]}"
        assert parts[2].isdigit(), "Bagian terakhir harus angka (batch_id)"


# ---------------------------------------------------------------------------
# TC-RT-04 & TC-RT-10: Validasi Input ReviewBatchRequest
# Non-regression: aturan validasi tidak boleh berubah.
# ---------------------------------------------------------------------------

class TestReviewValidation:
    """TC-RT-04 & TC-RT-10 -- Validasi input keputusan."""

    def test_tidak_lulus_without_rekomendasi_returns_422(self, api):
        """tidak_lulus tanpa tindakan_rekomendasi harus HTTP 422."""
        payload = {"keputusan_akhir": "tidak_lulus"}
        response = api.put(
            f"{BASE_URL}/batches/{BATCH_ID}/review",
            data=json.dumps(payload)
        )
        assert response.status_code == 422, (
            f"Expected 422, got {response.status_code}"
        )

    def test_tidak_lulus_error_on_tindakan_field(self, api):
        """Error 422 harus menunjuk field tindakan_rekomendasi."""
        payload = {"keputusan_akhir": "tidak_lulus"}
        response = api.put(
            f"{BASE_URL}/batches/{BATCH_ID}/review",
            data=json.dumps(payload)
        )
        if response.status_code == 422:
            data = response.json()
            assert "errors" in data, "Response 422 harus berisi key 'errors'"
            assert "tindakan_rekomendasi" in data["errors"]

    def test_invalid_keputusan_returns_422(self, api):
        """Nilai keputusan yang tidak ada di enum harus HTTP 422."""
        payload = {"keputusan_akhir": "nilai_tidak_ada"}
        response = api.put(
            f"{BASE_URL}/batches/{BATCH_ID}/review",
            data=json.dumps(payload)
        )
        assert response.status_code == 422


# ---------------------------------------------------------------------------
# TC-RT-07: GET Review Menampilkan Data Lengkap
# Non-regression: response schema tidak boleh berubah.
# ---------------------------------------------------------------------------

class TestReviewDetailEndpoint:
    """TC-RT-07 -- Schema response GET /batches/{id}/review."""

    REQUIRED_KEYS = ["batch", "test_results", "documents", "test_decision", "audit_trails"]

    def test_review_detail_returns_200(self, api):
        response = api.get(f"{BASE_URL}/batches/{BATCH_ID}/review")
        assert response.status_code == 200

    def test_review_detail_has_all_required_keys(self, api):
        response = api.get(f"{BASE_URL}/batches/{BATCH_ID}/review")
        if response.status_code != 200:
            pytest.skip("Batch tidak bisa diakses")
        data = response.json()["data"]
        for key in self.REQUIRED_KEYS:
            assert key in data, f"Key '{key}' tidak ada di response"

    def test_unknown_batch_returns_404(self, api):
        response = api.get(f"{BASE_URL}/batches/99999/review")
        assert response.status_code == 404


# ---------------------------------------------------------------------------
# TC-RT-09: Enum Compatibility Setelah Migrasi
# Regression dari CR-01 -- risiko tertinggi epic ini.
# ---------------------------------------------------------------------------

class TestEnumCompatibility:
    """TC-RT-09 -- Status lama tetap dikenali setelah enum baru ditambahkan."""

    VALID_STATUSES = [
        "menunggu_penerimaan", "sedang_diuji", "menunggu_review",
        "lulus", "tidak_lulus", "ditahan", "uji_ulang"
    ]

    def test_batch_status_is_valid_enum_value(self, api):
        response = api.get(f"{BASE_URL}/batches/{BATCH_ID}/review")
        if response.status_code != 200:
            pytest.skip("Batch tidak bisa diakses")
        status = response.json()["data"]["batch"]["status"]
        assert status in self.VALID_STATUSES, (
            f"Status '{status}' bukan nilai enum yang valid"
        )

    def test_review_endpoint_not_returning_500_after_migration(self, api):
        """Setelah migrasi enum baru, endpoint tidak boleh error 500."""
        response = api.get(f"{BASE_URL}/batches/{BATCH_ID}/review")
        assert response.status_code != 500, (
            "Endpoint review error 500 -- kemungkinan enum casting gagal"
        )


# ---------------------------------------------------------------------------
# TC-RT-11: CoA Tidak Terbit untuk Bukan "lulus"
# Non-regression (Critical): dokumen legal tidak boleh salah terbit.
# ---------------------------------------------------------------------------

class TestCoANotGeneratedForNonLulus:
    """TC-RT-11 -- CoA hanya terbit untuk keputusan lulus."""

    def test_ditahan_has_no_coa(self, api):
        payload = {"keputusan_akhir": "ditahan"}
        put_resp = api.put(
            f"{BASE_URL}/batches/{BATCH_ID}/review",
            data=json.dumps(payload)
        )
        if put_resp.status_code != 200:
            pytest.skip("Batch tidak dalam status yang bisa di-review")

        get_resp = api.get(f"{BASE_URL}/batches/{BATCH_ID}/review")
        documents = get_resp.json()["data"]["documents"]
        assert documents.get("coa_number") is None, (
            f"CoA tidak boleh terbit untuk 'ditahan', "
            f"tapi coa_number = {documents.get('coa_number')}"
        )

    def test_tidak_lulus_has_no_coa(self, api):
        payload = {
            "keputusan_akhir": "tidak_lulus",
            "tindakan_rekomendasi": "disposal"
        }
        put_resp = api.put(
            f"{BASE_URL}/batches/{BATCH_ID}/review",
            data=json.dumps(payload)
        )
        if put_resp.status_code != 200:
            pytest.skip("Batch tidak dalam status yang bisa di-review")

        get_resp = api.get(f"{BASE_URL}/batches/{BATCH_ID}/review")
        documents = get_resp.json()["data"]["documents"]
        assert documents.get("coa_number") is None, (
            "CoA tidak boleh terbit untuk 'tidak_lulus'"
        )


# ---------------------------------------------------------------------------
# TC-RT-UI: Selenium UI Tests (halaman playground)
# Memastikan UI tetap berfungsi setelah perubahan epic.
# ---------------------------------------------------------------------------

class TestReviewPlaygroundUI:
    """UI regression menggunakan Selenium WebDriver."""

    def test_homepage_loads_without_error(self, browser):
        """Halaman utama harus bisa dimuat tanpa PHP fatal error."""
        browser.get(REVIEW_UI_URL)
        wait = WebDriverWait(browser, 10)
        wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        page_source = browser.page_source
        assert "Fatal error" not in page_source, "PHP Fatal error terdeteksi"
        assert "Whoops" not in page_source, "Laravel error page (Whoops) terdeteksi"

    def test_homepage_has_content(self, browser):
        """Halaman tidak boleh kosong."""
        browser.get(REVIEW_UI_URL)
        body_text = browser.find_element(By.TAG_NAME, "body").text
        assert len(body_text.strip()) > 0, "Halaman tidak menampilkan konten apapun"

    def test_page_title_not_error(self, browser):
        """Title halaman tidak boleh mengandung '500' atau 'Error'."""
        browser.get(REVIEW_UI_URL)
        title = browser.title
        assert "500" not in title
        assert "Error" not in title.lower()
