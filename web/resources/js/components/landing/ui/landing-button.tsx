import type { AnchorHTMLAttributes, ButtonHTMLAttributes, MouseEventHandler } from "react"

import { cn } from "@/lib/utils"

type LandingButtonProps = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: "primary" | "secondary" | "outline"
  href?: string
}

export function LandingButton({
  className,
  variant = "primary",
  href,
  children,
  onClick,
  type = "button",
  ...props
}: LandingButtonProps) {
  const classes = cn(
    "inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent focus-visible:ring-offset-2",
    variant === "primary" && "bg-brand-green text-white hover:bg-brand-green/90",
    variant === "secondary" && "bg-brand-accent text-white hover:bg-brand-accent/90",
    variant === "outline" && "border border-white/20 bg-transparent text-white hover:bg-white/10",
    className,
  )

  if (href) {
    return (
      <a
        href={href}
        className={classes}
        onClick={onClick as MouseEventHandler<HTMLAnchorElement> | undefined}
      >
        {children}
      </a>
    )
  }

  return (
    <button type={type} className={classes} onClick={onClick} {...props}>
      {children}
    </button>
  )
}
