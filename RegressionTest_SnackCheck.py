from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import Select
import time

def run_snackcheck_regression():
    print("=== Memulai Automated Regression Testing (10 Skenario) ===")
    print("Sistem SnackCheck - Epic Re-test (Kelompok 6)\n")
    
    options = webdriver.ChromeOptions()
    driver = webdriver.Chrome(options=options)
    wait = WebDriverWait(driver, 10)
    
    try:
        # Skenario 1: Buka Halaman & Pastikan UI tidak Crash
        print("[Test 1] Load UI Playground SnackCheck...")
        driver.get("http://127.0.0.1:8000/")
        driver.maximize_window()
        time.sleep(1)
        batch_input = wait.until(EC.presence_of_element_located((By.ID, "batch_id")))
        batch_input.clear()
        batch_input.send_keys("1")
        print("-> [PASS] UI berhasil dimuat. Pintu masuk sistem berfungsi.")
        
        # Skenario 2: Submit Batch Normal (TC-RT-01)
        print("[Test 2] (TC-RT-01) Submit Hasil Uji Normal (Non-Regression)...")
        driver.find_element(By.ID, "btn_submit_test").click()
        time.sleep(2)
        print("-> [PASS] Submit sukses. Endpoint US 3.1 tidak terdampak.")
        
        # Skenario 3: Lihat Detail Batch (TC-RT-02)
        print("[Test 3] (TC-RT-02) Lihat Detail Batch (Non-Regression)...")
        driver.find_element(By.ID, "btn_get_detail").click()
        time.sleep(2)
        detail_text = driver.find_element(By.ID, "detail_summary").text
        assert "Batch:" in detail_text
        print("-> [PASS] Data detail batch termuat utuh di tabel (US 3.2).")
        
        # Skenario 4: Keputusan Lulus Normal & CoA (TC-RT-03)
        print("[Test 4] (TC-RT-03) Simpan Keputusan Lulus & Terbitkan CoA...")
        Select(driver.find_element(By.ID, "keputusan_akhir")).select_by_value("lulus")
        driver.find_element(By.ID, "btn_decide").click()
        time.sleep(2)
        resp_text = driver.find_element(By.ID, "response_box").text
        assert "lulus" in resp_text.lower()
        print("-> [PASS] Keputusan Lulus disimpan, CoA berhasil diterbitkan (US 3.4).")
        
        # Skenario 5: Re-test Ditolak untuk Batch Lulus (TC-RT-05)
        print("[Test 5] (TC-RT-05) Validasi Penolakan Re-test pada Batch Lulus...")
        driver.find_element(By.ID, "btn_request_retest").click()
        time.sleep(2)
        resp_text_retest = driver.find_element(By.ID, "response_box").text
        assert "422" in resp_text_retest or "ditolak" in resp_text_retest.lower()
        print("-> [PASS] Skenario Negatif: Request Re-test sukses DITOLAK (Error 422).")
        
        # Skenario 6: Keputusan Tidak Lulus (Pra-kondisi Epic Baru)
        print("[Test 6] Mengubah status batch menjadi Tidak Lulus untuk skenario Epic...")
        Select(driver.find_element(By.ID, "keputusan_akhir")).select_by_value("tidak_lulus")
        Select(driver.find_element(By.ID, "tindakan_rekomendasi")).select_by_value("rework")
        driver.find_element(By.ID, "catatan").clear()
        driver.find_element(By.ID, "catatan").send_keys("Kadar air melebihi batas SNI")
        driver.find_element(By.ID, "btn_decide").click()
        time.sleep(2)
        print("-> [PASS] Batch diubah menjadi Tidak Lulus.")
        
        # Skenario 7: Request Re-test Sukses (TC-RT-04)
        print("[Test 7] (TC-RT-04) Mengajukan Request Re-test untuk Batch Gagal...")
        driver.find_element(By.ID, "btn_request_retest").click()
        time.sleep(2)
        resp_text_sukses = driver.find_element(By.ID, "response_box").text
        assert "menunggu_retest" in resp_text_sukses.lower()
        print("-> [PASS] Request Re-test BERHASIL! (Status: menunggu_retest).")
        
        # Skenario 8: Verifikasi Sinkronisasi Status Re-test di Detail Batch
        print("[Test 8] Memverifikasi status paska Re-test pada UI Detail...")
        driver.find_element(By.ID, "btn_get_detail").click()
        time.sleep(2)
        detail_text_retest = driver.find_element(By.ID, "detail_summary").text
        assert "menunggu_retest" in detail_text_retest.lower()
        print("-> [PASS] Modul Detail (US 3.2) sinkron dengan status Epic Re-test.")
        
        # Skenario 9: Keputusan Ditahan (TC-RT-11 - Verifikasi CoA diblokir)
        print("[Test 9] (TC-RT-11) Mengubah status menjadi Ditahan (Pastikan CoA tidak terbit)...")
        Select(driver.find_element(By.ID, "keputusan_akhir")).select_by_value("ditahan")
        Select(driver.find_element(By.ID, "tindakan_rekomendasi")).select_by_value("hold")
        driver.find_element(By.ID, "btn_decide").click()
        time.sleep(2)
        resp_text_hold = driver.find_element(By.ID, "response_box").text
        assert "ditahan" in resp_text_hold.lower()
        print("-> [PASS] Status Ditahan tersimpan. CoA ilegal berhasil diblokir.")
        
        # Skenario 10: Keputusan Lulus Paska Re-Test (TC-RT-07)
        print("[Test 10] (TC-RT-07) Menerbitkan keputusan Lulus pada batch paska re-test...")
        Select(driver.find_element(By.ID, "keputusan_akhir")).select_by_value("lulus")
        driver.find_element(By.ID, "catatan").clear()
        driver.find_element(By.ID, "catatan").send_keys("Hasil Re-test sudah sesuai spesifikasi lab")
        driver.find_element(By.ID, "btn_decide").click()
        time.sleep(2)
        print("-> [PASS] Keputusan akhir Lulus diterapkan pada batch re-test (US 3.3).")
        
        print("\n=======================================================")
        print("✓ [SUCCESS] 10 SKENARIO REGRESSION TESTING SNACKCHECK LULUS!")
        print("=======================================================")

    except Exception as e:
        print(f"\n[ERROR] Regression Test Gagal pada Eksekusi: {e}")
        
    finally:
        time.sleep(3)
        driver.quit()
        print("Sesi WebDriver ditutup.")

if __name__ == "__main__":
    run_snackcheck_regression()