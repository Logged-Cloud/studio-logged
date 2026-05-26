<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Playground · Studio Logged</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <style>
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0E1116; color: #E6EDF3; }
        .topnav { display: flex; align-items: center; justify-content: space-between; padding: .75rem 1.25rem; background: #161B22; border-bottom: 1px solid #30363D; gap: .5rem; flex-wrap: wrap; }
        .topnav a, .topnav button { color: #E6EDF3; text-decoration: none; background: transparent; border: 1px solid #30363D; padding: .4rem .75rem; border-radius: .4rem; cursor: pointer; font-size: .85rem; font-family: inherit; }
        .topnav .brand { font-weight: 700; letter-spacing: .02em; border: 0; padding: 0; }
        .topnav .group { display: flex; gap: .35rem; flex-wrap: wrap; }
        main { padding: 0; }
        @media (max-width: 640px) {
            .topnav { padding: .55rem .75rem; }
            .topnav a, .topnav button { padding: .3rem .55rem; font-size: .75rem; }
        }
    </style>
    @livewireStyles
</head>
<body>
    <div class="topnav">
        <a class="brand" href="/">Studio Logged</a>
        <div class="group">
            {{-- Page picker · switches the editor's bound page. Picking
                 a curated demo (Article / Product / Customer) reloads
                 the playground with ?page=N so the page-builder mounts
                 against that page row directly · same surface used to
                 view the live route, only here it's editable. The
                 first option goes back to the visitor's own session
                 page. --}}
            @if (! empty($pages))
                <select onchange="if (this.value) window.location.href = '/playground?page=' + this.value; else window.location.href = '/playground';"
                        style="background:#0E1116; color:#E6EDF3; border:1px solid #30363D; padding:.4rem .55rem; border-radius:.4rem; font-size:.85rem">
                    <option value="" {{ $currentPageKind === 'session' ? 'selected' : '' }}>My session page</option>
                    @foreach ($pages as $p)
                        <option value="{{ $p['id'] }}" {{ ($currentPageKind === 'route' && $pageId === $p['id']) ? 'selected' : '' }}>
                            {{ $p['label'] }}
                        </option>
                    @endforeach
                </select>
            @endif

            <a href="/lab">Templates</a>
            <a href="/preview" target="_blank">Preview ↗</a>
            <form method="POST" action="/reset" style="display:inline">@csrf
                <button type="submit" onclick="return confirm('Reset this demo page to defaults?')">Reset demo</button>
            </form>
        </div>
    </div>
    <main>
        <livewire:page-studio.page-builder :pageId="$pageId" />
    </main>
    @livewireScripts
    @stack('scripts')
</body>
</html>
