import { SITE_CONTACT } from "@/components/site/lib/emails"
import { FOOTER_COLUMNS } from "@/components/site/lib/nav"

type SiteFooterProps = {
  logoSrc: string
}

export function SiteFooter({ logoSrc }: SiteFooterProps) {
  return (
    <footer className="border-t border-black/5 bg-[#0b1220] text-white">
      <div className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div className="grid gap-10 lg:grid-cols-[1.2fr_3fr]">
          <div>
            <img
              src={logoSrc}
              alt="InSyte"
              className="h-9 w-auto max-w-[160px] object-contain object-left brightness-0 invert"
              draggable={false}
            />
            <p className="mt-4 max-w-xs text-sm leading-relaxed text-white/55">
              CRM + Automation + AI for modern sales teams — built for real estate channel partners.
            </p>
            <div className="mt-6 space-y-1 text-sm text-white/50">
              <p>
                Sales:{" "}
                <a className="text-white/80 hover:text-white" href={`mailto:${SITE_CONTACT.sales}`}>
                  {SITE_CONTACT.sales}
                </a>
              </p>
              <p>
                Support:{" "}
                <a className="text-white/80 hover:text-white" href={`mailto:${SITE_CONTACT.support}`}>
                  {SITE_CONTACT.support}
                </a>
              </p>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:grid-cols-6">
            {FOOTER_COLUMNS.map((column) => (
              <div key={column.title}>
                <p className="text-xs font-semibold uppercase tracking-[0.16em] text-white/40">
                  {column.title}
                </p>
                <ul className="mt-4 space-y-2">
                  {column.links.map((link) => (
                    <li key={link.href + link.label}>
                      <a
                        href={link.href}
                        className="text-sm text-white/65 transition-colors hover:text-white"
                      >
                        {link.label}
                      </a>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>
        </div>

        <div className="mt-12 flex flex-col gap-3 border-t border-white/10 pt-6 text-sm text-white/40 sm:flex-row sm:items-center sm:justify-between">
          <p>© {new Date().getFullYear()} InSyte. All rights reserved.</p>
          <p className="text-white/35">
            Designed to support responsible handling of customer information. Customers remain
            responsible for lawful use.
          </p>
        </div>
      </div>
    </footer>
  )
}
