export type HelpArticle = {
  slug: string
  title: string
  summary: string
  category: string
  steps: { title: string; body: string; image?: string; markers?: { x: number; y: number; label: string }[] }[]
}

export const HELP_CATEGORIES = [
  "Getting Started",
  "Leads",
  "Activities",
  "Integrations",
] as const

export const HELP_ARTICLES: HelpArticle[] = [
  {
    slug: "add-a-lead",
    title: "Add a lead",
    summary: "Create a lead manually from the Leads screen.",
    category: "Leads",
    steps: [
      {
        title: "Open Leads",
        body: "From the sidebar, go to Leads. This is your main inbox for every enquiry.",
        image: "/images/site/19-tenant-leads.png",
        markers: [{ x: 12, y: 18, label: "Leads" }],
      },
      {
        title: "Click Add Lead",
        body: "Use Add Lead to open the form. Fill name and phone at minimum, then save.",
        image: "/images/site/51-tenant-add-lead.png",
        markers: [{ x: 88, y: 12, label: "Add Lead" }],
      },
    ],
  },
  {
    slug: "schedule-a-follow-up",
    title: "Schedule a follow-up",
    summary: "Plan the next call or WhatsApp touch so nothing goes cold.",
    category: "Activities",
    steps: [
      {
        title: "Open Follow-ups",
        body: "Go to Activities → Follow-ups to see what’s due today and overdue.",
        image: "/images/site/26-tenant-follow-ups.png",
        markers: [{ x: 14, y: 22, label: "Follow-ups" }],
      },
      {
        title: "Schedule from the lead",
        body: "On a lead, schedule the next follow-up with date, time, and notes. Your agenda updates automatically.",
        image: "/images/site/26-tenant-follow-ups.png",
        markers: [{ x: 82, y: 14, label: "Schedule" }],
      },
    ],
  },
  {
    slug: "schedule-a-site-visit",
    title: "Schedule a site visit",
    summary: "Book a property visit and keep it on your team’s agenda.",
    category: "Activities",
    steps: [
      {
        title: "Open Site Visits",
        body: "Use Activities → Site Visits to track fresh visits and revisits.",
        image: "/images/site/27-tenant-site-visits.png",
        markers: [{ x: 14, y: 28, label: "Site Visits" }],
      },
    ],
  },
  {
    slug: "connect-facebook-leads",
    title: "Connect Facebook Lead Ads",
    summary: "Pull Meta lead forms straight into InSyte.",
    category: "Integrations",
    steps: [
      {
        title: "Open Facebook settings",
        body: "Go to Settings → Integrations → Facebook. Connect your page and map fields.",
        image: "/images/site/49-tenant-facebook.png",
        markers: [{ x: 55, y: 20, label: "Connect" }],
      },
    ],
  },
  {
    slug: "connect-google-sheets",
    title: "Sync Google Sheets",
    summary: "Map columns once — new rows become leads.",
    category: "Integrations",
    steps: [
      {
        title: "Connect a sheet",
        body: "Paste your sheet URL, map columns to CRM fields, then sync or pause anytime.",
        image: "/images/site/48-tenant-google-sheets.png",
        markers: [{ x: 70, y: 18, label: "Map columns" }],
      },
    ],
  },
  {
    slug: "create-a-booking",
    title: "Create a booking",
    summary: "Convert a won lead into a booking with agreement value.",
    category: "Getting Started",
    steps: [
      {
        title: "Open Bookings",
        body: "From Bookings, record property, unit, agreement value, and date — then track revenue from there.",
        image: "/images/site/30-tenant-bookings.png",
        markers: [{ x: 88, y: 12, label: "New booking" }],
      },
    ],
  },
]

export function helpArticle(slug: string): HelpArticle | undefined {
  return HELP_ARTICLES.find((article) => article.slug === slug)
}

export type DocArticle = {
  slug: string
  title: string
  summary: string
  category: string
  sections: { title: string; body: string; image?: string; markers?: { x: number; y: number; label: string }[] }[]
}

