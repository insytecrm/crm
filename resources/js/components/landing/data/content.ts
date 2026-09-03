export type PlanId = "starter" | "growth" | "pro"
export type BillingCycle = "monthly" | "yearly"

export const PRICING = {
  starter: { monthly: 999, yearly: 9990, name: "Starter" },
  growth: { monthly: 1999, yearly: 19990, name: "Growth" },
  pro: { monthly: 4999, yearly: 49990, name: "Pro" },
} as const

export const STATS = [
  { value: 10000, suffix: "+", label: "Leads managed" },
  { value: 500, suffix: "+", label: "Channel partners" },
  { value: 50, prefix: "₹", suffix: " Cr+", label: "Booking value tracked" },
  { value: 2, suffix: "x", label: "Faster follow-ups on average" },
] as const

export const HERO_WORDS = ["Capture.", "Follow up.", "Book.", "Earn."] as const

export const OUTCOME_CARDS = [
  {
    title: "Never miss a lead",
    body: "Every enquiry lands in one place — Facebook ads, portals, WhatsApp, your team. Assigned automatically.",
  },
  {
    title: "Close more site visits",
    body: "Know who to call today. Schedule site visits. Track every follow-up.",
  },
  {
    title: "Know your earnings",
    body: "See booking value, your commission, pending payouts, and received money — clearly, every day.",
  },
] as const

export const AI_OS_STEPS = [
  {
    title: "Know who to call first",
    body: "InSyte AI OS looks at every lead and tells your team exactly who to contact next. Hot buyers first.",
  },
  {
    title: "Follow-ups on autopilot",
    body: "WhatsApp reminders, call schedules, and email follow-ups go out on time — so leads don't go cold.",
  },
  {
    title: "See who's ready to buy",
    body: "Every lead gets a score. High score = ready to book. Low score = needs nurturing.",
  },
  {
    title: "Match buyers to the right property",
    body: "InSyte AI OS suggests matching properties from your inventory instantly based on budget and location.",
  },
  {
    title: "WhatsApp replies written for you",
    body: "Draft messages based on lead history. Your team reviews, edits if needed, and sends in seconds.",
  },
  {
    title: "Run WhatsApp & email campaigns",
    body: "Send property updates and launch alerts to hundreds of buyers in one click.",
  },
  {
    title: "Voice call campaigns",
    body: "Reach buyers who don't read WhatsApp with automated voice calls for launches and reminders.",
  },
  {
    title: "See where your money is",
    body: "Pipeline value, expected commission, team performance, and best lead sources in one view.",
  },
  {
    title: "Set rules once, let it work",
    body: "Auto-assign, auto-follow-up, and auto-remind when there's no response.",
  },
  {
    title: "Social posts for your projects",
    body: "Create Instagram and Facebook posts for your properties in seconds.",
  },
] as const

export const LEAD_SOURCES = [
  {
    name: "Meta",
    logo: "/images/logos/meta.svg",
    title: "Leads from your ads, automatically",
    body: "When someone fills your Facebook or Instagram lead form, their details appear in InSyte CRM within seconds.",
  },
  {
    name: "99acres",
    logo: "/images/logos/99acres.svg",
    title: "Portal enquiries, direct to CRM",
    body: "Buyers who enquire on 99acres land in your pipeline — assigned and ready for follow-up.",
  },
  {
    name: "Housing.com",
    logo: "/images/logos/housing.svg",
    title: "Portal enquiries, direct to CRM",
    body: "Every Housing.com lead is captured automatically. No more checking portal dashboards every hour.",
  },
  {
    name: "Google Sheets",
    logo: "/images/logos/google-sheets.svg",
    title: "Already using a sheet? Connect it.",
    body: "Connect your sheet once. New rows become leads in InSyte CRM.",
  },
  {
    name: "Your website",
    logo: "/images/logos/insyte.svg",
    title: "Your own landing pages",
    body: "Connect your enquiry form or property page. Leads flow in automatically.",
  },
  {
    name: "WhatsApp",
    logo: "/images/logos/whatsapp.svg",
    title: "Add leads anytime",
    body: "Your team can add leads manually or import a list when needed.",
  },
] as const

