import { AnnotatedShot } from "@/components/site/ui/annotated-shot"
import { PageHero } from "@/components/site/ui/page-hero"
import { Reveal } from "@/components/site/ui/reveal"
import { SiteCard } from "@/components/site/ui/site-card"
import {
  DOC_ARTICLES,
  GUIDES,
  HELP_ARTICLES,
  HELP_CATEGORIES,
  docArticle,
  guideArticle,
  helpArticle,
} from "@/components/site/content/resources"

export function HelpIndexPage() {
  return (
    <>
      <PageHero
        eyebrow="Help Center"
        title="Learn InSyte in a few steps."
        description="Short, visual how-tos using the real product — not every screen, just the journeys that matter."
      />
      <section className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        {HELP_CATEGORIES.map((category) => {
          const articles = HELP_ARTICLES.filter((article) => article.category === category)

          if (articles.length === 0) {
            return null
          }

          return (
            <div key={category} className="mb-12">
              <h2 className="text-sm font-semibold uppercase tracking-[0.16em] text-black/40">
                {category}
              </h2>
              <div className="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                {articles.map((article) => (
                  <Reveal key={article.slug}>
                    <SiteCard href={`/help/${article.slug}`}>
                      <p className="text-lg font-semibold text-black">{article.title}</p>
                      <p className="mt-2 text-sm text-black/55">{article.summary}</p>
                    </SiteCard>
                  </Reveal>
                ))}
              </div>
            </div>
          )
        })}
      </section>
    </>
  )
}

export function HelpArticlePage({ slug }: { slug: string }) {
  const article = helpArticle(slug)

  if (! article) {
    return <MissingResource kind="help article" />
  }

  return (
    <>
      <PageHero eyebrow={article.category} title={article.title} description={article.summary} />
      <section className="mx-auto max-w-3xl space-y-12 px-4 py-14 sm:px-6 lg:px-8">
        {article.steps.map((step, index) => (
          <Reveal key={step.title} delay={index * 0.05}>
            <p className="text-xs font-semibold uppercase tracking-[0.16em] text-brand-accent">
              Step {index + 1}
            </p>
            <h2 className="mt-2 text-2xl font-bold text-black">{step.title}</h2>
            <p className="mt-3 text-base leading-relaxed text-black/60">{step.body}</p>
            {step.image ? (
              <AnnotatedShot
                className="mt-6"
                src={step.image}
                alt={step.title}
                markers={step.markers}
              />
            ) : null}
          </Reveal>
        ))}
        <div className="flex gap-4 border-t border-black/5 pt-8 text-sm">
          <a href="/help" className="font-semibold text-brand-accent hover:underline">
            ← All help articles
          </a>
        </div>
      </section>
    </>
  )
}

export function DocsIndexPage() {
  return (
    <>
      <PageHero
        eyebrow="Documentation"
        title="API, webhooks, and integrations."
        description="Technical reference for connecting InSyte to your stack."
      />
      <section className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {DOC_ARTICLES.map((article) => (
            <Reveal key={article.slug}>
              <SiteCard href={`/docs/${article.slug}`}>
                <p className="text-xs font-semibold uppercase tracking-wide text-brand-accent">
                  {article.category}
                </p>
                <p className="mt-2 text-lg font-semibold text-black">{article.title}</p>
                <p className="mt-2 text-sm text-black/55">{article.summary}</p>
              </SiteCard>
            </Reveal>
          ))}
        </div>
      </section>
    </>
  )
}

export function DocsArticlePage({ slug }: { slug: string }) {
  const article = docArticle(slug)

  if (! article) {
    return <MissingResource kind="doc" />
  }

  return (
    <>
      <PageHero eyebrow={article.category} title={article.title} description={article.summary} />
      <section className="mx-auto max-w-3xl space-y-12 px-4 py-14 sm:px-6 lg:px-8">
        {article.sections.map((section, index) => (
          <Reveal key={section.title} delay={index * 0.05}>
            <h2 className="text-2xl font-bold text-black">{section.title}</h2>
            <p className="mt-3 text-base leading-relaxed text-black/60">{section.body}</p>
            {section.image ? (
              <AnnotatedShot
                className="mt-6"
                src={section.image}
                alt={section.title}
                markers={section.markers}
              />
            ) : null}
          </Reveal>
        ))}
        <a href="/docs" className="inline-block font-semibold text-brand-accent hover:underline">
          ← All docs
        </a>
      </section>
    </>
  )
}

export function GuidesIndexPage() {
  return (
    <>
      <PageHero
        eyebrow="Guides"
        title="Playbooks for property sales teams."
        description="Longer journeys — still visual, still online. No PDFs."
      />
      <section className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div className="grid gap-4 md:grid-cols-2">
          {GUIDES.map((guide) => (
            <Reveal key={guide.slug}>
              <SiteCard href={`/guides/${guide.slug}`} className="min-h-[160px]">
                <p className="text-xl font-semibold text-black">{guide.title}</p>
                <p className="mt-3 text-sm text-black/55">{guide.summary}</p>
              </SiteCard>
            </Reveal>
          ))}
        </div>
      </section>
    </>
  )
}

export function GuideArticlePage({ slug }: { slug: string }) {
  const guide = guideArticle(slug)

  if (! guide) {
    return <MissingResource kind="guide" />
  }

  return (
    <>
      <PageHero eyebrow="Guide" title={guide.title} description={guide.summary} />
      <section className="mx-auto max-w-3xl space-y-14 px-4 py-14 sm:px-6 lg:px-8">
        {guide.sections.map((section, index) => (
          <Reveal key={section.title} delay={index * 0.05}>
            <h2 className="text-2xl font-bold text-black">{section.title}</h2>
            <p className="mt-3 text-base leading-relaxed text-black/60">{section.body}</p>
            {section.image ? (
              <AnnotatedShot
                className="mt-6"
                src={section.image}
                alt={section.title}
                markers={section.markers}
              />
            ) : null}
          </Reveal>
        ))}
        <a href="/guides" className="inline-block font-semibold text-brand-accent hover:underline">
          ← All guides
        </a>
      </section>
    </>
  )
}

function MissingResource({ kind }: { kind: string }) {
  return (
    <section className="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6">
      <h1 className="text-3xl font-bold text-black">We couldn’t find that {kind}.</h1>
      <p className="mt-3 text-black/55">Pick another topic from the index.</p>
      <a href="/help" className="mt-6 inline-block font-semibold text-brand-accent hover:underline">
        Back to Help
      </a>
    </section>
  )
}
