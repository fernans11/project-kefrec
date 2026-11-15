(function () {
  const storageKey = 'kefrec-theme';
  const themes = ['dark', 'light'];

  function getStoredTheme() {
    try {
      return localStorage.getItem(storageKey);
    } catch (_) {
      return null;
    }
  }

  function storeTheme(theme) {
    try {
      localStorage.setItem(storageKey, theme);
    } catch (_) {}
  }

  function getCurrentTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    return themes.includes(current) ? current : 'dark';
  }

  function applyTheme(theme) {
    const nextTheme = themes.includes(theme) ? theme : 'dark';
    document.documentElement.setAttribute('data-theme', nextTheme);
    document.documentElement.style.colorScheme = nextTheme;

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      const isLight = nextTheme === 'light';
      button.setAttribute('aria-pressed', String(isLight));
      button.setAttribute('title', isLight ? 'Gunakan mode gelap' : 'Gunakan mode terang');

      const icon = button.querySelector('[data-theme-icon]');
      const text = button.querySelector('[data-theme-text]');

      if (icon) {
        icon.setAttribute('data-lucide', isLight ? 'sun' : 'moon');
        icon.textContent = '';
      }
      if (text) text.textContent = isLight ? 'Light' : 'Dark';
    });

    if (window.KefrecIcons) {
      window.KefrecIcons.render(document);
    }
  }

  applyTheme(getStoredTheme() || getCurrentTheme());

  document.addEventListener('DOMContentLoaded', function () {
    applyTheme(getStoredTheme() || getCurrentTheme());

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      button.addEventListener('click', function () {
        const nextTheme = getCurrentTheme() === 'light' ? 'dark' : 'light';
        applyTheme(nextTheme);
        storeTheme(nextTheme);
      });
    });
  });
})();
