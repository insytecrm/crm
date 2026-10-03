import type { ReactNode } from "react"

import { cn } from "@/lib/utils"

type SiteCardProps = {
  children: ReactNode
  className?: string
  href?: string
}

export function SiteCard({ children, className, href }: SiteCardProps) {
  const classes = cn(
    "rounded-2xl border border-black/[0.06] bg-white p-6 shadow-[0_1px_0_rgba(0,0,0,0.03)] transition-shadow hover:shadow-md hover:shadow-black/5",
    className,
  )

  if (href) {
    return (
      <a href={href} className={cn(classes, "block")}>
        {children}
      </a>
    )
  }

  return <div className={classes}>{children}</div>
}
