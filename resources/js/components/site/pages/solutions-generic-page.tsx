import {
  BarChart3,
  Building2,
  CalendarCheck2,
  ClipboardCheck,
  ContactRound,
  FileSpreadsheet,
  ListTodo,
  Settings2,
  Users2,
  Waypoints,
  Workflow,
} from "lucide-react"

import { AnnotatedShot } from "@/components/site/ui/annotated-shot"
import { CtaBand } from "@/components/site/ui/cta-band"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteButton } from "@/components/site/ui/site-button"
import { SiteCard } from "@/components/site/ui/site-card"

type SolutionsGenericPageProps = {
  kind: "sales-teams" | "small-businesses" | "agencies" | "other"
}

const SHARED_FEATURES = {
  leads: { title: "Lead Records", description: "Keep contact, source, owner and activity together.", icon: ContactRound },
  followUps: { title: "Follow-Ups", description: "Make the next action and due date visible.", icon: Waypoints },
  tasks: { title: "Tasks", description: "Create, assign and complete sales work.", icon: ListTodo },
  team: { title: "Team Access", description: "Organize users with roles and permissions.", icon: Users2 },
  reports: { title: "Reports", description: "Review activity and business performance.", icon: BarChart3 },
  automation: { title: "Automation", description: "Turn repeatable CRM events into workflows.", icon: Workflow },
  bookings: { title: "Bookings", description: "Record successful outcomes in the CRM.", icon: CalendarCheck2 },
  properties: { title: "Properties", description: "Connect property inventory with sales work.", icon: Building2 },
  sheets: { title: "Sheet Sync", description: "Bring structured leads in from Google Sheets.", icon: FileSpreadsheet },
  setup: { title: "Flexible Setup", description: "Configure teams, access and supported domains.", icon: Settings2 },
  process: { title: "Clear Process", description: "Give each opportunity an owner and next step.", icon: ClipboardCheck },
} as const

const CONTENT = {
  "sales-teams": {
    eyebrow: "For Sales Teams",
    title: "Keep Every Rep Focused on the Next Best Step.",
    description: "Bring leads, ownership, follow-ups, tasks and results into one shared sales workspace.",
    features: [
      SHARED_FEATURES.leads,
      SHARED_FEATURES.followUps,
      SHARED_FEATURES.tasks,
      SHARED_FEATURES.team,
      SHARED_FEATURES.automation,
      SHARED_FEATURES.reports,
    ],
    image: "/images/site/36-tenant-teams.png",
    imageAlt: "Sales team management in InSyte",
    marker: "Team workspace",
    cta: "Give your sales team one clear place to work.",
  },
  "small-businesses": {
    eyebrow: "For Small Businesses",
    title: "A Simple CRM for a Growing Sales Operation.",
    description: "Stay on top of customer opportunities without adding more spreadsheets or scattered reminders.",
    features: [
      SHARED_FEATURES.leads,
      SHARED_FEATURES.followUps,
      SHARED_FEATURES.tasks,
      SHARED_FEATURES.sheets,
      SHARED_FEATURES.automation,
      SHARED_FEATURES.reports,
    ],
    image: "/images/site/19-tenant-leads.png",
    imageAlt: "Lead workspace for a small business in InSyte",
    marker: "All leads",
    cta: "Replace scattered sales work with one clear system.",
  },
  agencies: {
    eyebrow: "For Agencies",
    title: "Manage More Enquiries Without Losing the Personal Follow-Up.",
    description: "Centralize incoming leads, assign ownership and keep client-facing work moving.",
    features: [
      SHARED_FEATURES.leads,
      SHARED_FEATURES.team,
      SHARED_FEATURES.followUps,
      SHARED_FEATURES.tasks,
      SHARED_FEATURES.automation,
      SHARED_FEATURES.reports,
    ],
    image: "/images/site/34-tenant-reports.png",
    imageAlt: "Agency reporting workspace in InSyte",
    marker: "Performance view",
    cta: "Build a more consistent agency sales process.",
  },
  other: {
    eyebrow: "For Your Business",
    title: "A Clearer Way to Manage Leads and Sales Work.",
    description: "Use InSyte when your process needs structured ownership, follow-up and reporting.",
    features: [
      SHARED_FEATURES.process,
      SHARED_FEATURES.leads,
      SHARED_FEATURES.followUps,
      SHARED_FEATURES.tasks,
      SHARED_FEATURES.team,
      SHARED_FEATURES.setup,
      SHARED_FEATURES.automation,
      SHARED_FEATURES.reports,
    ],
    image: "/images/site/26-tenant-follow-ups.png",
    imageAlt: "Follow-up workspace in InSyte",
    marker: "Next steps",
    cta: "See how InSyte fits your sales process.",
  },
} as const

export function SolutionsGenericPage({ kind }: SolutionsGenericPageProps) {
  const content = CONTENT[kind]

  return (
    <>
      <PageHero
        eyebrow={content.eyebrow}
        title={content.title}
        description={content.description}
        actions={
          <>
            <SiteButton href="/signup">Start Free →</SiteButton>
            <SiteButton href="/demo" variant="secondary">Book a Demo</SiteButton>
          </>
        }
      />

      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-[0.72fr_1.28fr] lg:px-8">
          <Reveal>
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">One shared view</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">Know what needs attention now.</h2>
            <p className="mt-4 leading-relaxed text-black/60">
              Keep customer context, ownership and the next action visible to the people doing the work.
            </p>
          </Reveal>
          <Reveal delay={0.08}>
            <AnnotatedShot
              src={content.image}
              alt={content.imageAlt}
              markers={[{ x: 34, y: 30, label: content.marker }]}
            />
          </Reveal>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="text-center">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Core capabilities</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">Less admin between the lead and the outcome.</h2>
          </Reveal>
          <div className="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            {content.features.map((feature, index) => {
              const Icon = feature.icon

              return (
                <Reveal key={feature.title} delay={(index % 3) * 0.05}>
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

      <CtaBand
        title={content.cta}
        description="Start with your current workflow and bring the important sales steps into one CRM."
      />
    </>
  )
}
