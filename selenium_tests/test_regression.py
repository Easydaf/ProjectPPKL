import re
from pathlib import Path

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC


BASE_URL = "http://127.0.0.1:8000"

EVIDENCE_DIR = Path("evidence/selenium")
EVIDENCE_DIR.mkdir(parents=True, exist_ok=True)


def test_rt_01_halaman_utama_dapat_dibuka():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        heading = wait.until(
            EC.visibility_of_element_located((By.TAG_NAME, "h1"))
        )

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )

        assert "SnackCheck" in heading.text
        assert batch_input.is_displayed()

        driver.save_screenshot(
            str(EVIDENCE_DIR / "rt-01-halaman-utama.png")
        )

    finally:
        driver.quit()

def test_rt_02_detail_batch_valid_dapat_dibuka():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )

        batch_input.clear()
        batch_input.send_keys("1")

        tombol_detail = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_get_detail"))
        )

        tombol_detail.click()

        wait.until(
            lambda browser: "BATCH-001"
            in browser.find_element(By.ID, "detail_summary").text
        )

        detail_summary = driver.find_element(
            By.ID,
            "detail_summary"
        ).text

        response_box = driver.find_element(
            By.ID,
            "response_box"
        ).text

        test_result_table = driver.find_element(
            By.ID,
            "test_result_table"
        ).text

        assert "BATCH-001" in detail_summary
        assert "HTTP 200" in response_box
        assert "Kadar Air" in test_result_table

        driver.save_screenshot(
            str(EVIDENCE_DIR / "rt-02-detail-batch-valid.png")
        )

    finally:
        driver.quit()

def test_rt_03_batch_tidak_ditemukan():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )

        batch_input.clear()
        batch_input.send_keys("99999")

        tombol_detail = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_get_detail"))
        )

        tombol_detail.click()

        wait.until(
            lambda browser: "HTTP 404"
            in browser.find_element(By.ID, "response_box").text
        )

        response_box = driver.find_element(
            By.ID,
            "response_box"
        ).text

        assert "HTTP 404" in response_box
        assert "Batch tidak ditemukan" in response_box

        driver.save_screenshot(
            str(EVIDENCE_DIR / "rt-03-batch-tidak-ditemukan.png")
        )

    finally:
        driver.quit()

def test_rt_04_submit_hasil_uji_berhasil():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )

        batch_input.clear()
        batch_input.send_keys("1")

        tombol_submit = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_submit_test"))
        )

        tombol_submit.click()

        wait.until(
            lambda browser: "menunggu_review"
            in browser.find_element(By.ID, "response_box").text
        )

        response_box = driver.find_element(
            By.ID,
            "response_box"
        ).text

        assert "HTTP 200" in response_box
        assert "menunggu_review" in response_box

        driver.save_screenshot(
            str(EVIDENCE_DIR / "rt-04-submit-hasil-uji.png")
        )

    finally:
        driver.quit()
    
def test_rt_05_keputusan_lulus_berhasil_disimpan():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )
        batch_input.clear()
        batch_input.send_keys("1")

        keputusan_select = wait.until(
            EC.visibility_of_element_located(
                (By.ID, "keputusan_akhir")
            )
        )
        Select(keputusan_select).select_by_value("lulus")

        catatan_input = driver.find_element(By.ID, "catatan")
        catatan_input.clear()
        catatan_input.send_keys(
            "Seluruh parameter pengujian memenuhi standar kualitas."
        )

        tombol_keputusan = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_decide"))
        )
        tombol_keputusan.click()

        wait.until(
            lambda browser: (
                "HTTP 200"
                in browser.find_element(By.ID, "response_box").text
                and "lulus"
                in browser.find_element(By.ID, "response_box").text.lower()
            )
        )

        response_box = driver.find_element(
            By.ID,
            "response_box"
        ).text

        assert "HTTP 200" in response_box
        assert "lulus" in response_box.lower()

        driver.save_screenshot(
            str(EVIDENCE_DIR / "rt-05-keputusan-lulus.png")
        )

    finally:
        driver.quit()