export const DOC_ARTICLES: DocArticle[] = [
  {
    slug: "lead-api",
    title: "Lead API",
    summary: "Push website or form enquiries into InSyte with a Bearer token.",
    category: "API",
    sections: [
      {
        title: "Issue a token",
        body: "In Settings → Lead API, issue a tenant token. Treat it like a password — never expose it in public frontends without a backend proxy.",
        image: "/images/site/47-tenant-lead-api.png",
        markers: [{ x: 72, y: 24, label: "API token" }],
      },
      {
        title: "Send a lead",
        body: "POST /api/v1/leads with Authorization: Bearer {token}. Include name (required) plus phone, email, source, budget, location, property_type, or configuration as needed.",
      },
    ],
  },
  {
    slug: "portal-webhooks",
    title: "Portal webhooks",
    summary: "Receive 99acres, Housing, MagicBricks, and NoBroker leads via webhook URL.",
    category: "Webhooks",
    sections: [
      {
        title: "Copy your webhook",
        body: "Each portal connection gets a unique webhook URL and secret. Configure that URL in the portal and keep the secret private.",
        image: "/images/site/50-tenant-portal-99acres.png",
        markers: [{ x: 60, y: 30, label: "Webhook URL" }],
      },
    ],
  },
  {
    slug: "facebook-oauth",
    title: "Facebook Lead Ads",
    summary: "OAuth connect, field mapping, automatic lead intake.",
    category: "Integrations",
    sections: [
      {
        title: "Connect Meta",
        body: "Authorize your Facebook page, map lead form fields to CRM fields, and new Lead Ads submissions appear in InSyte.",
        image: "/images/site/49-tenant-facebook.png",
        markers: [{ x: 50, y: 22, label: "Facebook" }],
      },
    ],
  },
]

export function docArticle(slug: string): DocArticle | undefined {
  return DOC_ARTICLES.find((article) => article.slug === slug)
}

export type Guide = {
  slug: string
  title: string
  summary: string
  sections: { title: string; body: string; image?: string; markers?: { x: number; y: number; label: string }[] }[]
}

export const GUIDES: Guide[] = [
  {
    slug: "real-estate-lead-management",
    title: "Real Estate Lead Management",
    summary: "A practical loop for channel partners: capture, assign, follow up, visit, book.",
    sections: [
      {
        title: "Capture everything in one inbox",
        body: "Portals, Facebook, sheets, and website forms should land in the same Leads list — assigned and ready.",
        image: "/images/site/19-tenant-leads.png",
        markers: [{ x: 20, y: 16, label: "All leads" }],
      },
      {
        title: "Follow up on a schedule",
        body: "Use follow-ups and tasks so every hot enquiry gets a next action — not a WhatsApp reminder you’ll forget.",
        image: "/images/site/26-tenant-follow-ups.png",
        markers: [{ x: 18, y: 20, label: "Due today" }],
      },
      {
        title: "Close with site visits and bookings",
        body: "Move qualified buyers to site visits, then bookings — and keep commission visible in revenue.",
        image: "/images/site/30-tenant-bookings.png",
        markers: [{ x: 50, y: 20, label: "Bookings" }],
      },
    ],
  },
  {
    slug: "follow-up-playbook",
    title: "Property Sales Follow-Up Playbook",
    summary: "How to keep momentum from first enquiry to site visit.",
    sections: [
      {
        title: "Same-day first touch",
        body: "New leads should get a task or follow-up the day they arrive — automation can create that for you.",
        image: "/images/site/38-tenant-automations.png",
        markers: [{ x: 55, y: 25, label: "Automations" }],
      },
      {
        title: "Use templates",
        body: "Message templates keep WhatsApp and email outreach consistent without rewriting every time.",
        image: "/images/site/40-tenant-templates.png",
        markers: [{ x: 70, y: 16, label: "Templates" }],
      },
    ],
  },
]

export function guideArticle(slug: string): Guide | undefined {
  return GUIDES.find((guide) => guide.slug === slug)
}
