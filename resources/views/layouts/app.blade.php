<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kost Management')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="header">
        <div class="container header-inner">
            <a href="{{ route('tenants.index') }}" class="brand">Kost Management</a>
            @auth
            <nav class="nav" style="display: flex; align-items: center; gap: var(--spacing-lg);">
                <a href="{{ route('tenants.index') }}" class="nav-link active">Tenants</a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline; margin: 0; padding: 0;">
                    @csrf
                    <button type="submit" class="nav-link" style="background: none; border: none; cursor: pointer; font-family: inherit; font-size: 0.875rem; font-weight: 500; padding: 0;">Logout</button>
                </form>
            </nav>
            @endauth
        </div>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="alert" id="success-alert">
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" onclick="document.getElementById('success-alert').style.display='none'">&times;</button>
            </div>
        @endif

        @if ($errors->any() && !request()->routeIs('login'))
            <div class="error-list">
                <strong>Terdapat beberapa kesalahan:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
