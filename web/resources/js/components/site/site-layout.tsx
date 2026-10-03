import type { ReactNode } from "react"

import { SiteFooter } from "@/components/site/site-footer"
import { SiteNav } from "@/components/site/site-nav"
import { useSiteSmoothScroll } from "@/components/site/lib/use-site-smooth-scroll"

type SiteLayoutProps = {
  children: ReactNode
  logoLight: string
  logoDark: string
}

export function SiteLayout({ children, logoLight, logoDark }: SiteLayoutProps) {
  useSiteSmoothScroll()

  return (
    <div className="min-h-screen bg-white text-black">
      <SiteNav logoLight={logoLight} logoDark={logoDark} />
      <main>{children}</main>
      <SiteFooter logoSrc={logoDark} />
    </div>
  )
}
