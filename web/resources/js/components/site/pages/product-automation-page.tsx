import { ArrowRight, BellRing, Filter, Play, Repeat2, Zap } from "lucide-react"

import { AnnotatedShot } from "@/components/site/ui/annotated-shot"
import { CtaBand } from "@/components/site/ui/cta-band"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteButton } from "@/components/site/ui/site-button"
import { SiteCard } from "@/components/site/ui/site-card"

const TRIGGERS = [
  "A new lead is created",
  "A lead stage changes",
  "A follow-up becomes due",
  "A site visit is scheduled",
] as const

const STEPS = [
  { label: "Trigger", detail: "A CRM event starts the workflow", icon: Zap },
  { label: "Check", detail: "Conditions decide if it should continue", icon: Filter },
  { label: "Act", detail: "Create the right task or follow-up", icon: Play },
] as const

export function ProductAutomationPage() {
  return (
    <>
      <PageHero
        eyebrow="Sales Automation"
        title={
          <>
            Automate the Follow-Up. <span className="text-brand-accent">Close More Property Deals.</span>
          </>
        }
        description="Turn repeatable sales steps into workflows your team can rely on."
        actions={
          <>
            <SiteButton href="/signup">Start Free →</SiteButton>
            <SiteButton href="/demo" variant="secondary">See Automation</SiteButton>
          </>
        }
      />

      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="grid items-center gap-10 lg:grid-cols-[0.75fr_1.25fr]">
            <div>
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Automation overview</p>
              <h2 className="mt-3 text-3xl font-bold tracking-tight">Know what is running and why.</h2>
              <p className="mt-4 leading-relaxed text-black/60">
                Keep active workflows visible, review their triggers and pause them when your process changes.
              </p>
            </div>
            <AnnotatedShot
              src="/images/site/38-tenant-automations.png"
              alt="InSyte automation overview"
              markers={[
                { x: 32, y: 31, label: "Active workflow" },
                { x: 80, y: 23, label: "Create automation" },
              ]}
            />
          </Reveal>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="max-w-2xl">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Start with a signal</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">Use the moments that already happen in your CRM.</h2>
          </Reveal>
          <div className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {TRIGGERS.map((trigger, index) => (
              <Reveal key={trigger} delay={index * 0.05}>
                <SiteCard className="h-full">
                  <span className="text-sm font-bold text-brand-accent">0{index + 1}</span>
                  <p className="mt-5 font-semibold">{trigger}</p>
                </SiteCard>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-black py-16 text-white sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal>
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-green">A simple workflow model</p>
            <div className="mt-8 grid gap-4 lg:grid-cols-[1fr_auto_1fr_auto_1fr] lg:items-center">
              {STEPS.map((step, index) => {
                const Icon = step.icon

                return (
                  <div key={step.label} className="contents">
                    <div className="rounded-2xl border border-white/10 bg-white/5 p-6">
                      <Icon className="size-5 text-brand-accent" aria-hidden="true" />
                      <h3 className="mt-4 text-lg font-bold">{step.label}</h3>
                      <p className="mt-2 text-sm leading-relaxed text-white/60">{step.detail}</p>
                    </div>
                    {index < STEPS.length - 1 ? (
                      <ArrowRight className="hidden size-5 text-white/30 lg:block" aria-hidden="true" />
                    ) : null}
                  </div>
                )
              })}
            </div>
          </Reveal>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
          <Reveal>
            <AnnotatedShot
              src="/images/site/39-tenant-workflows.png"
              alt="InSyte workflow builder"
              markers={[
                { x: 28, y: 28, label: "Trigger" },
                { x: 68, y: 49, label: "Action step" },
              ]}
            />
          </Reveal>
          <Reveal delay={0.08}>
            <div className="grid gap-5">
              <SiteCard>
                <Filter className="size-5 text-brand-accent" aria-hidden="true" />
                <h3 className="mt-4 text-lg font-bold">Run only when conditions match.</h3>
                <p className="mt-2 text-sm leading-relaxed text-black/60">
                  Narrow workflows by lead fields and sales context before an action runs.
                </p>
              </SiteCard>
              <SiteCard>
                <BellRing className="size-5 text-brand-green" aria-hidden="true" />
                <h3 className="mt-4 text-lg font-bold">Keep the message consistent.</h3>
                <p className="mt-2 text-sm leading-relaxed text-black/60">
                  Reuse saved templates where supported instead of rewriting routine communication.
                </p>
              </SiteCard>
              <SiteCard>
                <Repeat2 className="size-5 text-brand-accent" aria-hidden="true" />
                <h3 className="mt-4 text-lg font-bold">Adjust as the process evolves.</h3>
                <p className="mt-2 text-sm leading-relaxed text-black/60">
                  Edit, activate or pause workflows without rebuilding your CRM.
                </p>
              </SiteCard>
            </div>
          </Reveal>
        </div>
      </section>

      <CtaBand
        title="Stop depending on memory."
        description="Build repeatable follow-up workflows that keep the next step moving."
      />
    </>
  )
}
