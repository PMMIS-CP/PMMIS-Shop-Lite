<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-4">{{ $category->name }}</h1>
        
        <div class="bg-white p-6 rounded shadow">
            <p><strong>{{ __('Status') }}:</strong> {{ $category->is_active ? __('Active') : __('Inactive') }}</p>
            
            @if($category->parent)
                <p><strong>{{ __('Parent Category') }}:</strong> {{ $category->parent->name }}</p>
            @endif
        </div>

        @if($category->products->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                @foreach($category->products as $product)
                    <div class="border p-4 rounded">
                        <a href="{{ $product->url }}">
                            <h2 class="font-bold">{{ $product->name }}</h2>
                            <p>{{ $product->formatted_price }}</p>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>