function getFavorites() {
    const match = document.cookie.match(/(?:^|; )favorites=([^;]*)/);
    if (!match || !match[1]) return [];
    try {
        return Array.from(new Set(decodeURIComponent(match[1]).split(",").map(Number).filter(function (id) {
            return Number.isSafeInteger(id) && id > 0;
        }))).slice(0, 100);
    } catch (_) {
        return [];
    }
}

function saveFavorites(list) {
    const secure = location.protocol === "https:" ? "; Secure" : "";
    document.cookie = "favorites=" + encodeURIComponent(Array.from(new Set(list)).slice(0, 100).join(",")) +
        "; path=/; max-age=" + 60 * 60 * 24 * 365 + "; SameSite=Lax" + secure;
}

function paintButton(button, isFavorite) {
    button.dataset.favorite = isFavorite ? "1" : "0";
    button.textContent = isFavorite ? "♥ お気に入り登録済み" : "♡ お気に入りに追加";
    button.setAttribute("aria-pressed", isFavorite ? "true" : "false");
}

function removeFavoriteCard(button, favoritesPage, count) {
    if (!favoritesPage) return;
    const card = button.closest(".property-card");
    if (card) card.remove();
    if (count) count.textContent = String(Math.max(0, Number(count.textContent) - 1));
    if (!favoritesPage.querySelector(".property-card")) {
        const empty = document.createElement("p");
        empty.className = "empty-state";
        empty.textContent = "お気に入りに登録した物件はありません。";
        favoritesPage.appendChild(empty);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const favorites = getFavorites();
    const favoritesPage = document.querySelector("[data-favorites-page]");
    const count = document.querySelector("[data-favorites-count]");

    document.querySelectorAll(".fav-button").forEach(function (button) {
        const isFavorite = button.dataset.authenticated === "1"
            ? button.dataset.favorite === "1"
            : favorites.includes(Number(button.dataset.id));
        paintButton(button, isFavorite);
    });

    document.addEventListener("click", async function (event) {
        const button = event.target.closest(".fav-button");
        if (!button || button.disabled) return;
        const id = Number(button.dataset.id);
        if (!Number.isSafeInteger(id) || id < 1) return;

        if (button.dataset.authenticated === "1") {
            button.disabled = true;
            try {
                const response = await fetch("favorite-toggle.php", {
                    method: "POST",
                    credentials: "same-origin",
                    headers: { "Content-Type": "application/json", "X-CSRF-Token": button.dataset.csrf || "" },
                    body: JSON.stringify({ property_id: id })
                });
                const result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.message || "更新できませんでした。");
                paintButton(button, result.favorite);
                if (!result.favorite) removeFavoriteCard(button, favoritesPage, count);
            } catch (_) {
                alert("お気に入りを更新できませんでした。ページを再読み込みして、もう一度お試しください。");
            } finally {
                button.disabled = false;
            }
            return;
        }

        let list = getFavorites();
        const isFavorite = !list.includes(id);
        list = isFavorite ? list.concat(id) : list.filter(function (favoriteId) { return favoriteId !== id; });
        saveFavorites(list);
        paintButton(button, isFavorite);
        if (!isFavorite) removeFavoriteCard(button, favoritesPage, count);
    });
});
