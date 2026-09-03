import { OUTCOME_CARDS } from "../data/content"
import { SectionHeading } from "../ui/section-heading"

export function OutcomeSection() {
  return (
    <section className="bg-white py-20 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          eyebrow="Why channel partners switch"
          title="Stop losing money on leads you already paid for"
          description="You run ads. You get calls from 99acres and Housing. Buyers message on WhatsApp. But half the leads never get a second follow-up. Bookings happen but commission tracking is a mess. InSyte CRM fixes the full journey — from first call to money in your account."
        />
        <div className="mt-12 grid gap-6 md:grid-cols-3">
          {OUTCOME_CARDS.map((card) => (
            <div key={card.title} className="rounded-2xl border border-slate-100 bg-slate-50 p-6">
              <h3 className="text-xl font-semibold text-black">{card.title}</h3>
              <p className="mt-3 text-sm leading-relaxed text-slate-500">{card.body}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
