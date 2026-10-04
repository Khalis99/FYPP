@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}"
   @class([
       'mx-2 my-0.5 flex items-center gap-4 rounded-md px-4 py-3 text-[16px] transition-colors',
       'bg-brand-50 font-semibold text-brand-700' => $active,
       'text-slate-700 hover:bg-slate-100' => ! $active,
   ])
   @if ($active) aria-current="page" @endif>
    <x-icon :name="$icon" @class(['size-6', 'text-brand-600' => $active, 'text-slate-500' => ! $active]) />
    <span>{{ $slot }}</span>
</a>
