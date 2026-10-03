<x-legal-layout :title="'Data Processing & Security | InSyte'" :heading="'Data Processing & Security'">
    <p><strong>Effective Date:</strong> September 8, 2026</p>

    <p>InSyte CRM helps businesses manage leads, customer information, sales activities and related operations through a centralized cloud platform. Security and appropriate data handling are important components of the platform.</p>

    <h2 class="mt-8 text-xl font-semibold text-black">1. Data Processing</h2>
    <p>When a customer uses InSyte to process information about its customers, prospects or other individuals, the customer generally determines the purpose of that processing. InSyte processes that information as necessary to provide the Services. Customers remain responsible for ensuring that their collection, use and disclosure of personal information is lawful.</p>
    <p class="mt-3">InSyte is designed to support responsible handling of customer and personal information. Customers remain responsible for configuring and using the Services in accordance with applicable law.</p>

    <h2 class="mt-8 text-xl font-semibold text-black">2. Tenant Architecture</h2>
    <p>InSyte is built as a multi-tenant application. Each company operates as its own tenant. The architecture uses separate tenant databases and tenant-specific isolation for cache, files and queues. The central platform environment is separated from individual tenant CRM environments.</p>

    <h2 class="mt-8 text-xl font-semibold text-black">3. Authentication &amp; Permissions</h2>
    <p>InSyte provides authentication for platform and tenant environments. Organizations can control access with configurable roles and permissions across leads, activities, revenue, teams, settings, AI and other functions. Customers are responsible for maintaining credentials and user access.</p>

    <h2 class="mt-8 text-xl font-semibold text-black">4. Integrations &amp; API Access</h2>
    <p>API and webhook integrations may use bearer tokens, OAuth, verification tokens, webhook secrets or other integration-specific controls. The Lead API uses tenant authorization credentials. Customers must keep credentials and secrets confidential and contact support promptly if compromise is suspected.</p>

    <h2 class="mt-8 text-xl font-semibold text-black">5. AI Processing</h2>
    <p>InSyte AI OS can interact with permitted CRM data to perform supported actions such as searching leads, checking today’s agenda, scheduling or completing follow-ups and site visits, creating or completing tasks and adding notes. AI access is subject to permissions and plan controls and should not replace human review.</p>

    <h2 class="mt-8 text-xl font-semibold text-black">6. Customer Security Responsibilities</h2>
    <p>Customers should protect credentials, control user access, assign appropriate permissions, remove former users, protect API credentials, review connected integrations, maintain device security, avoid unnecessary sensitive information and report suspected unauthorized access.</p>

    <h2 class="mt-8 text-xl font-semibold text-black">7. Security Measures &amp; Incidents</h2>
    <p>We maintain reasonable technical and organizational measures appropriate to the Services, which may include tenant isolation, authentication, authorization, role-based access, application and integration security, access management and monitoring. No system can be guaranteed completely secure; we do not warrant immunity from every threat, unauthorized access, data loss or cyberattack. If InSyte becomes aware of a security incident affecting customer information, we will assess it and take appropriate response measures, with notification where required by law or contract.</p>

    <h2 class="mt-8 text-xl font-semibold text-black">8. Contact</h2>
    <ul class="list-disc space-y-1 pl-6">
        <li>Security: <a href="mailto:development@insytecrm.com" class="text-brand-accent hover:underline">development@insytecrm.com</a></li>
        <li>Privacy: <a href="mailto:legal@insytecrm.com" class="text-brand-accent hover:underline">legal@insytecrm.com</a></li>
        <li>Support: <a href="mailto:support@insytecrm.com" class="text-brand-accent hover:underline">support@insytecrm.com</a></li>
        <li>Sales: <a href="mailto:hello@insytecrm.com" class="text-brand-accent hover:underline">hello@insytecrm.com</a></li>
    </ul>
</x-legal-layout>
