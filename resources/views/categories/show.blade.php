<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $category->name }} - {{ config('app.name') }}</title>
</head>
<body>
    <h1>{{ $category->name }}</h1>
    <p>ID: {{ $category->id }}</p>
    <p>وضعیت: {{ $category->is_active ? 'فعال' : 'غیرفعال' }}</p>
    
    @if($category->parent)
        <p>دسته مادر: {{ $category->parent->name }}</p>
    @endif
    
    @if($category->children->count() > 0)
        <h3>زیردسته ها:</h3>
        <ul>
            @foreach($category->children as $child)
                <li>{{ $child->name }}</li>
            @endforeach
        </ul>
    @endif
    
    <p>آدرس: {{ $category->url }}</p>
</body>
</html>