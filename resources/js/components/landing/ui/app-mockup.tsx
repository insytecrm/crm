import type { ReactNode } from "react"

import { cn } from "@/lib/utils"

const NAV_ITEMS = ["Dashboard", "Leads", "Activities", "Bookings", "Revenue", "Teams"]

type MockVariant =
  | "leads"
  | "activities"
  | "bookings"
  | "revenue"
  | "payouts"
  | "properties"
  | "teams"
  | "ai"
  | "microsite"

type AppMockupProps = {
  variant?: MockVariant
  className?: string
}

export function AppMockup({ variant = "leads", className }: AppMockupProps) {
  return (
    <div
      className={cn(
        "overflow-hidden rounded-2xl border border-slate-100/80 bg-white shadow-[0_20px_50px_-24px_rgba(26,57,66,0.35)]",
        className,
      )}
    >
      <div className="flex min-h-[320px] sm:min-h-[380px]">
        <aside className="hidden w-44 shrink-0 bg-navy p-3 sm:block">
          <div className="mb-4 flex items-center gap-2 px-2 py-1">
            <div className="h-7 w-7 rounded bg-brand-accent/20" />
            <span className="text-xs font-semibold text-white">InSyte CRM</span>
          </div>
          <nav className="space-y-1">
            {NAV_ITEMS.map((item) => (
              <div
                key={item}
                className={cn(
                  "rounded-md px-2 py-1.5 text-[11px] font-medium",
                  (variant === "leads" && item === "Leads") ||
                    (variant === "bookings" && item === "Bookings") ||
                    (variant === "revenue" && item === "Revenue") ||
                    (variant === "activities" && item === "Activities") ||
                    (variant === "teams" && item === "Teams")
                    ? "bg-brand-muted text-white"
                    : "text-white/70",
                )}
              >
                {item}
              </div>
            ))}
          </nav>
        </aside>

        <div className="min-w-0 flex-1 bg-slate-50 p-4 sm:p-5">
          {variant === "leads" && <LeadsMock />}
          {variant === "activities" && <ActivitiesMock />}
          {variant === "bookings" && <BookingsMock />}
          {variant === "revenue" && <RevenueMock />}
          {variant === "payouts" && <PayoutsMock />}
          {variant === "properties" && <PropertiesMock />}
          {variant === "teams" && <TeamsMock />}
          {variant === "ai" && <AiMock />}
          {variant === "microsite" && <MicrositeMock />}
        </div>
      </div>
    </div>
  )
}

