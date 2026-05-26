<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lab · Studio Logged</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <style>
        :root { --bg:#0E1116; --surface:#161B22; --surface-2:#1F2632; --accent:#7C5CFF; --accent-hover:#6B4BFF; --ink:#E6EDF3; --ink-dim:#8B949E; --line:#30363D; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--ink); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; min-height: 100vh; }
        .topnav { display: flex; align-items: center; justify-content: space-between; padding: .75rem 1.25rem; background: var(--surface); border-bottom: 1px solid var(--line); gap: .5rem; flex-wrap: wrap; }
        .topnav a { color: var(--ink); text-decoration: none; font-weight: 600; }
        .topnav .actions { display: flex; gap: .35rem; flex-wrap: wrap; }
        .topnav .actions a { font-weight: 500; background: transparent; border: 1px solid var(--line); padding: .4rem .75rem; border-radius: .4rem; font-size: .85rem; }
        @media (max-width: 640px) {
            .topnav { padding: .55rem .75rem; }
            .topnav .actions a { padding: .3rem .55rem; font-size: .75rem; }
            main { padding: 2rem 1.25rem 4rem; }
            h1 { font-size: 1.5rem; }
            .grid { grid-template-columns: 1fr; }
        }
        main { max-width: 64rem; margin: 0 auto; padding: 3rem 1.5rem 4rem; }
        h1 { font-size: 2rem; margin-bottom: .5rem; }
        .lede { color: var(--ink-dim); margin-bottom: 2.5rem; max-width: 38rem; line-height: 1.6; }
        .grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr)); }
        .card { background: var(--surface); border: 1px solid var(--line); border-radius: .75rem; padding: 1.25rem; display: flex; flex-direction: column; gap: .75rem; }
        .card h3 { font-size: 1.05rem; }
        .card p { color: var(--ink-dim); font-size: .9rem; line-height: 1.5; flex: 1; }
        .card button { background: var(--accent); color: white; border: 0; padding: .55rem .9rem; border-radius: .45rem; font-weight: 600; cursor: pointer; font-size: .85rem; }
        .card button:hover { background: var(--accent-hover); }
        .note { margin-top: 2.5rem; font-size: .85rem; color: var(--ink-dim); padding: 1rem 1.25rem; background: var(--surface); border: 1px solid var(--line); border-radius: .5rem; }
    </style>
</head>
<body>
    <div class="topnav">
        <a href="/">Studio Logged</a>
        <div class="actions">
            <a href="/playground">Playground</a>
            <a href="https://github.com/Logged-Cloud/page-studio" target="_blank" rel="noreferrer">GitHub ↗</a>
        </div>
    </div>
    <main>
        <h1>Template lab</h1>
        <p class="lede">Each card seeds the playground with a different starting block tree. Pick one, get dropped into the editor, drag the blocks around. Resetting the demo (top-right of the playground) wipes back to a default landing.</p>

        <div class="grid">
            @foreach ($templates as $slug => $tpl)
                <form class="card" method="POST" action="{{ route('lab.use', $slug) }}">
                    @csrf
                    <h3>{{ $tpl['name'] }}</h3>
                    <p>{{ $tpl['summary'] }}</p>
                    <button type="submit">Use this template</button>
                </form>
            @endforeach
        </div>

        <p class="note">Templates use only built-in block types (heading, paragraph, button, hero, columns, list, quote, panel, divider) so they work without any host-app extensions. Add your own templates by extending <code>App\PageStudio\DemoTemplates::all()</code> in <a href="https://github.com/Logged-Cloud/studio-logged" style="color:#BBA8FF" target="_blank">studio-logged</a>.</p>
    </main>
</body>
</html>
