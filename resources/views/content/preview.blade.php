@extends('layouts.preview')

@section('title', $content->title)

@section('content')
    <article class="prose mx-auto max-w-3xl py-12">
        <header class="mb-6 border-b pb-4">
            <h1 class="text-4xl font-bold text-gray-900">{{ $content->title }}</h1>
            <p class="mt-2 text-sm text-gray-500">Draft preview • Last updated {{ optional($content->updated_at)->diffForHumans() }}</p>
        </header>

        @if($content->excerpt)
            <p class="text-lg text-gray-600">{{ $content->excerpt }}</p>
        @endif

        <section class="mt-6 content-body">
            {!! $content->body !!}
        </section>
    </article>
@endsection
