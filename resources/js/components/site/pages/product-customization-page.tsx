import { Building2, Globe2, Layers3, Settings2, ShieldCheck, Users2 } from "lucide-react"

import { AnnotatedShot } from "@/components/site/ui/annotated-shot"
import { CtaBand } from "@/components/site/ui/cta-band"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteButton } from "@/components/site/ui/site-button"
import { SiteCard } from "@/components/site/ui/site-card"

const CUSTOMIZATION_OPTIONS = [
  {
    title: "Custom CRM Domain",
    description: "Give your team a branded address for accessing InSyte.",
    icon: Globe2,
  },
  {
    title: "Property Domain",
    description: "Use a verified domain for your public property experience.",
    icon: Building2,
  },
  {
    title: "Roles & Permissions",
    description: "Control access based on each person's responsibilities.",
    icon: ShieldCheck,
  },
  {
    title: "Sales Teams",
    description: "Organize users and lead ownership around the way you sell.",
    icon: Users2,
  },
  {
    title: "Plan-Based Configuration",
    description: "Use the capabilities and limits included in your selected plan.",
    icon: Layers3,
  },
  {
    title: "Guided Setup",
    description: "Talk to our team about domains, roles and the right setup.",
    icon: Settings2,
  },
] as const

export function ProductCustomizationPage() {
  return (
    <>
      <PageHero
        eyebrow="CRM Customization"
        title={
          <>
            Your Sales Process. Your Rules. <span className="text-brand-accent">Your CRM.</span>
          </>
        }
        description="Shape access, teams and domains around the way your property business operates."
        actions={
          <>
            <SiteButton href="/demo">Talk to Our Team →</SiteButton>
            <SiteButton href="/signup" variant="secondary">Start Free</SiteButton>
          </>
        }
      />

      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-[0.75fr_1.25fr] lg:px-8">
          <Reveal>
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Your domains</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">Put your business name at the front.</h2>
            <p className="mt-4 leading-relaxed text-black/60">
              Add a CRM or property domain, review the required DNS record and verify it from settings.
            </p>
          </Reveal>
          <Reveal delay={0.08}>
            <AnnotatedShot
              src="/images/site/45-tenant-settings-domains.png"
              alt="Custom domain settings in InSyte"
              markers={[
                { x: 33, y: 32, label: "Domain purpose" },
                { x: 75, y: 26, label: "Add domain" },
              ]}
            />
          </Reveal>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="text-center">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Configure what matters</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">A workspace that fits the team using it.</h2>
          </Reveal>
          <div className="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            {CUSTOMIZATION_OPTIONS.map((option, index) => {
              const Icon = option.icon

              return (
                <Reveal key={option.title} delay={index * 0.05}>
                  <SiteCard className="h-full">
                    <span className="flex size-11 items-center justify-center rounded-xl bg-brand-accent/10 text-brand-accent">
                      <Icon className="size-5" aria-hidden="true" />
                    </span>
                    <h3 className="mt-5 text-lg font-bold">{option.title}</h3>
                    <p className="mt-2 text-sm leading-relaxed text-black/60">{option.description}</p>
                  </SiteCard>
                </Reveal>
              )
            })}
          </div>
        </div>
      </section>

      <section className="bg-black py-16 text-white sm:py-20">
        <Reveal className="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
          {["Define access", "Organize the team", "Verify your domains"].map((step, index) => (
            <div key={step} className="border-l border-white/15 pl-5">
              <span className="text-xs font-bold text-brand-green">0{index + 1}</span>
              <p className="mt-3 text-lg font-semibold">{step}</p>
            </div>
          ))}
        </Reveal>
      </section>

      <CtaBand
        title="Let’s configure InSyte around your business."
        description="Talk to our team about your users, domains and sales setup."
      />
    </>
  )
}
