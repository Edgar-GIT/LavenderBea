//fichas de produto, admin view
function setupAdminPreview() {
  const imgIn = document.querySelector("[data-preview-input]");
  const img = document.querySelector("[data-preview-image]");
  const empty = document.querySelector("[data-preview-empty]");
  const nameIn = document.querySelector("[data-preview-name-source]");
  const nameOut = document.querySelector("[data-preview-name]");
  const priceIn = document.querySelector("[data-preview-price-source]");
  const priceOut = document.querySelector("[data-preview-price]");
  const descIn = document.querySelector("[data-preview-desc-source]");
  const descOut = document.querySelector("[data-preview-desc]");
  const stockIn = document.querySelector("[data-preview-stock-source]");
  const stockOut = document.querySelector("[data-preview-stock]");

  if (!nameOut || !priceOut || !descOut || !stockOut) {
    return;
  }

  const syncTxt = () => {
    nameOut.textContent = nameIn?.value.trim() || "Novo produto";
    descOut.textContent =
      descIn?.value.trim() || "A descrição vai aparecer aqui assim que começares a escrever no formulário.";

    const price = Number(priceIn?.value || "0");
    priceOut.textContent = `${price.toFixed(2).replace(".", ",")}€`;

    const stock = Number(stockIn?.value || "0");
    stockOut.textContent = stock > 0 ? `${stock} unidades em stock` : "Sem stock de momento";
  };

  const syncImg = () => {
    if (!imgIn || !img || !imgIn.files?.[0]) {
      return;
    }

    const reader = new FileReader();

    reader.addEventListener("load", () => {
      img.src = String(reader.result || "");
      img.classList.remove("hidden");
      empty?.classList.add("hidden");
    });

    reader.readAsDataURL(imgIn.files[0]);
  };

  nameIn?.addEventListener("input", syncTxt);
  priceIn?.addEventListener("input", syncTxt);
  descIn?.addEventListener("input", syncTxt);
  stockIn?.addEventListener("input", syncTxt);
  imgIn?.addEventListener("change", syncImg);
  syncTxt();
}
