(function () {
    var root = document.documentElement;
    root.classList.add('js');
    try {
        var theme = localStorage.getItem('theme');
        if (theme === 'light' || theme === 'dark') {
            root.dataset.theme = theme;
            document.querySelectorAll('meta[name="theme-color"]').forEach(function (meta) {
                meta.content = meta.dataset[theme];
            });
        }
        if (localStorage.getItem('motion') === 'paused') {
            root.dataset.motion = 'paused';
        } else if (window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
            root.classList.add('is-intro');
        }
    } catch (error) {}
})();
