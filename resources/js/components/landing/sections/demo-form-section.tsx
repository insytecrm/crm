import { LandingForm } from "../ui/landing-form"
import { SectionHeading } from "../ui/section-heading"

const TRUST = [
  "Free demo, no obligation",
  "Setup help included",
  "InSyte AI OS included in every plan",
  "Response within 24 hours",
]

export function DemoFormSection() {
  return (
    <section id="book-demo" className="scroll-mt-20 bg-white py-20 sm:py-24">
      <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          align="center"
          title="See InSyte CRM in action"
          description="Book a free demo. We'll walk you through leads, follow-ups, bookings, and commission tracking — using your business as the example."
        />

        <div className="mt-10 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
          <LandingForm type="demo" />
        </div>

        <div className="mt-8 flex flex-wrap justify-center gap-3">
          {TRUST.map((item) => (
            <span
              key={item}
              className="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-500"
            >
              {item}
            </span>
          ))}
        </div>
      </div>
    </section>
  )
}
