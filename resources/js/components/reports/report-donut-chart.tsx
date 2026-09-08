import {
  Cell,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
} from "recharts"

export type ReportDonutSegment = {
  key: string
  label: string
  count: number
  percentage: number
  color: string
}

type ReportDonutChartProps = {
  segments: ReportDonutSegment[]
}

type TooltipPayloadItem = {
  name?: string | number
  value?: number | string
  payload?: ReportDonutSegment
}

function DonutTooltip({
  active,
  payload,
}: {
  active?: boolean
  payload?: TooltipPayloadItem[]
}) {
  if (! active || ! payload?.length) {
    return null
  }

  const item = payload[0]
  const segment = item.payload

  if (! segment) {
    return null
  }

  return (
    <div className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-md">
      <div className="flex items-center gap-2 font-medium text-slate-700">
        <span
          className="size-2.5 shrink-0 rounded-sm"
          style={{ backgroundColor: segment.color }}
        />
        {segment.label}
      </div>
      <p className="mt-1 tabular-nums text-slate-500">
        {segment.count.toLocaleString()} · {segment.percentage.toFixed(1)}%
      </p>
    </div>
  )
}

export function ReportDonutChart({ segments }: ReportDonutChartProps) {
  return (
    <div className="flex w-full flex-col items-center gap-5">
      <div className="aspect-square w-full max-w-[13.5rem]">
        <ResponsiveContainer
          width="100%"
          height="100%"
          initialDimension={{ width: 216, height: 216 }}
        >
          <PieChart>
            <Pie
              data={segments}
              dataKey="count"
              nameKey="label"
              cx="50%"
              cy="50%"
              innerRadius="62%"
              outerRadius="88%"
              paddingAngle={segments.length > 1 ? 5 : 0}
              cornerRadius={12}
              stroke="none"
              startAngle={90}
              endAngle={-270}
              isAnimationActive={false}
            >
              {segments.map((segment) => (
                <Cell key={segment.key} fill={segment.color} />
              ))}
            </Pie>
            <Tooltip content={<DonutTooltip />} />
          </PieChart>
        </ResponsiveContainer>
      </div>

      <div className="flex w-full flex-wrap items-center justify-center gap-x-5 gap-y-2.5">
        {segments.map((segment) => (
          <div
            key={segment.key}
            className="inline-flex max-w-full items-center gap-2"
            title={`${segment.label}: ${segment.count.toLocaleString()} (${segment.percentage.toFixed(1)}%)`}
          >
            <span
              className="size-2.5 shrink-0 rounded-sm"
              style={{ backgroundColor: segment.color }}
              aria-hidden="true"
            />
            <span className="truncate text-xs text-slate-500">
              {segment.label}
            </span>
          </div>
        ))}
      </div>
    </div>
  )
}
