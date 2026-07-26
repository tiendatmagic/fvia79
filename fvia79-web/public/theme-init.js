(function () {
  'use strict';

  const savedTheme = localStorage.getItem('theme_mode');
  const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  if (
    savedTheme === 'dark' ||
    (!savedTheme && systemDark) ||
    (savedTheme === 'auto' && systemDark)
  ) {
    document.documentElement.classList.add('dark');
  }
})();
