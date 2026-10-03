import { createRoot } from "react-dom/client"

import {
  ReportDonutChart,
  type ReportDonutSegment,
} from "@/components/reports/report-donut-chart"

function parseSegments(element: HTMLElement): ReportDonutSegment[] | null {
  const raw = element.dataset.segments

  if (! raw) {
    return null
  }

  try {
    const parsed = JSON.parse(raw) as ReportDonutSegment[]

    if (! Array.isArray(parsed) || parsed.length === 0) {
      return null
    }

    return parsed
  } catch {
    return null
  }
}

function mountChart(element: HTMLElement): void {
  if (element.dataset.mounted === "true") {
    return
  }

  const segments = parseSegments(element)

  if (! segments) {
    return
  }

  createRoot(element).render(<ReportDonutChart segments={segments} />)
  element.dataset.mounted = "true"
}

export function initReportDonutCharts(scope: ParentNode = document): void {
  scope
    .querySelectorAll<HTMLElement>("[data-report-donut]")
    .forEach((element) => mountChart(element))
}
