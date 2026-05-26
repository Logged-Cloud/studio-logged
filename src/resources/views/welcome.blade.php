<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Studio Logged · a visual page-builder for Laravel</title>
    <meta name="description" content="A public sandbox for logged-cloud/page-studio. This page IS built and rendered with the package — open it in the editor to see the block tree.">
    <meta name="theme-color" content="#0E1116">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <style>
        :root {
            --bg: #0E1116;
            --surface: #161B22;
            --accent: #7C5CFF;
            --accent-hover: #6B4BFF;
            --ink: #0F172A;
            --ink-dim: #475569;
            --line: #E2E8F0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #fff;
            color: var(--ink);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
        }
        .topnav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 1.5rem; background: #fff; border-bottom: 1px solid var(--line);
            position: sticky; top: 0; z-index: 10;
        }
        .topnav .brand { display: inline-flex; align-items: center; gap: .5rem; font-weight: 700; color: var(--ink); text-decoration: none; }
        .topnav img { width: 28px; height: 28px; border-radius: 6px; }
        .topnav nav { display: flex; gap: .5rem; }
        .topnav nav a {
            color: var(--ink-dim); text-decoration: none; padding: .45rem .85rem;
            border-radius: .4rem; font-size: .9rem; font-weight: 500;
        }
        .topnav nav a:hover { background: rgba(15,23,42,.05); color: var(--ink); }
        .topnav nav a.cta { background: var(--accent); color: #fff; }
        .topnav nav a.cta:hover { background: var(--accent-hover); }
        .dogfood-banner {
            background: rgba(124,92,255,.08); border-bottom: 1px solid rgba(124,92,255,.18);
            padding: .65rem 1.5rem; text-align: center; font-size: .85rem; color: #4F378B;
        }
        .dogfood-banner a { color: #4F378B; font-weight: 600; }
        main.canvas { max-width: 56rem; margin: 0 auto; padding: 3rem 1.5rem 5rem; }
        footer.foot {
            border-top: 1px solid var(--line); padding: 1.5rem; text-align: center;
            color: var(--ink-dim); font-size: .85rem;
        }
        footer.foot a { color: var(--ink-dim); }
    </style>
</head>
<body>
    <div class="topnav">
        <a class="brand" href="/">
            <img src="/icon-192.png" alt="">
            <span>Studio Logged</span>
        </a>
        <nav>
            <a href="/lab">Templates</a>
            <a href="https://github.com/Logged-Cloud/page-studio" target="_blank" rel="noreferrer">GitHub ↗</a>
            <a class="cta" href="/playground">Open playground</a>
        </nav>
    </div>

    <div class="dogfood-banner">
        🪄 This page is itself built with <strong>logged-cloud/page-studio</strong>. <a href="/playground?seed=home">Fork it into the editor</a> to see the block tree.
    </div>

    <main class="canvas">
        {!! \LoggedCloud\PageStudio\Support\PageRenderer::render($homePage->blocks ?? []) !!}
    </main>

    <footer class="foot">
        Built with <a href="https://github.com/Logged-Cloud/page-studio" target="_blank" rel="noreferrer">logged-cloud/page-studio</a>
        · <a href="https://github.com/Logged-Cloud/studio-logged" target="_blank" rel="noreferrer">studio-logged source</a>
    </footer>
</body>
</html>
