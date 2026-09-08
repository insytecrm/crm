<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('leadWhatsAppSender', (config) => ({
            templates: [],
            selectedId: '',
            message: '',
            leadName: '',
            hasPhone: true,
            whatsappDigits: '',
            loading: false,
            error: null,

            get selectedTemplate() {
                return this.templates.find((template) => String(template.id) === String(this.selectedId)) ?? null;
            },

            get sendUrl() {
                return config.sendUrlTemplate.replace('__ID__', String(this.leadId || ''));
            },

            get canSend() {
                return ! this.loading
                    && this.hasPhone
                    && this.whatsappDigits
                    && this.message.trim() !== '';
            },

            leadId: null,

            async open(leadId) {
                this.leadId = leadId;
                this.loading = true;
                this.error = null;
                this.templates = [];
                this.selectedId = '';
                this.message = '';
                this.leadName = '';
                this.hasPhone = true;
                this.whatsappDigits = '';

                try {
                    const response = await fetch(config.showUrlTemplate.replace('__ID__', String(leadId)), {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json().catch(() => ({}));

                    if (! response.ok) {
                        this.error = data.message || @json(__('Could not load WhatsApp templates.'));
                        this.loading = false;

                        return;
                    }

                    this.leadName = data.lead_name || '';
                    this.hasPhone = data.has_phone !== false;
                    this.whatsappDigits = data.whatsapp_digits || '';
                    this.templates = data.templates || [];
                    this.selectedId = this.templates[0]?.id ?? '';
                    this.applyTemplate();
                } catch {
                    this.error = @json(__('Could not load WhatsApp templates.'));
                } finally {
                    this.loading = false;
                }
            },

            applyTemplate() {
                this.message = this.selectedTemplate?.preview || '';
            },

            composeUrl() {
                if (! this.whatsappDigits || this.message.trim() === '') {
                    return null;
                }

                return 'https://web.whatsapp.com/send?phone=' + this.whatsappDigits + '&text=' + encodeURIComponent(this.message);
            },

            openWhatsApp() {
                const url = this.composeUrl();

                if (url) {
                    window.open(url, '_blank', 'noopener,noreferrer');
                }
            },
        }));
    });
</script>

<div
    x-data="leadWhatsAppSender({
        showUrlTemplate: @js(route('tenant.leads.whatsapp.show', ['lead' => '__ID__'])),
        sendUrlTemplate: @js(route('tenant.leads.whatsapp.store', ['lead' => '__ID__'])),
    })"
    @prepare-whatsapp.window="open($event.detail)"
>
    <x-modal name="send-whatsapp" maxWidth="lg" focusable>
        <x-ui.modal.header
            :title="__('Send WhatsApp')"
            :description="__('Choose a template or write your own message, then open WhatsApp Web.')"
            modal-name="send-whatsapp"
        >
            <x-slot:icon>
                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" />
                </svg>
            </x-slot:icon>
        </x-ui.modal.header>

        <form method="POST" :action="sendUrl">
            @csrf

            <x-ui.modal.body>
                <p class="text-sm text-slate-500" x-show="leadName" x-cloak>
                    {{ __('Message for') }} <span class="font-semibold text-black" x-text="leadName"></span>
                </p>

                <p class="text-sm text-red-600" x-show="error" x-text="error" x-cloak></p>
                <p class="text-sm text-slate-500" x-show="loading" x-cloak>{{ __('Loading…') }}</p>

                <div class="flex flex-col gap-4" x-show="! loading" x-cloak>
                    <div>
                        <x-ui.modal.field-label for="whatsapp_template_id" :value="__('Select template')" />
                        <select
                            id="whatsapp_template_id"
                            name="template_id"
                            x-model="selectedId"
                            @change="applyTemplate()"
                            class="mt-1 block w-full rounded-lg border-slate-200 text-sm text-black shadow-sm focus:border-navy focus:ring-navy"
                        >
                            <option value="">{{ __('Custom message') }}</option>
                            <template x-for="template in templates" :key="template.id">
                                <option :value="template.id" x-text="template.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <x-ui.modal.field-label for="whatsapp_message" :value="__('Message')" required />
                        <textarea
                            id="whatsapp_message"
                            name="message"
                            x-model="message"
                            rows="7"
                            maxlength="5000"
                            required
                            class="mt-1 block min-h-32 w-full rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50/80 to-white px-3 py-3 text-sm leading-relaxed text-slate-700 shadow-sm whitespace-pre-wrap focus:border-navy focus:ring-navy"
                            placeholder="{{ __('Write the message that will be sent.') }}"
                        ></textarea>
                    </div>
                </div>
            </x-ui.modal.body>

            <x-ui.modal.footer>
                <x-ui.modal.cancel-button modal-name="send-whatsapp" />
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-[#25D366] px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#20bd5a] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                    :disabled="! canSend"
                    @click="openWhatsApp()"
                >
                    {{ __('Send message') }}
                </button>
            </x-ui.modal.footer>
        </form>
    </x-modal>
</div>
