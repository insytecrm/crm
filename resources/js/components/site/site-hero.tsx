import { motion, useReducedMotion } from "framer-motion"
import {
  Building2,
  CalendarDays,
  Check,
  IndianRupee,
  Play,
  Users,
  Waypoints,
} from "lucide-react"

import { SiteHeroMock } from "@/components/site/site-hero-mock"
import { cn } from "@/lib/utils"

type SiteHeroProps = {
  dashboardSrc: string
}

const TRUST_ITEMS = [
  "7-day free trial",
  "No credit card required",
  "Set up in minutes",
] as const

const FEATURES = [
  { label: "Manage Leads", icon: Users, tone: "bg-brand-accent/15 text-brand-accent" },
  { label: "Track Follow-ups", icon: Waypoints, tone: "bg-brand-green/15 text-brand-green" },
  { label: "Schedule Site Visits", icon: Building2, tone: "bg-rose-100 text-rose-500" },
  { label: "Manage Bookings", icon: CalendarDays, tone: "bg-[#ffbd59]/20 text-[#d97706]" },
  { label: "Grow Your Commission", icon: IndianRupee, tone: "bg-violet-100 text-violet-600" },
] as const

export function SiteHero({ dashboardSrc }: SiteHeroProps) {
  const reduceMotion = useReducedMotion()

  const leftMotion = reduceMotion
    ? { initial: false as const, animate: { opacity: 1, x: 0 } }
    : {
        initial: { opacity: 0, x: -48 },
        animate: { opacity: 1, x: 0 },
      }

  const rightMotion = reduceMotion
    ? { initial: false as const, animate: { opacity: 1, x: 0 } }
    : {
        initial: { opacity: 0, x: 48 },
        animate: { opacity: 1, x: 0 },
      }

  return (
    <section className="relative overflow-hidden bg-white">
      <div
        aria-hidden="true"
        className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(56,182,255,0.12),_transparent_55%),radial-gradient(ellipse_at_bottom_left,_rgba(0,191,99,0.08),_transparent_50%)]"
      />

      <div className="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:gap-12 lg:px-8 lg:py-16">
        <motion.div
          className="relative z-10 max-w-xl"
          initial={leftMotion.initial}
          animate={leftMotion.animate}
          transition={{ duration: 0.65, ease: [0.22, 1, 0.36, 1] }}
        >
          <span className="inline-flex rounded-full bg-brand-accent/15 px-3 py-1 text-[11px] font-semibold tracking-wide text-brand-accent uppercase">
            AI-powered CRM for real estate channel partners
          </span>

          <h1 className="mt-5 text-4xl font-bold tracking-tight text-black sm:text-5xl lg:text-[3.25rem] lg:leading-[1.1]">
            Turn More Property Enquiries{" "}
            <span className="text-brand-accent">into Bookings.</span>
          </h1>

          <p className="mt-5 text-base leading-relaxed text-black/60 sm:text-lg">
            InSyte brings your leads, follow-ups, site visits, team, bookings and automation into one
            simple CRM — so you can stay organized, close more deals and grow your business.
          </p>

          <div className="mt-8 flex flex-wrap items-center gap-3">
            <motion.a
              href="/signup"
              className="inline-flex h-11 items-center rounded-lg bg-brand-accent px-5 text-sm font-semibold text-white shadow-sm"
              whileHover={reduceMotion ? undefined : { scale: 1.03 }}
              whileTap={reduceMotion ? undefined : { scale: 0.98 }}
            >
              Start Free →
            </motion.a>
            <motion.a
              href="/crm"
              className="inline-flex h-11 items-center gap-2 rounded-lg border border-black/10 bg-white px-5 text-sm font-semibold text-black shadow-sm"
              whileHover={reduceMotion ? undefined : { scale: 1.03 }}
              whileTap={reduceMotion ? undefined : { scale: 0.98 }}
            >
              <Play className="size-3.5 fill-current" aria-hidden="true" />
              Explore Product
            </motion.a>
            <a
              href="/demo"
              className="inline-flex h-11 items-center px-2 text-sm font-semibold text-black/60 transition-colors hover:text-black"
            >
              Book a Demo
            </a>
          </div>

          <ul className="mt-6 flex flex-wrap gap-x-5 gap-y-2">
            {TRUST_ITEMS.map((item) => (
              <li key={item} className="inline-flex items-center gap-1.5 text-sm text-black/70">
                <Check className="size-4 text-brand-green" aria-hidden="true" strokeWidth={2.5} />
                {item}
              </li>
            ))}
          </ul>

          <ul className="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-5">
            {FEATURES.map((feature) => {
              const Icon = feature.icon

              return (
                <li key={feature.label} className="flex flex-col items-start gap-2 sm:items-center sm:text-center">
                  <span
                    className={cn(
                      "inline-flex size-10 items-center justify-center rounded-xl",
                      feature.tone,
                    )}
                  >
                    <Icon className="size-5" aria-hidden="true" />
                  </span>
                  <span className="text-xs font-semibold leading-snug text-black">{feature.label}</span>
                </li>
              )
            })}
          </ul>
        </motion.div>

        <motion.div
          className="relative z-10"
          initial={rightMotion.initial}
          animate={rightMotion.animate}
          transition={{ duration: 0.7, delay: 0.12, ease: [0.22, 1, 0.36, 1] }}
        >
          <SiteHeroMock src={dashboardSrc} />
        </motion.div>
      </div>
    </section>
  )
}
