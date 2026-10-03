import { createRoot, type Root } from "react-dom/client"

import { CrmSelect, type CrmSelectOption } from "@/components/ui/crm-select"

const roots = new WeakMap<HTMLElement, Root>()

function parseOptions(raw: string | undefined): CrmSelectOption[] {
  if (! raw) {
    return []
  }

  try {
    const parsed = JSON.parse(raw) as unknown

    if (! Array.isArray(parsed)) {
      return []
    }

    return parsed
      .map((option) => {
        if (! option || typeof option !== "object") {
          return null
        }

        const value = "value" in option ? String((option as { value: unknown }).value ?? "") : ""
        const label = "label" in option ? String((option as { label: unknown }).label ?? value) : value

        return { value, label }
      })
      .filter((option): option is CrmSelectOption => option !== null)
  } catch {
    return []
  }
}

function setHiddenInput(
  input: HTMLInputElement,
  value: string,
  disabled: boolean,
  emitEvents = false,
): void {
  input.disabled = disabled

  if (input.value !== value) {
    input.value = value
  }

  if (emitEvents) {
    input.dispatchEvent(new Event("input", { bubbles: true }))
    input.dispatchEvent(new Event("change", { bubbles: true }))
  }
}

function renderSelect(root: HTMLElement): boolean {
  const container = root.closest<HTMLElement>(".crm-select")
  const hiddenInput = container?.querySelector<HTMLInputElement>("[data-crm-select-input]")

  if (! hiddenInput) {
    return false
  }

  const options = parseOptions(root.dataset.options)
  const disabled = root.dataset.disabled === "true"
  const required = root.dataset.required === "true"
  const placeholder = root.dataset.placeholder ?? "Select an option"
  const value = root.dataset.value ?? hiddenInput.value ?? ""
  const id = root.dataset.inputId || hiddenInput.id || undefined

  const existing = roots.get(root)
  const reactRoot = existing ?? createRoot(root)

  if (! existing) {
    roots.set(root, reactRoot)
  }

  reactRoot.render(
    <CrmSelect
      id={id}
      options={options}
      value={value}
      placeholder={placeholder}
      disabled={disabled}
      required={required}
      onValueChange={(nextValue) => {
        root.dataset.value = nextValue
        setHiddenInput(hiddenInput, nextValue, disabled, true)
      }}
    />,
  )

  setHiddenInput(hiddenInput, value, disabled, false)

  return true
}

export function mountCrmSelect(root: HTMLElement, force = false): boolean {
  if (! force && root.dataset.mounted === "true" && roots.has(root)) {
    renderSelect(root)

    return true
  }

  if (renderSelect(root)) {
    root.dataset.mounted = "true"

    return true
  }

  return false
}

export function mountCrmSelects(scope: ParentNode = document): void {
  scope
    .querySelectorAll<HTMLElement>("[data-crm-select-root]")
    .forEach((root) => {
      mountCrmSelect(root, root.dataset.mounted !== "true")
    })
}

function observeSelectMounts(): void {
  const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      if (mutation.type === "attributes" && mutation.target instanceof HTMLElement) {
        if (mutation.target.matches("[data-crm-select-root]")) {
          mountCrmSelect(mutation.target, true)
        }

        continue
      }

      mutation.addedNodes.forEach((node) => {
        if (! (node instanceof HTMLElement)) {
          return
        }

        if (node.matches("[data-crm-select-root]")) {
          mountCrmSelect(node, true)

          return
        }

        mountCrmSelects(node)
      })
    }
  })

  observer.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ["data-options", "data-value", "data-disabled", "data-required", "data-placeholder"],
  })
}

function remountVisibleSelects(): void {
  window.requestAnimationFrame(() => {
    mountCrmSelects()
  })
}

export function initCrmSelects(): void {
  mountCrmSelects()
  observeSelectMounts()

  window.addEventListener("open-modal", remountVisibleSelects)
  window.addEventListener("open-drawer", remountVisibleSelects)
}
