import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'
import Quill from 'quill'
import 'quill/dist/quill.snow.css'

Alpine.plugin(collapse)

/**
 * Motion is opt-out, not opt-in. Anyone who has asked their system for less
 * of it gets the finished state immediately and no observers at all.
 */
const wantsStillness = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches

/**
 * x-reveal — fade and lift an element the first time it scrolls into view.
 *
 *   x-reveal            a single element
 *   x-reveal.stagger    a container; its children arrive one after another
 *   x-reveal.delay.200  hold back 200ms
 */
Alpine.directive('reveal', (el, { modifiers }) => {
    const targets = modifiers.includes('stagger') ? [...el.children] : [el]

    if (wantsStillness()) {
        targets.forEach((t) => t.classList.add('is-revealed'))
        return
    }

    const delayIndex = modifiers.indexOf('delay')
    const base = delayIndex !== -1 ? parseInt(modifiers[delayIndex + 1] ?? 0, 10) : 0
    const step = modifiers.includes('stagger') ? 90 : 0

    targets.forEach((t) => t.classList.add('reveal'))

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return

            const i = targets.indexOf(entry.target)
            setTimeout(() => entry.target.classList.add('is-revealed'), base + i * step)
            observer.unobserve(entry.target)
        })
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.1 })

    targets.forEach((t) => observer.observe(t))
})

/**
 * x-counter="1250" — count up to the number once it is on screen.
 * The element's existing text is the formatted target, so the final frame is
 * always exactly what the server rendered, digits and suffix included.
 */
Alpine.directive('counter', (el, { expression }) => {
    const target = parseInt(expression, 10)
    const final = el.textContent

    if (!Number.isFinite(target) || wantsStillness()) return

    const localise = (n) => {
        const western = n.toLocaleString('en-US')
        return document.documentElement.lang === 'bn'
            ? western.replace(/[0-9]/g, (d) => '০১২৩৪৫৬৭৮৯'[d])
            : western
    }

    const suffix = final.replace(/[\d,০-৯]/g, '')
    el.textContent = localise(0) + suffix

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return
            observer.unobserve(entry.target)

            const duration = 1400
            const started = performance.now()

            const tick = (now) => {
                const progress = Math.min((now - started) / duration, 1)
                // ease-out: fast at first, settling at the end
                const eased = 1 - Math.pow(1 - progress, 3)
                el.textContent = localise(Math.round(target * eased)) + suffix

                if (progress < 1) requestAnimationFrame(tick)
                else el.textContent = final
            }

            requestAnimationFrame(tick)
        })
    }, { threshold: 0.4 })

    observer.observe(el)
})

/**
 * Rich text for the fields that are rendered as HTML.
 *
 * Quill reads the initial content out of its own container, so the server
 * renders the stored HTML there and no separate loading step is needed. The
 * hidden input is what actually submits; Quill only ever writes to it.
 */
Alpine.data('richText', (fieldName) => ({
    init() {
        const editor = new Quill(this.$refs.editor, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote', 'link'],
                    ['clean'],
                ],
            },
        })

        const sync = () => {
            const html = editor.root.innerHTML
            // Quill's idea of empty still contains a paragraph and a break.
            this.$refs.input.value = html === '<p><br></p>' ? '' : html
        }

        editor.on('text-change', sync)
        sync()

        // Bangla needs its own face inside the editor too, not just on the page.
        if (fieldName.endsWith('_bn')) {
            editor.root.style.fontFamily = 'var(--font-bangla)'
            editor.root.style.lineHeight = '1.85'
        }
    },
}))

/** Reading progress for long articles. */
Alpine.data('readingProgress', () => ({
    progress: 0,
    track() {
        const article = document.getElementById('article-body')
        if (!article) return

        const top = article.offsetTop
        const height = article.offsetHeight - window.innerHeight
        const scrolled = window.scrollY - top

        this.progress = height > 0 ? Math.min(Math.max((scrolled / height) * 100, 0), 100) : 0
    },
}))

window.Alpine = Alpine
Alpine.start()
