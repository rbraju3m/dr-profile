{{--
    Settles the theme before the first paint. The server can name `light` and
    `dark` itself; `system` it cannot, so that one is resolved here — and kept
    in step afterwards, because following the device means following it while
    the page is open too.
--}}
<script>
    (function () {
        var media = window.matchMedia('(prefers-color-scheme: dark)')

        window.applyTheme = function (theme) {
            var resolved = theme === 'system' ? (media.matches ? 'dark' : 'light') : theme

            window.themePreference = theme
            document.documentElement.classList.toggle('dark', resolved === 'dark')
        }

        media.addEventListener('change', function () {
            if (window.themePreference === 'system') window.applyTheme('system')
        })

        window.applyTheme(@json($theme));
    })();
</script>
