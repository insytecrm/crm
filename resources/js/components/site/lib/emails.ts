export const SITE_EMAILS = {
  hello: "hello@insytecrm.com",
  support: "support@insytecrm.com",
  legal: "legal@insytecrm.com",
  development: "development@insytecrm.com",
  careers: "careers@insytecrm.com",
} as const

export const SITE_CONTACT = {
  sales: SITE_EMAILS.hello,
  support: SITE_EMAILS.support,
  privacy: SITE_EMAILS.legal,
  security: SITE_EMAILS.development,
  careers: SITE_EMAILS.careers,
} as const
