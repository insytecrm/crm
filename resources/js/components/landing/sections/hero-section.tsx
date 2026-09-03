import { HERO_WORDS } from "../data/content"
import { scrollToSection } from "../lib/landing-api"
import { AppMockup } from "../ui/app-mockup"
import { LandingButton } from "../ui/landing-button"

export function HeroSection() {
  return (
    <section id="home" className="scroll-mt-20 bg-navy pb-16 pt-12 sm:pb-20 sm:pt-16">
      <div className="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
        <div>
          <p className="mb-4 inline-flex rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">
            Real Estate CRM · Powered by InSyte AI OS
          </p>
          <h1 className="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            For People Who Sell Property.
          </h1>
          <p className="mt-5 text-lg leading-relaxed text-white/80 sm:text-xl">
            One place to manage leads, follow-ups, site visits, inventory and commissions.
          </p>
          <p className="mt-4 max-w-xl text-sm leading-relaxed text-white/55">
            Built for channel partners who are tired of losing leads in WhatsApp chats and Excel
            sheets. Capture every enquiry, follow up on time, close more deals, and see exactly how
            much you&apos;ve earned.
          </p>
          <p className="mt-6 text-xl font-semibold text-brand-accent">
            {HERO_WORDS.join(" · ")}
          </p>
          <div className="mt-8 flex flex-wrap gap-3">
            <LandingButton onClick={() => scrollToSection("book-demo")}>Book Demo</LandingButton>
            <LandingButton variant="outline" onClick={() => scrollToSection("ai-os")}>
              See how it works
            </LandingButton>
          </div>
          <div className="mt-8 flex flex-wrap gap-2">
            {["Leads", "Site Visits", "Bookings", "Commission", "Payouts"].map((pill) => (
              <span
                key={pill}
                className="rounded-full border border-white/10 px-3 py-1 text-xs font-medium text-white/70"
              >
                {pill}
              </span>
            ))}
          </div>
        </div>

        <AppMockup variant="leads" />
      </div>
    </section>
  )
}
