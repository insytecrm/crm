import { FEATURE_CARDS } from "../data/content"
import { AppMockup } from "../ui/app-mockup"
import { SectionHeading } from "../ui/section-heading"

export function FeaturesSection() {
  return (
    <section id="features" className="scroll-mt-20 bg-slate-50 py-20 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          eyebrow="InSyte CRM"
          title="Everything from first call to final payout"
          description="Beyond AI and integrations — InSyte CRM is a full operations platform for leads, bookings, revenue, and teams."
        />

        <div className="mt-12 grid gap-6 lg:grid-cols-2">
          {FEATURE_CARDS.map((card) => (
            <article
              key={card.number}
              className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
            >
              <div className="p-6">
                <p className="text-sm font-semibold text-brand-accent">{card.number}</p>
                <h3 className="mt-2 text-xl font-semibold text-black">{card.title}</h3>
                <p className="mt-3 text-sm leading-relaxed text-slate-500">{card.body}</p>
                <div className="mt-4 flex flex-wrap gap-2">
                  {card.tags.map((tag) => (
                    <span
                      key={tag}
                      className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                    >
                      {tag}
                    </span>
                  ))}
                </div>
              </div>
              <AppMockup variant={card.mock} className="rounded-none border-0 border-t border-slate-100 shadow-none" />
            </article>
          ))}
        </div>
      </div>
    </section>
  )
}
