import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import Lenis from 'lenis'

import '../css/microsite.css'

gsap.registerPlugin(ScrollTrigger)

const root = document.querySelector('[data-microsite]')

if (root instanceof HTMLElement) {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches

    if (! reduceMotion) {
        const lenis = new Lenis({
            autoRaf: true,
            smoothWheel: true,
        })

        lenis.on('scroll', ScrollTrigger.update)

        const heroMedia = root.querySelector('.ms-hero-media')

        if (heroMedia) {
            gsap.fromTo(heroMedia, { scale: 1.12, opacity: 0.65 }, {
                scale: 1,
                opacity: 1,
                duration: 1.6,
                ease: 'power2.out',
            })

            gsap.to(heroMedia, {
                yPercent: 8,
                ease: 'none',
                scrollTrigger: {
                    trigger: root.querySelector('[data-hero]'),
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true,
                },
            })
        }

        gsap.utils.toArray('[data-reveal]').forEach((element, index) => {
            if (! (element instanceof HTMLElement)) {
                return
            }

            gsap.fromTo(element, { opacity: 0, y: 32 }, {
                opacity: 1,
                y: 0,
                duration: 0.9,
                delay: Math.min(index * 0.03, 0.2),
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: element,
                    start: 'top 86%',
                    once: true,
                },
            })
        })

        gsap.utils.toArray('[data-stagger]').forEach((group) => {
            if (! (group instanceof HTMLElement)) {
                return
            }

            gsap.from(group.children, {
                opacity: 0,
                y: 24,
                duration: 0.7,
                stagger: 0.08,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: group,
                    start: 'top 88%',
                    once: true,
                },
            })
        })
    }

    const dialog = document.getElementById('microsite-enquire')
    const openers = root.querySelectorAll('[data-open-enquire]')
    const closers = root.querySelectorAll('[data-close-enquire]')

    if (dialog instanceof HTMLDialogElement) {
        openers.forEach((button) => {
            button.addEventListener('click', () => dialog.showModal())
        })

        closers.forEach((button) => {
            button.addEventListener('click', () => dialog.close())
        })

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close()
            }
        })

        if (root.dataset.enquireOpen === '1') {
            dialog.showModal()
        }
    }
}
