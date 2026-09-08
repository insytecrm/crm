import { motion, useReducedMotion } from "framer-motion"
import { BarChart3 } from "lucide-react"

type SiteHeroMockProps = {
  src: string
}

export function SiteHeroMock({ src }: SiteHeroMockProps) {
  const reduceMotion = useReducedMotion()

  return (
    <div className="relative mx-auto w-full max-w-xl lg:max-w-none">
      <motion.div
        aria-hidden="true"
        className="pointer-events-none absolute -left-6 top-8 size-40 rounded-full bg-brand-accent/20 blur-3xl sm:size-56"
        animate={reduceMotion ? undefined : { scale: [1, 1.12, 1], opacity: [0.45, 0.7, 0.45] }}
        transition={{ duration: 6, repeat: Infinity, ease: "easeInOut" }}
      />
      <motion.div
        aria-hidden="true"
        className="pointer-events-none absolute -right-4 bottom-10 size-36 rounded-full bg-brand-green/15 blur-3xl sm:size-48"
        animate={reduceMotion ? undefined : { scale: [1, 1.15, 1], opacity: [0.35, 0.6, 0.35] }}
        transition={{ duration: 7, repeat: Infinity, ease: "easeInOut", delay: 0.6 }}
      />

      <motion.p
        className="absolute -top-2 left-2 z-20 max-w-[11rem] text-[11px] font-medium leading-snug text-black/55 sm:left-4 sm:max-w-[13rem] sm:text-xs"
        initial={reduceMotion ? false : { opacity: 0, y: 8 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.55, duration: 0.4 }}
      >
        Everything your sales team needs in one place.
      </motion.p>

      <motion.p
        className="absolute right-0 top-8 z-20 hidden max-w-[8.5rem] text-right text-[11px] font-medium leading-snug text-black/55 sm:block sm:text-xs"
        initial={reduceMotion ? false : { opacity: 0, x: 12 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ delay: 0.7, duration: 0.4 }}
      >
        On the go, always in sync.
      </motion.p>

      <div className="relative pt-8 sm:pt-10">
        <motion.div
          className="relative"
          initial={reduceMotion ? false : { scale: 0.96, opacity: 0.7 }}
          animate={{ scale: 1, opacity: 1 }}
          transition={{ duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
        >
          <motion.img
            src={src}
            alt="InSyte CRM dashboard on desktop and mobile"
            className="relative z-10 w-full select-none bg-transparent object-contain"
            draggable={false}
            animate={
              reduceMotion
                ? undefined
                : {
                    y: [0, -8, 0],
                  }
            }
            transition={{ duration: 5.5, repeat: Infinity, ease: "easeInOut" }}
          />

          {! reduceMotion ? (
            <motion.div
              aria-hidden="true"
              className="pointer-events-none absolute inset-y-[8%] left-0 z-20 w-1/4 bg-gradient-to-r from-transparent via-white/40 to-transparent mix-blend-soft-light"
              initial={{ x: "-120%", opacity: 0 }}
              animate={{ x: ["-120%", "280%"], opacity: [0, 0.9, 0] }}
              transition={{ duration: 2.4, delay: 0.9, ease: "easeInOut", repeat: Infinity, repeatDelay: 4 }}
            />
          ) : null}

          <motion.div
            aria-hidden="true"
            className="pointer-events-none absolute inset-x-[14%] top-[20%] z-20 h-[5%] rounded-full bg-brand-accent/30 blur-md"
            animate={reduceMotion ? undefined : { opacity: [0.15, 0.55, 0.15], scaleX: [0.92, 1.04, 0.92] }}
            transition={{ duration: 2.8, repeat: Infinity, ease: "easeInOut" }}
          />
          <motion.div
            aria-hidden="true"
            className="pointer-events-none absolute inset-x-[20%] top-[30%] z-20 h-[4%] rounded-full bg-brand-green/25 blur-md"
            animate={reduceMotion ? undefined : { opacity: [0.1, 0.45, 0.1], scaleX: [0.9, 1.06, 0.9] }}
            transition={{ duration: 3.2, repeat: Infinity, ease: "easeInOut", delay: 0.4 }}
          />
          <motion.div
            aria-hidden="true"
            className="pointer-events-none absolute inset-x-[24%] top-[38%] z-20 h-[4%] rounded-full bg-[#ffbd59]/30 blur-md"
            animate={reduceMotion ? undefined : { opacity: [0.1, 0.4, 0.1], scaleX: [0.88, 1.05, 0.88] }}
            transition={{ duration: 3.4, repeat: Infinity, ease: "easeInOut", delay: 0.8 }}
          />
        </motion.div>
      </div>

      <motion.div
        className="absolute -bottom-1 right-2 z-30 flex max-w-[11rem] items-center gap-2 rounded-xl border border-black/5 bg-white px-3 py-2.5 shadow-lg shadow-black/10 sm:right-4 sm:max-w-[13rem]"
        initial={reduceMotion ? false : { opacity: 0, y: 16, scale: 0.96 }}
        animate={
          reduceMotion
            ? { opacity: 1, y: 0, scale: 1 }
            : { opacity: 1, y: [0, -4, 0], scale: 1 }
        }
        transition={
          reduceMotion
            ? { delay: 0.8, duration: 0.35 }
            : {
                opacity: { delay: 0.85, duration: 0.4 },
                scale: { delay: 0.85, duration: 0.4 },
                y: { delay: 1.4, duration: 3.6, repeat: Infinity, ease: "easeInOut" },
              }
        }
      >
        <span className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-green/15 text-brand-green">
          <BarChart3 className="size-4" aria-hidden="true" />
        </span>
        <p className="text-xs font-semibold leading-snug text-black">
          Grow your commission with ease.
        </p>
      </motion.div>

      <motion.p
        className="absolute bottom-16 left-0 z-20 hidden text-[11px] font-medium text-black/50 sm:block"
        initial={reduceMotion ? false : { opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ delay: 1, duration: 0.4 }}
      >
        From leads to bookings
      </motion.p>
    </div>
  )
}
