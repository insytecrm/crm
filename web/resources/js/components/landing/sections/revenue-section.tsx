import { scrollToSection } from "../lib/landing-api"
import { AppMockup } from "../ui/app-mockup"
import { LandingButton } from "../ui/landing-button"
import { SectionHeading } from "../ui/section-heading"

const METRICS = [
  "Total Revenue — total agreement value from all bookings",
  "Revenue This Month — what you closed this month",
  "Total Commission — your full earning potential",
  "Pending Commission — money you're still waiting for",
  "Received Commission — what's already in your account",
  "Revenue by Project — which projects are making you money",
  "Top performers — which team member is closing the most",
]

export function RevenueSection() {
  return (
    <section id="revenue" className="scroll-mt-20 bg-white py-20 sm:py-24">
      <div className="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
        <AppMockup variant="revenue" />
        <div>
          <SectionHeading
            eyebrow="Bookings & earnings"
            title="Know exactly how much you've earned"
            description="How many bookings this month, total agreement value, pending commission, money in your account — InSyte CRM shows it clearly every day."
          />
          <ul className="mt-8 space-y-3">
            {METRICS.map((metric) => (
              <li key={metric} className="flex gap-3 text-sm leading-relaxed text-slate-500">
                <span className="text-brand-accent">✓</span>
                <span>{metric}</span>
              </li>
            ))}
          </ul>
          <p className="mt-6 text-sm font-medium text-black">
            Powered by InSyte AI OS — insights that don&apos;t just report, they recommend.
          </p>
          <div className="mt-8">
            <LandingButton onClick={() => scrollToSection("register")}>Start Free Trial</LandingButton>
          </div>
        </div>
      </div>
    </section>
  )
}
