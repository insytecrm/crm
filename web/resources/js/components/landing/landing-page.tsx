import { LandingFooter } from "@/components/landing/landing-footer"
import { LandingHeader } from "@/components/landing/landing-header"
import { AiOsSection } from "@/components/landing/sections/ai-os-section"
import { DemoFormSection } from "@/components/landing/sections/demo-form-section"
import { FaqSection } from "@/components/landing/sections/faq-section"
import { FeaturesSection } from "@/components/landing/sections/features-section"
import { HeroSection } from "@/components/landing/sections/hero-section"
import { HowItWorksSection } from "@/components/landing/sections/how-it-works-section"
import { LeadSourcesSection } from "@/components/landing/sections/lead-sources-section"
import { MicrositesSection } from "@/components/landing/sections/microsites-section"
import { OutcomeSection } from "@/components/landing/sections/outcome-section"
import { PricingSection } from "@/components/landing/sections/pricing-section"
import { RegisterFormSection } from "@/components/landing/sections/register-form-section"
import { RevenueSection } from "@/components/landing/sections/revenue-section"
import { StatsSection } from "@/components/landing/sections/stats-section"
import { TestimonialsSection } from "@/components/landing/sections/testimonials-section"
import { WhySection } from "@/components/landing/sections/why-section"

export function LandingPage() {
  return (
    <div className="min-h-screen bg-white text-black">
      <LandingHeader />
      <main>
        <HeroSection />
        <StatsSection />
        <OutcomeSection />
        <AiOsSection />
        <LeadSourcesSection />
        <MicrositesSection />
        <FeaturesSection />
        <RevenueSection />
        <HowItWorksSection />
        <WhySection />
        <TestimonialsSection />
        <PricingSection />
        <FaqSection />
        <DemoFormSection />
        <RegisterFormSection />
      </main>
      <LandingFooter />
    </div>
  )
}
