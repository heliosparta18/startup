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
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <div class="my-6 rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between pb-6 border-b border-slate-100 dark:border-zinc-800">
                <div class="space-y-1">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ __('Personal Information') }}
                    </h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('Manage your display name and registered email address') }}
                    </p>
                </div>
                @if (! $this->hasUnverifiedEmail)
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/40">
                        <span class="size-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                        {{ __('Verified') }}
                    </div>
                @endif
            </div>

            <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
                <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

                <div>
                    <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                    @if ($this->hasUnverifiedEmail)
                        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800/50 dark:bg-amber-950/30 dark:text-amber-300">
                            <flux:text class="text-amber-800 dark:text-amber-300">
                                {{ __('Your email address is unverified.') }}

                                <flux:link class="text-sm font-medium underline cursor-pointer text-amber-900 dark:text-amber-200" wire:click.prevent="resendVerificationNotification">
                                    {{ __('Click here to re-send the verification email.') }}
                                </flux:link>
                            </flux:text>

                            @if (session('status') === 'verification-link-sent')
                                <flux:text class="mt-2 font-medium !dark:text-emerald-400 !text-emerald-600">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </flux:text>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <div class="flex items-center justify-end w-full">
                        <flux:button variant="primary" type="submit" class="w-full sm:w-auto px-6 font-semibold" data-test="update-profile-button">
                            {{ __('Save') }}
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>
    </x-pages::settings.layout>
</section>
