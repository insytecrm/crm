import { createRoot } from "react-dom/client"

import { SiteApp } from "@/components/site/site-app"

const welcomeRoot = document.getElementById("welcome-root")

if (welcomeRoot) {
  const logoLight = welcomeRoot.dataset.logoLight ?? "/images/2.png"
  const logoDark = welcomeRoot.dataset.logoDark ?? "/images/inSyte%20(2).png"
  const dashboardSrc = welcomeRoot.dataset.dashboardSrc ?? "/images/hero-dashboard.png"
  const page = welcomeRoot.dataset.page ?? "home"
  const slug = welcomeRoot.dataset.slug || null

  createRoot(welcomeRoot).render(
    <SiteApp
      page={page}
      slug={slug}
      logoLight={logoLight}
      logoDark={logoDark}
      dashboardSrc={dashboardSrc}
    />,
  )
}
