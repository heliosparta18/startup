@blaze(fold: true, unsafe: ['icon:variant', 'icon:trailing'])

@php $iconVariant ??= $attributes->pluck('icon:variant'); @endphp
@php $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp

@props([
    'iconVariant' => 'micro',
    'iconTrailing' => null,
    'initials' => null,
    'chevron' => true,
    'circle' => null,
    'avatar' => null,
    'name' => null,
    'role' => null,
])

@php
$iconTrailing = $iconTrailing ?? ($chevron ? 'chevron-down' : null);

// When using the outline icon variant, we need to size it down to match the default icon sizes...
$iconClasses = Flux::classes('text-zinc-400 dark:text-white/80 group-hover:text-zinc-800 dark:group-hover:text-white')
    ->add($iconVariant === 'outline' ? 'size-4' : '');

$classes = Flux::classes()
    ->add('group flex items-center')
    ->add('rounded-lg has-data-[circle=true]:rounded-full')
    ->add('[ui-dropdown>&]:w-full') // Without this, the "name" won't get truncated in a sidebar dropdown...
    ->add('p-1 hover:bg-zinc-800/5 dark:hover:bg-white/10')
    ;
@endphp

<button type="button" {{ $attributes->class($classes) }} data-flux-sidebar-profile>
    <div class="shrink-0">
        <?php if ($avatar instanceof \Illuminate\View\ComponentSlot): ?>
            {{ $avatar }}
        <?php else: ?>
            <?php $avatarAttributes = Flux::attributesAfter('avatar:', $attributes, ['src' => $avatar, 'size' => 'sm', 'circle' => $circle, 'name' => $name, 'initials' => $initials]); ?>
            <flux:avatar :attributes="$avatarAttributes" />
        <?php endif; ?>
    </div>

    <?php if ($name): ?>
        <div class="in-data-flux-sidebar-collapsed-desktop:hidden mx-2 grid flex-1 text-start leading-tight min-w-0">
            <span class="text-sm font-medium text-zinc-700 dark:text-white/80 group-hover:text-zinc-800 dark:group-hover:text-white truncate">
                {{ $name }}
            </span>
            <?php if (!empty($role)): ?>
                <span class="text-xs text-emerald-700 dark:text-emerald-400 font-medium truncate">
                    {{ $role }}
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (is_string($iconTrailing) && $iconTrailing !== ''): ?>
        <div class="in-data-flux-sidebar-collapsed-desktop:hidden shrink-0 ms-auto size-8 flex justify-center items-center">
            <flux:icon :icon="$iconTrailing" :variant="$iconVariant" :class="$iconClasses" />
        </div>
    <?php elseif ($iconTrailing): ?>
        {{ $iconTrailing }}
    <?php endif; ?>
</button>
