import {
  BarChart3,
  Building2,
  CalendarCheck2,
  IndianRupee,
  ListChecks,
  Users,
  Waypoints,
} from "lucide-react"

import { AnnotatedShot } from "@/components/site/ui/annotated-shot"
import { CtaBand } from "@/components/site/ui/cta-band"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteButton } from "@/components/site/ui/site-button"
import { SiteCard } from "@/components/site/ui/site-card"

const CRM_FEATURES = [
  {
    title: "Lead Management",
    description: "Capture every enquiry with source, property, owner and activity in one record.",
    icon: ListChecks,
  },
  {
    title: "Follow-Ups",
    description: "Plan calls and next steps, then keep overdue work visible.",
    icon: Waypoints,
  },
  {
    title: "Property",
    description: "Keep property inventory and lead interest connected.",
    icon: Building2,
  },
  {
    title: "Booking & Revenue",
    description: "Record bookings and see the revenue attached to closed business.",
    icon: IndianRupee,
  },
  {
    title: "Team",
    description: "Assign leads, organize sales teams and keep ownership clear.",
    icon: Users,
  },
  {
    title: "Reports",
    description: "Review activity, conversions and business performance.",
    icon: BarChart3,
  },
] as const

const PIPELINE = ["New", "Contacted", "Qualified", "Site Visit", "Negotiation", "Converted / Lost"] as const

export function ProductCrmPage() {
  return (
    <>
      <PageHero
        eyebrow="InSyte CRM"
        title={
          <>
            Turn Every Property Enquiry Into a <span className="text-brand-accent">Booking.</span>
          </>
        }
        description="One clear workspace for leads, follow-ups, properties, site visits, bookings and revenue."
        actions={
          <>
            <SiteButton href="/signup">Start Free →</SiteButton>
            <SiteButton href="/demo" variant="secondary">Book a Demo</SiteButton>
          </>
        }
      />

      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="grid items-center gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Lead workspace</p>
              <h2 className="mt-3 text-3xl font-bold tracking-tight">See the full story behind every lead.</h2>
              <p className="mt-4 leading-relaxed text-black/60">
                Contact details, source, property interest, owner and next action stay together.
              </p>
            </div>
            <AnnotatedShot
              src="/images/site/19-tenant-leads.png"
              alt="InSyte lead management workspace"
              markers={[
                { x: 28, y: 30, label: "Lead context" },
                { x: 76, y: 23, label: "Fast filters" },
              ]}
            />
          </Reveal>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="text-center">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Built for daily selling</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">Everything between enquiry and booking.</h2>
          </Reveal>
          <div className="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            {CRM_FEATURES.map((feature, index) => {
              const Icon = feature.icon

              return (
                <Reveal key={feature.title} delay={index * 0.05}>
                  <SiteCard className="h-full">
                    <span className="flex size-11 items-center justify-center rounded-xl bg-brand-accent/10 text-brand-accent">
                      <Icon className="size-5" aria-hidden="true" />
                    </span>
                    <h3 className="mt-5 text-lg font-bold">{feature.title}</h3>
                    <p className="mt-2 text-sm leading-relaxed text-black/60">{feature.description}</p>
                  </SiteCard>
                </Reveal>
              )
            })}
          </div>
        </div>
      </section>

      <section className="bg-black py-16 text-white sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal>
            <div className="flex items-center gap-3">
              <CalendarCheck2 className="size-5 text-brand-green" aria-hidden="true" />
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-green">A pipeline everyone can follow</p>
            </div>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">Move deals forward without losing context.</h2>
            <div className="mt-8 grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
              {PIPELINE.map((stage, index) => (
                <div key={stage} className="rounded-xl border border-white/10 bg-white/5 p-4">
                  <span className="text-xs font-semibold text-brand-accent">0{index + 1}</span>
                  <p className="mt-2 text-sm font-semibold">{stage}</p>
                </div>
              ))}
            </div>
          </Reveal>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
          <Reveal>
            <AnnotatedShot
              src="/images/site/26-tenant-follow-ups.png"
              alt="InSyte follow-up planner"
              markers={[{ x: 72, y: 23, label: "Next action" }]}
              caption="Follow-ups stay visible to reps and managers."
            />
          </Reveal>
          <Reveal delay={0.08}>
            <AnnotatedShot
              src="/images/site/30-tenant-bookings.png"
              alt="InSyte property bookings"
              markers={[{ x: 30, y: 31, label: "Booking record" }]}
              caption="Bookings connect sales work to real outcomes."
            />
          </Reveal>
        </div>
      </section>

      <CtaBand
        title="Run your property sales process in one place."
        description="Give every enquiry a clear owner, next step and path to booking."
      />
    </>
  )
}
