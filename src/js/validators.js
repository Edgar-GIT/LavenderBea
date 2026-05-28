const validators = {
  isValidEmail: (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email.trim());
  },

  isValidPassword: (password) => {
    return password.length >= 8;
  },

  isValidPhone: (phone) => {
    if (phone.trim() === '') return true;
    const phoneRegex = /^[\d\s()+-]{7,}$/;
    return phoneRegex.test(phone.trim());
  },

  isNotEmpty: (value) => {
    return value.trim().length > 0;
  },
};

function setupFormValidation(formSelector) {
  const form = document.querySelector(formSelector);
  if (!form) return;

  const inputs = form.querySelectorAll('input, textarea');

  inputs.forEach((input) => {
    input.addEventListener('blur', () => {
      validateField(input);
    });

    input.addEventListener('input', () => {
      clearFieldError(input);
    });
  });

  form.addEventListener('submit', (e) => {
    let isValid = true;

    inputs.forEach((input) => {
      if (!validateField(input)) {
        isValid = false;
      }
    });

    if (!isValid) {
      e.preventDefault();
      focusFirstError(form);
    }
  });
}

function validateField(field) {
  const value = field.value;
  const type = field.type;
  const name = field.name;
  let isValid = true;
  let errorMsg = '';

  if (field.hasAttribute('required') && !validators.isNotEmpty(value)) {
    isValid = false;
    errorMsg = `${getFieldLabel(field)} é obrigatório.`;
  }

  if (isValid) {
    if (type === 'email' && value.trim() !== '') {
      if (!validators.isValidEmail(value)) {
        isValid = false;
        errorMsg = 'Introduz um email válido.';
      }
    }

    if (name === 'password' && value !== '') {
      if (!validators.isValidPassword(value)) {
        isValid = false;
        errorMsg = 'A password deve ter pelo menos 8 caracteres.';
      }
    }

    if (type === 'tel' && value.trim() !== '') {
      if (!validators.isValidPhone(value)) {
        isValid = false;
        errorMsg = 'Introduz um telemóvel válido.';
      }
    }
  }

  if (isValid) {
    clearFieldError(field);
  } else {
    showFieldError(field, errorMsg);
  }

  return isValid;
}

function getFieldLabel(field) {
  const label = field.closest('.field-group')?.querySelector('.field-label');
  return label?.textContent?.toLowerCase().trim() || field.name;
}

function showFieldError(field, message) {
  const group = field.closest('.field-group');
  if (!group) return;

  group.classList.add('field-error');

  let errorBox = group.querySelector('.field-error-message');
  if (!errorBox) {
    errorBox = document.createElement('div');
    errorBox.className = 'field-error-message';
    group.appendChild(errorBox);
  }
  errorBox.textContent = message;
}

function clearFieldError(field) {
  const group = field.closest('.field-group');
  if (!group) return;

  group.classList.remove('field-error');
  const errorBox = group.querySelector('.field-error-message');
  if (errorBox) {
    errorBox.remove();
  }
}

function focusFirstError(form) {
  const firstError = form.querySelector('.field-error input, .field-error textarea');
  if (firstError) {
    firstError.focus();
  }
}
