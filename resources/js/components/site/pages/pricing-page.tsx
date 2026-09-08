import { Check, Mail } from "lucide-react"

import { SITE_CONTACT } from "@/components/site/lib/emails"
import { CtaBand } from "@/components/site/ui/cta-band"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteButton } from "@/components/site/ui/site-button"
import { SiteCard } from "@/components/site/ui/site-card"

const PLANS = [
  {
    name: "Starter",
    description: "Core CRM tools for getting your sales process organized.",
    capabilities: ["Lead and follow-up management", "Tasks and site visits", "Properties and bookings", "Team workspace"],
    featured: false,
  },
  {
    name: "Growth",
    description: "Automation and AI support for teams building repeatable sales operations.",
    capabilities: ["Everything in Starter", "Workflow automation", "AI-assisted tools", "Microsites and custom domains", "Expanded integrations"],
    featured: true,
  },
  {
    name: "Pro",
    description: "Advanced controls and capacity for established sales teams.",
    capabilities: ["Everything in Growth", "Advanced reporting", "Broader team capabilities", "Higher plan limits", "Implementation guidance"],
    featured: false,
  },
] as const

const FAQS = [
  ["Why are prices not listed?", "Plans are matched to team needs, capabilities and usage. Contact Sales for a clear recommendation."],
  ["Which plans include AI?", "AI-assisted capabilities are available from Growth, subject to the selected plan configuration and limits."],
  ["Where are microsites and custom domains available?", "Microsites and custom domains are available on Growth and Pro plans."],
  ["Can I see InSyte before choosing?", "Yes. Book a demo and we’ll walk through the workflows that matter to your team."],
] as const

export function PricingPage() {
  return (
    <>
      <PageHero
        eyebrow="Pricing"
        title="Simple plans that grow with your team."
        description="No confusing public price grid. Tell us how your team works and we’ll help you choose the right capabilities."
        actions={
          <>
            <SiteButton href={`mailto:${SITE_CONTACT.sales}`}>Contact Sales</SiteButton>
            <SiteButton href="/demo" variant="secondary">Book a Demo</SiteButton>
          </>
        }
      />

      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
          {PLANS.map((plan, index) => (
            <Reveal key={plan.name} delay={index * 0.07}>
              <SiteCard className={plan.featured ? "h-full border-brand-accent/30 ring-1 ring-brand-accent/15" : "h-full"}>
                {plan.featured ? (
                  <span className="rounded-full bg-brand-accent/10 px-3 py-1 text-xs font-semibold text-brand-accent">Popular for growing teams</span>
                ) : null}
                <h2 className="mt-5 text-2xl font-bold text-black">{plan.name}</h2>
                <p className="mt-3 min-h-14 text-sm leading-relaxed text-black/55">{plan.description}</p>
                <p className="mt-6 text-lg font-semibold text-black">Talk to Sales</p>
                <ul className="mt-6 grid gap-3">
                  {plan.capabilities.map((capability) => (
                    <li key={capability} className="flex gap-2 text-sm text-black/70">
                      <Check className="mt-0.5 size-4 shrink-0 text-brand-green" strokeWidth={2.5} aria-hidden="true" />
                      {capability}
                    </li>
                  ))}
                </ul>
                <SiteButton href={`mailto:${SITE_CONTACT.sales}?subject=InSyte%20${plan.name}%20plan`} className="mt-8 w-full">
                  Contact Sales
                </SiteButton>
              </SiteCard>
            </Reveal>
          ))}
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
          <Reveal>
            <h2 className="text-3xl font-bold tracking-tight text-black">Plan questions</h2>
          </Reveal>
          <div className="mt-8 grid gap-4 sm:grid-cols-2">
            {FAQS.map(([question, answer], index) => (
              <Reveal key={question} delay={index * 0.05}>
                <SiteCard className="h-full">
                  <h3 className="font-semibold text-black">{question}</h3>
                  <p className="mt-2 text-sm leading-relaxed text-black/55">{answer}</p>
                </SiteCard>
              </Reveal>
            ))}
          </div>
          <Reveal className="mt-8 flex items-center gap-3 rounded-2xl bg-black p-5 text-white">
            <Mail className="size-5 text-brand-accent" aria-hidden="true" />
            <p className="text-sm text-white/70">
              Prefer email? Write to{" "}
              <a className="font-semibold text-white hover:text-brand-accent" href={`mailto:${SITE_CONTACT.sales}`}>
                {SITE_CONTACT.sales}
              </a>
            </p>
          </Reveal>
        </div>
      </section>

      <CtaBand title="Find the right InSyte plan." description="Book a demo and get a capability recommendation for your team." />
    </>
  )
}
