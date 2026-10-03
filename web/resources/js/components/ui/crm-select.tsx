import { useEffect, useMemo, useState } from "react"

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { cn } from "@/lib/utils"

export type CrmSelectOption = {
  value: string
  label: string
}

type CrmSelectProps = {
  options: CrmSelectOption[]
  value?: string
  placeholder?: string
  disabled?: boolean
  required?: boolean
  id?: string
  className?: string
  onValueChange: (value: string) => void
}

export function CrmSelect({
  options,
  value = "",
  placeholder = "Select an option",
  disabled = false,
  required = false,
  id,
  className,
  onValueChange,
}: CrmSelectProps) {
  const [selected, setSelected] = useState(value)

  useEffect(() => {
    setSelected(value)
  }, [value])

  const items = useMemo(
    () => Object.fromEntries(options.map((option) => [option.value, option.label])),
    [options],
  )

  return (
    <Select
      value={selected === "" ? null : selected}
      onValueChange={(nextValue) => {
        const resolved = nextValue ?? ""
        setSelected(resolved)
        onValueChange(resolved)
      }}
      disabled={disabled}
      required={required}
      items={items}
      modal={false}
    >
      <SelectTrigger
        id={id}
        className={cn(
          "flex h-10 w-full min-w-0 items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm transition-colors hover:bg-slate-50 focus-visible:border-navy focus-visible:ring-2 focus-visible:ring-navy/20 data-placeholder:text-slate-400 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:opacity-50",
          className,
        )}
      >
        <SelectValue placeholder={placeholder} />
      </SelectTrigger>
      <SelectContent
        align="start"
        alignItemWithTrigger={false}
        sideOffset={4}
        className="max-h-72 rounded-2xl border border-slate-200 bg-white text-black shadow-lg shadow-slate-900/10 ring-0"
      >
        {options.map((option) => (
          <SelectItem
            key={option.value}
            value={option.value}
            className="rounded-lg px-2 py-2 text-sm text-black focus:bg-slate-100 focus:text-black"
          >
            {option.label}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  )
}
