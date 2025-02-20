@props(['items'])

<nav class="text-sm text-gray-600 mb-4">
    <ol class="list-reset flex">
        @foreach($items as $item)
            @if (!$loop->last)
                <li>
                    <a href="{{ $item['url'] }}" class="text-blue-500 hover:underline">{{ $item['name'] }}</a>
                    <span class="mx-2">/</span>
                </li>
            @else
                <li class="text-gray-700">{{ $item['name'] }}</li>
            @endif
        @endforeach
    </ol>
</nav>
