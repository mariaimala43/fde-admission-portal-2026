{{-- components/info-tooltip.blade.php
     Usage:
       <x-info-tooltip text="Your explanation here." />
       <x-info-tooltip text="..." position="right" />   ← default, sidebar links
       <x-info-tooltip text="..." position="bottom" />  ← page headers
       <x-info-tooltip text="..." position="left" />
       <x-info-tooltip text="..." position="top" />

     Uses x-teleport="body" so the bubble is appended to <body>, fully escaping:
       • the sidebar's transform: translateX(0) containing-block on mobile
       • the sidebar's overflow-y:auto clipping
       • any stacking-context issues (z-index now applies in the root context)

     Transition uses opacity-only (no scale) to avoid conflicting with the
     transform:translateY(-50%) set via :style.
--}}
@props(['text', 'position' => 'right'])

<span
    x-data="{ show: false, styleStr: '' }"
    class="relative inline-flex items-center ml-1.5 cursor-help"
    @mouseenter="
        show = true;
        var b = $el.getBoundingClientRect(), p = '{{ $position }}';
        if (p === 'right') {
            styleStr = 'position:fixed;left:' + (b.right + 8) + 'px;top:' + (b.top + b.height / 2) + 'px;transform:translateY(-50%)';
        } else if (p === 'left') {
            styleStr = 'position:fixed;right:' + (window.innerWidth - b.left + 8) + 'px;top:' + (b.top + b.height / 2) + 'px;transform:translateY(-50%)';
        } else if (p === 'bottom') {
            styleStr = 'position:fixed;left:' + (b.left + b.width / 2) + 'px;top:' + (b.bottom + 8) + 'px;transform:translateX(-50%)';
        } else {
            styleStr = 'position:fixed;left:' + (b.left + b.width / 2) + 'px;top:' + (b.top - 8) + 'px;transform:translate(-50%,-100%)';
        }
    "
    @mouseleave="show = false"
    @touchstart.prevent="
        var b = $el.getBoundingClientRect(), p = '{{ $position }}';
        if (p === 'right') {
            styleStr = 'position:fixed;left:' + (b.right + 8) + 'px;top:' + (b.top + b.height / 2) + 'px;transform:translateY(-50%)';
        } else if (p === 'left') {
            styleStr = 'position:fixed;right:' + (window.innerWidth - b.left + 8) + 'px;top:' + (b.top + b.height / 2) + 'px;transform:translateY(-50%)';
        } else if (p === 'bottom') {
            styleStr = 'position:fixed;left:' + (b.left + b.width / 2) + 'px;top:' + (b.bottom + 8) + 'px;transform:translateX(-50%)';
        } else {
            styleStr = 'position:fixed;left:' + (b.left + b.width / 2) + 'px;top:' + (b.top - 8) + 'px;transform:translate(-50%,-100%)';
        }
        show = !show;
    "
    @click.outside="show = false"
    @click.stop
    role="tooltip"
    aria-label="{{ $text }}"
>
    {{-- ℹ icon --}}
    <svg class="h-4 w-4 text-gray-400 hover:text-blue-500 transition-colors flex-shrink-0"
         fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>

    {{--
        Tooltip bubble — teleported to <body> so it escapes:
          1. The sidebar's transform:translateX(0) containing-block on mobile
          2. The sidebar's overflow-y:auto (which becomes overflow-x:auto too)
          3. Any ancestor stacking context below z-index: 9999
        Transition is opacity-only (no scale) to avoid conflicting with
        the transform:translateY/translateX set on styleStr.
    --}}
    <template x-teleport="body">
        <span
            x-show="show"
            :style="styleStr"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="z-[9999] w-64 sm:w-72 rounded-xl bg-gray-900 text-white text-xs leading-relaxed px-3.5 py-2.5 shadow-xl pointer-events-none"
            style="display:none"
        >
            {{-- Directional arrow --}}
            @if ($position === 'right')
                <span class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-gray-900 rotate-45"></span>
            @elseif ($position === 'left')
                <span class="absolute -right-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-gray-900 rotate-45"></span>
            @elseif ($position === 'bottom')
                <span class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></span>
            @else {{-- top --}}
                <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></span>
            @endif
            {{ $text }}
        </span>
    </template>
</span>
