<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $this->name = strip_tags(trim($this->name));
        $this->email = trim($this->email);

        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    /**
     * @return array<string, \Illuminate\Support\Collection<int, array<string, mixed>>>
     */
    #[Computed]
    public function groupedRoleActions(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        return collect($user->profileActions(session('active_branch') ?? session('active_client')))
            ->groupBy('section')
            ->all();
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        @php
            $profileUser = Auth::user();
            $isCustomerProfile = $profileUser && ! ($profileUser->hasRole('super_admin') || $profileUser->hasRole('apoteker') || $profileUser->hasRole('kasir'));
            $sectionLabels = [
                'portal' => 'Portal Utama Akun',
                'crud' => 'CRUD Pengaturan Lengkap & Hak Akses',
                'audit' => 'Cek Operasional Apoteker & Audit Kasir',
                'pelanggan' => $isCustomerProfile ? 'Navigasi Belanja & Pesanan' : 'Etalase & Katalog Publik',
            ];
            $groupedActions = $this->groupedRoleActions;
        @endphp

        @if(count($groupedActions) > 0)
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900">
                            Akses Portal &amp; Fitur Akun
                        </h3>
                        <p class="text-xs text-slate-500">
                            Tombol navigasi otomatis menyesuaikan peran serta hak akses akun Anda.
                        </p>
                    </div>
                    <a
                        href="{{ $profileUser->homeRoute() }}"
                        class="min-h-[40px] inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition"
                    >
                        <span>Buka Portal Utama &rarr;</span>
                    </a>
                </div>

                @foreach($groupedActions as $sectionKey => $actions)
                    <div class="space-y-2.5">
                        <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                            {{ $sectionLabels[$sectionKey] ?? 'Navigasi Cepat' }}
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($actions as $action)
                                <a
                                    href="{{ $action['href'] }}"
                                    class="group flex items-start gap-3 p-3.5 rounded-2xl border transition min-h-[56px] {{ !empty($action['primary']) ? 'border-emerald-300 bg-emerald-50/80 hover:bg-emerald-100' : 'border-slate-200 bg-slate-50/70 hover:bg-emerald-50/60 hover:border-emerald-200' }}"
                                >
                                    <div class="w-9 h-9 rounded-xl {{ !empty($action['primary']) ? 'bg-emerald-600 text-white' : 'bg-white border border-slate-200 text-emerald-700 group-hover:border-emerald-300' }} flex items-center justify-center shrink-0">
                                        @include('partials.profile-role-icon', ['icon' => $action['icon'], 'class' => 'w-4 h-4'])
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-emerald-800 truncate">
                                            {{ $action['label'] }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 leading-snug mt-0.5 line-clamp-2">
                                            {{ $action['description'] }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-5 border-b border-slate-100">
                <div class="space-y-1">
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900">
                        {{ __('Personal Information') }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ __('Manage your display name and registered email address') }}
                    </p>
                </div>
                @if (! $this->hasUnverifiedEmail)
                    <div class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                        {{ __('Verified') }}
                    </div>
                @endif
            </div>

            <form wire:submit="updateProfileInformation" class="mt-6 space-y-5">
                <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

                <div>
                    <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                    @if ($this->hasUnverifiedEmail)
                        <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 p-3.5 text-sm text-amber-800">
                            <flux:text class="text-amber-800">
                                {{ __('Your email address is unverified.') }}

                                <flux:link class="text-sm font-bold underline cursor-pointer text-amber-900" wire:click.prevent="resendVerificationNotification">
                                    {{ __('Click here to re-send the verification email.') }}
                                </flux:link>
                            </flux:text>

                            @if (session('status') === 'verification-link-sent')
                                <flux:text class="mt-2 font-semibold !text-emerald-700">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </flux:text>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                    <flux:button variant="primary" type="submit" class="w-full sm:w-auto px-7 font-bold rounded-xl bg-[#059669] hover:bg-[#047857] text-white" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </x-pages::settings.layout>
</section>
