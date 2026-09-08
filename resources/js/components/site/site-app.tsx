import { SiteLayout } from "@/components/site/site-layout"
import { HomePage } from "@/components/site/pages/home-page"
import { ProductCrmPage } from "@/components/site/pages/product-crm-page"
import { ProductAutomationPage } from "@/components/site/pages/product-automation-page"
import { ProductAiPage } from "@/components/site/pages/product-ai-page"
import { ProductIntegrationsPage } from "@/components/site/pages/product-integrations-page"
import { ProductCustomizationPage } from "@/components/site/pages/product-customization-page"
import { SolutionsRealEstatePage } from "@/components/site/pages/solutions-real-estate-page"
import { SolutionsGenericPage } from "@/components/site/pages/solutions-generic-page"
import { PricingPage } from "@/components/site/pages/pricing-page"
import { DemoPage } from "@/components/site/pages/demo-page"
import { SignupPage } from "@/components/site/pages/signup-page"
import { FaqsPage } from "@/components/site/pages/faqs-page"
import {
  AboutPage,
  BlogPage,
  CareersPage,
  ContactPage,
} from "@/components/site/pages/simple-pages"
import {
  DocsArticlePage,
  DocsIndexPage,
  GuideArticlePage,
  GuidesIndexPage,
  HelpArticlePage,
  HelpIndexPage,
} from "@/components/site/pages/resource-pages"

type SiteAppProps = {
  page: string
  slug: string | null
  logoLight: string
  logoDark: string
  dashboardSrc: string
}

export function SiteApp({ page, slug, logoLight, logoDark, dashboardSrc }: SiteAppProps) {
  return (
    <SiteLayout logoLight={logoLight} logoDark={logoDark}>
      {renderPage(page, slug, dashboardSrc)}
    </SiteLayout>
  )
}

function renderPage(page: string, slug: string | null, dashboardSrc: string) {
  switch (page) {
    case "home":
      return <HomePage dashboardSrc={dashboardSrc} />
    case "crm":
      return <ProductCrmPage />
    case "automation":
      return <ProductAutomationPage />
    case "ai":
      return <ProductAiPage />
    case "integrations":
      return <ProductIntegrationsPage />
    case "customization":
      return <ProductCustomizationPage />
    case "solutions-real-estate":
      return <SolutionsRealEstatePage />
    case "solutions-sales-teams":
      return <SolutionsGenericPage kind="sales-teams" />
    case "solutions-small-businesses":
      return <SolutionsGenericPage kind="small-businesses" />
    case "solutions-agencies":
      return <SolutionsGenericPage kind="agencies" />
    case "solutions-other":
      return <SolutionsGenericPage kind="other" />
    case "pricing":
      return <PricingPage />
    case "demo":
      return <DemoPage />
    case "signup":
      return <SignupPage />
    case "faqs":
      return <FaqsPage />
    case "help":
      return slug ? <HelpArticlePage slug={slug} /> : <HelpIndexPage />
    case "docs":
      return slug ? <DocsArticlePage slug={slug} /> : <DocsIndexPage />
    case "guides":
      return slug ? <GuideArticlePage slug={slug} /> : <GuidesIndexPage />
    case "blog":
      return <BlogPage />
    case "about":
      return <AboutPage />
    case "contact":
      return <ContactPage />
    case "careers":
      return <CareersPage />
    default:
      return <HomePage dashboardSrc={dashboardSrc} />
  }
}
