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
    $page = studio_session_page($request);
    return view('playground', [
        'pageId'    => $page->id,
        'templates' => \App\PageStudio\DemoTemplates::all(),
        // Curated demo routes that pair a real Eloquent model with a
        // wired node graph · listed in the playground topnav so visitors
        // can jump to a working example without leaving the page.
        'demoRoutes' => [
            ['label' => 'Article · /docs/{slug}',       'url' => '/docs/getting-started'],
            ['label' => 'Product · /products/{sku}',    'url' => '/products/STUDIO-PRO'],
            ['label' => 'Customer · /customers/{email}','url' => '/customers/ada@example.com'],
        ],
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
