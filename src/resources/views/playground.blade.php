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
        .topnav { display: flex; align-items: center; justify-content: space-between; padding: .75rem 1.25rem; background: #161B22; border-bottom: 1px solid #30363D; }
        .topnav a, .topnav button { color: #E6EDF3; text-decoration: none; background: transparent; border: 1px solid #30363D; padding: .4rem .75rem; border-radius: .4rem; cursor: pointer; font-size: .85rem; }
        .topnav .brand { font-weight: 700; letter-spacing: .02em; }
        .topnav .group { display: flex; gap: .5rem; }
        main { padding: 0; }
    </style>
    @livewireStyles
</head>
<body>
    <div class="topnav">
        <a class="brand" href="/">Studio Logged</a>
        <div class="group">
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