def test_rt_06_coa_berhasil_dibuat_untuk_batch_lulus():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        # Memilih BATCH-001
        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )
        batch_input.clear()
        batch_input.send_keys("1")

        # Menetapkan keputusan lulus
        keputusan_select = wait.until(
            EC.visibility_of_element_located(
                (By.ID, "keputusan_akhir")
            )
        )
        Select(keputusan_select).select_by_value("lulus")

        catatan_input = driver.find_element(By.ID, "catatan")
        catatan_input.clear()
        catatan_input.send_keys(
            "Seluruh parameter memenuhi standar dan CoA dapat diterbitkan."
        )

        tombol_keputusan = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_decide"))
        )
        tombol_keputusan.click()

        # Memastikan keputusan berhasil disimpan
        wait.until(
            lambda browser: (
                "HTTP 200"
                in browser.find_element(By.ID, "response_box").text
                and "lulus"
                in browser.find_element(By.ID, "response_box").text.lower()
            )
        )

        response_keputusan = driver.find_element(
            By.ID,
            "response_box"
        ).text.lower()

        assert "http 200" in response_keputusan
        assert "lulus" in response_keputusan

        # Mengambil detail batch setelah keputusan lulus
        tombol_detail = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_get_detail"))
        )
        tombol_detail.click()

        wait.until(
            lambda browser: (
                "COA-"
                in browser.find_element(By.ID, "detail_summary").text
            )
        )

        detail_summary = driver.find_element(
            By.ID,
            "detail_summary"
        ).text

        assert "lulus" in detail_summary.lower()
        assert "COA-" in detail_summary

        # Memastikan link PDF CoA tersedia
        coa_link = wait.until(
            EC.presence_of_element_located(
                (
                    By.CSS_SELECTOR,
                    'a[href*="/storage/coa/"]'
                )
            )
        )

        coa_url = coa_link.get_attribute("href")

        assert coa_url is not None
        assert coa_url.lower().endswith(".pdf")

        # Memastikan file PDF benar-benar tersimpan
        nama_file_coa = coa_url.rsplit("/", 1)[-1]

        lokasi_file_coa = (
            Path("storage")
            / "app"
            / "public"
            / "coa"
            / nama_file_coa
        )

        assert lokasi_file_coa.exists()
        assert lokasi_file_coa.stat().st_size > 0

        driver.save_screenshot(
            str(
                EVIDENCE_DIR
                / "rt-06-coa-berhasil-dibuat.png"
            )
        )

    finally:
        driver.quit()
    
