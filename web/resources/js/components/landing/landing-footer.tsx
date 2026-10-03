import { scrollToSection } from "@/components/landing/lib/landing-api"

export function LandingFooter() {
  return (
    <footer className="border-t border-white/10 bg-brand-dark py-14">
      <div className="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div>
          <p className="text-lg font-semibold text-white">InSyte</p>
          <p className="mt-3 text-sm leading-relaxed text-white/60">
            Software for real estate channel partners.
          </p>
        </div>
        <div>
          <p className="text-sm font-semibold uppercase tracking-[0.15em] text-white/50">Product</p>
          <ul className="mt-4 space-y-2">
            <li>
              <button type="button" className="text-sm text-white/70 hover:text-white" onClick={() => scrollToSection("features")}>
                InSyte CRM
              </button>
            </li>
            <li>
              <button type="button" className="text-sm text-white/70 hover:text-white" onClick={() => scrollToSection("ai-os")}>
                InSyte AI OS
              </button>
            </li>
            <li>
              <button type="button" className="text-sm text-white/70 hover:text-white" onClick={() => scrollToSection("lead-sources")}>
                Lead Sources
              </button>
            </li>
            <li>
              <button type="button" className="text-sm text-white/70 hover:text-white" onClick={() => scrollToSection("microsites")}>
                Property Pages
              </button>
            </li>
            <li>
              <button type="button" className="text-sm text-white/70 hover:text-white" onClick={() => scrollToSection("pricing")}>
                Pricing
              </button>
            </li>
          </ul>
        </div>
        <div>
          <p className="text-sm font-semibold uppercase tracking-[0.15em] text-white/50">Support</p>
          <ul className="mt-4 space-y-2">
            <li>
              <button type="button" className="text-sm text-white/70 hover:text-white" onClick={() => scrollToSection("faq")}>
                FAQ
              </button>
            </li>
            <li>
              <button type="button" className="text-sm text-white/70 hover:text-white" onClick={() => scrollToSection("book-demo")}>
                Book Demo
              </button>
            </li>
            <li>
              <button type="button" className="text-sm text-white/70 hover:text-white" onClick={() => scrollToSection("register")}>
                Start Free Trial
              </button>
            </li>
          </ul>
        </div>
        <div>
          <p className="text-sm font-semibold uppercase tracking-[0.15em] text-white/50">Legal</p>
          <ul className="mt-4 space-y-2">
            <li>
              <a href="/privacy" className="text-sm text-white/70 hover:text-white">Privacy Policy</a>
            </li>
            <li>
              <a href="/terms" className="text-sm text-white/70 hover:text-white">Terms of Service</a>
            </li>
            <li>
              <a href="/refund" className="text-sm text-white/70 hover:text-white">Refund Policy</a>
            </li>
          </ul>
        </div>
      </div>
      <div className="mx-auto mt-10 max-w-7xl border-t border-white/10 px-4 pt-6 text-center text-sm text-white/40 sm:px-6 lg:px-8">
        © {new Date().getFullYear()} InSyte. All rights reserved.
      </div>
    </footer>
  )
}
