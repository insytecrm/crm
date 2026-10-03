import { LEAD_SOURCES } from "../data/content"
import { scrollToSection } from "../lib/landing-api"
import { LandingButton } from "../ui/landing-button"
import { SectionHeading } from "../ui/section-heading"

export function LeadSourcesSection() {
  return (
    <section id="lead-sources" className="scroll-mt-20 bg-slate-50 py-20 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          align="center"
          eyebrow="Lead capture"
          title="Every enquiry. One inbox."
          description="Whether the buyer found you on Facebook, a property portal, or sent a WhatsApp message — it all comes into InSyte CRM automatically. No copy-paste. No leads falling through the cracks."
        />

        <div className="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {LEAD_SOURCES.map((source) => (
            <article
              key={source.name}
              className="rounded-2xl border border-slate-100 bg-white p-6"
            >
              <div className="flex h-12 w-12 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 p-2">
                <img
                  src={source.logo}
                  alt={source.name}
                  className="max-h-full max-w-full object-contain"
                />
              </div>
              <h3 className="mt-5 text-lg font-semibold text-black">{source.title}</h3>
              <p className="mt-2 text-sm leading-relaxed text-slate-500">{source.body}</p>
            </article>
          ))}
        </div>

        <div className="mt-10 text-center">
          <p className="text-sm text-slate-500">
            Every lead that comes in is scored and queued for action by InSyte AI OS.
          </p>
          <div className="mt-5">
            <LandingButton variant="secondary" onClick={() => scrollToSection("book-demo")}>
              Connect your lead sources
            </LandingButton>
          </div>
        </div>
      </div>
    </section>
  )
}
