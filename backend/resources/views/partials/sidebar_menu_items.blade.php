@foreach($items as $item)
    @php
        $isSubActive = request()->routeIs($activeRoute . '.*') && (
            !empty($item['active'])
                ? request()->routeIs($item['active'])
                : ($currentQueryTab === ($item['tab'] ?? null) ||
                   (($item['tab'] ?? null) === 'users-management' && $currentQueryTab === 'users-list'))
        );
    @endphp
    <a href="{{ $item['url'] ?? route($activeRoute . '.dashboard', ['tab' => $item['tab']]) }}"
       @if(empty($item['url'])) onclick="return handleSidebarTabClick(event, '{{ $role }}', '{{ $item['tab'] }}')" @endif
       data-sidebar-role="{{ $role }}" @if(!empty($item['tab'])) data-sidebar-tab="{{ $item['tab'] }}" @endif
       class="sidebar-subitem flex items-center gap-2 px-3 py-2 rounded-xl text-[11px] font-semibold transition {{ $isSubActive ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}">
        <i data-lucide="{{ $item['icon'] }}" class="w-3.5 h-3.5 shrink-0 {{ $isSubActive ? 'text-white' : 'text-[#64748B]' }}"></i>
        <span class="truncate">{{ $item['label'] }}</span>
    </a>
@endforeach
