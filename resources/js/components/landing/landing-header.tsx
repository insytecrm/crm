import { useState } from "react"

import { scrollToSection } from "@/components/landing/lib/landing-api"
import { cn } from "@/lib/utils"

const NAV_LINKS = [
  { label: "Features", href: "features" },
  { label: "InSyte AI OS", href: "ai-os" },
  { label: "Lead Sources", href: "lead-sources" },
  { label: "Pricing", href: "pricing" },
  { label: "FAQ", href: "faq" },
] as const

const LOGO_SRC = "/images/inSyte%20(2).png"

export function LandingHeader() {
  const [mobileOpen, setMobileOpen] = useState(false)

  const navigate = (id: string) => {
    setMobileOpen(false)
    scrollToSection(id)
  }

  return (
    <header className="sticky top-0 z-50 border-b border-white/10 bg-navy">
      <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
        <a href="/" className="shrink-0">
          <img
            src={LOGO_SRC}
            alt="InSyte"
            className="block h-10 w-auto max-w-[180px] object-contain object-left"
            draggable={false}
          />
        </a>

        <nav className="hidden items-center gap-8 md:flex" aria-label="Main">
          {NAV_LINKS.map((link) => (
            <button
              key={link.href}
              type="button"
              onClick={() => navigate(link.href)}
              className="text-sm font-medium text-white/75 transition-colors hover:text-white"
            >
              {link.label}
            </button>
          ))}
        </nav>

        <div className="flex items-center gap-3">
          <button
            type="button"
            className="inline-flex h-9 items-center rounded-lg bg-brand-green px-4 text-sm font-semibold text-white hover:bg-brand-green/90"
            onClick={() => navigate("register")}
          >
            Start Free Trial
          </button>
          <button
            type="button"
            className="inline-flex rounded-lg border border-white/15 p-2 text-white md:hidden"
            aria-label={mobileOpen ? "Close menu" : "Open menu"}
            onClick={() => setMobileOpen((open) => !open)}
          >
            {mobileOpen ? "✕" : "☰"}
          </button>
        </div>
      </div>

      {mobileOpen ? (
        <div className="border-t border-white/10 bg-navy px-4 py-4 md:hidden">
          <nav className="flex flex-col gap-3">
            {NAV_LINKS.map((link) => (
              <button
                key={link.href}
                type="button"
                onClick={() => navigate(link.href)}
                className="rounded-lg px-3 py-2 text-left text-sm font-medium text-white/80 hover:bg-white/5"
              >
                {link.label}
              </button>
            ))}
            <button
              type="button"
              className={cn(
                "mt-2 inline-flex h-10 items-center justify-center rounded-lg bg-brand-green px-4 text-sm font-semibold text-white",
              )}
              onClick={() => navigate("register")}
            >
              Start Free Trial
            </button>
          </nav>
        </div>
      ) : null}
    </header>
  )
}
