<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LNT-IA - Traductor Español-Aimara')</title>
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-gradient: radial-gradient(circle at 50% 50%, #11131e 0%, #08090d 100%);
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.3);
            --accent: #06b6d4;
            --accent-glow: rgba(6, 182, 212, 0.3);
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.06);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --border-color: rgba(255, 255, 255, 0.08);
            --font-title: 'Outfit', sans-serif;
            --font-body: 'Inter', sans-serif;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg-gradient);
            color: var(--text-main);
            font-family: var(--font-body);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Fondo Decorativo SOTA */
        .ambient-glow {
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.06) 0%, rgba(0,0,0,0) 70%);
            top: -200px;
            left: -100px;
            z-index: -1;
            pointer-events: none;
        }

        .ambient-glow-2 {
            position: fixed;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.05) 0%, rgba(0,0,0,0) 70%);
            bottom: -150px;
            right: -100px;
            z-index: -1;
            pointer-events: none;
        }

        /* Glass Container Principal */
        .app-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            width: 100%;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Navbar SOTA */
        header {
            background: rgba(13, 15, 24, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 0.75rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .brand-logo {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.25rem;
            font-weight: 800;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .brand-text {
            font-family: var(--font-title);
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #fff 30%, var(--text-muted) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            gap: 0.5rem;
        }

        .nav-item {
            text-decoration: none;
            color: var(--text-muted);
            font-family: var(--font-title);
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.65rem 1.25rem;
            border-radius: 14px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid transparent;
        }

        .nav-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.02);
            border-color: var(--glass-border);
        }

        .nav-item.active {
            color: #fff;
            background: rgba(139, 92, 246, 0.08);
            border-color: rgba(139, 92, 246, 0.2);
            box-shadow: 0 0 15px rgba(139, 92, 246, 0.05);
        }

        /* Glassmorphic Cards standard */
        .glass-card {
            background: rgba(13, 15, 24, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-family: var(--font-body);
            border-top: 1px solid var(--border-color);
            margin-top: auto;
            background: rgba(8, 9, 13, 0.8);
        }

        footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        footer a:hover {
            color: var(--accent);
            text-shadow: 0 0 8px var(--accent-glow);
        }

        /* Scrollbar personalizado */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #08090d;
        }
        ::-webkit-scrollbar-thumb {
            background: #222533;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
            }
            .nav-item {
                font-size: 0.85rem;
                padding: 0.5rem 0.85rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="ambient-glow"></div>
    <div class="ambient-glow-2"></div>

    <div class="app-container">
        <!-- Header / Navbar SOTA -->
        <header>
            <a href="{{ route('translator.index') }}" class="brand">
                <div class="brand-logo"><i class="fa-solid fa-brain"></i></div>
                <div class="brand-text">LNT-IA</div>
            </a>
            <nav class="nav-links">
                <a href="{{ route('translator.index') }}" class="nav-item {{ Route::is('translator.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-language"></i> Traductor
                </a>
                <a href="{{ route('translator.compare') }}" class="nav-item {{ Route::is('translator.compare') ? 'active' : '' }}">
                    <i class="fa-solid fa-code-compare"></i> Comparar Modelos
                </a>
                <a href="{{ route('translator.reports') }}" class="nav-item {{ Route::is('translator.reports') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Reportes
                </a>
            </nav>
        </header>

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 LNT-IA Project. Desarrollado con tecnología SOTA para traducción Español-Aimara.</p>
        <p style="margin-top: 0.5rem;">Potenciado por GPU local <a href="#">NVIDIA RTX 5060</a> &bull; Llama-3 / Gemma-2 / NLLB-200 &bull; Whisper &bull; MMS</p>
    </footer>

    @yield('scripts')
</body>
</html>