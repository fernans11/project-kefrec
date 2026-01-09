// KeFrec - Login Page JS (submit ke Laravel backend)

document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("login-form");
  const loginErrorEl = document.getElementById("login-error");
  const emailInput = document.getElementById("login-email");
  const passwordInput = document.getElementById("login-password");
  const togglePasswordBtn = document.getElementById("toggle-password");
  const forgotBtn = document.getElementById("btn-forgot");
  const backBtn = document.getElementById("btn-back");
  const goRegisterBtn = document.getElementById("btn-go-register");

  function showError(message) {
    if (!loginErrorEl) return;
    loginErrorEl.textContent = message;
    loginErrorEl.classList.remove("hidden");
  }

  function clearError() {
    if (!loginErrorEl) return;
    loginErrorEl.textContent = "";
    loginErrorEl.classList.add("hidden");
  }

  // Submit Login -> VALIDASI FRONTEND, lalu submit normal ke Laravel
  if (loginForm) {
    loginForm.addEventListener("submit", function (event) {
      clearError();

      const email = (emailInput?.value || "").trim();
      const password = (passwordInput?.value || "").trim();

      if (!email || !password) {
        event.preventDefault();
        showError("Email dan password harus diisi.");
        return;
      }

      // jika user mengetik email, validasi sederhana
      if (email.includes("@")) {
        const basicEmailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!basicEmailPattern.test(email)) {
          event.preventDefault();
          showError("Format email tidak valid.");
          return;
        }
      }

      if (password.length < 6) {
        event.preventDefault();
        showError("Password minimal 6 karakter.");
        return;
      }

      // Lolos -> biarkan form POST ke route('login')
    });
  }

  // Toggle password
  if (togglePasswordBtn && passwordInput) {
    togglePasswordBtn.addEventListener("click", function () {
      const isPassword = passwordInput.type === "password";
      passwordInput.type = isPassword ? "text" : "password";
      togglePasswordBtn.textContent = isPassword ? "🙈" : "👁";
    });
  }

  // Lupa password -> arahkan ke route forgot-password bawaan Fortify
  if (forgotBtn) {
    forgotBtn.addEventListener("click", function () {
      window.location.href = "/forgot-password";
    });
  }

  // Back -> ke landing
  if (backBtn) {
    backBtn.addEventListener("click", function () {
      const url = window.__KEFREC__?.landingUrl || "/";
      window.location.href = url;
    });
  }

  // Ke register
  if (goRegisterBtn) {
    goRegisterBtn.addEventListener("click", function () {
      const url = window.__KEFREC__?.registerUrl || "/register";
      window.location.href = url;
    });
  }
});
