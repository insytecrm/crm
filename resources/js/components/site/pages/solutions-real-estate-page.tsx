import {
  Bot,
  Building2,
  Check,
  IndianRupee,
  Inbox,
  Sparkles,
  Target,
  Waypoints,
} from "lucide-react"

import { AnnotatedShot } from "@/components/site/ui/annotated-shot"
import { CtaBand } from "@/components/site/ui/cta-band"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteButton } from "@/components/site/ui/site-button"
import { SiteCard } from "@/components/site/ui/site-card"

const JOURNEY = [
  {
    step: "Capture",
    description: "Bring portal, Facebook, sheet, website and microsite enquiries together.",
    icon: Inbox,
  },
  {
    step: "Manage",
    description: "Assign ownership and keep every follow-up, task and site visit visible.",
    icon: Waypoints,
  },
  {
    step: "Sell",
    description: "Connect lead interest to properties and move each opportunity forward.",
    icon: Building2,
  },
  {
    step: "Earn",
    description: "Record bookings and review the revenue your team creates.",
    icon: IndianRupee,
  },
] as const

const OUTCOMES = [
  "One record for every property enquiry",
  "Clear ownership across the sales team",
  "Visible follow-ups and site visits",
  "Bookings connected to revenue",
] as const

export function SolutionsRealEstatePage() {
  return (
    <>
      <PageHero
        eyebrow="For Channel Partners"
        title={
          <>
            The CRM Built for <span className="text-brand-accent">Real Estate Channel Partners.</span>
          </>
        }
        description="Capture enquiries, coordinate your team and move property opportunities from first contact to booking."
        actions={
          <>
            <SiteButton href="/signup">Start Free →</SiteButton>
            <SiteButton href="/demo" variant="secondary">Book a Demo</SiteButton>
          </>
        }
      />

      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="text-center">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">One connected journey</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">From enquiry to earnings.</h2>
          </Reveal>
          <div className="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
            {JOURNEY.map((stage, index) => {
              const Icon = stage.icon

              return (
                <Reveal key={stage.step} delay={index * 0.06}>
                  <SiteCard className="relative h-full overflow-hidden">
                    <span className="absolute right-4 top-2 text-6xl font-black text-black/[0.035]">0{index + 1}</span>
                    <span className="flex size-11 items-center justify-center rounded-xl bg-brand-accent/10 text-brand-accent">
                      <Icon className="size-5" aria-hidden="true" />
                    </span>
                    <h3 className="mt-5 text-xl font-bold">{stage.step}</h3>
                    <p className="mt-2 text-sm leading-relaxed text-black/60">{stage.description}</p>
                  </SiteCard>
                </Reveal>
              )
            })}
          </div>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-[1.2fr_0.8fr] lg:px-8">
          <Reveal>
            <AnnotatedShot
              src="/images/site/29-tenant-properties.png"
              alt="Property management workspace in InSyte"
              markers={[
                { x: 29, y: 28, label: "Property inventory" },
                { x: 76, y: 21, label: "Add property" },
              ]}
            />
          </Reveal>
          <Reveal delay={0.08}>
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Built around property sales</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">Keep leads and inventory in the same operating system.</h2>
            <div className="mt-6 grid gap-3">
              {OUTCOMES.map((outcome) => (
                <div key={outcome} className="flex items-center gap-3 text-sm font-semibold">
                  <span className="flex size-6 shrink-0 items-center justify-center rounded-full bg-brand-green/15 text-brand-green">
                    <Check className="size-3.5" aria-hidden="true" strokeWidth={3} />
                  </span>
                  {outcome}
                </div>
              ))}
            </div>
          </Reveal>
        </div>
      </section>

      <section className="bg-black py-16 text-white sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="max-w-2xl">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-green">More leverage for every rep</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">A CRM that helps the process keep moving.</h2>
          </Reveal>
          <div className="mt-10 grid gap-5 lg:grid-cols-2">
            <Reveal>
              <div className="h-full rounded-2xl border border-white/10 bg-white/5 p-7">
                <Sparkles className="size-6 text-brand-accent" aria-hidden="true" />
                <h3 className="mt-5 text-xl font-bold">Automate repeatable follow-up.</h3>
                <p className="mt-2 text-sm leading-relaxed text-white/60">
                  Use CRM events and conditions to create consistent next steps.
                </p>
                <SiteButton href="/product/automation" variant="secondary" className="mt-6">Explore Automation</SiteButton>
              </div>
            </Reveal>
            <Reveal delay={0.08}>
              <div className="h-full rounded-2xl border border-white/10 bg-white/5 p-7">
                <Bot className="size-6 text-brand-green" aria-hidden="true" />
                <h3 className="mt-5 text-xl font-bold">Ask your CRM and take action.</h3>
                <p className="mt-2 text-sm leading-relaxed text-white/60">
                  Find leads, view your agenda and complete supported sales tasks in AI OS.
                </p>
                <SiteButton href="/product/ai" variant="secondary" className="mt-6">Explore AI OS</SiteButton>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <Reveal className="mx-auto grid max-w-7xl items-center gap-8 px-4 sm:px-6 lg:grid-cols-[0.7fr_1.3fr] lg:px-8">
          <div>
            <Target className="size-6 text-brand-accent" aria-hidden="true" />
            <h2 className="mt-4 text-3xl font-bold tracking-tight">See the business behind the activity.</h2>
            <p className="mt-3 text-black/60">Connect sales execution to bookings and recorded revenue.</p>
          </div>
          <AnnotatedShot
            src="/images/site/31-tenant-revenue.png"
            alt="Revenue overview in InSyte"
            markers={[{ x: 38, y: 30, label: "Revenue view" }]}
          />
        </Reveal>
      </section>

      <CtaBand
        title="Turn your next enquiry into a managed opportunity."
        description="Give your channel partner business one place to capture, manage, sell and earn."
      />
    </>
  )
}
