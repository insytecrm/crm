import { scrollToSection } from "../lib/landing-api"
import { AppMockup } from "../ui/app-mockup"
import { LandingButton } from "../ui/landing-button"
import { SectionHeading } from "../ui/section-heading"

const BULLETS = [
  "One page per project — photos, pricing, floor plan, location",
  "WhatsApp & call buttons — one tap to contact",
  "Works perfectly on mobile — share on WhatsApp",
  "Track every enquiry — know which page or ad brought the lead",
  "InSyte AI OS takes over — score and next step instantly",
]

export function MicrositesSection() {
  return (
    <section id="microsites" className="scroll-mt-20 bg-white py-20 sm:py-24">
      <div className="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
        <div>
          <SectionHeading
            eyebrow="Property pages"
            title="Share a property link. Get enquiries instantly."
            description="Create a beautiful property page for each project in minutes. Share the link on WhatsApp, Instagram, or in your ads. When a buyer fills the form, the lead goes straight into InSyte CRM."
          />
          <ul className="mt-8 space-y-3">
            {BULLETS.map((bullet) => (
              <li key={bullet} className="flex gap-3 text-sm leading-relaxed text-slate-600">
                <span className="text-brand-green">✓</span>
                <span>{bullet}</span>
              </li>
            ))}
          </ul>
          <div className="mt-8">
            <LandingButton onClick={() => scrollToSection("register")}>
              Create your first property page
            </LandingButton>
          </div>
        </div>

        <AppMockup variant="microsite" />
      </div>
    </section>
  )
}
