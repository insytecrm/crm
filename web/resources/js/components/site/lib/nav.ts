export type NavItem = {
  label: string
  href: string
  description?: string
}

export type NavGroup = {
  id: "product" | "solutions" | "resources"
  label: string
  items: NavItem[]
}

export const NAV_GROUPS: NavGroup[] = [
  {
    id: "product",
    label: "Product",
    items: [
      { label: "CRM", href: "/crm", description: "Leads to bookings in one place" },
      { label: "Automation", href: "/automation", description: "Never miss a follow-up" },
      { label: "AI", href: "/ai", description: "Talk to your CRM" },
      { label: "Integrations", href: "/integrations", description: "Every lead source, one inbox" },
      { label: "Customization", href: "/customization", description: "Your process, your rules" },
    ],
  },
  {
    id: "solutions",
    label: "Solutions",
    items: [
      { label: "Real Estate", href: "/solutions/real-estate", description: "Built for channel partners" },
      { label: "Sales Teams", href: "/solutions/sales-teams", description: "One place to work" },
      { label: "Small Businesses", href: "/solutions/small-businesses", description: "Leave the spreadsheets" },
      { label: "Agencies", href: "/solutions/agencies", description: "Multiple sources, one CRM" },
      { label: "Other Industries", href: "/solutions/other-industries", description: "Sales-ready for any team" },
    ],
  },
  {
    id: "resources",
    label: "Resources",
    items: [
      { label: "Help Center", href: "/help", description: "Step-by-step how-tos" },
      { label: "Documentation", href: "/docs", description: "API & webhooks" },
      { label: "Guides", href: "/guides", description: "Playbooks that convert" },
      { label: "FAQs", href: "/faqs", description: "Quick answers" },
      { label: "Blog", href: "/blog", description: "Updates & insights" },
    ],
  },
]

export const FOOTER_COLUMNS = [
  {
    title: "Product",
    links: [
      { label: "CRM", href: "/crm" },
      { label: "Automation", href: "/automation" },
      { label: "AI", href: "/ai" },
      { label: "Integrations", href: "/integrations" },
      { label: "Customization", href: "/customization" },
      { label: "Pricing", href: "/pricing" },
    ],
  },
  {
    title: "Solutions",
    links: [
      { label: "Real Estate", href: "/solutions/real-estate" },
      { label: "Sales Teams", href: "/solutions/sales-teams" },
      { label: "Small Businesses", href: "/solutions/small-businesses" },
      { label: "Agencies", href: "/solutions/agencies" },
      { label: "Other Industries", href: "/solutions/other-industries" },
    ],
  },
  {
    title: "Resources",
    links: [
      { label: "Help Center", href: "/help" },
      { label: "Documentation", href: "/docs" },
      { label: "Guides", href: "/guides" },
      { label: "FAQs", href: "/faqs" },
      { label: "Blog", href: "/blog" },
    ],
  },
  {
    title: "Company",
    links: [
      { label: "About", href: "/about" },
      { label: "Contact", href: "/contact" },
      { label: "Careers", href: "/careers" },
    ],
  },
  {
    title: "Support",
    links: [
      { label: "Help Center", href: "/help" },
      { label: "Book a Demo", href: "/demo" },
      { label: "Start Free", href: "/signup" },
    ],
  },
  {
    title: "Legal",
    links: [
      { label: "Privacy Policy", href: "/privacy" },
      { label: "Terms of Service", href: "/terms" },
      { label: "Cookie Policy", href: "/cookies" },
      { label: "Security", href: "/security" },
      { label: "Refund Policy", href: "/refund" },
    ],
  },
] as const
