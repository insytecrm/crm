export function getCsrfToken(): string {
  return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ""
}

export function getLandingEndpoints(): { demoUrl: string; trialUrl: string } {
  const root = document.getElementById("landing-root")

  return {
    demoUrl: root?.dataset.demoUrl ?? "/landing/demo",
    trialUrl: root?.dataset.trialUrl ?? "/landing/trial",
  }
}

export async function postLandingForm(
  url: string,
  data: Record<string, string>,
): Promise<{ message: string }> {
  const response = await fetch(url, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": getCsrfToken(),
    },
    body: JSON.stringify(data),
  })

  const payload = (await response.json()) as { message?: string; errors?: Record<string, string[]> }

  if (!response.ok) {
    const firstError = payload.errors
      ? Object.values(payload.errors)[0]?.[0]
      : undefined

    throw new Error(firstError ?? "Something went wrong. Please try again.")
  }

  return { message: payload.message ?? "Submitted successfully." }
}

export function scrollToSection(id: string): void {
  const element = document.getElementById(id)

  if (element) {
    element.scrollIntoView({ behavior: "smooth", block: "start" })
  }
}

export function formatPrice(amount: number): string {
  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 0,
  }).format(amount)
}
