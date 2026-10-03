import { motion, useReducedMotion, type HTMLMotionProps } from "framer-motion"
import type { ReactNode } from "react"

import { cn } from "@/lib/utils"

type SiteButtonProps = {
  href?: string
  variant?: "primary" | "secondary" | "ghost" | "dark"
  children: ReactNode
  className?: string
} & Omit<HTMLMotionProps<"a">, "href" | "children" | "className">

const variants = {
  primary: "bg-brand-accent text-white shadow-sm hover:bg-brand-accent/90",
  secondary: "border border-black/10 bg-white text-black shadow-sm hover:bg-black/[0.02]",
  ghost: "text-black/70 hover:bg-black/[0.03] hover:text-black",
  dark: "bg-black text-white hover:bg-black/90",
} as const

export function SiteButton({
  href = "#",
  variant = "primary",
  children,
  className,
  ...props
}: SiteButtonProps) {
  const reduceMotion = useReducedMotion()

  return (
    <motion.a
      href={href}
      className={cn(
        "inline-flex h-11 items-center justify-center rounded-xl px-5 text-sm font-semibold transition-colors",
        variants[variant],
        className,
      )}
      whileHover={reduceMotion ? undefined : { scale: 1.02 }}
      whileTap={reduceMotion ? undefined : { scale: 0.98 }}
      {...props}
    >
      {children}
    </motion.a>
  )
}
