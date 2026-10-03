import { SiteButton } from "@/components/site/ui/site-button"
import { Reveal } from "@/components/site/ui/reveal"
import { cn } from "@/lib/utils"

type CtaBandProps = {
  title: string
  description?: string
  className?: string
}

export function CtaBand({ title, description, className }: CtaBandProps) {
  return (
    <section className={cn("bg-black py-16 sm:py-20", className)}>
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <Reveal className="mx-auto max-w-2xl text-center">
          <h2 className="text-3xl font-bold tracking-tight text-white sm:text-4xl">{title}</h2>
          {description ? (
            <p className="mt-4 text-base leading-relaxed text-white/65">{description}</p>
          ) : null}
          <div className="mt-8 flex flex-wrap justify-center gap-3">
            <SiteButton href="/signup">Start Free →</SiteButton>
            <SiteButton href="/demo" variant="secondary">
              Book a Demo
            </SiteButton>
          </div>
        </Reveal>
      </div>
    </section>
  )
}
