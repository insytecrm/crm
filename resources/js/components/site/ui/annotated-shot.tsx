import { cn } from "@/lib/utils"

export type ShotMarker = {
  x: number
  y: number
  label: string
}

type AnnotatedShotProps = {
  src: string
  alt: string
  markers?: ShotMarker[]
  caption?: string
  className?: string
}

export function AnnotatedShot({ src, alt, markers = [], caption, className }: AnnotatedShotProps) {
  return (
    <figure className={cn("overflow-hidden rounded-2xl border border-black/10 bg-slate-50 shadow-sm", className)}>
      <div className="relative">
        <img src={src} alt={alt} className="block w-full" loading="lazy" />
        {markers.map((marker) => (
          <div
            key={`${marker.label}-${marker.x}-${marker.y}`}
            className="pointer-events-none absolute z-10"
            style={{ left: `${marker.x}%`, top: `${marker.y}%` }}
          >
            <span className="relative flex size-8 -translate-x-1/2 -translate-y-1/2 items-center justify-center">
              <span className="absolute inset-0 animate-ping rounded-full bg-brand-accent/40" />
              <span className="relative size-3 rounded-full border-2 border-white bg-brand-accent shadow-md" />
            </span>
            <span className="absolute left-4 top-0 whitespace-nowrap rounded-lg bg-black px-2.5 py-1 text-xs font-semibold text-white shadow-lg">
              {marker.label}
            </span>
          </div>
        ))}
      </div>
      {caption ? (
        <figcaption className="border-t border-black/5 px-4 py-3 text-sm text-black/55">
          {caption}
        </figcaption>
      ) : null}
    </figure>
  )
}