def test_rt_07_tidak_lulus_tanpa_rekomendasi_ditolak():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )
        batch_input.clear()
        batch_input.send_keys("1")

        tombol_submit = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_submit_test"))
        )
        tombol_submit.click()

        wait.until(
            lambda browser: (
                "menunggu_review"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        keputusan_select = wait.until(
            EC.visibility_of_element_located(
                (By.ID, "keputusan_akhir")
            )
        )
        Select(keputusan_select).select_by_value("tidak_lulus")

        tindakan_select = driver.find_element(
            By.ID,
            "tindakan_rekomendasi"
        )
        Select(tindakan_select).select_by_value("")

        catatan_input = driver.find_element(By.ID, "catatan")
        catatan_input.clear()
        catatan_input.send_keys(
            "Hasil pengujian tidak memenuhi standar."
        )

        tombol_keputusan = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_decide"))
        )
        tombol_keputusan.click()

        wait.until(
            lambda browser: (
                "HTTP 422"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        response_box = driver.find_element(
            By.ID,
            "response_box"
        ).text

        assert "HTTP 422" in response_box
        assert "tindakan_rekomendasi" in response_box.lower()

        driver.save_screenshot(
            str(
                EVIDENCE_DIR
                / "rt-07-tidak-lulus-tanpa-rekomendasi.png"
            )
        )

    finally:
        driver.quit()

def test_rt_08_tidak_lulus_dengan_rework_berhasil():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )
        batch_input.clear()
        batch_input.send_keys("1")

        # Menjadikan test mandiri dengan submit hasil uji terlebih dahulu
        tombol_submit = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_submit_test"))
        )
        tombol_submit.click()

        wait.until(
            lambda browser: (
                "menunggu_review"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        keputusan_select = wait.until(
            EC.visibility_of_element_located(
                (By.ID, "keputusan_akhir")
            )
        )
        Select(keputusan_select).select_by_value("tidak_lulus")

        tindakan_select = driver.find_element(
            By.ID,
            "tindakan_rekomendasi"
        )
        Select(tindakan_select).select_by_value("rework")

        catatan_input = driver.find_element(By.ID, "catatan")
        catatan_input.clear()
        catatan_input.send_keys(
            "Batch tidak memenuhi standar dan harus diproses ulang."
        )

        tombol_keputusan = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_decide"))
        )
        tombol_keputusan.click()

        wait.until(
            lambda browser: (
                "HTTP 200"
                in browser.find_element(By.ID, "response_box").text
                and "tidak_lulus"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        response_keputusan = driver.find_element(
            By.ID,
            "response_box"
        ).text

        assert "HTTP 200" in response_keputusan
        assert "tidak_lulus" in response_keputusan

        # Memuat detail agar action_recommendation tampil di response API
        tombol_detail = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_get_detail"))
        )
        tombol_detail.click()

        wait.until(
            lambda browser: (
                "HTTP 200"
                in browser.find_element(By.ID, "response_box").text
                and "rework"
                in browser.find_element(By.ID, "response_box").text.lower()
            )
        )

        response_detail = driver.find_element(
            By.ID,
            "response_box"
        ).text.lower()

        detail_summary = driver.find_element(
            By.ID,
            "detail_summary"
        ).text.lower()

        assert "tidak_lulus" in detail_summary
        assert "action_recommendation" in response_detail
        assert "rework" in response_detail

        driver.save_screenshot(
            str(
                EVIDENCE_DIR
                / "rt-08-tidak-lulus-dengan-rework.png"
            )
        )

    finally:
        driver.quit()

def test_rt_09_keputusan_uji_ulang_tanpa_coa():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )
        batch_input.clear()
        batch_input.send_keys("1")

        tombol_submit = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_submit_test"))
        )
        tombol_submit.click()

        wait.until(
            lambda browser: (
                "menunggu_review"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        keputusan_select = wait.until(
            EC.visibility_of_element_located(
                (By.ID, "keputusan_akhir")
            )
        )
        Select(keputusan_select).select_by_value("uji_ulang")

        catatan_input = driver.find_element(By.ID, "catatan")
        catatan_input.clear()
        catatan_input.send_keys(
            "Hasil pengujian perlu diverifikasi melalui pengujian ulang."
        )

        driver.find_element(By.ID, "btn_decide").click()

        wait.until(
            lambda browser: (
                "Keputusan akhir batch berhasil diperbarui"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        response_keputusan = driver.find_element(
            By.ID,
            "response_box"
        ).text.lower()

        assert "http 200" in response_keputusan
        assert "uji_ulang" in response_keputusan

        driver.find_element(By.ID, "btn_get_detail").click()

        wait.until(
            lambda browser: (
                "Detail batch berhasil diambil"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        detail_summary = driver.find_element(
            By.ID,
            "detail_summary"
        ).text.lower()

        response_detail = driver.find_element(
            By.ID,
            "response_box"
        ).text.lower()

        assert "uji_ulang" in detail_summary
        assert '"coa_number": null' in response_detail
        assert '"coa_document": null' in response_detail
        assert "coa-" not in detail_summary

        driver.save_screenshot(
            str(
                EVIDENCE_DIR
                / "rt-09-keputusan-uji-ulang-tanpa-coa.png"
            )
        )

    finally:
        driver.quit()

def test_rt_10_keputusan_ditahan_tanpa_coa():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )
        batch_input.clear()
        batch_input.send_keys("1")

        # Mengirim hasil pengujian
        tombol_submit = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_submit_test"))
        )
        tombol_submit.click()

        wait.until(
            lambda browser: (
                "menunggu_review"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        # Memilih keputusan ditahan
        keputusan_select = wait.until(
            EC.visibility_of_element_located(
                (By.ID, "keputusan_akhir")
            )
        )
        Select(keputusan_select).select_by_value("ditahan")

        tindakan_select = driver.find_element(
            By.ID,
            "tindakan_rekomendasi"
        )
        Select(tindakan_select).select_by_value("")

        catatan_input = driver.find_element(By.ID, "catatan")
        catatan_input.clear()
        catatan_input.send_keys(
            "Batch ditahan sementara untuk pemeriksaan lanjutan."
        )

        tombol_keputusan = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_decide"))
        )
        tombol_keputusan.click()

        wait.until(
            lambda browser: (
                "HTTP 200"
                in browser.find_element(By.ID, "response_box").text
                and "ditahan"
                in browser.find_element(By.ID, "response_box").text.lower()
            )
        )

        response_keputusan = driver.find_element(
            By.ID,
            "response_box"
        ).text.lower()

        assert "http 200" in response_keputusan
        assert "ditahan" in response_keputusan

        # Memeriksa detail batch
        tombol_detail = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_get_detail"))
        )
        tombol_detail.click()

        wait.until(
            lambda browser: (
                "ditahan"
                in browser.find_element(By.ID, "detail_summary").text.lower()
            )
        )

        detail_summary = driver.find_element(
            By.ID,
            "detail_summary"
        ).text.lower()

        assert "ditahan" in detail_summary
        assert "coa-" not in detail_summary

        driver.save_screenshot(
            str(
                EVIDENCE_DIR
                / "rt-10-keputusan-ditahan-tanpa-coa.png"
            )
        )

    finally:
        driver.quit()

def test_rt_11_audit_trail_keputusan_tercatat():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )
        batch_input.clear()
        batch_input.send_keys("1")

        # Status awal hasil seeder adalah menunggu_review
        keputusan_select = wait.until(
            EC.visibility_of_element_located(
                (By.ID, "keputusan_akhir")
            )
        )
        Select(keputusan_select).select_by_value("ditahan")

        tindakan_select = driver.find_element(
            By.ID,
            "tindakan_rekomendasi"
        )
        Select(tindakan_select).select_by_value("")

        catatan_input = driver.find_element(By.ID, "catatan")
        catatan_input.clear()
        catatan_input.send_keys(
            "Batch ditahan untuk pemeriksaan lanjutan."
        )

        tombol_keputusan = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_decide"))
        )
        tombol_keputusan.click()

        wait.until(
            lambda browser: (
                "HTTP 200"
                in browser.find_element(By.ID, "response_box").text
                and "ditahan"
                in browser.find_element(By.ID, "response_box").text.lower()
            )
        )

        # Membuka detail batch untuk mengambil data audit trail
        tombol_detail = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_get_detail"))
        )
        tombol_detail.click()

        wait.until(
            lambda browser: (
                "final_review_updated"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        response_detail = driver.find_element(
            By.ID,
            "response_box"
        ).text.lower()

        assert "http 200" in response_detail
        assert "audit_trails" in response_detail
        assert "final_review_updated" in response_detail
        assert "old_values" in response_detail
        assert "new_values" in response_detail
        assert "menunggu_review" in response_detail
        assert "ditahan" in response_detail

        driver.save_screenshot(
            str(
                EVIDENCE_DIR
                / "rt-11-audit-trail-keputusan.png"
            )
        )

    finally:
        driver.quit()

def test_rt_12_coa_tidak_terduplikasi():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get(BASE_URL)

        wait = WebDriverWait(driver, 10)

        batch_input = wait.until(
            EC.visibility_of_element_located((By.ID, "batch_id"))
        )
        batch_input.clear()
        batch_input.send_keys("1")

        keputusan_select = wait.until(
            EC.visibility_of_element_located(
                (By.ID, "keputusan_akhir")
            )
        )
        Select(keputusan_select).select_by_value("lulus")

        catatan_input = driver.find_element(By.ID, "catatan")
        catatan_input.clear()
        catatan_input.send_keys(
            "Keputusan lulus pertama untuk pengujian duplikasi CoA."
        )

        driver.find_element(By.ID, "btn_decide").click()

        wait.until(
            lambda browser: (
                "Keputusan akhir batch berhasil diperbarui"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        driver.find_element(By.ID, "btn_get_detail").click()

        wait.until(
            lambda browser: (
                "Detail batch berhasil diambil"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        response_pertama = driver.find_element(
            By.ID,
            "response_box"
        ).text

        nomor_pertama_match = re.search(
            r'"coa_number"\s*:\s*"([^"]+)"',
            response_pertama,
        )

        path_pertama_match = re.search(
            r'"coa_document"\s*:\s*"([^"]+)"',
            response_pertama,
        )

        assert nomor_pertama_match is not None
        assert path_pertama_match is not None

        nomor_coa_pertama = nomor_pertama_match.group(1)
        path_coa_pertama = path_pertama_match.group(1)

        assert nomor_coa_pertama.startswith("COA-")
        assert path_coa_pertama.endswith(".pdf")

        keputusan_select = wait.until(
            EC.visibility_of_element_located(
                (By.ID, "keputusan_akhir")
            )
        )
        Select(keputusan_select).select_by_value("lulus")

        catatan_input = wait.until(
            EC.visibility_of_element_located((By.ID, "catatan"))
        )
        catatan_input.clear()
        catatan_input.send_keys(
            "Keputusan lulus kedua untuk memastikan CoA tidak terduplikasi."
        )

        tombol_keputusan = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_decide"))
        )
        tombol_keputusan.click()

        wait.until(
            lambda browser: (
                "Keputusan akhir batch berhasil diperbarui"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        tombol_detail = wait.until(
            EC.element_to_be_clickable((By.ID, "btn_get_detail"))
        )
        tombol_detail.click()

        wait.until(
            lambda browser: (
                "Detail batch berhasil diambil"
                in browser.find_element(By.ID, "response_box").text
            )
        )

        response_kedua = driver.find_element(
            By.ID,
            "response_box"
        ).text

        nomor_kedua_match = re.search(
            r'"coa_number"\s*:\s*"([^"]+)"',
            response_kedua,
        )

        path_kedua_match = re.search(
            r'"coa_document"\s*:\s*"([^"]+)"',
            response_kedua,
        )

        assert nomor_kedua_match is not None
        assert path_kedua_match is not None

        nomor_coa_kedua = nomor_kedua_match.group(1)
        path_coa_kedua = path_kedua_match.group(1)

        assert nomor_coa_kedua == nomor_coa_pertama
        assert path_coa_kedua == path_coa_pertama

        lokasi_file_coa = (
            Path("storage")
            / "app"
            / "public"
            / path_coa_kedua
        )

        assert lokasi_file_coa.exists()
        assert lokasi_file_coa.stat().st_size > 0

        driver.save_screenshot(
            str(
                EVIDENCE_DIR
                / "rt-12-coa-tidak-terduplikasi.png"
            )
        )

    finally:
        driver.quit()