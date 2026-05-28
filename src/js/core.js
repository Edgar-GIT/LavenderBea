//splash screen
function setupSplashScreen() {
  if (document.body.classList.contains("admin-body")) {
    return;
  }

  if (bodyData("activePage") !== "home") {
    return;
  }

  const url = new URL(window.location.href);
  const referrer = document.referrer ? new URL(document.referrer) : null;
  const same = referrer && referrer.origin === url.origin && referrer.pathname.startsWith("/LavenderBea/");

  if (same) {
    try {
      window.sessionStorage.setItem("lavenderSplashSeen", "1");
    }
    catch {
    }

    return;
  }

  try {
    if (window.sessionStorage.getItem("lavenderSplashSeen") === "1") {
      return;
    }

    window.sessionStorage.setItem("lavenderSplashSeen", "1");
  }
  catch {
  }

  const splash = document.createElement("div");
  splash.className = "splash-screen";
  splash.setAttribute("aria-label", "Lavender Bea");

  const word = document.createElement("div");
  word.className = "splash-word";

  ["LAVENDER", "BEA"].forEach((lineText, lineIndex) => {
    const line = document.createElement("div");
    line.className = "splash-line";

    [...lineText].forEach((letter, letterIndex) => {
      const span = document.createElement("span");
      span.className = "splash-letter";
      span.textContent = letter;
      span.style.setProperty("--letter-index", String(lineIndex * 8 + letterIndex));
      line.appendChild(span);
    });

    word.appendChild(line);
  });

  splash.appendChild(word);
  document.body.classList.add("has-splash");
  document.body.prepend(splash);

  window.setTimeout(() => {
    splash.classList.add("is-leaving");
  }, 1900);

  window.setTimeout(() => {
    splash.remove();
    document.body.classList.remove("has-splash");
  }, 2600);
}

//leitura dos data attributes globais
function bodyData(name) {
  return document.body?.dataset?.[name] || "";
}

//indicacao do estado de autenticacao
function isUserLoggedIn() {
  return bodyData("isLogged") === "1";
}

//notificacao
function showToast(message) {
  let stack = document.querySelector(".site-toast-stack");

  if (!stack) {
    stack = document.createElement("div");
    stack.className = "site-toast-stack";
    document.body.appendChild(stack);
  }

  const toast = document.createElement("div");
  toast.className = "site-toast";
  toast.textContent = message;
  stack.appendChild(toast);

  window.setTimeout(() => {
    toast.classList.add("is-hiding");

    window.setTimeout(() => {
      toast.remove();

      if (stack && stack.childElementCount === 0) {
        stack.remove();
      }
    }, 220);
  }, 2800);
}

//scroll inicial para secoes da home
function setupLandingPosition() {
  const url = new URL(window.location.href);
  const section = url.searchParams.get("section");

  if (!section) {
    return;
  }

  const target = document.getElementById(section);

  if (!target) {
    return;
  }

  window.requestAnimationFrame(() => {
    const rect = target.getBoundingClientRect();
    const navH = document.querySelector(".navbar")?.offsetHeight || 84;
    const offset = window.scrollY + rect.top - navH - 12;
    window.scrollTo({ top: offset, behavior: "auto" });

    url.searchParams.delete("section");
    window.history.replaceState({}, "", `${url.pathname}${url.search}`);
  });
}

//botao de copia
function setupCopyButtons() {
  const buttons = document.querySelectorAll("[data-copy-text]");

  buttons.forEach((button) => {
    if (button.dataset.bound === "1") {
      return;
    }

    button.dataset.bound = "1";

    button.addEventListener("click", async () => {
      const text = button.getAttribute("data-copy-text") || "";

      if (!text) {
        return;
      }

      try {
        if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(text);
        } else {
          const input = document.createElement("textarea");
          input.value = text;
          document.body.appendChild(input);
          input.select();
          document.execCommand("copy");
          input.remove();
        }

        showToast("ID copiado.");
      }
      catch {
        showToast("Não foi possível copiar o ID.");
      }
    });
  });
}
