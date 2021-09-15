@props(['errors'])

@if ($errors->any())
    <div {{ $attributes }}>
        <ul class="text-danger">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
