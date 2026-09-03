import { createRoot, type Root } from "react-dom/client"

import { CrmDateTimePicker } from "@/components/ui/crm-datetime-picker"

type PickerMode = "date" | "datetime"

const roots = new WeakMap<HTMLElement, Root>()

function parseMode(value: string | undefined): PickerMode {
  return value === "date" ? "date" : "datetime"
}

function mountPicker(root: HTMLElement): boolean {
  if (roots.has(root)) {
    return true
  }

  const container = root.closest<HTMLElement>(".crm-datetime-picker")
  const hiddenInput = container?.querySelector<HTMLInputElement>(
    "[data-crm-datetime-picker-input]",
  )

  if (! hiddenInput) {
    return false
  }

  const mode = parseMode(root.dataset.mode)
  const disabled = root.dataset.disabled === "true"
  const required = root.dataset.required === "true"

  const reactRoot = createRoot(root)
  roots.set(root, reactRoot)

  reactRoot.render(
    <CrmDateTimePicker
      mode={mode}
      value={hiddenInput.value}
      disabled={disabled}
      required={required}
      onValueChange={(nextValue) => {
        hiddenInput.value = nextValue
        hiddenInput.dispatchEvent(new Event("input", { bubbles: true }))
        hiddenInput.dispatchEvent(new Event("change", { bubbles: true }))
      }}
    />,
  )

  return true
}

export function mountDateTimePickers(scope: ParentNode = document): void {
  scope
    .querySelectorAll<HTMLElement>("[data-crm-datetime-picker-root]:not([data-mounted])")
    .forEach((root) => {
      if (mountPicker(root)) {
        root.dataset.mounted = "true"
      }
    })
}

function observePickerMounts(): void {
  const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      mutation.addedNodes.forEach((node) => {
        if (!(node instanceof HTMLElement)) {
          return
        }

        if (node.matches("[data-crm-datetime-picker-root]")) {
          if (mountPicker(node)) {
            node.dataset.mounted = "true"
          }

          return
        }

        mountDateTimePickers(node)
      })
    }
  })

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  })
}

function remountVisiblePickers(): void {
  window.requestAnimationFrame(() => {
    mountDateTimePickers()
  })
}

export function initDateTimePickers(): void {
  mountDateTimePickers()
  observePickerMounts()

  window.addEventListener("open-modal", remountVisiblePickers)
  window.addEventListener("open-drawer", remountVisiblePickers)
}
