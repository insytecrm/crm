import { createRoot } from "react-dom/client"

import { MobileNavDrawer } from "@/components/drawers/mobile-nav-drawer"
import { TeamInboxDrawer } from "@/components/drawers/team-inbox-drawer"

const mobileNavRoot = document.getElementById("mobile-nav-drawer-root")

if (mobileNavRoot) {
  createRoot(mobileNavRoot).render(<MobileNavDrawer />)
}

const teamInboxRoot = document.getElementById("team-inbox-drawer-root")

if (teamInboxRoot) {
  createRoot(teamInboxRoot).render(<TeamInboxDrawer />)
}
