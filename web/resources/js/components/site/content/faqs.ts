export type FaqItem = {
  q: string
  a: string
}

export type FaqGroup = {
  title: string
  items: FaqItem[]
}

export const FAQ_GROUPS: FaqGroup[] = [
  {
    title: "Getting started",
    items: [
      {
        q: "Who is InSyte built for?",
        a: "InSyte is designed for real estate channel partners and sales teams managing property enquiries, follow-ups, site visits and bookings.",
      },
      {
        q: "Can I see the product before choosing a plan?",
        a: "Yes. Book a demo for a guided walkthrough based on your team and sales process.",
      },
      {
        q: "Can my team start with a trial?",
        a: "You can submit a trial request from the signup page. The team will confirm the appropriate setup and plan limits.",
      },
    ],
  },
  {
    title: "CRM and automation",
    items: [
      {
        q: "What can I manage in the CRM?",
        a: "You can keep supported lead, follow-up, task, site visit, property and booking workflows connected in one workspace.",
      },
      {
        q: "What does automation do?",
        a: "Automation helps teams run configured, repeatable actions when selected triggers and conditions are met. Your team controls the workflows.",
      },
      {
        q: "Which lead sources can connect?",
        a: "Available integrations include supported portal, Google Sheets, Facebook and lead API workflows. Exact availability depends on your plan and configuration.",
      },
    ],
  },
  {
    title: "AI and data",
    items: [
      {
        q: "How does InSyte use AI?",
        a: "AI-assisted tools can help your team work with CRM context, prepare content and identify next steps. Outputs should be reviewed before use.",
      },
      {
        q: "Does AI replace sales decisions?",
        a: "No. InSyte is designed to support people, not replace their judgment. Your team remains responsible for customer communication and decisions.",
      },
      {
        q: "How should we handle customer data?",
        a: "Use InSyte in line with applicable privacy, consent and communication laws. Your organization remains responsible for lawful data collection and use.",
      },
    ],
  },
  {
    title: "Plans and support",
    items: [
      {
        q: "Which plans include AI?",
        a: "AI-assisted capabilities are available from the Growth plan, subject to plan configuration and limits.",
      },
      {
        q: "Which plans include microsites and custom domains?",
        a: "Microsites and custom domains are available on Growth and Pro plans.",
      },
      {
        q: "How do I get pricing or support?",
        a: "Contact Sales for plan guidance and pricing, or contact Support if you already use InSyte.",
      },
    ],
  },
]
