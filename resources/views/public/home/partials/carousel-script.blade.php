{{--
    The hero's slide machinery, shared by all three homepage designs: each
    arranges the slides differently, but they advance identically and a second
    copy of this would be a second thing to keep in step.

    Include it inside a @push('scripts') in whichever layout drew a hero.
--}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('heroCarousel', (count) => ({
            current: 0,
            timer: null,
            count,

            start() {
                // A hero that moves on its own is exactly what
                // prefers-reduced-motion is asking us not to do.
                const still = window.matchMedia('(prefers-reduced-motion: reduce)').matches
                if (this.count < 2 || still) return
                this.timer = setInterval(() => this.next(), 6000)
            },
            pause() { clearInterval(this.timer); this.timer = null },
            resume() { if (!this.timer) this.start() },
            go(i) { this.current = i; this.pause(); this.resume() },
            next() { this.current = (this.current + 1) % this.count },
            prev() { this.current = (this.current - 1 + this.count) % this.count },
        }))
    })
</script>
