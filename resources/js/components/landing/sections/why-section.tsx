import { WHY_POINTS } from "../data/content"
import { SectionHeading } from "../ui/section-heading"

export function WhySection() {
  return (
    <section className="bg-white py-20 sm:py-24">
      <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          align="center"
          eyebrow="Why InSyte"
          title="Built for channel partners. Not a generic CRM."
        />
        <ul className="mt-10 space-y-4">
          {WHY_POINTS.map((point) => (
            <li
              key={point}
              className="flex gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 text-sm leading-relaxed text-slate-600"
            >
              <span className="text-brand-green">✓</span>
              <span>{point}</span>
            </li>
          ))}
        </ul>
      </div>
    </section>
  )
}
