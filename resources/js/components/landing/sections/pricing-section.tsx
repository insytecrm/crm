import { useState } from "react"

import { PLAN_FEATURES, PRICING, type BillingCycle, type PlanId } from "../data/content"
import { formatPrice, scrollToSection } from "../lib/landing-api"
import { cn } from "@/lib/utils"

import { LandingButton } from "../ui/landing-button"
import { SectionHeading } from "../ui/section-heading"

export function PricingSection() {
  const [billing, setBilling] = useState<BillingCycle>("monthly")

  const selectPlan = (plan: PlanId) => {
    window.dispatchEvent(new CustomEvent("landing:select-plan", { detail: { plan, billing } }))
    scrollToSection("register")
  }

  return (
    <section id="pricing" className="scroll-mt-20 bg-white py-20 sm:py-24">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          align="center"
          eyebrow="Pricing"
          title="Simple plans. InSyte AI OS included in every plan."
          description="No hidden charges. No extra fee for AI. Pick a plan and start closing more deals."
        />

        <div className="mt-8 flex justify-center">
          <div className="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-1">
            {(["monthly", "yearly"] as const).map((cycle) => (
              <button
                key={cycle}
                type="button"
                className={cn(
                  "rounded-md px-4 py-2 text-sm font-semibold",
                  billing === cycle ? "bg-navy text-white" : "text-slate-500 hover:text-black",
                )}
                onClick={() => setBilling(cycle)}
              >
                {cycle === "monthly" ? "Monthly" : "Yearly · 2 months free"}
              </button>
            ))}
          </div>
        </div>

        <div className="mt-12 grid gap-6 lg:grid-cols-3">
          {(Object.keys(PRICING) as PlanId[]).map((planId) => {
            const plan = PRICING[planId]
            const price = billing === "monthly" ? plan.monthly : plan.yearly
            const suffix = billing === "monthly" ? "/month" : "/year"
            const isPopular = planId === "growth"

            return (
              <article
                key={planId}
                className={cn(
                  "flex h-full flex-col rounded-2xl border p-6",
                  isPopular
                    ? "border-brand-accent bg-slate-50"
                    : "border-slate-100 bg-white",
                )}
              >
                {isPopular ? (
                  <span className="mb-3 inline-flex w-fit rounded-full bg-brand-accent px-3 py-1 text-xs font-semibold text-white">
                    Most Popular
                  </span>
                ) : (
                  <span className="mb-3 block h-6" />
                )}
                <h3 className="text-2xl font-semibold text-black">{plan.name}</h3>
                <p className="mt-4 text-4xl font-bold text-black">
                  {formatPrice(price)}
                  <span className="text-sm font-medium text-slate-400">{suffix}</span>
                </p>
                {billing === "yearly" ? (
                  <p className="mt-2 text-xs font-semibold text-emerald-600">
                    Buy 10 months, get 12
                  </p>
                ) : (
                  <p className="mt-2 h-4" />
                )}
                <ul className="mt-8 flex-1 space-y-3">
                  {PLAN_FEATURES[planId].map((feature) => (
                    <li key={feature} className="flex gap-3 text-sm text-slate-500">
                      <span className="text-brand-green">✓</span>
                      <span>{feature}</span>
                    </li>
                  ))}
                </ul>
                <div className="mt-8">
                  <LandingButton
                    className="w-full"
                    variant={isPopular ? "primary" : "secondary"}
                    onClick={() => selectPlan(planId)}
                  >
                    Start Free Trial
                  </LandingButton>
                </div>
              </article>
            )
          })}
        </div>

        <p className="mt-8 text-center text-sm text-slate-400">
          All plans include InSyte AI OS. Cancel anytime. GST applicable.
        </p>
      </div>
    </section>
  )
}
