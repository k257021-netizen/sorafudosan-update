document.addEventListener("DOMContentLoaded", function () {
    const sectionButtons = document.querySelectorAll("[aria-controls]");
    const sections = document.querySelectorAll(".search-panel");

    sectionButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const target = document.getElementById(button.getAttribute("aria-controls"));
            const wasOpen = target && !target.hidden;

            sections.forEach(function (section) { section.hidden = true; });
            sectionButtons.forEach(function (item) { item.setAttribute("aria-expanded", "false"); });

            if (target && !wasOpen) {
                target.hidden = false;
                button.setAttribute("aria-expanded", "true");
                target.scrollIntoView({ behavior: "smooth", block: "nearest" });
            }
        });
    });
});
