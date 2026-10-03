import { useEffect } from "react"
import gsap from "gsap"
import { ScrollTrigger } from "gsap/ScrollTrigger"
import Lenis from "lenis"

gsap.registerPlugin(ScrollTrigger)

export function useSiteSmoothScroll(enabled = true) {
  useEffect(() => {
    if (! enabled) {
      return
    }

    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches

    if (reduceMotion) {
      return
    }

    const lenis = new Lenis({
      autoRaf: true,
      smoothWheel: true,
    })

    lenis.on("scroll", ScrollTrigger.update)

    const reveals = gsap.utils.toArray<HTMLElement>("[data-site-reveal]")

    reveals.forEach((el) => {
      gsap.fromTo(
        el,
        { opacity: 0, y: 32 },
        {
          opacity: 1,
          y: 0,
          duration: 0.7,
          ease: "power2.out",
          scrollTrigger: {
            trigger: el,
            start: "top 85%",
            toggleActions: "play none none none",
          },
        },
      )
    })

    return () => {
      ScrollTrigger.getAll().forEach((trigger) => trigger.kill())
      lenis.destroy()
    }
  }, [enabled])
}
