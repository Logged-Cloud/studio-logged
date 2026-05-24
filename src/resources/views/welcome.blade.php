<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Studio Logged, live demo of the page-studio Laravel package</title>
    <meta name="description" content="A public sandbox for logged-cloud/page-studio. Drag blocks, edit a page, preview the result. No login, no setup.">
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
            --ink: #E6EDF3;
            --ink-dim: #8B949E;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--ink);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
        }
        .card {
            background: var(--surface);
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            max-width: 32rem;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,.45);
        }
        h1 { font-size: 1.75rem; margin-bottom: .5rem; }
        p { color: var(--ink-dim); line-height: 1.55; margin-bottom: 1.5rem; }
        .actions { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; }
        a.btn {
            background: var(--accent);
            color: white;
            text-decoration: none;
            padding: .8rem 1.4rem;
            border-radius: .6rem;
            font-weight: 600;
            display: inline-block;
        }
        a.btn:hover { background: var(--accent-hover); }
        a.btn.ghost { background: transparent; color: var(--ink); border: 1px solid #30363D; }
        .tag {
            display: inline-block;
            background: rgba(124,92,255,.15);
            color: #BBA8FF;
            padding: .25rem .6rem;
            border-radius: .4rem;
            font-size: .75rem;
            margin-bottom: 1rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <main class="card">
        <img src="/icon-192.png" alt="" width="72" height="72" style="border-radius:16px;margin-bottom:1rem;">
        <span class="tag">Live demo</span>
        <h1>Studio Logged</h1>
        <p>A public sandbox for <strong>logged-cloud/page-studio</strong>, the visual page-builder Laravel package. Open the playground, drag blocks around, edit text, then preview the rendered page.</p>
        <div class="actions">
            <a class="btn" href="/playground">Open playground</a>
            <a class="btn ghost" href="https://github.com/Logged-Cloud/page-studio" target="_blank" rel="noreferrer">View on GitHub</a>
        </div>
    </main>
</body>
</html>
