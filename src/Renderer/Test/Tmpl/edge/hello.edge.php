@extends('layouts.master')

@section('sidebar')
    @parent

    <p>This is appended to the master sidebar.</p>
@stop

@section('content')
    <p>This is my body content.</p>

    @verbatim
        asd
        @test
        @foreach(array('qwe') as $a)
            {{ $a }}
        @endforeach
    @endverbatim
    
@stop
