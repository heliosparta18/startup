<x-layouts.customer :title="$title ?? 'OmniPharma'" :fullWidth="true">
    <div class="w-full py-5 sm:py-8">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist
</x-layouts.customer>
