import { AI_OS_STEPS } from "../data/content"
import { scrollToSection } from "../lib/landing-api"
import { LandingButton } from "../ui/landing-button"
import { SectionHeading } from "../ui/section-heading"

export function AiOsSection() {
  return (
    <section id="ai-os" className="scroll-mt-20 bg-navy py-20 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          dark
          eyebrow="InSyte AI OS"
          title="Your smart assistant inside InSyte CRM"
          description="InSyte AI OS works inside your CRM like a sales manager who never sleeps. It tells your team who to call, what to say, and what to do next — so you close more deals without hiring more people."
        />

        <div className="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {AI_OS_STEPS.map((step, index) => (
            <article
              key={step.title}
              className="rounded-2xl border border-white/10 bg-white/5 p-6"
            >
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">
                {String(index + 1).padStart(2, "0")}
              </p>
              <h3 className="mt-3 text-lg font-semibold text-white">{step.title}</h3>
              <p className="mt-2 text-sm leading-relaxed text-white/65">{step.body}</p>
            </article>
          ))}
        </div>

        <div className="mt-10">
          <LandingButton onClick={() => scrollToSection("book-demo")}>
            See InSyte AI OS in action
          </LandingButton>
        </div>
      </div>
    </section>
  )
}
