<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="SIM Sistem Informasi Meter — platform monitoring, pencatatan, dan pengelolaan data kWh meter terpadu.">
        <meta name="theme-color" content="#4263eb">
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <title>{{ config('app.name', 'SIM Sistem Informasi Meter') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
