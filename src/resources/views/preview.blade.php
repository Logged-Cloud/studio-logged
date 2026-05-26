<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview · Studio Logged</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <style>
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #fff; color: #111; }
        .topnav { display: flex; align-items: center; justify-content: space-between; padding: .75rem 1.25rem; background: #161B22; color: #E6EDF3; border-bottom: 1px solid #30363D; gap: .5rem; flex-wrap: wrap; }
        .topnav a { color: #E6EDF3; text-decoration: none; background: transparent; border: 1px solid #30363D; padding: .4rem .75rem; border-radius: .4rem; font-size: .85rem; }
        .topnav a.brand { font-weight: 700; border: 0; padding: 0; }
        @media (max-width: 640px) {
            .topnav { padding: .55rem .75rem; }
            .topnav a { padding: .3rem .55rem; font-size: .75rem; }
            .canvas { padding: 2rem 1.25rem 4rem; }
        }
        .canvas { max-width: 56rem; margin: 0 auto; padding: 3rem 1.5rem 6rem; }
    </style>
</head>
<body>
    <div class="topnav">
        <a class="brand" href="/">Studio Logged</a>
        <a href="/playground">← Back to playground</a>
    </div>
    <div class="canvas">
        {!! \LoggedCloud\PageStudio\Support\PageRenderer::render($page->blocks ?? []) !!}
    </div>
</body>
</html>
