import { Bot, CalendarDays, CheckCircle2, ListTodo, MessageSquareText, Search, Sparkles } from "lucide-react"

import { AnnotatedShot } from "@/components/site/ui/annotated-shot"
import { CtaBand } from "@/components/site/ui/cta-band"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteButton } from "@/components/site/ui/site-button"
import { SiteCard } from "@/components/site/ui/site-card"

const PROMPTS = [
  "Show leads added this week.",
  "What is on my agenda today?",
  "Schedule a site visit for this lead.",
  "Add a note and create a follow-up task.",
] as const

const AI_CAPABILITIES = [
  {
    title: "Ask",
    description: "Ask for your agenda and get a focused view of the work ahead.",
    icon: MessageSquareText,
  },
  {
    title: "Find",
    description: "Search CRM leads through a natural conversation.",
    icon: Search,
  },
  {
    title: "Take Action",
    description: "Update routine CRM work without clicking through multiple screens.",
    icon: Sparkles,
  },
] as const

const ACTIONS = [
  "Search leads",
  "View your agenda",
  "Schedule or complete follow-ups",
  "Schedule or complete site visits",
  "Create or complete tasks",
  "Add lead notes",
] as const

export function ProductAiPage() {
  return (
    <>
      <PageHero
        eyebrow="InSyte AI OS"
        title={
          <>
            Your CRM. Now You Can <span className="text-brand-accent">Talk to It.</span>
          </>
        }
        description="Ask for CRM information and handle supported sales tasks from one conversational workspace."
        actions={
          <>
            <SiteButton href="/signup">Start Free →</SiteButton>
            <SiteButton href="/demo" variant="secondary">See AI OS</SiteButton>
          </>
        }
        dark
      />

      <section className="bg-black pb-16 sm:pb-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal>
            <AnnotatedShot
              src="/images/site/18-tenant-ai-os.png"
              alt="InSyte AI OS conversational CRM workspace"
              markers={[
                { x: 27, y: 18, label: "AI workspace" },
                { x: 64, y: 75, label: "Ask or act" },
              ]}
              className="border-white/10"
            />
          </Reveal>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="text-center">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-accent">Plain language in. CRM work out.</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight">Start with the question you already have.</h2>
          </Reveal>
          <div className="mx-auto mt-10 grid max-w-4xl gap-4 sm:grid-cols-2">
            {PROMPTS.map((prompt, index) => (
              <Reveal key={prompt} delay={index * 0.05}>
                <div className="flex items-center gap-3 rounded-2xl border border-black/[0.06] bg-slate-50 p-4">
                  <span className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-accent/10 text-brand-accent">
                    <Bot className="size-4" aria-hidden="true" />
                  </span>
                  <p className="text-sm font-semibold">“{prompt}”</p>
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="grid gap-5 md:grid-cols-3">
            {AI_CAPABILITIES.map((capability, index) => {
              const Icon = capability.icon

              return (
                <Reveal key={capability.title} delay={index * 0.06}>
                  <SiteCard className="h-full">
                    <Icon className="size-6 text-brand-accent" aria-hidden="true" />
                    <h2 className="mt-5 text-xl font-bold">{capability.title}</h2>
                    <p className="mt-2 text-sm leading-relaxed text-black/60">{capability.description}</p>
                  </SiteCard>
                </Reveal>
              )
            })}
          </div>

          <Reveal className="mt-10 rounded-2xl bg-black p-6 text-white sm:p-8">
            <div className="grid gap-8 lg:grid-cols-[0.7fr_1.3fr]">
              <div>
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-green">Available actions</p>
                <h2 className="mt-3 text-2xl font-bold">Useful work, not a demo script.</h2>
              </div>
              <div className="grid gap-3 sm:grid-cols-2">
                {ACTIONS.map((action) => (
                  <div key={action} className="flex items-center gap-2 text-sm text-white/75">
                    <CheckCircle2 className="size-4 shrink-0 text-brand-green" aria-hidden="true" />
                    {action}
                  </div>
                ))}
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <section className="py-12">
        <Reveal className="mx-auto flex max-w-3xl items-center gap-4 px-4 text-black/55 sm:px-6">
          <CalendarDays className="size-5 shrink-0 text-brand-accent" aria-hidden="true" />
          <p className="text-sm leading-relaxed">
            More AI-assisted workflows are coming as they become ready for real sales teams.
          </p>
          <ListTodo className="size-5 shrink-0 text-brand-green" aria-hidden="true" />
        </Reveal>
      </section>

      <CtaBand
        title="Spend less time navigating your CRM."
        description="Ask, find and complete supported sales work from InSyte AI OS."
      />
    </>
  )
}
