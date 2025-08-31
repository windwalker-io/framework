@extends('layouts.html')

@once('log1')
    @push('scripts')
        <script>console.log(123);</script>
    @endpush
@endonce

@once('log1')
    @push('scripts')
        <script>console.log(456);</script>
    @endpush
@endonce

@pushonce('scripts', 'log2')
    <script>console.log(789);</script>
@endpushonce

@pushif(true, 'scripts')
    <link rel="stylesheet" href="foo.css" />
@endpushif

@pushif(false, 'scripts')
    <link rel="stylesheet" href="bar.css" />
@elsepush('bottom')
    <link rel="stylesheet" href="yoo.css" />
@endpushif
