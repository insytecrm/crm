import { createRoot } from "react-dom/client"

import { LandingPage } from "@/components/landing/landing-page"

const landingRoot = document.getElementById("landing-root")

if (landingRoot) {
  createRoot(landingRoot).render(<LandingPage />)
}
