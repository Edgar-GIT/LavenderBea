//overlay account click
function setupUserMenu() {
  const trigger = document.querySelector("[data-user-menu-trigger]");
  const overlay = document.querySelector("[data-user-menu-overlay]");
  const closes = document.querySelectorAll("[data-user-menu-close]");

  if (!trigger || !overlay) {
    return;
  }

  const openMenu = () => {
    overlay.classList.remove("hidden");
    document.body.classList.add("account-overlay-open");
  };

  const closeMenu = () => {
    overlay.classList.add("hidden");
    document.body.classList.remove("account-overlay-open");
  };

  trigger.addEventListener("click", openMenu);

  closes.forEach((button) => {
    button.addEventListener("click", closeMenu);
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeMenu();
    }
  });
}
