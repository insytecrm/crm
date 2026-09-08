import { FileSpreadsheet, Globe2, Plug, RadioTower, Share2, Sheet, Webhook } from "lucide-react"

import { AnnotatedShot } from "@/components/site/ui/annotated-shot"
import { CtaBand } from "@/components/site/ui/cta-band"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteButton } from "@/components/site/ui/site-button"
import { SiteCard } from "@/components/site/ui/site-card"

const INTEGRATIONS = [
  { name: "Facebook", description: "Connect Facebook pages and map lead form fields.", icon: Share2 },
  { name: "99acres", description: "Route portal enquiries into the CRM through a webhook.", icon: RadioTower },
  { name: "Housing", description: "Bring supported property portal leads into one queue.", icon: RadioTower },
  { name: "MagicBricks", description: "Keep portal enquiries with the rest of your leads.", icon: RadioTower },
  { name: "NoBroker", description: "Centralize supported portal lead submissions.", icon: RadioTower },
  { name: "Google Sheets", description: "Sync structured lead rows from a connected sheet.", icon: FileSpreadsheet },
  { name: "Website / API", description: "Send website leads through InSyte's lead API.", icon: Webhook },
  { name: "Microsites", description: "Capture enquiries from property microsites.", icon: Globe2 },
] as const

export function ProductIntegrationsPage() {
  return (
    <>
      <PageHero
        eyebrow="Lead Integrations"
        title={
          <>
            Bring Every Lead Into <span className="text-brand-accent">One CRM.</span>
          </>
        }
        description="Connect the lead sources your property business already uses and keep follow-up in one place."
        actions={
          <>
            <SiteButton href="/signup">Start Free →</SiteButton>
            <SiteButton href="/demo" variant="secondary">Plan Your Setup</SiteButton>
          </>
        }
      />

      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="max-w-2xl">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Connected lead capture</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">Fewer inboxes. One follow-up process.</h2>
          </Reveal>
          <div className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {INTEGRATIONS.map((integration, index) => {
              const Icon = integration.icon

              return (
                <Reveal key={integration.name} delay={(index % 4) * 0.05}>
                  <SiteCard className="h-full">
                    <span className="flex size-11 items-center justify-center rounded-xl bg-brand-accent/10 text-brand-accent">
                      <Icon className="size-5" aria-hidden="true" />
                    </span>
                    <h3 className="mt-5 text-lg font-bold">{integration.name}</h3>
                    <p className="mt-2 text-sm leading-relaxed text-black/60">{integration.description}</p>
                  </SiteCard>
                </Reveal>
              )
            })}
          </div>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Set up with confidence</p>
              <h2 className="mt-3 text-3xl font-bold tracking-tight">Map, verify and start capturing.</h2>
            </div>
            <div className="flex items-center gap-2 text-sm font-semibold text-black/50">
              <Plug className="size-4 text-brand-green" aria-hidden="true" />
              Built into your InSyte settings
            </div>
          </Reveal>
          <div className="mt-10 grid gap-8 lg:grid-cols-2">
            <Reveal>
              <AnnotatedShot
                src="/images/site/49-tenant-facebook.png"
                alt="Facebook lead connection settings in InSyte"
                markers={[{ x: 72, y: 29, label: "Connect page" }]}
                caption="Connect and verify Facebook lead capture."
              />
            </Reveal>
            <Reveal delay={0.06}>
              <AnnotatedShot
                src="/images/site/48-tenant-google-sheets.png"
                alt="Google Sheets lead connection in InSyte"
                markers={[{ x: 72, y: 28, label: "Add sheet" }]}
                caption="Choose a sheet and map its lead fields."
              />
            </Reveal>
            <Reveal>
              <AnnotatedShot
                src="/images/site/50-tenant-portal-99acres.png"
                alt="99acres portal webhook connection in InSyte"
                markers={[{ x: 30, y: 37, label: "Portal webhook" }]}
                caption="Receive supported portal enquiries through a dedicated endpoint."
              />
            </Reveal>
            <Reveal delay={0.06}>
              <AnnotatedShot
                src="/images/site/47-tenant-lead-api.png"
                alt="Lead API token settings in InSyte"
                markers={[{ x: 73, y: 30, label: "API access" }]}
                caption="Connect your own website or lead form."
              />
            </Reveal>
          </div>
        </div>
      </section>

      <section className="bg-black py-12 text-white">
        <Reveal className="mx-auto flex max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
          <Sheet className="size-6 shrink-0 text-brand-green" aria-hidden="true" />
          <p className="text-sm leading-relaxed text-white/65">
            Integration availability and setup requirements can vary by source. Our team can confirm the right route for your lead flow.
          </p>
        </Reveal>
      </section>

      <CtaBand
        title="Give every incoming lead the same clear next step."
        description="Bring your lead sources together and follow up from one CRM."
      />
    </>
  )
}
