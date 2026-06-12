@extends('layouts.app')
@section('header', 'Email Templates')

@section('content')
    <style>
        /* Desktop default layout for header actions & bulk actions */
        .templates-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.75rem;
        }
        
        .templates-actions-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .templates-bulk-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .templates-mobile-only {
            display: none !important;
        }

        .templates-desktop-only {
            display: inline-block !important;
        }

        /* Mobile media query overrides */
        @media (max-width: 639px) {
            .templates-header-right {
                width: 100% !important;
                align-items: stretch !important;
                gap: 0.75rem !important;
            }

            .templates-actions-row {
                width: 100% !important;
            }

            .templates-actions-row a {
                width: 100% !important;
            }

            .templates-bulk-bar {
                width: 100% !important;
                background-color: #f8fafc !important; /* bg-slate-50 */
                border: 1px solid #e2e8f0 !important; /* border-slate-200 */
                border-radius: 0.75rem !important; /* rounded-xl */
                padding: 0.875rem !important; /* p-3.5 */
                flex-direction: column !important; /* flex-col */
                align-items: stretch !important;
                gap: 0.75rem !important;
            }

            .templates-bulk-bar-buttons {
                flex-direction: column !important;
                align-items: stretch !important;
                width: 100% !important;
                gap: 0.5rem !important;
            }

            .templates-bulk-bar-buttons button {
                width: 100% !important;
            }

            .templates-mobile-only {
                display: flex !important;
            }

            .templates-desktop-only {
                display: none !important;
            }
        }
    </style>
    <div class="relative">
        <div x-data="{
            selectedIds: [],
            selectAll: false,
            toggleAll() {
                if (this.selectAll) {
                    this.selectedIds = Array.from(document.querySelectorAll('.template-checkbox')).map(cb => cb.value);
                } else {
                    this.selectedIds = [];
                }
            }
        }" class="flex flex-col h-full transition duration-200">
            <!-- Page Header & Actions -->
            <div class="flex items-start justify-between gap-4 mb-6" style="flex-wrap: wrap;">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 tracking-tight">Email Templates ✨</h1>
                    <p class="text-sm text-slate-500 mt-1">Manage reusable email templates for your marketing campaigns.</p>
                </div>                <div class="templates-header-right w-full sm:w-auto">
                    <div class="templates-actions-row w-full sm:w-auto">
                        @can('email-template-add')
                        <a href="{{ route('email-templates.create') }}"
                            class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-lg shadow-indigo-200 text-sm font-bold transition-all transform active:scale-95 add-template-btn">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Template
                        </a>
                        @endcan
                    </div>
 
                    <!-- Bulk Actions -->
                    <div x-show="selectedIds.length > 0" x-transition
                        class="templates-bulk-bar animate-in fade-in slide-in-from-top-2 duration-200" style="display: none;">
                        
                        <!-- Mobile Selection Info/Clear Bar (hidden on desktop) -->
                        <div class="templates-mobile-only w-full items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-6 px-2.5 bg-indigo-600 text-white rounded-full text-xs font-black flex items-center justify-center shadow-md shadow-indigo-100" x-text="selectedIds.length"></span>
                                <span class="text-xs font-bold text-slate-700">selected</span>
                            </div>
                            <button type="button" @click="selectedIds = []; selectAll = false"
                                class="text-xs font-bold text-rose-600 hover:text-rose-700 underline underline-offset-4 decoration-2">
                                Clear Selection
                            </button>
                        </div>
 
                        <!-- Desktop Selection Text (hidden on mobile) -->
                        <span class="templates-desktop-only text-xs font-semibold text-slate-500 mr-2 whitespace-nowrap">
                            <span x-text="selectedIds.length"></span> selected:
                        </span>
 
                        <div class="templates-bulk-bar-buttons flex items-center gap-2">
                            @can('email-template-delete')
                            <button type="button"
                                @click="if(confirm('Delete selected templates?')) { $refs.bulkDeleteForm.submit() }"
                                class="inline-flex items-center justify-center gap-2 px-4 h-10 rounded-lg shadow-sm font-bold text-white bg-rose-600 hover:bg-rose-700 transition-all text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Selected
                            </button>
                            <form x-ref="bulkDeleteForm" action="{{ route('email-templates.bulk-destroy') }}" method="POST"
                                class="hidden">
                                @csrf
                                <input type="hidden" name="ids" :value="selectedIds.join(',')">
                            </form>
                            @endcan
 
                            <!-- Desktop Clear button (hidden on mobile) -->
                            <button type="button" @click="selectedIds = []; selectAll = false"
                                class="templates-desktop-only text-[10px] font-medium text-rose-600 hover:text-rose-700 underline underline-offset-4 ml-2">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Pagination & Scrollbar -->
            <div x-data="topScrollHandler()" x-init="init()" class="flex flex-col gap-2 mt-6">
                @if($templates->hasPages())
                    <div class="bg-slate-50/50 px-6 py-3 border border-slate-200 rounded-xl shadow-sm">
                        {{ $templates->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif

                @include('partials.top-scrollbar')

                <!-- Table Container -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex-1">
                    <div x-ref="contentContainer" @scroll="sync($el, $refs.topScrollbar)" class="overflow-x-auto h-full">
                        <table x-ref="mainTable" class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/80 backdrop-blur-sm sticky top-0 z-10">
                                <tr>
                                    @can('email-template-delete')
                                    <th class="px-6 py-4 w-10">
                                        <input type="checkbox" x-model="selectAll" @change="toggleAll"
                                            class="rounded border-slate-300 text-indigo-600">
                                    </th>
                                    @endcan
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Subject</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Created At</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @forelse($templates as $template)
                                    <tr @can('email-template-edit') @click="window.location='{{ route('email-templates.edit', $template->id) }}'" @endcan
                                        class="hover:bg-slate-50/80 transition-colors group {{ auth()->user()->can('email-template-edit') ? 'cursor-pointer' : '' }}">
                                        @can('email-template-delete')
                                        <td class="px-6 py-4 w-10" @click.stop>
                                            <input type="checkbox" value="{{ $template->id }}" x-model="selectedIds"
                                                class="template-checkbox rounded border-slate-300 text-indigo-600">
                                        </td>
                                        @endcan
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-slate-900">{{ $template->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-600">{{ $template->subject }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-600">{{ $template->created_at->format('M d, Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                                            <div class="flex items-center justify-end gap-2">
                                                @can('email-template-edit')
                                                <a href="{{ route('email-templates.edit', $template->id) }}"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 rounded-lg text-xs font-bold transition-all">
                                                    Edit
                                                </a>
                                                @endcan

                                                @can('email-template-delete')
                                                <form action="{{ route('email-templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-xs font-bold transition-all">
                                                        Delete
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="h-12 w-12 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-4">
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <h3 class="text-sm font-bold text-slate-900">No templates found</h3>
                                                <p class="text-xs text-slate-500 mt-1">Get started by creating your first email template.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($templates->hasPages())
                    <div class="bg-slate-50/50 px-6 py-3 border border-slate-200 rounded-xl shadow-sm mt-4">
                        {{ $templates->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function topScrollHandler() {
            return {
                init() {
                    this.$nextTick(() => {
                        this.updateWidth();
                        window.addEventListener('resize', () => this.updateWidth());
                    });
                },
                updateWidth() {
                    const table = this.$refs.mainTable;
                    const topScrollbarInner = this.$refs.topScrollbarInner;
                    if (table && topScrollbarInner) {
                        topScrollbarInner.style.width = table.offsetWidth + 'px';
                    }
                },
                sync(source, target) {
                    target.scrollLeft = source.scrollLeft;
                }
            }
        }
    </script>
@endsection
