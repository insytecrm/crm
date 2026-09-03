import { TESTIMONIALS } from "../data/content"
import { SectionHeading } from "../ui/section-heading"

export function TestimonialsSection() {
  return (
    <section className="bg-slate-50 py-20 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          align="center"
          eyebrow="Client stories"
          title="Channel partners who grew with InSyte CRM"
        />

        <div className="mt-12 grid gap-6 lg:grid-cols-3">
          {TESTIMONIALS.map((item) => (
            <figure
              key={item.name}
              className="flex h-full flex-col rounded-2xl border border-slate-100 bg-white p-6"
            >
              <div className="text-brand-accent">★★★★★</div>
              <blockquote className="mt-4 flex-1 text-sm leading-relaxed text-slate-600">
                {item.quote}
              </blockquote>
              <figcaption className="mt-6 border-t border-slate-100 pt-4">
                <p className="font-semibold text-black">{item.name}</p>
                <p className="text-sm text-slate-400">{item.role}</p>
              </figcaption>
            </figure>
          ))}
        </div>
      </div>
    </section>
  )
}
