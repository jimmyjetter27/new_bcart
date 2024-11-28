<x-filament::page>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <!-- Title Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <!-- Replace with your desired Heroicon SVG path -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5.121 17.804A13.937 13.937 0 0112 16c2.485 0 4.847.556 7.121 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>

            <!-- Page Title -->
            <h1 class="text-2xl font-semibold">
                Edit Profile
            </h1>
        </div>
    </x-slot>

    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" color="success">
                Update Profile
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
