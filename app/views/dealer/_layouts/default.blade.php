<!doctype html>
<html lang="en" >
<head>
        <meta charset="utf-8">
        <title>Car Service</title>

        @include('common.includes.assets')
        @include('dealer.includes.assets')
        <script>
  </script>
</head>
<body>
<div class="container">
        @include('dealer._partials.header')

        @yield('main')
</div>
</body>
</html>