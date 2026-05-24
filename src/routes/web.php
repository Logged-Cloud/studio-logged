<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use LoggedCloud\PageStudio\Models\Page;

function studio_session_page(Request $request): Page
{
    $key = $request->session()->get('studio_page_id');

    if ($key && ($page = Page::find($key))) {
        return $page;
    }

    $page = Page::create([
        'blocks' => [
            ['id' => 'b-'.Str::random(6), 'type' => 'heading',   'settings' => ['text' => 'Welcome to Page Studio', 'level' => 'h1', 'align' => 'center']],
            ['id' => 'b-'.Str::random(6), 'type' => 'paragraph', 'settings' => ['text' => 'Drag, drop, edit. Your changes save to this browser session and reset when the demo prunes hourly.']],
            ['id' => 'b-'.Str::random(6), 'type' => 'button',    'settings' => ['label' => 'Preview', 'href' => '/preview', 'variant' => 'primary']],
        ],
        'status' => 'draft',
    ]);

    $request->session()->put('studio_page_id', $page->id);

    return $page;
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

Route::post('/reset', function (Request $request) {
    $id = $request->session()->pull('studio_page_id');
    if ($id) {
        Page::where('id', $id)->delete();
    }
    return redirect()->route('playground');
})->name('reset');
