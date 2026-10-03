import { useState } from "react"

import { cn } from "@/lib/utils"

import { getLandingEndpoints, postLandingForm } from "../lib/landing-api"
import { LandingButton } from "./landing-button"

const TEAM_SIZE_OPTIONS = [
  { value: "just_me", label: "Just me" },
  { value: "2_5", label: "2–5" },
  { value: "6_15", label: "6–15" },
  { value: "16_plus", label: "16+" },
] as const

const PLAN_OPTIONS = [
  { value: "starter", label: "Starter ₹999" },
  { value: "growth", label: "Growth ₹1,999" },
  { value: "pro", label: "Pro ₹4,999" },
] as const

type LandingFormProps = {
  type: "demo" | "trial"
  defaultPlan?: string
  defaultBilling?: "monthly" | "yearly"
}

export function LandingForm({ type, defaultPlan, defaultBilling = "monthly" }: LandingFormProps) {
  const [form, setForm] = useState({
    full_name: "",
    company: "",
    phone: "",
    email: "",
    team_size: "just_me",
    plan: defaultPlan ?? (type === "trial" ? "growth" : "not_sure"),
    billing_cycle: defaultBilling,
  })
  const [status, setStatus] = useState<"idle" | "loading" | "success" | "error">("idle")
  const [message, setMessage] = useState("")

  const update = (field: string, value: string) => {
    setForm((current) => ({ ...current, [field]: value }))
  }

  const onSubmit = async (event: React.FormEvent) => {
    event.preventDefault()
    setStatus("loading")
    setMessage("")

    try {
      const { demoUrl, trialUrl } = getLandingEndpoints()
      const result = await postLandingForm(type === "demo" ? demoUrl : trialUrl, form)
      setStatus("success")
      setMessage(result.message)
      setForm({
        full_name: "",
        company: "",
        phone: "",
        email: "",
        team_size: "just_me",
        plan: defaultPlan ?? (type === "trial" ? "growth" : "not_sure"),
        billing_cycle: defaultBilling,
      })
    } catch (error) {
      setStatus("error")
      setMessage(error instanceof Error ? error.message : "Something went wrong.")
    }
  }

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      <div className="grid gap-4 sm:grid-cols-2">
        <label className="block text-sm">
          <span className="mb-1.5 block font-medium text-black">Full name *</span>
          <input
            required
            value={form.full_name}
            onChange={(e) => update("full_name", e.target.value)}
            className="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-black outline-none ring-brand-accent focus:ring-2"
          />
        </label>
        <label className="block text-sm">
          <span className="mb-1.5 block font-medium text-black">Company / firm name *</span>
          <input
            required
            value={form.company}
            onChange={(e) => update("company", e.target.value)}
            className="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-black outline-none ring-brand-accent focus:ring-2"
          />
        </label>
        <label className="block text-sm">
          <span className="mb-1.5 block font-medium text-black">Phone number *</span>
          <input
            required
            type="tel"
            value={form.phone}
            onChange={(e) => update("phone", e.target.value)}
            className="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-black outline-none ring-brand-accent focus:ring-2"
          />
        </label>
        <label className="block text-sm">
          <span className="mb-1.5 block font-medium text-black">Email *</span>
          <input
            required
            type="email"
            value={form.email}
            onChange={(e) => update("email", e.target.value)}
            className="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-black outline-none ring-brand-accent focus:ring-2"
          />
        </label>
        <label className="block text-sm">
          <span className="mb-1.5 block font-medium text-black">Team size *</span>
          <select
            required
            value={form.team_size}
            onChange={(e) => update("team_size", e.target.value)}
            className="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-black outline-none ring-brand-accent focus:ring-2"
          >
            {TEAM_SIZE_OPTIONS.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        </label>
        <label className="block text-sm">
          <span className="mb-1.5 block font-medium text-black">
            {type === "demo" ? "Interested plan" : "Choose plan *"}
          </span>
          <select
            required={type === "trial"}
            value={form.plan}
            onChange={(e) => update("plan", e.target.value)}
            className="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-black outline-none ring-brand-accent focus:ring-2"
          >
            {type === "demo" ? <option value="not_sure">Not sure</option> : null}
            {PLAN_OPTIONS.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        </label>
      </div>

      {type === "trial" ? (
        <label className="block text-sm">
          <span className="mb-1.5 block font-medium text-black">Billing *</span>
          <select
            required
            value={form.billing_cycle}
            onChange={(e) => update("billing_cycle", e.target.value)}
            className="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-black outline-none ring-brand-accent focus:ring-2"
          >
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly — Buy 10 months, get 12</option>
          </select>
        </label>
      ) : null}

      <LandingButton
        type="submit"
        disabled={status === "loading"}
        className={cn("w-full sm:w-auto", status === "loading" && "opacity-70")}
      >
        {status === "loading"
          ? "Submitting..."
          : type === "demo"
            ? "Book My Free Demo"
            : "Start Free Trial"}
      </LandingButton>

      {message ? (
        <p
          className={cn(
            "text-sm font-medium",
            status === "success" ? "text-emerald-600" : "text-red-600",
          )}
        >
          {message}
        </p>
      ) : null}
    </form>
  )
}
