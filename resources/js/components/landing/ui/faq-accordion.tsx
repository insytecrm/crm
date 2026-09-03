import { useState } from "react"

import { cn } from "@/lib/utils"

import { FAQ_ITEMS } from "../data/content"

export function FaqAccordion() {
  const [openIndex, setOpenIndex] = useState<number | null>(0)

  return (
    <div className="space-y-3">
      {FAQ_ITEMS.map((item, index) => {
        const isOpen = openIndex === index

        return (
          <div
            key={item.question}
            className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
          >
            <button
              type="button"
              className="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
              onClick={() => setOpenIndex(isOpen ? null : index)}
              aria-expanded={isOpen}
            >
              <span className="text-base font-semibold text-black">{item.question}</span>
              <span className="text-xl text-brand-accent">{isOpen ? "−" : "+"}</span>
            </button>
            <div
              className={cn(
                "grid transition-all duration-300",
                isOpen ? "grid-rows-[1fr] opacity-100" : "grid-rows-[0fr] opacity-0",
              )}
            >
              <div className="overflow-hidden">
                <p className="px-5 pb-4 text-sm leading-relaxed text-slate-500">{item.answer}</p>
              </div>
            </div>
          </div>
        )
      })}
    </div>
  )
}
