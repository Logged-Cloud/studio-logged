<?php

use App\PageStudio\DemoTemplates;
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

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('/playground', function (Request $request) {
    $page = studio_session_page($request);
    return view('playground', ['pageId' => $page->id]);
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
