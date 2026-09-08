import { useId, useState } from "react"
import { AnimatePresence, motion, useReducedMotion } from "framer-motion"
import { ChevronDown } from "lucide-react"

import { FAQ_GROUPS } from "@/components/site/content/faqs"
import { CtaBand } from "@/components/site/ui/cta-band"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { cn } from "@/lib/utils"

export function FaqsPage() {
  const [openItem, setOpenItem] = useState<string | null>("0-0")

  return (
    <>
      <PageHero
        eyebrow="FAQs"
        title="Answers before you get started."
        description="Learn how InSyte approaches CRM, automation, AI, plans and support."
      />
      <section className="bg-slate-50 py-16 sm:py-20">
        <div className="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:px-8">
          {FAQ_GROUPS.map((group, groupIndex) => (
            <Reveal key={group.title}>
              <div className="grid gap-5 lg:grid-cols-[220px_1fr]">
                <h2 className="text-xl font-bold text-black">{group.title}</h2>
                <div className="overflow-hidden rounded-2xl border border-black/[0.06] bg-white">
                  {group.items.map((item, itemIndex) => {
                    const key = `${groupIndex}-${itemIndex}`

                    return (
                      <FaqRow
                        key={item.q}
                        question={item.q}
                        answer={item.a}
                        open={openItem === key}
                        onToggle={() => setOpenItem((current) => current === key ? null : key)}
                      />
                    )
                  })}
                </div>
              </div>
            </Reveal>
          ))}
        </div>
      </section>
      <CtaBand title="Still have a question?" description="Book a demo and talk through your team’s needs with us." />
    </>
  )
}

function FaqRow({
  question,
  answer,
  open,
  onToggle,
}: {
  question: string
  answer: string
  open: boolean
  onToggle: () => void
}) {
  const id = useId()
  const reduceMotion = useReducedMotion()

  return (
    <div className="border-b border-black/5 last:border-b-0">
      <button
        type="button"
        className="flex w-full items-center justify-between gap-5 px-5 py-5 text-left font-semibold text-black sm:px-6"
        aria-expanded={open}
        aria-controls={id}
        onClick={onToggle}
      >
        {question}
        <ChevronDown className={cn("size-5 shrink-0 text-black/40 transition-transform", open && "rotate-180")} aria-hidden="true" />
      </button>
      <AnimatePresence initial={false}>
        {open ? (
          <motion.div
            id={id}
            initial={reduceMotion ? false : { height: 0, opacity: 0 }}
            animate={{ height: "auto", opacity: 1 }}
            exit={reduceMotion ? undefined : { height: 0, opacity: 0 }}
            transition={{ duration: 0.22 }}
            className="overflow-hidden"
          >
            <p className="px-5 pb-5 text-sm leading-relaxed text-black/60 sm:px-6">{answer}</p>
          </motion.div>
        ) : null}
      </AnimatePresence>
    </div>
  )
}
