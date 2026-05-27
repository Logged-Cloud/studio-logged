<?php

use App\PageStudio\DemoTemplates;
use App\PageStudio\HomePage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LoggedCloud\PageStudio\Models\Page;

if (! function_exists('studio_session_page')) {
function studio_session_page(Request $request, ?string $templateSlug = null): Page
{
    $key = $request->session()->get('studio_page_id');

    if ($key && ($page = Page::find($key))) {
        if ($templateSlug !== null) {
            $tpl = DemoTemplates::find($templateSlug);
            if ($tpl) {
                $page->blocks = ($tpl['blocks'])();
                $page->save();
            }
        }
        return $page;
    }

    $tpl    = DemoTemplates::find($templateSlug ?? 'landing') ?? DemoTemplates::find('blank');
    $blocks = ($tpl['blocks'])();

    $page = Page::create(['blocks' => $blocks, 'status' => 'draft']);
    $request->session()->put('studio_page_id', $page->id);

    return $page;
}
}

Route::get('/', function () {
    return view('welcome', ['homePage' => HomePage::get()]);
})->name('home');

Route::get('/playground', function (Request $request) {
    // Visitor can land on /playground?seed=home to fork the curated home
    // page into their session and play with it.
    if ($request->query('seed') === 'home') {
        $blocks = \App\PageStudio\DemoTemplates::home();
        $page   = studio_session_page($request);
        $page->blocks = $blocks;
        $page->save();
        return redirect()->route('playground');
    }

    // Build the picker · one entry per curated demo route (Article /
    // Product / Customer). Each entry resolves to a real Page row so
    // the editor can mount against it directly with no template
    // copying, no fork-into-session, no double round-trip.
    $demoPages = [];
    $demoRouteNames = [
        'docs.show'                 => 'Article · text manipulation graph',
        'products.show'             => 'Product · math + currency graph',
        'customers.show'            => 'Customer · split + concat graph',
        'showcase.vintage'          => 'Vintage photo · image pipeline graph',
        'showcase.setting-sockets'  => 'Setting sockets · every setting wired',
    ];
    foreach ($demoRouteNames as $routeName => $label) {
        $rd = \LoggedCloud\PageStudio\Models\RouteDefinition::where('name', $routeName)->first();
        if (! $rd) continue;
        $pg = \LoggedCloud\PageStudio\Models\Page::where('route_id', $rd->id)->first();
        if (! $pg) continue;
        $demoPages[] = ['id' => $pg->id, 'label' => $label, 'route' => $rd->path_template];
    }

    // Picker selection · ?page=N points at one of the demo pages we
    // just discovered. Unknown / missing values silently fall back to
    // the visitor's session page so a stale bookmark never 404s.
    $requestedPageId = (int) $request->query('page');
    $editedPage      = null;
    $currentKind     = 'session';
    if ($requestedPageId > 0) {
        foreach ($demoPages as $entry) {
            if ($entry['id'] === $requestedPageId) {
                $editedPage  = \LoggedCloud\PageStudio\Models\Page::find($requestedPageId);
                $currentKind = 'route';
                break;
            }
        }
    }
    $editedPage ??= studio_session_page($request);

    return view('playground', [
        'pageId'          => $editedPage->id,
        'pages'           => $demoPages,
        'currentPageKind' => $currentKind,
    ]);
})->name('playground');

Route::get('/preview', function (Request $request) {
    $page = studio_session_page($request);
    return view('preview', ['page' => $page]);
})->name('preview');

Route::get('/lab', fn () => view('lab', ['templates' => DemoTemplates::all()]))->name('lab');

Route::post('/lab/use/{slug}', function (Request $request, string $slug) {
    abort_unless(DemoTemplates::find($slug), 404);
    studio_session_page($request, $slug);
    return redirect()->route('playground');
})->name('lab.use');

Route::post('/reset', function (Request $request) {
    $id = $request->session()->pull('studio_page_id');
    if ($id) {
        Page::where('id', $id)->delete();
    }
    return redirect()->route('playground');
})->name('reset');
