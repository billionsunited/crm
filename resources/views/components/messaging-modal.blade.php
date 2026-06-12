<!-- 
    Messaging Modal Component
    This modal handles the selection and sending of WhatsApp and RCS messages.
    It supports both individual lead messaging and bulk messaging.
-->
<div x-data="messagingModal" @open-messaging-modal.window="openModal($event.detail)" x-show="isOpen"
    class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6" style="display: none;" x-cloak>

    <!-- Backdrop: Darkens the background when the modal is open -->
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
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 z-10 flex flex-col max-h-[90vh]">

        <!-- Header: Contains Title and Close Button -->
        <div class="px-6 py-6 sm:px-8 border-b border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <!-- WhatsApp Icon (Visible if type is whatsapp) -->
                    <template x-if="type === 'whatsapp'">
                        <svg class="w-6 h-6 text-emerald-500" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.412-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.309 1.656zm6.222-4.032c1.53.93 3.069 1.403 4.662 1.403 5.888 0 10.68-4.791 10.682-10.679-.001-2.852-1.111-5.532-3.127-7.548-2.016-2.015-4.694-3.125-7.55-3.125-5.898 0-10.678 4.79-10.681 10.679 0 1.908.503 3.758 1.458 5.335l-.95 3.468 3.506-.933zm12.733-9.149c-.29-.145-1.713-.847-1.978-.944-.265-.097-.459-.145-.651.145-.191.29-.74.944-.906 1.139-.167.194-.332.218-.621.073-.29-.145-1.22-.45-2.323-1.433-.859-.767-1.439-1.714-1.606-2.004-.167-.29-.018-.448.126-.591.13-.13.29-.338.435-.507.145-.169.194-.29.29-.483.097-.194.048-.362-.024-.507-.073-.145-.651-1.571-.891-2.151-.233-.565-.47-.488-.651-.497-.169-.008-.362-.01-.554-.01-.193 0-.507.073-.772.362-.265.29-1.013.991-1.013 2.417 0 1.427 1.038 2.804 1.183 2.997.145.194 2.041 3.117 4.945 4.371.689.298 1.228.476 1.649.61.693.22 1.324.189 1.823.114.557-.084 1.713-.7 1.953-1.377.242-.677.242-1.257.17-1.377-.073-.121-.265-.194-.554-.34z" />
                        </svg>
                    </template>
                    <!-- RCS Icon (Visible if type is rcs) -->
                    <template x-if="type === 'rcs'">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                    </template>
                    Send <span x-text="type === 'whatsapp' ? 'WhatsApp' : (type === 'rcs' ? 'RCS' : 'SMS')"></span>
                </h3>
                <button @click="closeModal()"
                    class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Toggle Switch: Switch between WhatsApp and RCS channels -->
            <div class="flex p-1 bg-slate-100 rounded-xl">
                <button @click="type = 'whatsapp'; resetForm(); fetchTemplates();"
                    class="flex-1 py-2 text-sm font-bold rounded-lg transition-all"
                    :class="type === 'whatsapp' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                    WhatsApp
                </button>
                <button @click="type = 'rcs'; resetForm(); fetchRcsTemplates(); fetchRcsTemplate();"
                    class="flex-1 py-2 text-sm font-bold rounded-lg transition-all"
                    :class="type === 'rcs' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                    RCS
                </button>
                <button @click="type = 'sms'; resetForm(); fetchSmsTemplates();"
                    class="flex-1 py-2 text-sm font-bold rounded-lg transition-all"
                    :class="type === 'sms' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                    SMS
                </button>
            </div>
        </div>

        <!-- Scrollable Modal Body -->
        <div class="flex-1 overflow-y-auto px-6 py-6 sm:px-8 space-y-6">
            <!-- Recipient Display Area -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 mb-6">
                <!-- Single Recipient View -->
                <template x-if="bulkIds.length <= 1 && !sendToAll">
                    <div class="flex justify-between items-start">
                        <div>
                            <span
                                class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Recipient</span>
                            <span class="text-slate-900 font-bold" x-text="leadName"></span>
                        </div>
                        <div class="text-right">
                            <span
                                class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Mobile</span>
                            <span class="text-slate-900 font-bold" x-text="leadMobile"></span>
                        </div>
                    </div>
                </template>
                <!-- Bulk Recipients View -->
                <template x-if="bulkIds.length > 1 || sendToAll || sendToFiltered">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider"
                                    x-text="sendToAll || sendToFiltered ? 'Campaign Mode' : 'Bulk Recipients'"></span>
                                <span class="text-slate-900 font-bold"
                                    x-text="sendToAll ? ('All Customers (' + totalCount + ')') : (sendToFiltered ? ('Filtered Records (' + filteredCount + ')') : (bulkIds.length + ' leads selected'))"></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md"
                                x-text="sendToAll ? 'Mass Broadcast' : 'Batch Process'"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Send to All Checkbox -->
            <div class="flex items-center gap-3 p-4 bg-indigo-50 border border-indigo-100 rounded-xl mb-6">
                <input type="checkbox" x-model="sendToAll" id="send_to_all_checkbox"
                    class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                <div class="flex flex-col">
                    <label for="send_to_all_checkbox" class="text-sm font-bold text-indigo-900 cursor-pointer">
                        Send to ALL Leads / Customers
                    </label>
                    <span class="text-xs text-indigo-600 mt-0.5 font-medium">This will broadcast to every unique mobile
                        number in your database. 
                        <span x-show="totalCount > 0" class="font-bold">(Total: <span x-text="totalCount"></span>)</span>
                    </span>
                </div>
            </div>

            <!-- Send to Filtered Checkbox -->
            <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-100 rounded-xl mb-6"
                x-show="isFilteredCampaign">
                <input type="checkbox" x-model="sendToFiltered" id="send_to_filtered_checkbox"
                    class="w-5 h-5 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                <div class="flex flex-col">
                    <label for="send_to_filtered_checkbox" class="text-sm font-bold text-blue-900 cursor-pointer">
                        Send to ALL Filtered Records
                    </label>
                    <span class="text-xs text-blue-600 mt-0.5 font-medium">
                        This will broadcast to every lead matching your current filters.
                        <span x-show="filteredCount > 0" class="font-bold">(Total: <span x-text="filteredCount"></span>)</span>
                    </span>
                </div>
            </div>

            <!-- WhatsApp Configuration Section -->
            <template x-if="type === 'whatsapp'">
                <div class="space-y-4">
                    <!-- Template Dropdown selector -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Select Template</label>
                        <div class="relative">
                            <select x-model="selectedTemplateName" @change="onTemplateChange()"
                                class="block w-full h-12 pl-4 pr-10 bg-white border border-slate-300 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                                :disabled="isLoadingTemplates">
                                <option value="">Choose a template...</option>
                                <template x-for="tpl in templates" :key="tpl.templateName">
                                    <option :value="tpl.templateName" x-text="tpl.templateName"></option>
                                </template>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                <template x-if="isLoadingTemplates">
                                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </template>
                                <template x-if="!isLoadingTemplates">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </template>
                            </div>
                        </div>
                    </div>


                    <!-- Dynamic Body Parameters: Generated based on {{1}}, {{2}} in template body -->
                    <template x-if="parameters.length > 0">
                        <div class="space-y-4 pt-2">
                            <h4
                                class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">
                                Template Parameters</h4>
                            <template x-for="(param, index) in parameters" :key="index">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1.5"
                                        x-text="'Parameter ' + (index + 1)"></label>
                                    <input type="text" x-model="paramValues[index]"
                                        class="block w-full h-11 px-4 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                                        :placeholder="'Value for {{' + (index + 1) + '}}'">
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <!-- RCS Configuration Section -->
            <template x-if="type === 'rcs'">
                <div class="space-y-4">
                    <!-- RCS Template Dropdown selector -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Select Template</label>
                        <div class="relative">
                            <select x-model="rcsTemplateName" @change="fetchRcsTemplate()"
                                class="block w-full h-12 pl-4 pr-10 bg-white border border-slate-300 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                                :disabled="isLoadingRcsTemplates">
                                <option value="">Choose a template...</option>
                                <template x-for="tplName in rcsTemplates" :key="tplName">
                                    <option :value="tplName" x-text="tplName"></option>
                                </template>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                <template x-if="isLoadingRcsTemplates">
                                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </template>
                                <template x-if="!isLoadingRcsTemplates">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- RCS Dynamic Fields: Auto-generated from template JSON -->
                    <template x-if="rcsFields.length > 0">
                        <div class="space-y-4 pt-2">
                            <h4
                                class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">
                                Custom Parameters</h4>
                            <template x-for="field in rcsFields" :key="field">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1.5"
                                        x-text="field"></label>
                                    <input type="text" x-model="rcsParamValues[field]"
                                        class="block w-full h-11 px-4 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                                        :placeholder="'Value for ' + field">
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Active RCS Template Code Display -->
                    <template x-if="rcsTemplateCode">
                        <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl">
                            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider block mb-1">Active
                                Template Code</span>
                            <span class="text-base font-bold text-blue-900 font-mono" x-text="rcsTemplateCode"></span>
                        </div>
                    </template>
                </div>
            </template>

            <!-- SMS Configuration Section -->
            <template x-if="type === 'sms'">
                <div class="space-y-4">
                    <!-- SMS Template Dropdown selector -->
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-bold text-slate-700">Select SMS Template</label>
                        <button @click="smsTemplates = []; fetchSmsTemplates()"
                            class="text-[10px] text-orange-600 font-bold hover:underline">
                            Refresh List
                        </button>
                    </div>
                    <div class="relative">
                        <select x-model="smsTemplateId" @change="onSmsTemplateChange()"
                            class="block w-full h-12 pl-4 pr-10 bg-white border border-slate-300 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all shadow-sm"
                            :disabled="isLoadingSmsTemplates">
                            <option value="">Choose a template...</option>
                            <template x-for="tpl in smsTemplates" :key="tpl.templateId">
                                <option :value="tpl.templateId" x-text="tpl.templateName"></option>
                            </template>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                            <template x-if="isLoadingSmsTemplates">
                                <svg class="animate-spin h-5 w-5 text-orange-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </template>
                            <template x-if="!isLoadingSmsTemplates">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- SMS Dynamic Fields: Generated from {#Var#} in templateContent -->
                <template x-if="smsFieldsCount > 0">
                    <div class="space-y-4 pt-2">
                        <h4
                            class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">
                            SMS Parameters</h4>
                        <template x-for="i in smsFieldsCount" :key="i">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5"
                                    x-text="'Variable ' + i"></label>
                                <input type="text" x-model="smsParamValues[i-1]" @input="updateSmsPreview()"
                                    class="block w-full h-11 px-4 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all shadow-sm"
                                    :placeholder="'Value for Variable ' + i">
                            </div>
                        </template>
                    </div>
                </template>

                <!-- SMS Preview area -->
                <div class="p-4 bg-orange-50 border border-orange-100 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-orange-600 uppercase tracking-wider">Message
                            Preview</span>
                        <span class="text-[10px] text-slate-400" x-text="smsPreview.length + ' chars'"></span>
                    </div>
                    <div class="text-sm text-slate-700 whitespace-pre-line leading-relaxed italic" x-text="smsPreview">
                    </div>
                </div>
        </div>
        </template>
    </div>

    <!-- Modal Footer: Action Buttons -->
    <div class="bg-slate-50 px-6 py-4 sm:px-8 border-t border-slate-100 flex items-center justify-end gap-3">
        <button @click="closeModal()"
            class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
            Cancel
        </button>
        <button @click="sendMessage()"
            class="px-8 py-2.5 text-sm font-bold text-white bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-md shadow-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            :disabled="isSending || 
                               (type === 'whatsapp' && (!selectedTemplateName || (headerType === 'IMAGE' && !headerImageUrl))) || 
                               (type === 'rcs' && !rcsTemplateName) ||
                               (type === 'sms' && !smsPreview)">
            <template x-if="isSending">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </template>
            <span x-text="isSending ? 'Sending...' : 'Send Message'"></span>
        </button>
    </div>

    <!-- Progress Overlay: Only visible during bulk sending operations -->
    <template x-if="isSending && (bulkIds.length > 1 || sendToAll || sendToFiltered)">
        <div
            class="absolute inset-0 bg-white/90 backdrop-blur-sm z-[100] flex flex-col items-center justify-center p-8 text-center animate-in fade-in duration-300">
            <div class="w-full max-w-xs bg-slate-100 rounded-full h-3 mb-6 overflow-hidden border border-slate-200">
                <div class="bg-indigo-600 h-full rounded-full transition-all duration-300 shadow-[0_0_10px_rgba(79,70,229,0.4)]"
                    :style="'width: ' + sendingProgress + '%'"></div>
            </div>
            <h4 class="text-lg font-bold text-slate-900 mb-1">Sending Messages</h4>
            <p class="text-sm text-slate-600 font-medium mb-1" x-text="'Processing: ' + currentRecipientName"></p>
            <p class="text-xs text-slate-400" x-text="Math.round(sendingProgress) + '% completed'"></p>
        </div>
    </template>
</div>
</div>

<script>
    /**
     * Alpine.js component logic for the Messaging Modal.
     */
    document.addEventListener('alpine:init', () => {
        Alpine.data('messagingModal', () => ({
            isOpen: false,
            type: 'whatsapp', // Default channel
            leadId: null,
            leadName: '',
            leadMobile: '',
            bulkIds: [], // Stores multiple lead IDs if bulk messaging is active

            // WhatsApp State Variables
            templates: [],
            selectedTemplateName: '',
            headerImageUrl: 'https://billionsunited.com/assets/logo.png',
            headerType: 'TEXT',
            isLoadingTemplates: false,
            parameters: [],
            paramValues: [],

            // RCS State Variables
            rcsTemplates: [],
            rcsTemplateName: '',
            rcsTemplateCode: '',
            rcsBotId: '',
            rcsFields: [],
            rcsParamValues: {},
            isLoadingRcsTemplates: false,
            isLoadingRcsTemplate: false,

            // SMS State Variables
            smsTemplates: [],
            smsTemplateId: '',
            selectedSmsTemplate: null,
            smsParamValues: [],
            smsFieldsCount: 0,
            isLoadingSmsTemplates: false,
            smsPreview: '',

            isSending: false,
            sendingProgress: 0,
            currentRecipientName: '',
            sendToAll: false,
            sendToFiltered: false,
            isFilteredCampaign: false,
            filters: {},
            campaignRoute: null,
            allContactsRoute: '{{ route("messaging.all_contacts") }}',
            filteredContactsRoute: '{{ route("messaging.filtered_contacts") }}',
            allLeads: [],
            isFetchingAll: false,
            filteredCount: 0,
            totalCount: 0,
            isFetchingCounts: false,

            /**
             * Triggered by the 'open-messaging-modal' window event.
             */
            openModal(data) {
                this.isOpen = true;
                this.sendToAll = false;
                this.sendToFiltered = data.isFilteredCampaign || false;
                this.isFilteredCampaign = data.isFilteredCampaign || false;
                this.filters = data.filters || {};
                this.type = data.type || 'whatsapp';
                this.leadId = data.leadId;
                this.leadName = data.leadName;
                this.leadMobile = data.leadMobile;
                this.bulkIds = data.bulkIds || [];
                this.campaignRoute = data.campaignRoute || null;
                this.allContactsRoute = data.allContactsRoute || '{{ route("messaging.all_contacts") }}';
                this.filteredContactsRoute = data.filteredContactsRoute || '{{ route("messaging.filtered_contacts") }}';

                this.resetForm(data.isFilteredCampaign);
                this.fetchCounts();

                // Auto-fetch templates if WhatsApp or RCS is active
                if (this.type === 'whatsapp') {
                    this.fetchTemplates();
                } else if (this.type === 'rcs') {
                    this.fetchRcsTemplates();
                    this.fetchRcsTemplate();
                } else if (this.type === 'sms') {
                    this.fetchSmsTemplates();
                }
            },

            closeModal() {
                this.isOpen = false;
            },

            /**
             * Resets all input fields to their original state.
             */
            resetForm(isFiltered = false) {
                this.selectedTemplateName = '';
                this.headerImageUrl = 'https://billionsunited.com/assets/logo.png';
                this.headerType = 'TEXT';
                this.parameters = [];
                this.paramValues = [];
                this.rcsTemplateName = 'customer_reach';
                this.rcsTemplateCode = '';
                this.rcsBotId = '';
                this.rcsFields = [];
                this.rcsParamValues = {};
                this.isSending = false;
                this.sendingProgress = 0;
                this.currentRecipientName = '';
                this.sendToFiltered = isFiltered;
                this.filteredCount = 0;
                this.totalCount = 0;

                // SMS reset
                this.smsTemplateId = '';
                this.selectedSmsTemplate = null;
                this.smsParamValues = [];
                this.smsFieldsCount = 0;
                this.smsPreview = '';
            },

            /**
             * AJAX call to fetch WhatsApp templates from the backend.
             * Normalizes the response data to handle various API formats.
             */
            async fetchTemplates() {
                if (this.templates.length > 0) return;

                this.isLoadingTemplates = true;
                try {
                    const response = await fetch('{{ route("messaging.whatsapp.templates") }}');
                    const result = await response.json();

                    let rawTemplates = [];
                    if (result.data && Array.isArray(result.data)) {
                        rawTemplates = result.data;
                    } else if (Array.isArray(result)) {
                        rawTemplates = result;
                    }

                    // Map raw API data to a consistent template object
                    this.templates = rawTemplates.map(t => {
                        const name = t.templateName || t.name || t.template_name || '';
                        const body = t.body || t.template_body || t.content || '';
                        const status = t.status || t.template_status || 'APPROVED';
                        const language = t.language || t.languageCode || 'en';
                        const type = t.type || t.template_type || t.header_type || t.category || 'TEXT';

                        return {
                            templateName: name,
                            body: body,
                            status: status,
                            language: language,
                            type: type.toString().toUpperCase()
                        };
                    }).filter(t => t.templateName && (t.status === 'APPROVED' || t.status === 'active'));

                } catch (error) {
                    console.error('Fetch templates error:', error);
                } finally {
                    this.isLoadingTemplates = false;
                }
            },

            /**
             * AJAX call to fetch RCS template list.
             */
            async fetchRcsTemplates() {
                // Remove the early return to force a fetch
                this.isLoadingRcsTemplates = true;
                try {
                    const response = await fetch('{{ route("messaging.rcs.templates") }}');
                    const result = await response.json();

                    let rawTemplates = [];
                    if (Array.isArray(result)) {
                        rawTemplates = result;
                    } else if (result.data && Array.isArray(result.data)) {
                        rawTemplates = result.data;
                    } else if (result.templates && Array.isArray(result.templates)) {
                        rawTemplates = result.templates;
                    } else if (result.templateList && Array.isArray(result.templateList)) {
                        rawTemplates = result.templateList;
                    }

                    // Map to a list of template names (strings)
                    this.rcsTemplates = rawTemplates.map(t => {
                        if (typeof t === 'string') return t;
                        return t.templateName || t.name || t.templateCode || '';
                    }).filter(t => t !== '');
                } catch (error) {
                    console.error('Fetch RCS templates error:', error);
                } finally {
                    this.isLoadingRcsTemplates = false;
                }
            },

            /**
             * AJAX call to fetch RCS template JSON structure.
             * Identifies which parameters are required for the given template.
             */
            async fetchRcsTemplate() {
                if (!this.rcsTemplateName) {
                    this.rcsFields = [];
                    this.rcsParamValues = {};
                    this.rcsTemplateCode = '';
                    return;
                }

                this.isLoadingRcsTemplate = true;
                this.rcsFields = [];
                this.rcsParamValues = {};
                this.rcsTemplateCode = '';

                try {
                    const response = await fetch('{{ route("messaging.rcs.template") }}?template_name=' + encodeURIComponent(this.rcsTemplateName));
                    const result = await response.json();

                    if (result.success && result.data) {
                        const content = result.data.contentMessage;
                        this.rcsTemplateCode = content.templateMessage.templateCode;
                        this.rcsBotId = content.botId;

                        const params = content.templateMessage.customParams;
                        if (params) {
                            this.rcsFields = Object.keys(params);
                            this.rcsFields.forEach(field => {
                                this.rcsParamValues[field] = params[field];
                            });
                        }
                    } else {
                        alert(result.error || 'Failed to fetch RCS template structure.');
                    }
                } catch (error) {
                    console.error('Fetch RCS template error:', error);
                    alert('An error occurred while fetching the RCS template.');
                } finally {
                    this.isLoadingRcsTemplate = false;
                }
            },

            /**
             * AJAX call to fetch SMS templates.
             */
            async fetchSmsTemplates() {
                if (this.smsTemplates.length > 0) return;

                this.isLoadingSmsTemplates = true;
                try {
                    const response = await fetch('{{ route("messaging.sms.templates") }}');
                    const result = await response.json();

                    console.log('SMS Templates Result:', result);

                    if (result && result.templateList && Array.isArray(result.templateList)) {
                        this.smsTemplates = result.templateList;
                    } else if (Array.isArray(result)) {
                        this.smsTemplates = result;
                    } else if (result && result.data && Array.isArray(result.data)) {
                        this.smsTemplates = result.data;
                    }

                    // Auto-select if only one template exists
                    if (this.smsTemplates.length === 1) {
                        this.smsTemplateId = this.smsTemplates[0].templateId;
                        this.onSmsTemplateChange();
                    }
                } catch (error) {
                    console.error('Fetch SMS templates error:', error);
                } finally {
                    this.isLoadingSmsTemplates = false;
                }
            },

            /**
             * Fetches counts for filtered and all leads.
             */
            async fetchCounts() {
                this.isFetchingCounts = true;
                try {
                    // Fetch filtered count if filters are active
                    if (this.isFilteredCampaign) {
                        const params = new URLSearchParams(this.filters);
                        const response = await fetch(this.filteredContactsRoute + (this.filteredContactsRoute.includes('?') ? '&' : '?') + params.toString());
                        const result = await response.json();
                        this.filteredCount = result.count || 0;
                    }

                    // Fetch total count for "Send to ALL"
                    const responseAll = await fetch(this.allContactsRoute);
                    const resultAll = await responseAll.json();
                    this.totalCount = resultAll.count || 0;
                } catch (error) {
                    console.error('Error fetching counts:', error);
                } finally {
                    this.isFetchingCounts = false;
                }
            },

            /**
             * Logic for when an SMS template is selected.
             */
            onSmsTemplateChange() {
                if (!this.smsTemplateId) {
                    this.selectedSmsTemplate = null;
                    this.smsFieldsCount = 0;
                    this.smsParamValues = [];
                    this.smsPreview = 'Dear ' + this.leadName + ',\nThank you for choosing our marketing services. We truly appreciate the opportunity to work with you and support your business goals.\nWe are excited to embark on this journey together and are committed to delivering strategies and solutions that drive meaningful results for your brand.\nShould you require any further information or assistance, please do not hesitate to contact us.\nThank you once again for your association and trust in us.\nBest regards,\nBillions United Team\nwww.billionsunited.com';
                    return;
                }

                const template = this.smsTemplates.find(t => t.templateId == this.smsTemplateId);
                if (template) {
                    this.selectedSmsTemplate = template;

                    // Count {#Var#} or {#var#} placeholders
                    const content = template.templateContent || '';
                    const matches = content.match(/{#var#}/gi);
                    this.smsFieldsCount = matches ? matches.length : 0;
                    this.smsParamValues = Array(this.smsFieldsCount).fill('');

                    this.updateSmsPreview();
                }
            },

            /**
             * Updates the SMS preview by replacing placeholders with user input.
             */
            updateSmsPreview() {
                if (!this.selectedSmsTemplate) {
                    this.smsPreview = 'Dear ' + this.leadName + ',\nThank you for choosing our marketing services. We truly appreciate the opportunity to work with you and support your business goals.\nWe are excited to embark on this journey together and are committed to delivering strategies and solutions that drive meaningful results for your brand.\nShould you require any further information or assistance, please do not hesitate to contact us.\nThank you once again for your association and trust in us.\nBest regards,\nBillions United Team\nwww.billionsunited.com';
                    return;
                }

                let preview = this.selectedSmsTemplate.templateContent || '';

                // Replace {#Var#} or {#var#} sequentially
                this.smsParamValues.forEach((val, index) => {
                    const regex = /{#var#}/i;
                    preview = preview.replace(regex, val || '[Var ' + (index + 1) + ']');
                });

                this.smsPreview = preview;
            },

            /**
             * Handles logic when a WhatsApp template is selected.
             * Parses the template body to find  placeholders and create input fields for them.
             */
            onTemplateChange() {
                if (!this.selectedTemplateName) {
                    this.parameters = [];
                    this.paramValues = [];
                    this.headerType = 'TEXT';
                    return;
                }

                const template = this.templates.find(t => t.templateName === this.selectedTemplateName);
                if (template) {
                    this.headerType = template.type || 'TEXT';
                    if (template.body) {
                        const matches = template.body.match(/\{\{\d+\}\}/g);
                        const count = matches ? matches.length : 0;
                        this.parameters = Array(count).fill('');
                        this.paramValues = Array(count).fill('');
                    } else {
                        this.parameters = [];
                        this.paramValues = [];
                    }
                }
            },

            /**
             * Main function to execute the message sending.
             * Loops through all selected leads if in bulk mode.
             */
            async sendMessage() {
                // Validation for image templates
                if (this.type === 'whatsapp' && this.headerType === 'IMAGE' && !this.headerImageUrl) {
                    alert('This template requires a Header Image URL. Please provide one before sending.');
                    return;
                }

                this.isSending = true;
                this.sendingProgress = 0;

                let recipients = [];

                if (this.sendToAll) {
                    // Fetch all unique leads if "Send to All" is checked
                    try {
                        this.currentRecipientName = 'Fetching all unique customers...';
                        const response = await fetch(this.allContactsRoute);
                        const result = await response.json();
                        recipients = result.leads || [];
                        if (recipients.length === 0) {
                            alert('No unique customers found with mobile numbers.');
                            this.isSending = false;
                            return;
                        }
                    } catch (error) {
                        console.error('Error fetching all contacts:', error);
                        alert('Failed to fetch contacts list.');
                        this.isSending = false;
                        return;
                    }
                } else if (this.sendToFiltered) {
                    // Fetch all filtered leads
                    try {
                        this.currentRecipientName = 'Fetching all filtered leads...';
                        
                        // Build query string from filters
                        const params = new URLSearchParams(this.filters);
                        const response = await fetch(this.filteredContactsRoute + (this.filteredContactsRoute.includes('?') ? '&' : '?') + params.toString());
                        const result = await response.json();
                        recipients = result.leads || [];

                        if (recipients.length === 0) {
                            alert('No filtered leads found with mobile numbers.');
                            this.isSending = false;
                            return;
                        }
                    } catch (error) {
                        console.error('Error fetching filtered contacts:', error);
                        alert('Failed to fetch filtered contacts list.');
                        this.isSending = false;
                        return;
                    }
                } else if (this.bulkIds.length > 0) {
                    // Use the pre-selected bulk leads
                    recipients = this.bulkIds.map(id => {
                        const lead = window.leadData ? window.leadData[id] : null;
                        return {
                            id: id,
                            customer_name: lead ? lead.name : 'Lead #' + id,
                            mobile: lead ? lead.mobile : ''
                        };
                    });
                } else {
                    // Single recipient
                    recipients = [{
                        id: this.leadId,
                        customer_name: this.leadName,
                        mobile: this.leadMobile
                    }];
                }

                const total = recipients.length;
                let successCount = 0;
                let failCount = 0;

                const url = this.campaignRoute || (this.type === 'whatsapp'
                    ? '{{ route("messaging.whatsapp.send") }}'
                    : (this.type === 'rcs' ? '{{ route("messaging.rcs.send") }}' : '{{ route("messaging.sms.send") }}'));

                // Loop through each recipient
                for (let i = 0; i < recipients.length; i++) {
                    const recipient = recipients[i];
                    const currentId = recipient.id;
                    const currentMobile = recipient.mobile;
                    const currentName = recipient.customer_name;

                    this.currentRecipientName = currentName;
                    this.sendingProgress = Math.round(((i) / total) * 100);

                    const template = this.templates.find(t => t.templateName === this.selectedTemplateName);

                    // Build the body based on the channel type
                    let body = {};
                    if (this.type === 'whatsapp') {
                        body = {
                            lead_id: currentId,
                            to: currentMobile,
                            template_name: this.selectedTemplateName,
                            parameters: this.paramValues,
                            language: template ? template.language : 'en',
                            header_image: this.headerImageUrl,
                            type: this.type
                        };
                    } else if (this.type === 'rcs') {
                        body = {
                            lead_id: currentId,
                            to: currentMobile,
                            template_code: this.rcsTemplateCode,
                            bot_id: this.rcsBotId,
                            params: this.rcsParamValues,
                            type: this.type
                        };
                    } else if (this.type === 'sms') {
                        body = {
                            lead_id: currentId,
                            to: currentMobile,
                            sms: this.smsPreview,
                            senderid: this.selectedSmsTemplate ? this.selectedSmsTemplate.senderId : 'XXXXXX',
                            entityid: this.selectedSmsTemplate ? this.selectedSmsTemplate.entityId : '',
                            tempid: this.selectedSmsTemplate ? this.selectedSmsTemplate.templateId : '',
                            unicode: this.selectedSmsTemplate ? this.selectedSmsTemplate.isUnicode : 0,
                            type: this.type
                        };
                    }

                    try {
                        // Send request to the backend controller
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(body)
                        });

                        const result = await response.json();
                        if (result.success) {
                            successCount++;
                        } else {
                            failCount++;
                        }
                    } catch (error) {
                        console.error('Send error for ID ' + currentId, error);
                        failCount++;
                    }

                    // Update progress bar
                    this.sendingProgress = Math.round(((i + 1) / total) * 100);
                }

                // Show final result summary
                if (successCount > 0) {
                    const msg = total > 1
                        ? `Successfully sent to ${successCount} leads. ${failCount > 0 ? failCount + ' failed.' : ''}`
                        : 'Message sent successfully.';

                    if (window.showToast) {
                        window.showToast('Success', msg, 'success');
                    } else {
                        alert(msg);
                    }
                    this.closeModal();
                } else {
                    alert('Failed to send message(s).');
                }

                this.isSending = false;
            }
        }));
    });
</script>