<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>SITEMU ARSIP</title>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-danger">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">SITEMU ARSIP</span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-light btn-sm">Logout</button>
        </form>
    </div>
</nav>

<div class="container mt-4">
    @yield('content')
</div>

</body>
</html>
