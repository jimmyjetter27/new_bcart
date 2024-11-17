<div class="bg-white shadow rounded-lg p-6">
    <h3 class="text-lg font-semibold">Pending Approvals</h3>
    <ul>
        @foreach ($pendingPhotos as $photo)
            <li class="flex items-center justify-between py-2 border-b">
                <div class="flex items-center">
                    <img src="{{ $photo->thumbnail_url }}" alt="{{ $photo->title }}" class="h-10 w-10 rounded-full">
                    <span class="ml-4">{{ $photo->title }}</span>
                </div>
                <a href="{{ route('filament.resources.photos.edit', $photo->id) }}" class="text-blue-500 hover:underline">
                    View
                </a>
            </li>
        @endforeach
    </ul>
    <div class="mt-4">
        <a href="{{ url('/admin/resources/photos?tableFilters[is_approved]=false') }}"
           class="text-sm text-blue-600 hover:underline">
            View All Pending Items ({{ $totalPending }})
        </a>
    </div>
</div>
