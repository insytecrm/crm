import { ArrowRight, BriefcaseBusiness, Headphones, Mail, ShieldCheck } from "lucide-react"

import { SITE_CONTACT } from "@/components/site/lib/emails"
import { CtaBand } from "@/components/site/ui/cta-band"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteButton } from "@/components/site/ui/site-button"
import { SiteCard } from "@/components/site/ui/site-card"

export function BlogPage() {
  return (
    <>
      <PageHero
        eyebrow="Blog"
        title="Practical ideas for modern property sales teams."
        description="Product updates and field notes are coming soon. In the meantime, explore our guides."
        actions={<SiteButton href="/guides">Browse Guides →</SiteButton>}
      />
      <CenteredCard
        icon={ArrowRight}
        title="Publishing soon"
        description="We’re preparing useful, grounded writing on CRM operations, follow-up systems, automation and responsible AI."
      />
    </>
  )
}

export function AboutPage() {
  return (
    <>
      <PageHero
        eyebrow="About InSyte"
        title="Help sales teams work with clarity."
        description="InSyte brings the moving parts of property sales into one focused system—so channel partners can spend less time chasing context and more time serving customers."
      />
      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto grid max-w-5xl gap-6 px-4 sm:px-6 md:grid-cols-2 lg:px-8">
          <Reveal>
            <SiteCard className="h-full">
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Our mission</p>
              <h2 className="mt-4 text-2xl font-bold text-black">Make good sales operations easier to run.</h2>
              <p className="mt-4 leading-relaxed text-black/55">We build connected tools for leads, follow-ups, site visits, bookings, automation and team visibility.</p>
            </SiteCard>
          </Reveal>
          <Reveal delay={0.07}>
            <SiteCard className="h-full">
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-green">How we build</p>
              <h2 className="mt-4 text-2xl font-bold text-black">Useful technology, human judgment.</h2>
              <p className="mt-4 leading-relaxed text-black/55">Automation and AI should support responsible work. Teams stay in control of decisions, customer communication and outcomes.</p>
            </SiteCard>
          </Reveal>
        </div>
      </section>
      <CtaBand title="See InSyte in action." description="Explore the product or book a walkthrough for your team." />
    </>
  )
}

const CONTACTS = [
  ["Sales", "Plans, demos and fit", SITE_CONTACT.sales, Mail],
  ["Support", "Help for InSyte customers", SITE_CONTACT.support, Headphones],
  ["Privacy", "Privacy and legal enquiries", SITE_CONTACT.privacy, ShieldCheck],
  ["Security", "Responsible security reports", SITE_CONTACT.security, ShieldCheck],
] as const

export function ContactPage() {
  return (
    <>
      <PageHero
        eyebrow="Contact"
        title="Reach the right InSyte team."
        description="Choose a contact below and we’ll route your message to the people best placed to help."
        actions={<SiteButton href="/demo">Book a Demo</SiteButton>}
      />
      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto grid max-w-5xl gap-5 px-4 sm:px-6 sm:grid-cols-2 lg:px-8">
          {CONTACTS.map(([title, description, email, Icon], index) => (
            <Reveal key={title} delay={index * 0.05}>
              <SiteCard href={`mailto:${email}`} className="group h-full">
                <Icon className="size-6 text-brand-accent" aria-hidden="true" />
                <h2 className="mt-5 text-xl font-bold text-black">{title}</h2>
                <p className="mt-2 text-sm text-black/55">{description}</p>
                <p className="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-brand-accent">
                  {email}<ArrowRight className="size-4 transition-transform group-hover:translate-x-1" />
                </p>
              </SiteCard>
            </Reveal>
          ))}
        </div>
      </section>
    </>
  )
}

export function CareersPage() {
  return (
    <>
      <PageHero
        eyebrow="Careers"
        title="Build calmer, clearer sales software."
        description="We’re interested in thoughtful people who care about useful products, responsible technology and the details that make teams effective."
        actions={<SiteButton href={`mailto:${SITE_CONTACT.careers}?subject=Careers%20at%20InSyte`}>Introduce Yourself</SiteButton>}
      />
      <CenteredCard
        icon={BriefcaseBusiness}
        title="No open roles listed right now"
        description={`You can still share a concise introduction and relevant work at ${SITE_CONTACT.careers}. We’ll get in touch when there is a strong match.`}
      />
    </>
  )
}

function CenteredCard({
  icon: Icon,
  title,
  description,
}: {
  icon: typeof ArrowRight
  title: string
  description: string
}) {
  return (
    <section className="bg-slate-50 py-16 sm:py-20">
      <Reveal className="mx-auto max-w-2xl px-4 text-center sm:px-6">
        <SiteCard className="p-8 sm:p-10">
          <span className="mx-auto inline-flex size-12 items-center justify-center rounded-2xl bg-brand-accent/10 text-brand-accent">
            <Icon className="size-6" aria-hidden="true" />
          </span>
          <h2 className="mt-5 text-2xl font-bold text-black">{title}</h2>
          <p className="mt-3 leading-relaxed text-black/55">{description}</p>
        </SiteCard>
      </Reveal>
    </section>
  )
}
