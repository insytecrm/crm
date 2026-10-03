import type { ReactNode } from "react"

import { Reveal } from "@/components/site/ui/reveal"
import { cn } from "@/lib/utils"

type PageHeroProps = {
  eyebrow?: string
  title: ReactNode
  description: string
  actions?: ReactNode
  className?: string
  dark?: boolean
}

export function PageHero({
  eyebrow,
  title,
  description,
  actions,
  className,
  dark = false,
}: PageHeroProps) {
  return (
    <section
      className={cn(
        "relative overflow-hidden",
        dark ? "bg-black text-white" : "bg-white text-black",
        className,
      )}
    >
      <div
        aria-hidden="true"
        className={cn(
          "pointer-events-none absolute inset-0",
          dark
            ? "bg-[radial-gradient(ellipse_at_top,_rgba(56,182,255,0.18),_transparent_55%)]"
            : "bg-[radial-gradient(ellipse_at_top_right,_rgba(56,182,255,0.12),_transparent_55%),radial-gradient(ellipse_at_bottom_left,_rgba(0,191,99,0.08),_transparent_50%)]",
        )}
      />
      <div className="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <Reveal className="max-w-3xl">
          {eyebrow ? (
            <p
              className={cn(
                "mb-4 text-xs font-semibold uppercase tracking-[0.2em]",
                dark ? "text-brand-accent" : "text-brand-accent",
              )}
            >
              {eyebrow}
            </p>
          ) : null}
          <h1
            className={cn(
              "text-4xl font-bold tracking-tight sm:text-5xl lg:text-[3.25rem] lg:leading-[1.1]",
              dark ? "text-white" : "text-black",
            )}
          >
            {title}
          </h1>
          <p
            className={cn(
              "mt-5 max-w-2xl text-base leading-relaxed sm:text-lg",
              dark ? "text-white/70" : "text-black/60",
            )}
          >
            {description}
          </p>
          {actions ? <div className="mt-8 flex flex-wrap gap-3">{actions}</div> : null}
        </Reveal>
      </div>
    </section>
  )
}
