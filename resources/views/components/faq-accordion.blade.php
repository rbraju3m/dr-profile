@props(['faqs'])

<div x-data="{ open: null }" class="divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200 bg-white">
    @foreach ($faqs as $index => $faq)
        <div>
            <h3>
                <button type="button"
                        @click="open = open === {{ $index }} ? null : {{ $index }}"
                        :aria-expanded="open === {{ $index }}"
                        aria-controls="faq-panel-{{ $faq->id }}"
                        class="flex w-full items-center justify-between gap-4 px-5 py-4 text-start text-[15px] font-medium text-slate-800 transition hover:bg-slate-50">
                    <span>{{ $faq->tr('question') }}</span>
                    <x-icon name="chevron-down" class="h-5 w-5 shrink-0 text-slate-400 transition"
                            ::class="open === {{ $index }} && 'rotate-180 text-primary-600'"/>
                </button>
            </h3>

            <div x-show="open === {{ $index }}" x-collapse x-cloak id="faq-panel-{{ $faq->id }}">
                <div class="prose-content px-5 pb-5 pt-0">{!! $faq->tr('answer') !!}</div>
            </div>
        </div>
    @endforeach
</div>
