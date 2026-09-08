import { CheckCircle2, ShieldCheck, Users } from "lucide-react"

import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteCard } from "@/components/site/ui/site-card"
import { SiteLeadForm } from "@/components/site/ui/site-lead-form"

const TRUST_ITEMS = [
  ["A useful walkthrough", "See the workflows that match your sales process.", Users],
  ["Clear answers", "Ask about capabilities, integrations and onboarding.", CheckCircle2],
  ["No pressure", "Understand the fit before deciding on a plan.", ShieldCheck],
] as const

export function DemoPage() {
  return (
    <>
      <PageHero
        eyebrow="Book a demo"
        title="See how InSyte fits your sales team."
        description="Tell us a little about your business. We’ll tailor the walkthrough to the way you capture, follow up and close."
      />
      <section className="bg-slate-50 py-14 sm:py-20">
        <div className="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
          <Reveal className="grid content-start gap-4">
            {TRUST_ITEMS.map(([title, description, Icon]) => (
              <SiteCard key={title} className="flex gap-4">
                <span className="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-green/10 text-brand-green">
                  <Icon className="size-5" aria-hidden="true" />
                </span>
                <div>
                  <h2 className="font-semibold text-black">{title}</h2>
                  <p className="mt-1 text-sm leading-relaxed text-black/55">{description}</p>
                </div>
              </SiteCard>
            ))}
          </Reveal>
          <Reveal delay={0.08}>
            <SiteCard className="p-6 sm:p-8">
              <h2 className="text-2xl font-bold text-black">Request your walkthrough</h2>
              <p className="mt-2 text-sm text-black/55">Fields marked with * are required.</p>
              <div className="mt-7">
                <SiteLeadForm type="demo" />
              </div>
            </SiteCard>
          </Reveal>
        </div>
      </section>
    </>
  )
}
