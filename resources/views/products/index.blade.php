<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">{{ __('Products') }}</h1>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($products as $product)
                <a href="{{ $product->url }}" class="group block bg-white rounded-lg shadow hover:shadow-lg transition">
                    {{-- تصویر شاخص --}}
                    <div class="aspect-square bg-gray-100 rounded-t-lg overflow-hidden">
                        @if($product->featuredImage)
                            <img src="{{ $product->featuredImage->medium_thumbnail_url }}" 
                                 alt="{{ $product->localized_name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <div class="p-4">
                        <h2 class="font-semibold text-lg group-hover:text-blue-600 transition">
                            {{ $product->localized_name }}
                        </h2>
                        <p class="text-blue-600 font-bold mt-2">{{ $product->formatted_price }}</p>
                        
                        @if($product->stock_status === 'out_of_stock')
                            <span class="text-red-500 text-sm">{{ __('Out of Stock') }}</span>
                        @elseif($product->stock_status === 'low_stock')
                            <span class="text-orange-500 text-sm">{{ __('Low Stock') }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    {{ __('No products found.') }}
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>