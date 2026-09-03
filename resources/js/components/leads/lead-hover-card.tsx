import * as React from "react"

import {
  HoverCard,
  HoverCardContent,
  HoverCardTrigger,
} from "@/components/ui/hover-card"
import { cn } from "@/lib/utils"

export type LeadPreview = {
  id: number
  name: string
  phone: string | null
  email: string | null
  status: string
  source: string | null
  budget: string | null
  location: string | null
  assignedTo: string | null
}

type LeadHoverCardProps = {
  lead: LeadPreview
  className?: string
}

function openLead(id: number): void {
  window.dispatchEvent(new CustomEvent("open-lead", { detail: id }))
}

function prefetchLead(id: number): void {
  window.dispatchEvent(new CustomEvent("prefetch-lead", { detail: id }))
}

function PreviewField({
  label,
  value,
}: {
  label: string
  value: string | null
}) {
  return (
    <div>
      <dt className="text-xs text-slate-500">{label}</dt>
      <dd className="text-sm font-medium text-black">{value ?? "—"}</dd>
    </div>
  )
}

export function LeadHoverCard({ lead, className }: LeadHoverCardProps) {
  const metaLine = [lead.phone, lead.source].filter(Boolean).join(" · ") || "—"

  return (
    <HoverCard
      onOpenChange={(open) => {
        if (open) {
          prefetchLead(lead.id)
        }
      }}
    >
      <HoverCardTrigger
        delay={200}
        closeDelay={100}
        render={
          <button
            type="button"
            className={cn(
              "block min-w-0 max-w-[12rem] py-0.5 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy/30",
              className,
            )}
            onPointerDown={() => prefetchLead(lead.id)}
            onClick={() => openLead(lead.id)}
          />
        }
      >
        <span className="block truncate text-sm font-semibold leading-snug text-black hover:underline">
          {lead.name}
        </span>
        <span className="block truncate text-xs leading-snug text-slate-500">
          {metaLine}
        </span>
      </HoverCardTrigger>
      <HoverCardContent
        side="right"
        align="start"
        className="w-72 space-y-3 border-slate-200 p-4"
      >
        <div className="space-y-1">
          <p className="text-sm font-semibold text-black">{lead.name}</p>
          <p className="text-xs text-slate-500">{lead.status}</p>
        </div>

        <dl className="grid grid-cols-2 gap-3">
          <PreviewField label="Phone" value={lead.phone} />
          <PreviewField label="Source" value={lead.source} />
          <PreviewField label="Budget" value={lead.budget} />
          <PreviewField label="Location" value={lead.location} />
          <PreviewField label="Assigned To" value={lead.assignedTo} />
          <PreviewField label="Email" value={lead.email} />
        </dl>

        <p className="text-xs text-slate-500">Click to open lead details</p>
      </HoverCardContent>
    </HoverCard>
  )
}
