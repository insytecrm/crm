import { FaqAccordion } from "../ui/faq-accordion"
import { SectionHeading } from "../ui/section-heading"

export function FaqSection() {
  return (
    <section id="faq" className="scroll-mt-20 bg-slate-50 py-20 sm:py-24">
      <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <SectionHeading align="center" eyebrow="FAQ" title="Common questions" />
        <div className="mt-10">
          <FaqAccordion />
        </div>
      </div>
    </section>
  )
}
