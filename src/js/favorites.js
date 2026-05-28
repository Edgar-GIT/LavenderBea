//atalho do coracao no topo
function setupFavoritesEntry() {
  const button = document.querySelector("#fav-button");

  if (!button) {
    return;
  }

  button.addEventListener("click", () => {
    if (!isUserLoggedIn()) {
      showToast("Tens de iniciar sessão para usar os favoritos.");
      return;
    }

    const target = button.getAttribute("data-favorites-url");

    if (target) {
      window.location.href = target;
    }
  });
}

//alteraçao de favoritos
function setupFavoriteButtons() {
  const buttons = document.querySelectorAll("[data-favorite-toggle]");
  const favUrl = bodyData("favoriteUrl");

  buttons.forEach((button) => {
    if (button.dataset.bound === "1") {
      return;
    }

    button.dataset.bound = "1";

    button.addEventListener("click", async (event) => {
      event.preventDefault();
      event.stopPropagation();

      if (!isUserLoggedIn()) {
        showToast("Tens de iniciar sessão para usar os favoritos.");
        return;
      }

      const productId = button.getAttribute("data-product-id");

      if (!productId || !favUrl) {
        return;
      }

      const formData = new FormData();
      formData.append("product_id", productId);

      try {
        const response = await fetch(favUrl, {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });

        const result = (await response.text()).trim();

        if (!response.ok) {
          throw new Error(result || "Não foi possível atualizar os favoritos.");
        }

        const isFav = result === "added";
        button.classList.toggle("is-favorite", isFav);
        button.setAttribute(
          "aria-label",
          isFav ? "Remover dos favoritos" : "Adicionar aos favoritos",
        );

        const navHeart = document.querySelector("#fav-button");

        if (navHeart) {
          if (isFav) {
            navHeart.classList.add("is-filled");
          } else if (document.querySelectorAll("[data-favorite-toggle].is-favorite").length <= 1) {
            navHeart.classList.remove("is-filled");
          }
        }

        if (button.classList.contains("btn-inline-favorite")) {
          button.textContent = isFav ? "♥ Guardado" : "♥ Favorito";
        }

        if (window.location.pathname.endsWith("/favorites.php") && !isFav) {
          window.location.reload();
          return;
        }

        showToast(
          isFav ? "Produto adicionado aos favoritos." : "Produto removido dos favoritos.",
        );
      }
      catch (error) {
        showToast(error.message || "Não foi possível atualizar os favoritos.");
      }
    });
  });
}
