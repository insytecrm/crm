import { STATS } from "../data/content"

export function StatsSection() {
  return (
    <section className="border-y border-white/10 bg-brand-dark py-12">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <p className="mb-8 text-center text-sm font-semibold uppercase tracking-[0.2em] text-white/50">
          Trusted by channel partners across India
        </p>
        <div className="grid grid-cols-2 gap-6 lg:grid-cols-4">
          {STATS.map((stat) => (
            <div key={stat.label} className="text-center">
              <p className="text-3xl font-bold text-white sm:text-4xl">
                {"prefix" in stat ? stat.prefix : ""}
                {stat.value.toLocaleString("en-IN")}
                {stat.suffix}
              </p>
              <p className="mt-2 text-sm text-white/60">{stat.label}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
