// KeFrec - Register Page JS (submit ke Laravel backend)

document.addEventListener("DOMContentLoaded", () => {
  const registerForm = document.getElementById("register-form");
  const errorBox = document.getElementById("register-error");

  function showError(message) {
    if (!errorBox) return;
    errorBox.style.display = "block";
    errorBox.textContent = message;
  }

  function clearError() {
    if (!errorBox) return;
    errorBox.style.display = "none";
    errorBox.textContent = "";
  }

  // Back -> landing
  const backBtn = document.getElementById("btn-back");
  if (backBtn) {
    backBtn.addEventListener("click", () => {
      window.location.href = window.__KEFREC__?.landingUrl || "/";
    });
  }

  // Ke login
  const goLoginBtn = document.getElementById("btn-go-login");
  if (goLoginBtn) {
    goLoginBtn.addEventListener("click", () => {
      window.location.href = window.__KEFREC__?.loginUrl || "/login";
    });
  }

  // Toggle password (kalau kamu punya tombolnya)
  const toggle1 = document.getElementById("toggle-password");
  const toggle2 = document.getElementById("toggle-password-confirmation");

  function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input || !btn) return;
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    btn.textContent = isPassword ? "🙈" : "👁";
  }

  if (toggle1) toggle1.addEventListener("click", () => togglePassword("password", toggle1));
  if (toggle2) toggle2.addEventListener("click", () => togglePassword("password_confirmation", toggle2));

  if (!registerForm) return;

  registerForm.addEventListener("submit", function (e) {
    clearError();

    const name = (document.getElementById("full_name")?.value || document.querySelector('[name="name"]')?.value || "").trim();
    const email = (document.getElementById("email")?.value || "").trim();
    const phone = (document.getElementById("phone")?.value || "").trim();
    const password = (document.getElementById("password")?.value || "");
    const passwordConfirmation = (document.getElementById("password_confirmation")?.value || "");

    if (!name || !email || !password || !passwordConfirmation) {
      e.preventDefault();
      showError("Nama, email, dan password wajib diisi.");
      return;
    }

    const basicEmailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!basicEmailPattern.test(email)) {
      e.preventDefault();
      showError("Format email tidak valid.");
      return;
    }

    if (phone && phone.length < 10) {
      e.preventDefault();
      showError("Nomor HP tidak valid.");
      return;
    }

    if (password.length < 6) {
      e.preventDefault();
      showError("Password minimal 6 karakter.");
      return;
    }

    if (password !== passwordConfirmation) {
      e.preventDefault();
      showError("Konfirmasi password tidak sama.");
      return;
    }

    // Lolos -> biarkan POST ke route('register')
  });
});
