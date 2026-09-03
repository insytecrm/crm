import { HOW_IT_WORKS } from "../data/content"
import { SectionHeading } from "../ui/section-heading"

export function HowItWorksSection() {
  return (
    <section className="bg-slate-50 py-20 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          align="center"
          eyebrow="How it works"
          title="From lead to payout in four steps"
        />

        <div className="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
          {HOW_IT_WORKS.map((step) => (
            <article
              key={step.step}
              className="rounded-2xl border border-slate-100 bg-white p-6"
            >
              <p className="text-sm font-semibold tracking-[0.2em] text-brand-accent">
                {step.step}
              </p>
              <h3 className="mt-3 text-xl font-semibold text-black">{step.title}</h3>
              <p className="mt-3 text-sm leading-relaxed text-slate-500">{step.body}</p>
            </article>
          ))}
        </div>
      </div>
    </section>
  )
}
