<!DOCTYPE html>
<html>
<head>
    <title>{{ $post->title }}</title>
</head>
<body>

<h1>{{ $post->title }}</h1>

<p>
    Publié le {{ $post->validated_at?->format('d/m/Y') }}
</p>

<div>
    {!! nl2br(e($post->content)) !!}
</div>

@if ($post->file_path)
    <img src="{{ route('files.show', $post) }}" alt="{{ $post->title }}" style="max-width:100%; height:auto;">
@endif


</body>
</html>
