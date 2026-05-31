<x-app-layout>
    <div class="container mx-auto px-4 py-8" x-data="{ activeImage: '{{ $product->images->where('is_featured', true)->first()?->large_thumbnail_url ?? $product->images->first()?->large_thumbnail_url }}' }">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div class="space-y-4">
                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                    <img :src="activeImage" alt="{{ $product->localized_name }}" class="w-full h-full object-cover">
                </div>
                <div class="flex gap-2 overflow-x-auto">
                    @foreach($product->images as $image)
                        <button @click="activeImage = '{{ $image->large_thumbnail_url }}'" class="w-20 h-20 border-2 rounded hover:border-blue-500">
                            <img src="{{ $image->small_thumbnail_url }}" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <h1 class="text-3xl font-bold">{{ $product->localized_name }}</h1>
                <p class="text-2xl text-blue-600 font-semibold">{{ $product->formatted_price }}</p>
                
                <div class="prose max-w-none">
                    {!! $product->localized_description !!}
                </div>

                <div class="pt-4">
                    @if($product->is_in_stock)
                        <button class="bg-blue-600 text-white px-8 py-3 rounded hover:bg-blue-700">
                            {{ __('Add to Cart') }}
                        </button>
                    @else
                        <span class="text-red-500 font-bold">{{ __('Out of Stock') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>