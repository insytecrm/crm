import { useEffect, useState } from "react"
import { DatePicker, DateTimePicker } from "@atlaskit/datetime-picker"
import type { CSSObjectWithLabel } from "@atlaskit/select/types"

type PickerMode = "date" | "datetime"

type CrmDateTimePickerProps = {
  mode: PickerMode
  value: string
  disabled?: boolean
  required?: boolean
  onValueChange: (value: string) => void
}

const pickerMenuZIndex = 10000

const sharedSelectProps = {
  spacing: "compact" as const,
  menuPortalTarget: typeof document !== "undefined" ? document.body : undefined,
  menuPosition: "fixed" as const,
  menuPlacement: "auto" as const,
  styles: {
    control: (base: CSSObjectWithLabel) => ({
      ...base,
      minHeight: "2.5rem",
      height: "2.5rem",
      borderRadius: "0.5rem",
      borderColor: "#e2e8f0",
      backgroundColor: "#fff",
      boxShadow: "0 1px 2px 0 rgb(15 23 42 / 0.05)",
      fontSize: "0.875rem",
      lineHeight: "1.25rem",
      color: "#000000",
      cursor: "pointer",
      "&:hover": {
        borderColor: "#cbd5e1",
        backgroundColor: "#fff",
      },
    }),
    valueContainer: (base: CSSObjectWithLabel) => ({
      ...base,
      padding: "0 0.75rem",
      height: "2.5rem",
    }),
    input: (base: CSSObjectWithLabel) => ({
      ...base,
      margin: 0,
      padding: 0,
      color: "#000000",
    }),
    placeholder: (base: CSSObjectWithLabel) => ({
      ...base,
      color: "#94a3b8",
    }),
    indicatorsContainer: (base: CSSObjectWithLabel) => ({
      ...base,
      height: "2.5rem",
    }),
    dropdownIndicator: (base: CSSObjectWithLabel) => ({
      ...base,
      padding: "0 0.5rem",
      color: "#94a3b8",
    }),
    clearIndicator: (base: CSSObjectWithLabel) => ({
      ...base,
      padding: "0 0.25rem",
      color: "#94a3b8",
    }),
    menu: (base: CSSObjectWithLabel) => ({
      ...base,
      borderRadius: "0.5rem",
      overflow: "hidden",
      zIndex: pickerMenuZIndex,
    }),
    menuPortal: (base: CSSObjectWithLabel) => ({
      ...base,
      zIndex: pickerMenuZIndex,
    }),
  },
}

function toPickerValue(value: string, mode: PickerMode): string {
  if (value === "") {
    return ""
  }

  if (mode === "date") {
    return value.slice(0, 10)
  }

  if (value.includes("T")) {
    return value.slice(0, 16)
  }

  return value
}

function toFormValue(value: string, mode: PickerMode): string {
  if (value === "") {
    return ""
  }

  if (mode === "date") {
    return value.slice(0, 10)
  }

  return value.slice(0, 16)
}

export function CrmDateTimePicker({
  mode,
  value,
  disabled = false,
  required = false,
  onValueChange,
}: CrmDateTimePickerProps) {
  const [pickerValue, setPickerValue] = useState(() => toPickerValue(value, mode))

  useEffect(() => {
    setPickerValue(toPickerValue(value, mode))
  }, [mode, value])

  const handleChange = (nextValue: string) => {
    setPickerValue(nextValue)
    onValueChange(toFormValue(nextValue, mode))
  }

  if (mode === "date") {
    return (
      <DatePicker
        value={pickerValue}
        isDisabled={disabled}
        isRequired={required}
        spacing="compact"
        dateFormat="YYYY-MM-DD"
        onChange={handleChange}
        selectProps={sharedSelectProps}
      />
    )
  }

  return (
    <DateTimePicker
      value={pickerValue}
      isDisabled={disabled}
      isRequired={required}
      spacing="compact"
      onChange={handleChange}
      datePickerProps={{
        selectProps: sharedSelectProps,
      }}
      timePickerProps={{
        selectProps: sharedSelectProps,
      }}
    />
  )
}
