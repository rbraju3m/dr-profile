{{--
    Runs before the first paint, and only has work to do when the theme is
    "follow the device" — the server cannot know what the device says. Kept
    inline and dependency-free so it lands ahead of the stylesheet.
--}}
<script>
    (function () {
        var theme = @json($theme);

        if (theme === 'system') {
            theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
        }

        document.documentElement.classList.toggle('dark', theme === 'dark')
    })();
</script>
