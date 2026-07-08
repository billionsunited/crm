<!-- 
    Email Messaging Modal Component
    This modal handles the selection and sending of Email Templates to leads.
    It supports both individual lead messaging and bulk messaging.
-->
<div x-data="emailMessagingModal" @open-email-modal.window="openModal($event.detail)" x-show="isOpen"
    class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6" style="display: none;" x-cloak>

    <!-- Backdrop -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal()"></div>

    <!-- Modal Content Box -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 z-10 flex flex-col"
        style="max-height: 85vh; display: flex; flex-direction: column;">

        <!-- Header -->
        <div class="px-6 py-6 sm:px-8 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Send Email Marketing
                </h3>
                <button @click="closeModal()"
                    class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Scrollable Modal Body -->
        <div class="flex-1 overflow-y-auto px-6 py-6 sm:px-8 space-y-6" style="min-height: 0;">
            <!-- Recipient Display Area -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider" x-text="sendToAll ? 'Campaign Mode' : (sendToFiltered ? 'Filtered Mode' : 'Bulk Mode')"></span>
                        <span class="text-slate-900 font-bold" x-text="sendToAll ? 'All Customers' : (sendToFiltered ? 'Filtered Records' : bulkIds.length + ' leads selected')"></span>
                    </div>
                </div>
            </div>

            <!-- Campaign Options -->
            <div class="space-y-3">
                <div class="flex items-center gap-3 p-4 bg-indigo-50 border border-indigo-100 rounded-xl">
                    <input type="checkbox" x-model="sendToAll" id="email_send_to_all" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                    <label for="email_send_to_all" class="text-sm font-bold text-indigo-900 cursor-pointer">Send to ALL Leads / Customers</label>
                </div>

                <div x-show="isFilteredCampaign" class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-100 rounded-xl">
                    <input type="checkbox" x-model="sendToFiltered" id="email_send_to_filtered" class="w-5 h-5 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <label for="email_send_to_filtered" class="text-sm font-bold text-blue-900 cursor-pointer">Send to ALL Filtered Records</label>
                </div>
            </div>

            <!-- Template Selection -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Select Template</label>
                <div class="relative">
                    <select x-model="selectedTemplateId" class="block w-full h-12 pl-4 pr-10 bg-white border border-slate-300 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm">
                        <option value="">Choose an email template...</option>
                        @foreach(\App\Models\EmailTemplate::all() as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Design Theme Selection -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Select Layout Theme</label>
                <div class="relative">
                    <select x-model="selectedTheme" class="block w-full h-12 pl-4 pr-10 bg-white border border-slate-300 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm">
                        <option value="default">Modern Navy (Navy & Orange)</option>
                        <option value="emerald">Elegant Emerald (Emerald & Gold)</option>
                        <option value="purple">Minimalist Purple (Purple & Violet)</option>
                        <option value="charcoal">Creative Charcoal (Charcoal & Amber)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm bg-slate-50 p-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-3">Live Design Preview</span>
                <div class="w-full bg-white border border-slate-200 rounded-lg overflow-hidden shadow-inner max-w-sm mx-auto">
                    <!-- Preview Header -->
                    <div class="py-2.5 px-4 text-center transition-all duration-300"
                        :style="selectedTheme === 'default' ? 'background-color: #0f172a;' : 
                               (selectedTheme === 'emerald' ? 'background-color: #064e3b;' : 
                               (selectedTheme === 'purple' ? 'background-color: #1e1b4b;' : 
                                                             'background-color: #111827;'))">
                        <span class="text-xs font-black text-white uppercase tracking-widest" x-text="selectedTheme === 'purple' || selectedTheme === 'charcoal' ? 'UPDATES' : 'Billions United'">Billions United</span>
                    </div>
                    
                    <!-- Preview Body -->
                    <div class="p-4 text-left">
                        <div class="w-24 h-2 bg-slate-200 rounded mb-2"></div>
                        <div class="w-full h-2.5 bg-slate-100 rounded mb-1.5"></div>
                        <div class="w-full h-2.5 bg-slate-100 rounded mb-1.5"></div>
                        <div class="w-3/4 h-2.5 bg-slate-100 rounded"></div>
                    </div>
                    
                    <!-- Preview Footer Graphic -->
                    <div class="h-6 transition-all duration-300"
                        :style="selectedTheme === 'default' ? 'background: linear-gradient(15deg, #000000 0%, #000000 35%, #f97316 35%, #f97316 45%, #ef4444 45%, #ef4444 100%);' : 
                               (selectedTheme === 'emerald' ? 'background: linear-gradient(15deg, #022c22 0%, #022c22 35%, #d97706 35%, #d97706 45%, #10b981 45%, #10b981 100%);' : 
                               (selectedTheme === 'purple' ? 'background: linear-gradient(15deg, #2e1065 0%, #2e1065 35%, #db2777 35%, #db2777 45%, #8b5cf6 45%, #8b5cf6 100%);' : 
                                                             'background: linear-gradient(15deg, #030712 0%, #030712 35%, #f59e0b 35%, #f59e0b 45%, #6b7280 45%, #6b7280 100%);'))">
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-slate-50 px-6 py-4 sm:px-8 border-t border-slate-100 flex items-center justify-end gap-3">
            <button @click="closeModal()" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                Cancel
            </button>
            <button @click="sendEmail()" :disabled="isSending || !selectedTemplateId" class="px-8 py-2.5 text-sm font-bold text-white bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-md shadow-indigo-200 disabled:opacity-50 flex items-center gap-2">
                <template x-if="isSending">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="isSending ? 'Sending...' : 'Send Email Marketing'"></span>
            </button>
        </div>

        <!-- Progress Overlay -->
        <template x-if="isSending">
            <div class="absolute inset-0 bg-white/90 backdrop-blur-sm z-[100] flex flex-col items-center justify-center p-8 text-center">
                <div class="w-full max-w-xs bg-slate-100 rounded-full h-3 mb-6 overflow-hidden border border-slate-200">
                    <div class="bg-indigo-600 h-full rounded-full transition-all duration-300" style="width: 100%; animation: pulse 2s infinite;"></div>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-1" x-text="stopRequested ? 'Stopping Campaign...' : 'Sending Email Marketing'"></h4>
                <p class="text-sm text-slate-500 mb-6" x-text="stopRequested ? 'Please wait while we halt the process...' : 'Please wait while we process the emails...'"></p>
                <button type="button" @click="stopCampaign()" x-show="!stopRequested" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold transition-colors shadow-sm">
                    Stop Campaign
                </button>
            </div>
        </template>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('emailMessagingModal', () => ({
            isOpen: false,
            isSending: false,
            stopRequested: false,
            selectedTemplateId: '',
            selectedTheme: 'default',
            bulkIds: [],
            isFilteredCampaign: false,
            sendToAll: false,
            sendToFiltered: false,
            filters: {},
            emailCampaignRoute: '{{ route("leads.send_email_campaign") }}',

            openModal(data) {
                this.isOpen = true;
                this.bulkIds = data.bulkIds || [];
                this.isFilteredCampaign = data.isFilteredCampaign || false;
                this.filters = data.filters || {};
                this.sendToFiltered = this.isFilteredCampaign;
                this.sendToAll = false;
                this.selectedTheme = 'default';
                this.emailCampaignRoute = data.emailCampaignRoute || '{{ route("leads.send_email_campaign") }}';
            },

            closeModal() {
                if (!this.isSending) {
                    this.isOpen = false;
                }
            },

            async sendEmail() {
                if (!this.selectedTemplateId) return;

                this.isSending = true;
                
                try {
                    const response = await fetch(this.emailCampaignRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            template_id: this.selectedTemplateId,
                            theme: this.selectedTheme,
                            ids: this.bulkIds.join(','),
                            is_filtered_campaign: (this.sendToFiltered || this.sendToAll) ? 'true' : 'false',
                            ...(this.sendToAll ? {} : this.filters)
                        })
                    });

                    let result;
                    const contentType = response.headers.get("content-type");
                    
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        result = await response.json();
                    } else {
                        const text = await response.text();
                        console.error('Server returned non-JSON response:', text);
                        throw new Error('Server returned an invalid response (HTML). This usually means a session timeout or a critical server error.');
                    }
                    
                    if (result.success) {
                        const msg = this.stopRequested ? 'Campaign stopped manually. ' + result.message : result.message;
                        alert(msg);
                        this.isOpen = false;
                        this.selectedTemplateId = '';
                        
                        // Wait 2 seconds then reload the page
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Email campaign error:', error);
                    alert('Email campaign error: ' + error.message);
                } finally {
                    this.isSending = false;
                    this.stopRequested = false;
                }
            },

            async stopCampaign() {
                this.stopRequested = true;
                try {
                    await fetch('{{ route("campaigns.stop") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                } catch (e) {
                    console.error('Failed to send stop signal', e);
                }
            }
        }));
    });
</script>

<style>
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
</style>
