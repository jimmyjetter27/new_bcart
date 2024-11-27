
<div>
    @if($user->photos()->count() > 0)
        <div class="swiper-container">
            <div class="swiper-wrapper">
                @foreach($user->photos as $photo)
                    <div class="swiper-slide">
                        <img src="{{ $photo->image_url }}" alt="{{ $photo->title }}" class="w-full h-auto object-cover rounded-md">
                        <p class="mt-2 text-center font-semibold">{{ $photo->title }}</p>
                    </div>
                @endforeach
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
            <!-- Add Navigation -->
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    @else
        <p>No photos uploaded by this creative.</p>
    @endif
</div>

<!-- Include Swiper JS and CSS -->
@push('styles')
    <link
        rel="stylesheet"
        href="https://unpkg.com/swiper@8/swiper-bundle.min.css"
    />
@endpush

@push('scripts')
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var swiper = new Swiper('.swiper-container', {
                loop: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                autoplay: {
                    delay: 5000,
                },
            });
        });
    </script>
@endpush
