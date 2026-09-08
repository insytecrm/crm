import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { chromium } from 'playwright';

const htmlPath = path.join(import.meta.dirname, 'index.html');
const pdfPath = path.join(import.meta.dirname, 'InSyte-CRM-Product-and-Technical-Documentation.pdf');

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();

await page.goto(pathToFileURL(htmlPath).href, { waitUntil: 'load', timeout: 60000 });
await page.evaluate(async () => {
    await Promise.all(
        [...document.images].map((img) => {
            if (img.complete) {
                return null;
            }

            return new Promise((resolve) => {
                img.addEventListener('load', resolve, { once: true });
                img.addEventListener('error', resolve, { once: true });
            });
        }),
    );
});
await page.waitForTimeout(500);

await page.pdf({
    path: pdfPath,
    format: 'A4',
    printBackground: true,
    preferCSSPageSize: true,
    displayHeaderFooter: true,
    headerTemplate: `<div style="width:100%;font-family:Inter,system-ui,sans-serif;font-size:8px;color:#64748b;padding:0 18mm;display:flex;justify-content:space-between;">
        <span>InSyte CRM · Product & technical documentation</span>
        <span>Confidential — internal</span>
    </div>`,
    footerTemplate: `<div style="width:100%;font-family:Inter,system-ui,sans-serif;font-size:8px;color:#64748b;padding:0 18mm;display:flex;justify-content:space-between;">
        <span>September 2026</span>
        <span>Page <span class="pageNumber"></span> of <span class="totalPages"></span></span>
    </div>`,
    margin: { top: '14mm', bottom: '14mm', left: '0', right: '0' },
});

await browser.close();
console.log(`wrote ${pdfPath}`);
