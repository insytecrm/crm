import { Check } from "lucide-react"

import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteCard } from "@/components/site/ui/site-card"
import { SiteLeadForm } from "@/components/site/ui/site-lead-form"

export function SignupPage() {
  return (
    <>
      <PageHero
        eyebrow="Start free"
        title="Give your team one place to move deals forward."
        description="Choose a starting plan and tell us about your team. No card details are collected on this form."
      />
      <section className="bg-slate-50 py-14 sm:py-20">
        <div className="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-[0.75fr_1.25fr] lg:px-8">
          <Reveal>
            <SiteCard className="bg-black text-white">
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Your first week</p>
              <h2 className="mt-4 text-2xl font-bold">Start with a focused setup.</h2>
              <ul className="mt-6 grid gap-4">
                {[
                  "Bring your active leads into one workspace.",
                  "Set clear owners and next follow-up actions.",
                  "Explore the capabilities included in your chosen plan.",
                ].map((item) => (
                  <li key={item} className="flex gap-3 text-sm leading-relaxed text-white/70">
                    <Check className="mt-0.5 size-4 shrink-0 text-brand-green" strokeWidth={2.5} aria-hidden="true" />
                    {item}
                  </li>
                ))}
              </ul>
              <p className="mt-7 border-t border-white/10 pt-6 text-xs leading-relaxed text-white/45">
                Plan availability and limits are confirmed during onboarding.
              </p>
            </SiteCard>
          </Reveal>
          <Reveal delay={0.08}>
            <SiteCard className="p-6 sm:p-8">
              <h2 className="text-2xl font-bold text-black">Create your trial request</h2>
              <p className="mt-2 text-sm text-black/55">Select monthly or yearly billing for your preferred plan.</p>
              <div className="mt-7">
                <SiteLeadForm type="trial" />
              </div>
            </SiteCard>
          </Reveal>
        </div>
      </section>
    </>
  )
}
