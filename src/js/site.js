//estado base do documento e da sessao
document.addEventListener("DOMContentLoaded", () => {
  //entrada e posicao inicial
  setupSplashScreen();
  setupLandingPosition();

  //cabecalho e pesquisa
  setupNavMenu();
  setupLiveSearch();

  //favoritos e conta
  setupFavoritesEntry();
  setupFavoriteButtons();
  setupUserMenu();

  //produto e cesto
  setupQuantityTotal();
  setupCartAutoUpdate();

  //admin e utilitarios
  setupAdminPreview();
  setupCopyButtons();

  //formularios com validacoes
  setupFormValidation('form.contact-form');
});
