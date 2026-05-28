//total price
function setupQuantityTotal() {
  const qtyIn = document.querySelector("[data-qty-input]");
  const total = document.querySelector("[data-qty-total]");

  if (!qtyIn || !total) {
    return;
  }

  const price = Number(total.getAttribute("data-unit-price") || "0");

  const upd = () => {
    const qty = Math.max(1, Number(qtyIn.value || "1"));
    total.textContent = `${(price * qty).toFixed(2).replace(".", ",")}€`;
  };

  qtyIn.addEventListener("input", upd);
  upd();
}

//cesto com atualizacao automatica
function setupCartAutoUpdate() {
  const forms = document.querySelectorAll("[data-cart-auto-form]");
  const sumT = document.querySelector("[data-cart-summary-total]");
  const sumN = document.querySelector("[data-cart-summary-count]");

  if (forms.length === 0) {
    return;
  }

  const fmt = (value) => `${value.toFixed(2).replace(".", ",")}€`;

  const updCart = () => {
    let total = 0;
    let count = 0;

    document.querySelectorAll("[data-cart-line]").forEach((line) => {
      const input = line.querySelector("[data-cart-auto-input]");
      const lineTotal = line.querySelector("[data-cart-line-total]");
      const unitPrice = Number(line.getAttribute("data-unit-price") || "0");
      const quantity = Math.max(0, Number(input?.value || "0"));
      const subtotal = unitPrice * quantity;

      total += subtotal;
      count += quantity;

      if (lineTotal) {
        lineTotal.textContent = fmt(subtotal);
      }
    });

    if (sumT) {
      sumT.textContent = fmt(total);
    }

    if (sumN) {
      sumN.textContent = String(count);
    }
  };

  forms.forEach((form) => {
    const input = form.querySelector("[data-cart-auto-input]");
    let timer = 0;

    if (!input) {
      return;
    }

    const submit = () => {
      if (input.value === "") {
        updCart();
        return;
      }

      const max = Number(input.getAttribute("max") || "0");
      const value = Math.max(0, Number(input.value || "0"));

      if (max > 0 && value > max) {
        input.value = String(max);
      } else if (value < 0) {
        input.value = "0";
      }

      window.clearTimeout(timer);
      updCart();
      timer = window.setTimeout(() => form.requestSubmit(), 450);
    };

    input.addEventListener("input", submit);
    input.addEventListener("change", submit);
  });

  updCart();
}
