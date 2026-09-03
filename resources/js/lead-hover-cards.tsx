import { createRoot } from "react-dom/client"

import {
  LeadHoverCard,
  type LeadPreview,
} from "@/components/leads/lead-hover-card"

function parseLeadPreview(element: HTMLElement): LeadPreview | null {
  const raw = element.dataset.lead

  if (! raw) {
    return null
  }

  try {
    return JSON.parse(raw) as LeadPreview
  } catch {
    return null
  }
}

document.querySelectorAll<HTMLElement>("[data-lead-hover-card]").forEach((element) => {
  const lead = parseLeadPreview(element)

  if (! lead) {
    return
  }

  createRoot(element).render(
    <LeadHoverCard lead={lead} className={element.dataset.className} />,
  )
})
