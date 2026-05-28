//menu mobile do cabecalho
function setupNavMenu() {
  const navbar = document.querySelector(".navbar-inner");
  const toggle = document.querySelector(".nav-toggle");

  if (!navbar || !toggle) {
    return;
  }

  toggle.addEventListener("click", () => {
    navbar.classList.toggle("menu-open");
    document.body.classList.toggle("menu-open");
  });

  document.addEventListener("click", (event) => {
    if (!navbar.classList.contains("menu-open")) {
      return;
    }

    if (navbar.contains(event.target)) {
      return;
    }

    navbar.classList.remove("menu-open");
    document.body.classList.remove("menu-open");
  });
}

//pesquisa rapida para catalogo e navpesq
function setupLiveSearch() {
  const groups = document.querySelectorAll("[data-live-search-group]");

  groups.forEach((group) => {
    const url = group.getAttribute("data-search-url") || bodyData("searchUrl");
    const input = group.querySelector("[data-live-search-input]");
    const root = group.querySelector("[data-live-search-results]");

    if (!url || !input || !root) {
      return;
    }

    let timer = 0;

    const hide = () => {
      root.classList.add("hidden");
    };

    const load = async () => {
      const term = input.value.trim();

      if (!term) {
        root.innerHTML = "";
        hide();
        return;
      }

      try {
        const response = await fetch(`${url}?q=${encodeURIComponent(term)}`, {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });

        root.innerHTML = await response.text();
        root.classList.toggle("hidden", root.innerHTML.trim() === "");
      }
      catch {
        root.innerHTML = '<div class="nav-search-empty">Não foi possível carregar a pesquisa.</div>';
        root.classList.remove("hidden");
      }
    };

    input.addEventListener("input", () => {
      window.clearTimeout(timer);
      timer = window.setTimeout(load, 160);
    });

    input.addEventListener("focus", () => {
      if (input.value.trim() !== "") {
        load();
      }
    });

    input.addEventListener("blur", () => {
      window.setTimeout(hide, 160);
    });

    document.addEventListener("click", (event) => {
      if (group.contains(event.target)) {
        return;
      }

      hide();
    });
  });
}
