{{--
    Base layout
    Tujuan:
    - Menghindari duplikasi <html><head> meta/fonts/vite di banyak layout.
    - Semua layout area (project-manager/programmer/qa/client) bisa extend file ini.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <style>
    html { overflow-y: scroll; }
</style>
        @include('layouts.partials.head', ['title' => trim($__env->yieldContent('title', config('app.name', 'DevPanel')))])
        @stack('styles')
    </head>

    <body class="font-sans antialiased" @stack('body-attrs')>
        @yield('body')
        @stack('scripts')
    </body>
</html>
