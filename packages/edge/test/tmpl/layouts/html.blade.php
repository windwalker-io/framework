<html lang="en">
<head>
    <title>@yield('title')</title>
    @stack('scripts')
</head>
<body>
@section('superbody')
    <div class="container">
        @yield('content')
    </div>
@show

@stack('bottom')
</body>
</html>
