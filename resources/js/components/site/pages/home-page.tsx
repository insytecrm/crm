import {
  ArrowRight,
  CalendarCheck2,
  Check,
  CircleDollarSign,
  Inbox,
  Sparkles,
  Workflow,
} from "lucide-react"

import { SiteHero } from "@/components/site/site-hero"
import { AnnotatedShot } from "@/components/site/ui/annotated-shot"
import { CtaBand } from "@/components/site/ui/cta-band"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteButton } from "@/components/site/ui/site-button"
import { SiteCard } from "@/components/site/ui/site-card"

type HomePageProps = {
  dashboardSrc: string
}

const PROBLEMS = [
  ["Scattered enquiries", "Leads get lost across sheets, portals and chats."],
  ["Inconsistent follow-up", "The next action depends on memory and manual reminders."],
  ["Limited visibility", "Teams lack one clear view from enquiry to booking."],
] as const

const STEPS = [
  ["Capture", Inbox],
  ["Follow up", Workflow],
  ["Site visit", CalendarCheck2],
  ["Book", Check],
  ["Earn", CircleDollarSign],
] as const

const PRODUCTS = [
  {
    title: "One sales workspace",
    description: "Keep leads, tasks, visits and bookings connected.",
    href: "/crm",
    src: "/images/site/19-tenant-leads.png",
    label: "CRM",
  },
  {
    title: "Automations that assist",
    description: "Build workflows for repeatable follow-up and team actions.",
    href: "/automation",
    src: "/images/site/38-tenant-automations.png",
    label: "Automation",
  },
  {
    title: "AI with your team in control",
    description: "Use AI tools to help summarize context and prepare next steps.",
    href: "/ai",
    src: "/images/site/18-tenant-ai-os.png",
    label: "AI OS",
  },
  {
    title: "Bring lead sources together",
    description: "Connect supported portals, sheets and lead channels.",
    href: "/integrations",
    src: "/images/site/48-tenant-google-sheets.png",
    label: "Integrations",
  },
] as const

export function HomePage({ dashboardSrc }: HomePageProps) {
  return (
    <>
      <SiteHero dashboardSrc={dashboardSrc} />

      <section className="border-y border-black/5 bg-slate-50 py-10">
        <div className="mx-auto grid max-w-7xl gap-4 px-4 sm:px-6 md:grid-cols-3 lg:px-8">
          {PROBLEMS.map(([title, description], index) => (
            <Reveal key={title} delay={index * 0.06}>
              <SiteCard className="h-full p-5">
                <p className="font-semibold text-black">{title}</p>
                <p className="mt-2 text-sm leading-relaxed text-black/55">{description}</p>
              </SiteCard>
            </Reveal>
          ))}
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <SectionHeading
            eyebrow="A clearer sales flow"
            title="From first enquiry to earned commission"
            description="Give every lead a visible next step and every teammate the context to move it forward."
          />
          <div className="mt-10 grid gap-3 md:grid-cols-5">
            {STEPS.map(([label, Icon], index) => (
              <Reveal key={label} delay={index * 0.07}>
                <SiteCard className="relative flex h-full items-center gap-3 p-4 md:block">
                  <span className="inline-flex size-10 items-center justify-center rounded-xl bg-brand-accent/10 text-brand-accent">
                    <Icon className="size-5" aria-hidden="true" />
                  </span>
                  <p className="font-semibold text-black md:mt-5">{label}</p>
                  {index < STEPS.length - 1 ? (
                    <ArrowRight className="ml-auto size-4 text-black/25 md:absolute md:-right-3 md:top-1/2 md:z-10" aria-hidden="true" />
                  ) : null}
                </SiteCard>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <SectionHeading
            eyebrow="See the product"
            title="Built around the work your team already does"
            description="Explore focused tools that share one customer record and one operating rhythm."
          />
          <div className="mt-10 grid gap-6 lg:grid-cols-2">
            {PRODUCTS.map((product, index) => (
              <Reveal key={product.href} delay={index * 0.06}>
                <SiteCard href={product.href} className="group h-full overflow-hidden p-0">
                  <AnnotatedShot
                    src={product.src}
                    alt={`${product.title} in InSyte`}
                    markers={[{ x: 17, y: 18, label: product.label }]}
                    className="rounded-none border-0 border-b border-black/5 shadow-none"
                  />
                  <div className="p-6">
                    <h3 className="text-xl font-semibold text-black">{product.title}</h3>
                    <p className="mt-2 text-sm leading-relaxed text-black/55">{product.description}</p>
                    <span className="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-brand-accent">
                      Explore <ArrowRight className="size-4 transition-transform group-hover:translate-x-1" />
                    </span>
                  </div>
                </SiteCard>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
          <Reveal>
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Designed for better outcomes</p>
            <h2 className="mt-4 text-3xl font-bold tracking-tight text-black sm:text-4xl">Less chasing. More clarity.</h2>
          </Reveal>
          <Reveal className="grid gap-4" delay={0.08}>
            {[
              "Keep each lead’s context and next action together.",
              "Make follow-up ownership clear across the team.",
              "Spot stalled opportunities before they disappear.",
              "Use automation and AI as support—not a substitute for judgment.",
            ].map((item) => (
              <div key={item} className="flex gap-3 rounded-xl bg-brand-green/5 p-4 text-sm font-medium text-black/70">
                <Sparkles className="size-5 shrink-0 text-brand-green" aria-hidden="true" />
                {item}
              </div>
            ))}
          </Reveal>
        </div>
      </section>

      <section className="bg-brand-accent/5 py-14">
        <Reveal className="mx-auto flex max-w-7xl flex-col items-start justify-between gap-6 px-4 sm:px-6 md:flex-row md:items-center lg:px-8">
          <div>
            <p className="text-sm font-semibold text-brand-accent">Plans for growing teams</p>
            <h2 className="mt-2 text-2xl font-bold text-black">Choose capabilities with help from our sales team.</h2>
          </div>
          <SiteButton href="/pricing">Contact Sales →</SiteButton>
        </Reveal>
      </section>

      <CtaBand
        title="Bring your sales process into focus."
        description="Start free or book a guided walkthrough of InSyte."
      />
    </>
  )
}

function SectionHeading({
  eyebrow,
  title,
  description,
}: {
  eyebrow: string
  title: string
  description: string
}) {
  return (
    <Reveal className="max-w-2xl">
      <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">{eyebrow}</p>
      <h2 className="mt-4 text-3xl font-bold tracking-tight text-black sm:text-4xl">{title}</h2>
      <p className="mt-4 leading-relaxed text-black/55">{description}</p>
    </Reveal>
  )
}
