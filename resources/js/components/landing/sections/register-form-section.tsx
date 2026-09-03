import { useEffect, useState } from "react"

import type { BillingCycle, PlanId } from "../data/content"
import { LandingForm } from "../ui/landing-form"
import { SectionHeading } from "../ui/section-heading"

const TRUST = [
  "Free trial",
  "InSyte AI OS included",
  "Setup help",
  "Cancel anytime",
]

export function RegisterFormSection() {
  const [selectedPlan, setSelectedPlan] = useState<PlanId>("growth")
  const [billing, setBilling] = useState<BillingCycle>("monthly")

  useEffect(() => {
    const handler = (event: Event) => {
      const custom = event as CustomEvent<{ plan: PlanId; billing: BillingCycle }>
      setSelectedPlan(custom.detail.plan)
      setBilling(custom.detail.billing)
    }

    window.addEventListener("landing:select-plan", handler)

    return () => window.removeEventListener("landing:select-plan", handler)
  }, [])

  return (
    <section id="register" className="scroll-mt-20 bg-navy py-20 sm:py-24">
      <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          dark
          align="center"
          title="Start your free trial"
          description="Create your account and start managing leads, follow-ups, and commissions today. No credit card required."
        />

        <div className="mt-10 rounded-2xl bg-white p-6 sm:p-8">
          <LandingForm
            key={`${selectedPlan}-${billing}`}
            type="trial"
            defaultPlan={selectedPlan}
            defaultBilling={billing}
          />
        </div>

        <div className="mt-8 flex flex-wrap justify-center gap-3">
          {TRUST.map((item) => (
            <span
              key={item}
              className="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-white/60"
            >
              {item}
            </span>
          ))}
        </div>
      </div>
    </section>
  )
}
