@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView({ behavior: 'smooth', block: 'start' })
    JS
    : '';
@endphp

<div class="w-full">
    @if ($paginator->hasPages())
        <nav
            role="navigation"
            aria-label="Navigasi Halaman Katalog"
            class="bg-white border border-slate-200/90 rounded-2xl px-3.5 sm:px-5 py-3 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3 w-full"
        >
            {{-- Summary Text --}}
            <div class="text-xs text-slate-600 text-center sm:text-left">
                @if ($paginator->firstItem())
                    Menampilkan
                    <span class="font-bold text-slate-900">{{ $paginator->firstItem() }}</span>
                    -
                    <span class="font-bold text-slate-900">{{ $paginator->lastItem() }}</span>
                    dari
                    <span class="font-bold text-slate-900">{{ $paginator->total() }}</span>
                    produk
                @else
                    Menampilkan <span class="font-bold text-slate-900">{{ $paginator->count() }}</span> produk
                @endif
            </div>

            {{-- Mobile Compact Controls --}}
            <div class="flex items-center justify-between gap-2 w-full sm:hidden">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center justify-center min-h-[40px] px-3.5 py-2 text-xs font-semibold text-slate-300 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed">
                        &larr; Sebelumnya
                    </span>
                @else
                    <button
                        type="button"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center min-h-[40px] px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 hover:border-emerald-300 transition-colors cursor-pointer shadow-2xs"
                    >
                        &larr; Sebelumnya
                    </button>
                @endif

                <span class="text-xs font-bold text-slate-700 px-2">
                    Hal. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>

                @if ($paginator->hasMorePages())
                    <button
                        type="button"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center min-h-[40px] px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 hover:border-emerald-300 transition-colors cursor-pointer shadow-2xs"
                    >
                        Berikutnya &rarr;
                    </button>
                @else
                    <span class="inline-flex items-center justify-center min-h-[40px] px-3.5 py-2 text-xs font-semibold text-slate-300 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed">
                        Berikutnya &rarr;
                    </span>
                @endif
            </div>

            {{-- Desktop Page Numbers --}}
            <div class="hidden sm:flex items-center flex-wrap justify-end gap-1">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="inline-flex items-center justify-center min-w-[36px] h-9 px-2 rounded-xl bg-slate-50 text-slate-300 border border-slate-200 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @else
                    <button
                        type="button"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                        class="inline-flex items-center justify-center min-w-[36px] h-9 px-2 rounded-xl bg-white text-slate-700 border border-slate-200 hover:bg-emerald-50 hover:text-emerald-800 hover:border-emerald-300 transition-colors cursor-pointer shadow-2xs focus-visible:outline-2 focus-visible:outline-emerald-700"
                        aria-label="{{ __('pagination.previous') }}"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="inline-flex items-center justify-center min-w-[36px] h-9 px-2 text-xs font-semibold text-slate-400 bg-white border border-slate-200 rounded-xl select-none">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="inline-flex items-center justify-center min-w-[36px] h-9 px-3 rounded-xl bg-emerald-600 text-white border border-emerald-600 text-xs font-extrabold shadow-2xs select-none">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button
                                        type="button"
                                        wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                        class="inline-flex items-center justify-center min-w-[36px] h-9 px-3 rounded-xl bg-white text-slate-700 border border-slate-200 text-xs font-semibold hover:bg-emerald-50 hover:text-emerald-800 hover:border-emerald-300 transition-colors cursor-pointer focus-visible:outline-2 focus-visible:outline-emerald-700"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                    >
                                        {{ $page }}
                                    </button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <button
                        type="button"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                        class="inline-flex items-center justify-center min-w-[36px] h-9 px-2 rounded-xl bg-white text-slate-700 border border-slate-200 hover:bg-emerald-50 hover:text-emerald-800 hover:border-emerald-300 transition-colors cursor-pointer shadow-2xs focus-visible:outline-2 focus-visible:outline-emerald-700"
                        aria-label="{{ __('pagination.next') }}"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="inline-flex items-center justify-center min-w-[36px] h-9 px-2 rounded-xl bg-slate-50 text-slate-300 border border-slate-200 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>
