import { useState } from "react"
import { CheckCircle2 } from "lucide-react"

import { getLandingEndpoints, postLandingForm } from "@/components/landing/lib/landing-api"
import { cn } from "@/lib/utils"

const TEAM_SIZES = [
  ["just_me", "Just me"],
  ["2_5", "2–5"],
  ["6_15", "6–15"],
  ["16_plus", "16+"],
] as const

const PLANS = [
  ["starter", "Starter"],
  ["growth", "Growth"],
  ["pro", "Pro"],
  ["not_sure", "Not sure"],
] as const

type SiteLeadFormProps = {
  type: "demo" | "trial"
}

export function SiteLeadForm({ type }: SiteLeadFormProps) {
  const initialForm = {
    full_name: "",
    company: "",
    phone: "",
    email: "",
    team_size: "just_me",
    plan: type === "trial" ? "growth" : "not_sure",
    billing_cycle: "monthly",
  }
  const [form, setForm] = useState(initialForm)
  const [status, setStatus] = useState<"idle" | "loading" | "success" | "error">("idle")
  const [message, setMessage] = useState("")

  const update = (field: keyof typeof form, value: string) => {
    setForm((current) => ({ ...current, [field]: value }))
  }

  const submit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setStatus("loading")
    setMessage("")

    try {
      const endpoints = getLandingEndpoints()
      const result = await postLandingForm(
        type === "demo" ? endpoints.demoUrl : endpoints.trialUrl,
        form,
      )
      setStatus("success")
      setMessage(result.message)
      setForm(initialForm)
    } catch (error) {
      setStatus("error")
      setMessage(error instanceof Error ? error.message : "Something went wrong. Please try again.")
    }
  }

  const inputClass =
    "mt-1.5 w-full rounded-xl border border-black/10 bg-white px-3.5 py-3 text-sm text-black outline-none ring-brand-accent transition focus:ring-2"

  return (
    <form onSubmit={submit} className="grid gap-4" noValidate={false}>
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField label="Full name" required>
          <input
            className={inputClass}
            required
            autoComplete="name"
            value={form.full_name}
            onChange={(event) => update("full_name", event.target.value)}
          />
        </FormField>
        <FormField label="Company / firm" required>
          <input
            className={inputClass}
            required
            autoComplete="organization"
            value={form.company}
            onChange={(event) => update("company", event.target.value)}
          />
        </FormField>
        <FormField label="Phone" required>
          <input
            className={inputClass}
            required
            type="tel"
            autoComplete="tel"
            value={form.phone}
            onChange={(event) => update("phone", event.target.value)}
          />
        </FormField>
        <FormField label="Email" required>
          <input
            className={inputClass}
            required
            type="email"
            autoComplete="email"
            value={form.email}
            onChange={(event) => update("email", event.target.value)}
          />
        </FormField>
        <FormField label="Team size" required>
          <select
            className={inputClass}
            required
            value={form.team_size}
            onChange={(event) => update("team_size", event.target.value)}
          >
            {TEAM_SIZES.map(([value, label]) => (
              <option key={value} value={value}>{label}</option>
            ))}
          </select>
        </FormField>
        <FormField label={type === "demo" ? "Plan (optional)" : "Plan"} required={type === "trial"}>
          <select
            className={inputClass}
            required={type === "trial"}
            value={form.plan}
            onChange={(event) => update("plan", event.target.value)}
          >
            {PLANS.map(([value, label]) => (
              <option key={value} value={value}>{label}</option>
            ))}
          </select>
        </FormField>
      </div>

      {type === "trial" ? (
        <FormField label="Billing cycle" required>
          <select
            className={inputClass}
            required
            value={form.billing_cycle}
            onChange={(event) => update("billing_cycle", event.target.value)}
          >
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>
        </FormField>
      ) : null}

      <button
        type="submit"
        disabled={status === "loading"}
        className="mt-2 inline-flex h-12 items-center justify-center rounded-xl bg-brand-accent px-6 text-sm font-semibold text-white transition hover:bg-brand-accent/90 disabled:cursor-wait disabled:opacity-65 sm:w-fit"
      >
        {status === "loading"
          ? "Submitting…"
          : type === "demo"
            ? "Request My Demo"
            : "Start Free"}
      </button>

      {message ? (
        <p
          role="status"
          className={cn(
            "flex items-center gap-2 text-sm font-medium",
            status === "success" ? "text-emerald-700" : "text-red-600",
          )}
        >
          {status === "success" ? <CheckCircle2 className="size-4" aria-hidden="true" /> : null}
          {message}
        </p>
      ) : null}
    </form>
  )
}

function FormField({
  label,
  required = false,
  children,
}: {
  label: string
  required?: boolean
  children: React.ReactNode
}) {
  return (
    <label className="block text-sm font-medium text-black/75">
      {label}{required ? " *" : ""}
      {children}
    </label>
  )
}
