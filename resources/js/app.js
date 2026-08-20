import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'
import Sortable from 'sortablejs'
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
    const step = modifiers.includes('stagger') ? 70 : 0

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

/**
 * Drag-and-drop ordering for the admin lists.
 *
 * The new order is sent as soon as a row is dropped — no separate save step,
 * because a list that looks reordered but has not been saved is worse than one
 * that cannot be reordered at all.
 */
Alpine.data('sortableList', (endpoint) => ({
    saving: false,

    init() {
        Sortable.create(this.$refs.rows, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'is-dragging',
            onEnd: () => this.persist(),
        })
    },

    async persist() {
        const ids = [...this.$refs.rows.querySelectorAll('tr[data-id]')].map((row) => Number(row.dataset.id))

        this.saving = true

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ ids }),
            })

            if (!response.ok) throw new Error(response.status)
            this.flash(true)
        } catch (e) {
            // Reload rather than leave the screen showing an order that was
            // never stored.
            this.flash(false)
            setTimeout(() => window.location.reload(), 1200)
        } finally {
            this.saving = false
        }
    },

    flash(ok) {
        const note = document.getElementById('reorder-note')
        if (!note) return

        note.textContent = ok ? note.dataset.saved : note.dataset.failed
        note.className = ok
            ? 'fixed bottom-6 end-6 z-50 rounded-xl bg-accent-600 px-4 py-2.5 text-sm text-white shadow-lg'
            : 'fixed bottom-6 end-6 z-50 rounded-xl bg-rose-600 px-4 py-2.5 text-sm text-white shadow-lg'

        clearTimeout(this._t)
        this._t = setTimeout(() => { note.className = 'hidden' }, 2000)
    },
}))

/** A status flag switched from the listing rather than the edit form. */
Alpine.data('toggleSwitch', (url, column, initial) => ({
    on: initial,
    busy: false,

    async flip() {
        const previous = this.on

        this.on = !this.on          // move first; correct later if the write fails
        this.busy = true

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ column }),
            })

            if (!response.ok) throw new Error(response.status)

            this.on = (await response.json()).value
        } catch (e) {
            this.on = previous
            const note = document.getElementById('reorder-note')
            if (note) {
                note.textContent = note.dataset.failed
                note.className = 'fixed bottom-6 end-6 z-50 rounded-xl bg-rose-600 px-4 py-2.5 text-sm text-white shadow-lg'
                setTimeout(() => { note.className = 'hidden' }, 2000)
            }
        } finally {
            this.busy = false
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
/**
 * themeToggle — light, dark, or whatever the device asks for.
 *
 * The cookie is what the server reads on the next request, so the page it
 * sends back is already the right way round and nothing flashes. `system`
 * hands the decision back to the device, including when the device changes its
 * mind while the page is open.
 */
Alpine.data('themeToggle', (initial, labels) => ({
    theme: ['light', 'dark', 'system'].includes(initial) ? initial : 'light',
    labels: labels ?? {},

    get label() {
        return this.labels[this.theme] ?? ''
    },

    cycle() {
        const order = ['light', 'dark', 'system']
        this.theme = order[(order.indexOf(this.theme) + 1) % order.length]

        document.cookie = `theme=${this.theme};path=/;max-age=31536000;samesite=lax`
        window.applyTheme(this.theme)
    },
}))

/**
 * iconPicker — pick a glyph the site can actually draw.
 *
 * The SVG paths are printed once into a JSON block, so the grid can preview
 * every icon without a request and without a second copy of the icon set.
 */
Alpine.data('iconPicker', (initial, names) => ({
    chosen: initial || null,
    query: '',
    names,
    paths: JSON.parse(document.getElementById('icon-paths')?.textContent ?? '{}'),

    matches() {
        const q = this.query.trim().toLowerCase()

        return q ? this.names.filter((n) => n.includes(q)) : this.names
    },

    glyph(name) {
        const path = this.paths[name]
        if (!path) return ''

        return `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">${path}</svg>`
    },
}))

Alpine.start()
