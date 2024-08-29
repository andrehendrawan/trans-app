<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to My Laravel App</title>
    <!-- Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            text-align: center;
        }
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="font-sans antialiased bg-light text-dark">
    <header class="bg-danger text-white py-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="/" class="h1 text-white text-decoration-none">Trans App</a>
            @if (Route::has('login'))
                <nav class="d-flex">
                    @auth
                        <a href="{{ url('/transactions') }}" class="btn btn-light text-danger me-2">Transactions</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-light text-danger me-2">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-light text-danger">Register</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </div>
    </header>
    <main class="hero">
        <h1>Welcome to Trans App</h1>
        <p>Your transaction recorder app, that you can find anywhere.</p>
        <a href="{{ url('/transactions') }}" class="btn btn-danger text-white">Learn More</a>
    </main>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
