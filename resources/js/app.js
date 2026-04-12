import "./bootstrap";

document.addEventListener("DOMContentLoaded", () => {
    const root = document.documentElement;
    const shell = document.querySelector("[data-snack-shell]");
    const revealItems = document.querySelectorAll("[data-snack-reveal]");
    const decisionSelect = document.querySelector("[data-decision-select]");
    const recommendationField = document.querySelector(
        "[data-recommendation-field]",
    );
    const statusBadge = document.querySelector("[data-status-badge]");

    if (shell) {
        root.classList.add("scroll-smooth");
    }

    revealItems.forEach((element, index) => {
        const delay = 90 * (index + 1);

        element.style.setProperty("--snack-delay", `${delay}ms`);
        element.classList.add("snack-fade-in");
    });

    if (decisionSelect && recommendationField) {
        const syncRecommendationState = () => {
            const isRejected = decisionSelect.value === "tidak_lulus";

            recommendationField.toggleAttribute("required", isRejected);
            recommendationField.classList.toggle(
                "ring-amber-400/30",
                isRejected,
            );
            recommendationField.classList.toggle(
                "border-amber-300",
                isRejected,
            );

            if (statusBadge) {
                statusBadge.textContent = decisionSelect.value
                    ? decisionSelect.value.replaceAll("_", " ")
                    : "Belum ada keputusan";
            }
        };

        decisionSelect.addEventListener("change", syncRecommendationState);
        syncRecommendationState();
    }
});
