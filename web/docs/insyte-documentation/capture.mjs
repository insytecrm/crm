import { mkdir } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { chromium } from 'playwright';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const shotsDir = path.join(__dirname, 'screenshots');
const baseUrl = process.env.DOC_BASE_URL ?? 'http://localhost:8000';
const tenantSlug = process.env.DOC_TENANT ?? 'acme';
const platformEmail = process.env.DOC_PLATFORM_EMAIL ?? 'admin@platform.com';
const platformPassword = process.env.DOC_PLATFORM_PASSWORD ?? 'password';
const tenantEmail = process.env.DOC_TENANT_EMAIL ?? 'admin@acme.test';
const tenantPassword = process.env.DOC_TENANT_PASSWORD ?? 'password';

await mkdir(shotsDir, { recursive: true });

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    deviceScaleFactor: 1,
});
const page = await context.newPage();
page.setDefaultTimeout(20000);

async function shot(name, { fullPage = false, wait = 400 } = {}) {
    await page.waitForTimeout(wait);
    await page.screenshot({
        path: path.join(shotsDir, `${name}.png`),
        fullPage,
        animations: 'disabled',
    });
    console.log(`captured ${name}`);
}

async function open(pathname, name, options = {}) {
    const url = `${baseUrl}${pathname}`;
    try {
        const response = await page.goto(url, { waitUntil: 'domcontentloaded' });
        const status = response?.status() ?? 0;
        if (status >= 400) {
            console.warn(`skip ${name}: HTTP ${status} ${url}`);
            return false;
        }
        await page.waitForTimeout(600);
        await shot(name, options);
        return true;
    } catch (error) {
        console.warn(`skip ${name}: ${error.message}`);
        return false;
    }
}

async function login(pathname, email, password, successPattern) {
    await page.goto(`${baseUrl}${pathname}`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', email);
    await page.fill('#password', password);
    await Promise.all([
        page.waitForURL(successPattern, { timeout: 15000 }).catch(() => null),
        page.click('button[type="submit"]'),
    ]);
    const url = page.url();
    if (pathname.includes('login') && url.includes('login')) {
        throw new Error(`login failed at ${pathname} (still on ${url})`);
    }
    console.log(`logged in via ${pathname} → ${url}`);
}

await open('/', '01-landing-hero');
await page.evaluate(() => document.getElementById('features')?.scrollIntoView({ block: 'start' }));
await shot('02-landing-features');
await page.evaluate(() => document.getElementById('ai-os')?.scrollIntoView({ block: 'start' }));
await shot('03-landing-ai-os');
await page.evaluate(() => document.getElementById('lead-sources')?.scrollIntoView({ block: 'start' }));
await shot('04-landing-lead-sources');

await open('/platform/login', '05-platform-login');

await login('/platform/login', platformEmail, platformPassword, /\/platform\//);
await shot('06-platform-after-login');
await open('/platform/dashboard', '07-platform-dashboard');
await open('/platform/plans', '08-platform-plans');
await open('/platform/plans/create', '09-platform-plan-wizard');
await open('/platform/tenants', '10-platform-tenants');
await open(`/platform/tenants/${tenantSlug}`, '11-platform-tenant-overview');
await open(`/platform/tenants/${tenantSlug}/users`, '12-platform-tenant-users');
await open(`/platform/tenants/${tenantSlug}/subscription`, '13-platform-tenant-subscription');
await open(`/platform/tenants/${tenantSlug}/usage`, '14-platform-tenant-usage');

await open(`/${tenantSlug}/login`, '15-tenant-login');
await login(`/${tenantSlug}/login`, tenantEmail, tenantPassword, new RegExp(`/${tenantSlug}/`));
await shot('16-tenant-after-login');

const tenantPages = [
    ['dashboard', '17-tenant-dashboard'],
    ['ai', '18-tenant-ai-os'],
    ['leads', '19-tenant-leads'],
    ['leads/priority', '20-tenant-leads-priority'],
    ['leads/unassigned', '21-tenant-leads-unassigned'],
    ['leads/converted', '22-tenant-leads-converted'],
    ['leads/lost', '23-tenant-leads-lost'],
    ['leads/duplicates', '24-tenant-leads-duplicates'],
    ['activities', '25-tenant-activities'],
    ['follow-ups', '26-tenant-follow-ups'],
    ['site-visits', '27-tenant-site-visits'],
    ['tasks', '28-tenant-tasks'],
    ['properties', '29-tenant-properties'],
    ['bookings', '30-tenant-bookings'],
    ['revenue', '31-tenant-revenue'],
    ['invoices', '32-tenant-invoices'],
    ['payouts', '33-tenant-payouts'],
    ['reports', '34-tenant-reports'],
    ['reports/analytics', '35-tenant-reports-analytics'],
    ['teams', '36-tenant-teams'],
    ['teams/performance', '37-tenant-team-performance'],
    ['automations', '38-tenant-automations'],
    ['automations/workflows', '39-tenant-workflows'],
    ['automations/templates', '40-tenant-templates'],
    ['settings', '41-tenant-settings-profile'],
    ['settings?tab=company', '42-tenant-settings-company'],
    ['settings?tab=users', '43-tenant-settings-users'],
    ['settings?tab=roles', '44-tenant-settings-roles'],
    ['settings?tab=domains', '45-tenant-settings-domains'],
    ['settings?tab=integrations', '46-tenant-settings-integrations'],
    ['settings/integrations/api', '47-tenant-lead-api'],
    ['settings/integrations/google-sheets', '48-tenant-google-sheets'],
    ['settings/integrations/facebook', '49-tenant-facebook'],
    ['settings/integrations/portals/99acres', '50-tenant-portal-99acres'],
];

for (const [pathname, name] of tenantPages) {
    await open(`/${tenantSlug}/${pathname}`, name);
}

const addLeadOpened = await open(`/${tenantSlug}/leads?add=1`, '51-tenant-add-lead');
if (addLeadOpened) {
    await page.waitForTimeout(500);
    await shot('51-tenant-add-lead');
}

try {
    const propertyLink = page.locator('a[href*="/properties/"][href*="microsite"]').first();
    await page.goto(`${baseUrl}/${tenantSlug}/properties`, { waitUntil: 'domcontentloaded' });
    const count = await page.locator('a[href*="/projects/"]').count();
    if (count > 0) {
        const href = await page.locator('a[href*="/projects/"]').first().getAttribute('href');
        if (href) {
            await page.goto(href.startsWith('http') ? href : `${baseUrl}${href}`, { waitUntil: 'domcontentloaded' });
            await shot('52-public-microsite', { fullPage: true });
        }
    } else if (await propertyLink.count()) {
        await propertyLink.click();
        await page.waitForTimeout(800);
        await shot('52-property-microsite-cms');
    }
} catch (error) {
    console.warn(`microsite shot skipped: ${error.message}`);
}

await open(`/${tenantSlug}/properties`, '29-tenant-properties');
const manage = page.locator('a[href*="microsite/manage"]').first();
if (await manage.count()) {
    await manage.click();
    await page.waitForTimeout(800);
    await shot('53-tenant-microsite-cms');
}

await browser.close();
console.log('capture complete');
