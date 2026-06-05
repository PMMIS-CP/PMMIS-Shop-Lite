@props(['active' => false, 'href' => '#'])

<a href="{{ $href }}" 
   class="block px-3 py-2 rounded-md text-sm font-medium transition-colors
          {{ $active 
              ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200' 
              : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
    {{ $slot }}
</a>