function LeadsMock() {
  const rows = [
    ["Amit Sharma", "₹60–80L", "Hot", "Today 4 PM"],
    ["Neha Patil", "₹1–1.5Cr", "Warm", "Tomorrow"],
    ["Rahul Mehta", "₹40–60L", "New", "Pending"],
  ]

  return (
    <MockShell title="Leads" subtitle="Manage your buyer pipeline">
      <table className="w-full text-left text-[11px]">
        <thead className="text-slate-400">
          <tr>
            <th className="pb-2 font-medium">Name</th>
            <th className="pb-2 font-medium">Budget</th>
            <th className="pb-2 font-medium">Status</th>
            <th className="pb-2 font-medium">Follow-up</th>
          </tr>
        </thead>
        <tbody className="text-black">
          {rows.map((row) => (
            <tr key={row[0]} className="border-t border-slate-100">
              {row.map((cell) => (
                <td key={cell} className="py-2 pr-2">
                  {cell}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </MockShell>
  )
}

function ActivitiesMock() {
  return (
    <MockShell title="Follow-Ups" subtitle="Today's pending activities">
      <div className="space-y-2">
        {["Call Amit Sharma — Wakad 2BHK", "Site visit — Neha Patil", "WhatsApp follow-up — Rahul"].map(
          (item) => (
            <div key={item} className="rounded-lg border border-slate-100 bg-white px-3 py-2 text-[11px] text-black">
              {item}
            </div>
          ),
        )}
      </div>
    </MockShell>
  )
}

function BookingsMock() {
  return (
    <MockShell title="Bookings" subtitle="Track property bookings and conversions">
      <table className="w-full text-left text-[11px]">
        <thead className="text-slate-400">
          <tr>
            <th className="pb-2">Property</th>
            <th className="pb-2">Agreement Value</th>
            <th className="pb-2">Date</th>
          </tr>
        </thead>
        <tbody className="text-black">
          <tr className="border-t border-slate-100">
            <td className="py-2">Green Valley</td>
            <td className="py-2">₹82,00,000</td>
            <td className="py-2">12 Aug</td>
          </tr>
          <tr className="border-t border-slate-100">
            <td className="py-2">Skyline Heights</td>
            <td className="py-2">₹1,15,00,000</td>
            <td className="py-2">18 Aug</td>
          </tr>
        </tbody>
      </table>
    </MockShell>
  )
}

function RevenueMock() {
  const stats = [
    ["Total Revenue", "₹1.2 Cr"],
    ["Total Commission", "₹8.4 L"],
    ["Pending Commission", "₹1.6 L"],
  ]

  return (
    <MockShell title="Revenue" subtitle="Monitor sales performance and commissions">
      <div className="grid gap-2 sm:grid-cols-3">
        {stats.map(([label, value]) => (
          <div key={label} className="rounded-xl border border-emerald-100 bg-white p-3">
            <p className="text-[10px] text-slate-400">{label}</p>
            <p className="mt-1 text-sm font-bold text-black">{value}</p>
          </div>
        ))}
      </div>
    </MockShell>
  )
}

function PayoutsMock() {
  return (
    <MockShell title="Payouts" subtitle="Track received and pending commission">
      <div className="space-y-2">
        {[
          ["Green Valley — Aug", "₹1,20,000", "Paid"],
          ["Skyline Heights — Aug", "₹95,000", "Pending"],
        ].map(([title, amount, status]) => (
          <div
            key={title}
            className="flex items-center justify-between rounded-lg border border-slate-100 bg-white px-3 py-2 text-[11px]"
          >
            <span className="text-black">{title}</span>
            <span className="font-semibold text-black">{amount}</span>
            <span className="text-emerald-600">{status}</span>
          </div>
        ))}
      </div>
    </MockShell>
  )
}

function PropertiesMock() {
  return (
    <MockShell title="Properties" subtitle="Your active project inventory">
      <div className="grid gap-2 sm:grid-cols-2">
        {["Green Valley — Wakad", "Skyline Heights — Baner"].map((item) => (
          <div key={item} className="rounded-lg border border-slate-100 bg-white p-3 text-[11px] text-black">
            {item}
          </div>
        ))}
      </div>
    </MockShell>
  )
}

function TeamsMock() {
  return (
    <MockShell title="Teams" subtitle="Assign leads and track performance">
      <div className="space-y-2">
        {[
          ["Sales Team A", "18 leads"],
          ["Sales Team B", "12 leads"],
        ].map(([team, count]) => (
          <div
            key={team}
            className="flex items-center justify-between rounded-lg border border-slate-100 bg-white px-3 py-2 text-[11px] text-black"
          >
            <span>{team}</span>
            <span className="text-slate-400">{count}</span>
          </div>
        ))}
      </div>
    </MockShell>
  )
}

function AiMock() {
  return (
    <MockShell title="InSyte AI OS" subtitle="Next best actions for your team">
        <div className="space-y-2">
        <div className="rounded-xl border border-sky-100 bg-sky-50 p-3 text-[11px] text-black">
          Call <strong>Amit Sharma</strong> first — high score, budget matched to Green Valley.
        </div>
        <div className="rounded-xl border border-slate-100 bg-white p-3 text-[11px] text-black">
          Send WhatsApp follow-up to <strong>Neha Patil</strong> — site visit pending confirmation.
        </div>
      </div>
    </MockShell>
  )
}

function MicrositeMock() {
  return (
    <div className="mx-auto max-w-[220px] overflow-hidden rounded-[24px] border-4 border-navy bg-white shadow-lg">
      <div className="bg-navy px-3 py-2 text-center text-[10px] font-semibold text-white">
        Green Valley, Wakad
      </div>
      <div className="h-24 bg-gradient-to-br from-sky-100 to-emerald-100" />
      <div className="space-y-2 p-3 text-[10px] text-black">
        <p className="font-semibold">2 & 3 BHK from ₹68L</p>
        <div className="rounded bg-slate-100 px-2 py-1">Your name</div>
        <div className="rounded bg-slate-100 px-2 py-1">Phone number</div>
        <div className="rounded-lg bg-brand-green py-1.5 text-center font-semibold text-white">
          Enquire Now
        </div>
      </div>
    </div>
  )
}

function MockShell({
  title,
  subtitle,
  children,
}: {
  title: string
  subtitle: string
  children: ReactNode
}) {
  return (
    <div>
      <h3 className="text-sm font-bold text-black">{title}</h3>
      <p className="mt-0.5 text-[11px] text-slate-400">{subtitle}</p>
      <div className="mt-4 rounded-xl border border-slate-100 bg-white p-3">{children}</div>
    </div>
  )
}
