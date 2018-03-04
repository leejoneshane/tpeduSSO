<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/form-elements.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
</head>
<body>
    <div id="top-content">
	<div class="inner-bg">
        <main class="container">
            @yield('content')
        </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/jquery-1.11.1.min.js') }}"></script>
    <script src="{{ asset('js/jquery.backstretch.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/requirejs-2.1.14-min.js') }}"></script>
    <!--[if lt IE 10]>
	<script src="{{ asset('js/placeholder.js') }}"></script>
    <![endif]-->
</body>
</html>
