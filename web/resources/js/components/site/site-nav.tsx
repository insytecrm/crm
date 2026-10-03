import { useEffect, useId, useRef, useState } from "react"
import { AnimatePresence, motion, useReducedMotion } from "framer-motion"
import { ChevronDown, Menu, X } from "lucide-react"

import { NAV_GROUPS, type NavGroup } from "@/components/site/lib/nav"
import { cn } from "@/lib/utils"

type SiteNavProps = {
  logoLight: string
  logoDark: string
}

const listVariants = {
  hidden: {},
  show: {
    transition: { staggerChildren: 0.04, delayChildren: 0.04 },
  },
}

const itemVariants = {
  hidden: { opacity: 0, y: 6 },
  show: { opacity: 1, y: 0 },
}

export function SiteNav({ logoLight, logoDark }: SiteNavProps) {
  const reduceMotion = useReducedMotion()
  const [openMenu, setOpenMenu] = useState<NavGroup["id"] | null>(null)
  const [mobileOpen, setMobileOpen] = useState(false)
  const navRef = useRef<HTMLElement>(null)
  const menuId = useId()

  useEffect(() => {
    const onPointerDown = (event: PointerEvent) => {
      if (! navRef.current?.contains(event.target as Node)) {
        setOpenMenu(null)
      }
    }

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setOpenMenu(null)
        setMobileOpen(false)
      }
    }

    document.addEventListener("pointerdown", onPointerDown)
    document.addEventListener("keydown", onKeyDown)

    return () => {
      document.removeEventListener("pointerdown", onPointerDown)
      document.removeEventListener("keydown", onKeyDown)
    }
  }, [])

  useEffect(() => {
    document.body.classList.toggle("overflow-hidden", mobileOpen)

    return () => document.body.classList.remove("overflow-hidden")
  }, [mobileOpen])

  const closeAll = () => {
    setOpenMenu(null)
    setMobileOpen(false)
  }

  return (
    <motion.header
      ref={navRef}
      className="sticky top-0 z-50 border-b border-black/5 bg-white/90 backdrop-blur-md"
      initial={reduceMotion ? false : { y: -20, opacity: 0 }}
      animate={{ y: 0, opacity: 1 }}
      transition={{ duration: 0.45, ease: [0.22, 1, 0.36, 1] }}
    >
      <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="/" className="shrink-0" onClick={closeAll}>
          <img
            src={logoLight}
            alt="InSyte"
            className="block h-9 w-auto max-w-[160px] object-contain object-left dark:hidden"
            draggable={false}
          />
          <img
            src={logoDark}
            alt="InSyte"
            className="hidden h-9 w-auto max-w-[160px] object-contain object-left dark:block"
            draggable={false}
          />
        </a>

        <nav className="hidden items-center gap-1 lg:flex" aria-label="Main">
          {NAV_GROUPS.map((group) => {
            const isOpen = openMenu === group.id

            return (
              <div
                key={group.id}
                className="relative"
                onMouseEnter={() => setOpenMenu(group.id)}
                onMouseLeave={() => setOpenMenu(null)}
              >
                <button
                  type="button"
                  className={cn(
                    "inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-black/70 transition-colors hover:bg-black/[0.03] hover:text-black",
                    isOpen && "bg-black/[0.03] text-black",
                  )}
                  aria-expanded={isOpen}
                  aria-controls={`${menuId}-${group.id}`}
                  onClick={() => setOpenMenu(isOpen ? null : group.id)}
                >
                  {group.label}
                  <ChevronDown
                    className={cn("size-3.5 transition-transform", isOpen && "rotate-180")}
                    aria-hidden="true"
                  />
                </button>

                <AnimatePresence>
                  {isOpen ? (
                    <motion.div
                      id={`${menuId}-${group.id}`}
                      role="menu"
                      className="absolute left-0 top-full z-50 min-w-[280px] pt-2"
                      initial={reduceMotion ? false : { opacity: 0, y: 8, scale: 0.98 }}
                      animate={{ opacity: 1, y: 0, scale: 1 }}
                      exit={reduceMotion ? undefined : { opacity: 0, y: 6, scale: 0.98 }}
                      transition={{ duration: 0.18, ease: [0.22, 1, 0.36, 1] }}
                    >
                      <motion.ul
                        className="rounded-2xl border border-black/5 bg-white p-2 shadow-xl shadow-black/10"
                        variants={reduceMotion ? undefined : listVariants}
                        initial={reduceMotion ? false : "hidden"}
                        animate="show"
                      >
                        {group.items.map((item) => (
                          <motion.li
                            key={item.label}
                            variants={reduceMotion ? undefined : itemVariants}
                            role="none"
                          >
                            <a
                              href={item.href}
                              role="menuitem"
                              className="block rounded-xl px-3 py-2.5 transition-colors hover:bg-brand-accent/10"
                              onClick={closeAll}
                            >
                              <span className="block text-sm font-semibold text-black">{item.label}</span>
                              {item.description ? (
                                <span className="mt-0.5 block text-xs text-black/50">
                                  {item.description}
                                </span>
                              ) : null}
                            </a>
                          </motion.li>
                        ))}
                      </motion.ul>
                    </motion.div>
                  ) : null}
                </AnimatePresence>
              </div>
            )
          })}

          <a
            href="/pricing"
            className="rounded-lg px-3 py-2 text-sm font-medium text-black/70 transition-colors hover:bg-black/[0.03] hover:text-black"
          >
            Pricing
          </a>
        </nav>

        <div className="flex items-center gap-2">
          <a
            href="/demo"
            className="hidden h-9 items-center rounded-lg border border-black/10 px-3 text-sm font-semibold text-black sm:inline-flex"
            onClick={closeAll}
          >
            Book a Demo
          </a>
          <motion.a
            href="/signup"
            className="inline-flex h-9 items-center rounded-lg bg-brand-green px-4 text-sm font-semibold text-white shadow-sm"
            whileHover={reduceMotion ? undefined : { scale: 1.03 }}
            whileTap={reduceMotion ? undefined : { scale: 0.98 }}
            onClick={closeAll}
          >
            Start Free
          </motion.a>

          <button
            type="button"
            className="inline-flex size-9 items-center justify-center rounded-lg border border-black/10 text-black lg:hidden"
            aria-label={mobileOpen ? "Close menu" : "Open menu"}
            aria-expanded={mobileOpen}
            onClick={() => {
              setMobileOpen((open) => ! open)
              setOpenMenu(null)
            }}
          >
            {mobileOpen ? <X className="size-5" /> : <Menu className="size-5" />}
          </button>
        </div>
      </div>

      <AnimatePresence>
        {mobileOpen ? (
          <motion.div
            className="border-t border-black/5 bg-white lg:hidden"
            initial={reduceMotion ? false : { height: 0, opacity: 0 }}
            animate={{ height: "auto", opacity: 1 }}
            exit={reduceMotion ? undefined : { height: 0, opacity: 0 }}
            transition={{ duration: 0.25, ease: [0.22, 1, 0.36, 1] }}
          >
            <div className="max-h-[calc(100dvh-4rem)] space-y-4 overflow-y-auto px-4 py-4">
              {NAV_GROUPS.map((group) => (
                <div key={group.id}>
                  <p className="px-1 text-xs font-semibold uppercase tracking-wide text-black/40">
                    {group.label}
                  </p>
                  <ul className="mt-1 space-y-0.5">
                    {group.items.map((item) => (
                      <li key={item.label}>
                        <a
                          href={item.href}
                          className="block rounded-lg px-2 py-2 text-sm font-medium text-black/80 hover:bg-brand-accent/10 hover:text-black"
                          onClick={closeAll}
                        >
                          {item.label}
                        </a>
                      </li>
                    ))}
                  </ul>
                </div>
              ))}

              <a
                href="/pricing"
                className="block rounded-lg px-2 py-2 text-sm font-medium text-black/80 hover:bg-black/[0.03]"
                onClick={closeAll}
              >
                Pricing
              </a>
              <a
                href="/demo"
                className="block rounded-lg px-2 py-2 text-sm font-medium text-black/80 hover:bg-black/[0.03]"
                onClick={closeAll}
              >
                Book a Demo
              </a>
            </div>
          </motion.div>
        ) : null}
      </AnimatePresence>
    </motion.header>
  )
}
