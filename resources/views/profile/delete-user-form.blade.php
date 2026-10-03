<x-action-section>
    <x-slot name="title">
        {{ __('Delete Account & Data') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Permanently delete your account and all associated observing data.') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400 space-y-2">
            <p>
                {{ __('Once your account is deleted, all of your saved observing locations, coordinate thresholds, cached forecast data, and transit predictions will be permanently erased immediately.') }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-500">
                {{ __('In accordance with our zero data harvesting commitment, no personal data is retained. Only anonymous, non-personal system counters remain to keep the service stable.') }}
            </p>
        </div>

        <div class="mt-5">
            <x-danger-button wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                {{ __('Delete Account') }}
            </x-danger-button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingUserDeletion">
            <x-slot name="title">
                {{ __('Delete Account & Data') }}
            </x-slot>

            <x-slot name="content">
                @if (empty(Auth::user()->password))
                    <div class="space-y-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Are you sure you want to permanently delete your account? All of your saved observing sites, custom thresholds, and personal data will be wiped from our database immediately.') }}
                        </p>
                        <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 text-xs">
                            {{ __('Since you signed in via Google, no password is required. Click "Delete Account" below to confirm.') }}
                        </div>
                    </div>
                @else
                    {{ __('Are you sure you want to delete your account? Once your account is deleted, all of your saved locations, weather threshold preferences, transit predictions, and personal data will be permanently erased. Please enter your password to confirm.') }}

                    <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                        <x-input type="password" class="mt-1 block w-3/4"
                                    autocomplete="current-password"
                                    placeholder="{{ __('Password') }}"
                                    x-ref="password"
                                    wire:model="password"
                                    wire:keydown.enter="deleteUser" />

                        <x-input-error for="password" class="mt-2" />
                    </div>
                @endif
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="deleteUser" wire:loading.attr="disabled">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
