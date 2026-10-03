import * as React from "react"

import {
  Drawer,
  DrawerContent,
} from "@/components/ui/drawer"

declare global {
  interface Window {
    Alpine?: {
      store: (name: string) => {
        open: (name: string) => void
        close: (name: string) => void
      }
    }
  }
}

export function MobileNavDrawer() {
  const [open, setOpen] = React.useState(false)

  React.useEffect(() => {
    const handleOpen = (event: Event) => {
      if ((event as CustomEvent<string>).detail === "mobile-nav") {
        setOpen(true)
      }
    }

    const handleClose = (event: Event) => {
      if ((event as CustomEvent<string>).detail === "mobile-nav") {
        setOpen(false)
      }
    }

    window.addEventListener("open-drawer", handleOpen)
    window.addEventListener("close-drawer", handleClose)

    return () => {
      window.removeEventListener("open-drawer", handleOpen)
      window.removeEventListener("close-drawer", handleClose)
    }
  }, [])

  React.useEffect(() => {
    const store = window.Alpine?.store("drawers")

    if (! store) {
      return
    }

    if (open) {
      store.open("mobile-nav")
    } else {
      store.close("mobile-nav")
    }
  }, [open])

  return (
    <Drawer open={open} onOpenChange={setOpen} swipeDirection="left">
      <DrawerContent
        overlayClassName="z-40 bg-navy/40 backdrop-blur-sm lg:hidden"
        className="pointer-events-none invisible h-0 w-0 overflow-hidden opacity-0"
        aria-hidden="true"
      />
    </Drawer>
  )
}
