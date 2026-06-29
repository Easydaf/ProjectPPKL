import pytest
import pytest_html
import base64
import re
import subprocess
from pathlib import Path




PROJECT_ROOT = Path(__file__).resolve().parent.parent


@pytest.fixture(autouse=True)
def reset_database():
    result = subprocess.run(
        [
            "php",
            "artisan",
            "migrate:fresh",
            "--seed",
            "--force",
        ],
        cwd=PROJECT_ROOT,
        capture_output=True,
        text=True,
    )

    if result.returncode != 0:
        pytest.fail(
            "Gagal mereset database sebelum pengujian.\n"
            f"STDOUT:\n{result.stdout}\n"
            f"STDERR:\n{result.stderr}"
        )

    yield

@pytest.hookimpl(hookwrapper=True)
def pytest_runtest_makereport(item, call):
    outcome = yield
    report = outcome.get_result()

    # Screenshot dimasukkan setelah bagian utama test selesai
    if report.when != "call":
        return

    # Mengambil nomor RT dari nama fungsi, misalnya rt_01
    match = re.search(r"test_rt_(\d{2})_", item.name)

    if not match:
        return

    nomor_rt = match.group(1)

    screenshot_dir = (
        PROJECT_ROOT
        / "evidence"
        / "selenium"
    )

    screenshot_path = next(
        screenshot_dir.glob(f"rt-{nomor_rt}-*.png"),
        None,
    )

    if screenshot_path is None or not screenshot_path.exists():
        return

    # Mengubah gambar menjadi data Base64 agar masuk ke satu file HTML
    encoded_image = base64.b64encode(
        screenshot_path.read_bytes()
    ).decode("utf-8")

    data_uri = (
        "data:image/png;base64,"
        + encoded_image
    )

    extras = getattr(report, "extras", [])

    extras.append(
        pytest_html.extras.image(
            data_uri,
            name=screenshot_path.name,
            mime_type="image/png",
            extension="png",
        )
    )

    report.extras = extras