export const FEATURE_CARDS = [
  {
    number: "01",
    title: "Lead management",
    body: "All buyers in one place. Priority, converted, lost, and duplicates handled for you.",
    tags: ["Leads", "Priority", "Duplicates"],
    mock: "leads" as const,
  },
  {
    number: "02",
    title: "Follow-ups & site visits",
    body: "Schedule calls and site visits. Get reminders. See what's pending today.",
    tags: ["Follow-ups", "Site Visits", "Tasks"],
    mock: "activities" as const,
  },
  {
    number: "03",
    title: "Bookings",
    body: "Convert a lead to a booking. Record property, unit, agreement value, and booking date.",
    tags: ["Bookings", "Agreement", "Units"],
    mock: "bookings" as const,
  },
  {
    number: "04",
    title: "Revenue & commission",
    body: "See total revenue, commission earned, pending, and received — clearly every day.",
    tags: ["Revenue", "Commission", "Payouts"],
    mock: "revenue" as const,
  },
  {
    number: "05",
    title: "Invoices & payouts",
    body: "Generate invoices for bookings. Track which commissions are paid and which are pending.",
    tags: ["Invoices", "Payouts"],
    mock: "payouts" as const,
  },
  {
    number: "06",
    title: "Properties",
    body: "Manage your project inventory. Match buyers to the right property.",
    tags: ["Properties", "Inventory"],
    mock: "properties" as const,
  },
  {
    number: "07",
    title: "Teams",
    body: "Add your sales team. Assign leads. Control access with built-in team chat.",
    tags: ["Teams", "Roles", "Chat"],
    mock: "teams" as const,
  },
] as const

export const HOW_IT_WORKS = [
  {
    step: "01",
    title: "Capture",
    body: "Leads from Facebook, 99acres, Housing, WhatsApp, and your property pages — all in one inbox.",
  },
  {
    step: "02",
    title: "Follow up",
    body: "InSyte AI OS scores each lead and tells your team who to call, what to message, and when.",
  },
  {
    step: "03",
    title: "Book",
    body: "Convert interested buyers to confirmed bookings with property, unit, and agreement value.",
  },
  {
    step: "04",
    title: "Earn",
    body: "Track commission, generate invoices, and see pending vs received payouts.",
  },
] as const

export const WHY_POINTS = [
  "Made for real estate sales — leads, site visits, bookings, and commission in one place",
  "InSyte AI OS included in every plan",
  "Works with Facebook, 99acres, Housing, and WhatsApp — no technical setup needed",
  "Property pages you can share on WhatsApp",
  "Commission and payouts tracked clearly",
  "Team management built in",
] as const

export const TESTIMONIALS = [
  {
    quote:
      "We were getting 50+ leads a week from Facebook and 99acres but losing half of them. After InSyte CRM, every lead gets a follow-up within 2 hours. Our site visits doubled in the first month.",
    name: "Rajesh K.",
    role: "Channel Partner, Pune",
  },
  {
    quote:
      "The commission tracking alone is worth it. I used to maintain three Excel files. Now I open one screen and know exactly what I've earned and what's pending.",
    name: "Priya S.",
    role: "Sales Head, Mumbai",
  },
  {
    quote:
      "InSyte AI OS tells my team every morning — call these 5 people first. We closed 3 extra bookings last quarter.",
    name: "Amit D.",
    role: "Broker Network Owner, Bangalore",
  },
] as const

export const FAQ_ITEMS = [
  {
    question: "Do I need technical knowledge to use InSyte CRM?",
    answer:
      "No. If you use WhatsApp and Excel, you can use InSyte CRM. We set up your lead sources and train your team.",
  },
  {
    question: "Is InSyte AI OS a separate product?",
    answer:
      "No. InSyte AI OS is built into InSyte CRM. Every plan includes it — lead scoring, follow-up suggestions, AI replies, and campaigns.",
  },
  {
    question: "Can I connect my Facebook ads and 99acres leads?",
    answer:
      "Yes. Leads from Facebook, Instagram, 99acres, Housing.com, and Google Sheets connect directly. Your team doesn't need to copy-paste anything.",
  },
  {
    question: "How does commission tracking work?",
    answer:
      "When you create a booking, InSyte CRM records the agreement value and your commission. You can see total earned, pending, and received commission.",
  },
  {
    question: "Can I create property pages to share on WhatsApp?",
    answer:
      "Yes. Create a page for each project, share the link on WhatsApp or social media, and every enquiry comes into your CRM automatically.",
  },
  {
    question: "How many team members can I add?",
    answer: "Starter includes 2, Growth includes 10, and Pro includes unlimited team members.",
  },
  {
    question: "Can I try before I pay?",
    answer:
      "Start a free trial or book a demo. We'll help you set up with your lead sources and projects.",
  },
] as const

export const PLAN_FEATURES = {
  starter: [
    "Up to 500 active leads",
    "2 team members",
    "Lead management & follow-ups",
    "Site visit tracking",
    "Bookings & basic revenue view",
    "InSyte AI OS — scoring, next-action, AI replies",
    "2 property pages",
    "Facebook & 1 portal connection",
  ],
  growth: [
    "Up to 2,000 active leads",
    "10 team members",
    "Everything in Starter",
    "WhatsApp & email campaigns",
    "Full revenue, commission & payout tracking",
    "Invoices",
    "Unlimited property pages",
    "All portal connections",
    "Team performance reports",
  ],
  pro: [
    "Unlimited leads",
    "Unlimited team members",
    "Everything in Growth",
    "Voice call campaigns (IVR)",
    "Workflow automation",
    "Advanced analytics",
    "Priority support",
    "Dedicated onboarding call",
  ],
} as const
