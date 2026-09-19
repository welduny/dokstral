<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ config('app.name', 'LEHBOOK') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        .auth-page{min-height:calc(100vh - 190px);display:grid;place-items:center;padding:38px 0 76px}.auth-card{width:min(900px,100%);display:grid;grid-template-columns:1fr 1fr;background:#fffefa;border:1px solid #e3e7df;box-shadow:0 18px 50px rgba(24,53,45,.08)}.auth-intro{display:flex;flex-direction:column;justify-content:center;padding:54px;background:#18352d;color:white;min-height:540px}.auth-intro .eyebrow{color:#f7c879;margin-top:42px}.auth-intro h1{font-size:43px;letter-spacing:-1px;margin-bottom:20px}.auth-intro h1 em{color:#f7c879}.auth-intro p:last-child{color:#b6c7bd;line-height:1.7;font-size:13px;max-width:260px}.auth-symbol{width:48px;height:48px;display:grid;place-items:center;color:#f7c879;border:1px solid rgba(247,200,121,.5);font-size:21px}.register-intro{background:#d96b44}.register-intro .eyebrow{color:white}.register-intro h1 em{color:#ffe0a4}.register-intro p:last-child{color:#fff0df}.auth-form-wrap{padding:54px 48px}.auth-form-wrap h2{font-size:34px}.form-subtitle{color:#718078;font-size:13px;margin:8px 0 32px}.field{margin-bottom:19px}.field label{display:block;color:#18352d;font-size:12px;font-weight:700;margin-bottom:8px}.field input{width:100%;padding:13px 14px;background:#fbfcf8;border:1px solid #d8e0d9;color:#18352d;font:inherit;font-size:13px;outline:none}.field input:focus{border-color:#18352d;box-shadow:0 0 0 3px rgba(24,53,45,.08)}.field input::placeholder{color:#aab4ae}.form-error{display:block;color:#c5533b;font-size:11px;margin-top:6px}.check-row{display:flex;align-items:center;gap:8px;color:#718078;font-size:12px;margin:4px 0 24px}.check-row input{accent-color:#18352d}.auth-submit{width:100%;justify-content:space-between}.forgot-link{display:block;text-align:center;color:#718078;font-size:12px;margin-top:18px}.forgot-link:hover,.auth-switch a{color:#d96b44}.auth-switch{color:#718078;font-size:12px;text-align:center;margin:32px 0 0}.auth-switch a{font-weight:700}@media(max-width:720px){.auth-card{grid-template-columns:1fr}.auth-intro{min-height:300px;padding:34px}.auth-intro .eyebrow{margin-top:28px}.auth-intro h1{font-size:36px}.auth-form-wrap{padding:36px 26px}.auth-page{padding-top:20px}}
</style>
    <style>
        .menu-toggle { display: none; border: 1px solid #e3e7df; background: #fffefa; color: #18352d; width: 40px; height: 40px; cursor: pointer; }
        .menu-toggle span { display: block; width: 18px; height: 2px; margin: 4px auto; background: currentColor; transition: transform .2s, opacity .2s; }
        .menu-toggle[aria-expanded="true"] span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
        .menu-toggle[aria-expanded="true"] span:nth-child(2) { opacity: 0; }
        .menu-toggle[aria-expanded="true"] span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }
        @media (max-width: 900px) {
            .shell { padding: 0 28px; }
            .hero { gap: 28px; }
            .hero-art { height: 330px; }
            .book-grid { grid-template-columns: repeat(2, 1fr); }
            .dashboard-content { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .shell { padding: 0 18px; }
            .topbar { height: 72px; }
            .brand { font-size: 15px; }
            .topnav { gap: 12px; font-size: 12px; }
            .topnav > a:first-child { display: none; }
            .nav-pill { padding: 10px 13px; }
            .hero { display: flex; flex-direction: column; align-items: stretch; min-height: 0; padding: 34px 0 42px; }
            h1 { font-size: clamp(42px, 13vw, 60px); }
            .hero-text { font-size: 14px; }
            .hero-actions { align-items: stretch; flex-direction: column; gap: 17px; }
            .hero-actions .button { justify-content: space-between; }
            .hero-art { height: 270px; }
            .art-sun { width: 130px; height: 130px; right: 22px; }
            .art-book { transform: scale(.72) rotate(-18deg); transform-origin: bottom right; }
            .book-two { transform: scale(.72) rotate(9deg); }
            .book-three { transform: scale(.72) rotate(-2deg); }
            .catalog-section { padding: 48px 0 55px; }
            .section-heading { align-items: flex-start; flex-direction: column; }
            h2 { font-size: 28px; }
            .book-grid, .stats-grid { grid-template-columns: 1fr; gap: 14px; }
            .book-card { padding: 14px; }
            .quote-band { margin: 0 -18px; padding: 40px 18px; }
            .quote-band p { font-size: 22px; }
            .footer { align-items: flex-start; flex-direction: column; gap: 10px; padding: 22px 0; }
            .dashboard-head { align-items: stretch; flex-direction: column; padding: 38px 0 28px; }
            .dashboard-head h1 { font-size: 38px; }
            .dashboard-head .button { justify-content: space-between; }
            .panel, .tip-panel { padding: 22px 18px; }
            .panel-heading { align-items: flex-start; flex-direction: column; }
            .mini-book { align-items: flex-start; flex-wrap: wrap; }
            .mini-book .availability { margin-left: 52px; }
            .auth-page { min-height: 0; padding: 20px 0 52px; }
            .auth-card { display: flex; flex-direction: column; }
            .auth-intro { min-height: 285px; padding: 32px 25px; }
            .auth-intro .eyebrow { margin-top: 25px; }
            .auth-intro h1 { font-size: 36px; }
            .auth-form-wrap { padding: 32px 22px; }
            .menu-toggle { display: block; }
            .topbar { position: relative; }
            .topnav { display: none; position: absolute; z-index: 10; top: 64px; left: 0; right: 0; flex-direction: column; align-items: stretch; gap: 0; padding: 8px; background: #fffefa; border: 1px solid #e3e7df; box-shadow: 0 14px 30px rgba(24,53,45,.12); }
            .topnav.is-open { display: flex; }
            .topnav a, .topnav form { padding: 13px 14px; }
            .topnav .nav-pill { text-align: center; }
            .topnav > a:first-child { display: block; }
        }
    </style>
        <style>
            .brand > span:last-child { font-size: 0; }
            .brand > span:last-child::after { content: 'LEHBOOK'; font-size: 17px; color: #18352d; }
            .book-action { margin-top: 14px; }
            .book-action .button { padding: 9px 11px; font-size: 11px; }
            .loan-confirm { margin-top: 14px; position: relative; }
            .loan-confirm summary { list-style: none; padding: 9px 11px; font-size: 11px; }
            .loan-confirm summary::-webkit-details-marker { display: none; }
            .loan-confirm[open] summary { background: #d96b44; }
            .loan-form { position: absolute; z-index: 5; top: calc(100% + 8px); right: 0; width: min(310px, 80vw); padding: 17px; background: #fffefa; border: 1px solid #e3e7df; box-shadow: 0 14px 30px rgba(24,53,45,.15); }
            .loan-rules strong { display: block; font: 700 16px 'Playfair Display', serif; margin-bottom: 8px; }
            .loan-rules p, .loan-rules li { color: #718078; font-size: 11px; line-height: 1.5; }
            .loan-rules p { margin-bottom: 8px; }
            .loan-rules ul { margin: 0 0 14px; padding-left: 17px; }
            .loan-agreement { display: flex; gap: 8px; align-items: flex-start; color: #18352d; font-size: 11px; line-height: 1.4; margin-bottom: 14px; }
            .loan-agreement input { flex: 0 0 auto; margin-top: 2px; accent-color: #18352d; }
            .loan-form .button { width: 100%; justify-content: space-between; padding: 10px; font-size: 11px; }
            .loan-page-head { align-items: center; }
            .loan-table-panel { margin: 0 0 80px; padding: 30px; background: #fffefa; border: 1px solid #e3e7df; }
            .loan-table-head { display: flex; align-items: end; justify-content: space-between; margin-bottom: 22px; }
            .loan-table-head h2 { margin: 0; font: 700 28px/1.1 'Playfair Display', serif; }
            .loan-row { display: grid; grid-template-columns: 42px minmax(180px, 1fr) 115px 115px minmax(245px, 280px); gap: 16px; align-items: center; padding: 16px 0; border-top: 1px solid #e3e7df; }
            .loan-book strong, .loan-book small, .loan-date strong, .loan-date small { display: block; }
            .loan-book strong { font-size: 13px; }
            .loan-book small, .loan-date small { color: #718078; font-size: 11px; margin-top: 4px; }
            .loan-date strong { font-size: 12px; }
            .late-text { color: #c5533b; }
            .loan-status { width: max-content; padding: 5px 8px; font-size: 10px; font-weight: 700; text-transform: capitalize; }
            .loan-admin-actions { display: flex; align-items: center; gap: 10px; min-width: 0; }
            .loan-admin-controls { display: flex; align-items: center; gap: 8px; min-width: 0; }
            .status-borrowed { color: #315f43; background: #edf7ee; }.status-returned { color: #526b83; background: #edf2f8; }.status-overdue { color: #8b3f30; background: #fff0eb; }.status-pending { color: #8b6730; background: #fff5dc; }.status-rejected { color: #777; background: #eee; }
            .status-form select { width: 100%; padding: 8px; border: 1px solid #d8e0d9; background: #fbfcf8; color: #18352d; font-size: 11px; }
            .admin-return { position: relative; }
            .admin-return summary { cursor: pointer; list-style: none; color: #fffefa; background: #18352d; padding: 9px 12px; font-size: 10px; font-weight: 700; letter-spacing: .2px; }
            .admin-return summary::-webkit-details-marker { display: none; }
            .admin-return summary::before { content: '+'; display: inline-block; margin-right: 6px; color: #f7c879; font-size: 14px; line-height: 8px; vertical-align: -1px; }
            .admin-return[open] summary { background: #d96b44; }
            .admin-return[open] summary::before { content: '-'; color: #fff0df; }
            .admin-return form { position: absolute; z-index: 4; right: 0; width: 280px; margin-top: 8px; padding: 20px; background: #fffefa; border: 1px solid #d8e0d9; box-shadow: 0 16px 34px rgba(24,53,45,.18); }
            .admin-return form::before { content: 'PEMROSESAN PENGEMBALIAN'; display: block; margin-bottom: 17px; color: #d96b44; font-size: 9px; font-weight: 700; letter-spacing: 1.5px; }
            .admin-return label { display: block; color: #18352d; font-size: 11px; font-weight: 700; margin-bottom: 13px; }
            .admin-return input, .admin-return select, .admin-return textarea { display: block; width: 100%; margin-top: 6px; padding: 10px 11px; border: 1px solid #d8e0d9; border-radius: 0; background: #f7f6f0; color: #18352d; font: inherit; font-size: 12px; outline: none; }
            .admin-return textarea { resize: vertical; min-height: 60px; }
            .admin-return input:focus, .admin-return select:focus, .admin-return textarea:focus { border-color: #d96b44; box-shadow: 0 0 0 3px rgba(217,107,68,.12); }
            .admin-return .button { width: 100%; justify-content: space-between; padding: 11px 12px; margin-top: 3px; font-size: 11px; }
            .loan-empty { padding: 50px 20px; text-align: center; }.loan-empty span { color: #d96b44; font-size: 27px; }.loan-empty h3 { margin: 12px 0 6px; font: 700 22px 'Playfair Display', serif; }
            .admin-page-head { align-items: center; }
            .admin-form-panel { max-width: 760px; margin: 0 0 80px; padding: 32px; background: #fffefa; border: 1px solid #e3e7df; }
            .admin-form-heading h2 { margin: 0 0 26px; font: 700 30px/1.1 'Playfair Display', serif; }
            .book-create-form { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
            .admin-field label { display: block; margin-bottom: 7px; color: #18352d; font-size: 12px; font-weight: 700; }.admin-field label span { color: #a3ada6; font-weight: 400; }
            .admin-field input { width: 100%; padding: 12px; border: 1px solid #d8e0d9; background: #f7f6f0; color: #18352d; outline: none; }.admin-field input:focus { border-color: #d96b44; box-shadow: 0 0 0 3px rgba(217,107,68,.12); }.admin-field small { color: #c5533b; font-size: 11px; }
            .admin-check { grid-column: 1 / -1; display: flex; gap: 9px; align-items: center; color: #718078; font-size: 12px; }.admin-check input { accent-color: #18352d; }.book-create-form > .button { grid-column: 1 / -1; justify-content: space-between; width: max-content; min-width: 150px; }
            .book-login-link { display: block; margin-top: 12px; color: #d96b44; font-size: 11px; font-weight: 700; }
            .flash { margin: 18px 0 0; padding: 13px 16px; border: 1px solid; font-size: 13px; }
            .flash-success { color: #315f43; background: #edf7ee; border-color: #b9d8be; }
            .flash-error { color: #8b3f30; background: #fff0eb; border-color: #edc2b7; }
            .footer span:first-child { font-size: 0; }
            .footer span:first-child::after { content: 'LEHBOOK'; font-size: 12px; }
            @media (max-width: 640px) { .brand > span:last-child::after { font-size: 15px; } }
        </style>
        <style>
            @media (max-width: 640px) {
                .loan-page-head { align-items: stretch; }
                .loan-table-panel { padding: 22px 16px; margin-bottom: 52px; }
                .loan-table-head { align-items: flex-start; flex-direction: column; gap: 12px; }
                .loan-row { grid-template-columns: 42px 1fr; gap: 10px 12px; padding: 18px 0; }
                .loan-date, .loan-status, .status-form { grid-column: 2; }
                .loan-date { display: inline-block; margin-right: 12px; }
                .status-form select { max-width: 180px; }
                .loan-admin-actions { grid-column: 2; align-items: flex-start; flex-direction: column; }
                .loan-admin-controls { width: 100%; flex-wrap: wrap; }
                .admin-return form { right: auto; left: 0; width: min(280px, calc(100vw - 70px)); }
                .admin-form-panel { padding: 24px 18px; margin-bottom: 52px; }.book-create-form { grid-template-columns: 1fr; }.admin-check, .book-create-form > .button { grid-column: auto; }.book-create-form > .button { width: 100%; }
            }
        </style>
    </head>
<body><div class="shell"><header class="topbar"><a class="brand" href="{{ url('/') }}"><span class="brand-mark">L</span><span>Lentera<span class="brand-muted"> Library</span></span></a><button class="menu-toggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="main-navigation"><span></span><span></span><span></span></button><nav class="topnav" id="main-navigation"><a href="{{ url('/') }}">Katalog</a>@auth @if(Auth::user()->role === 'admin')<a href="{{ route('admin.books.create') }}" class="nav-pill">Tambah buku</a><a href="{{ route('admin.loans.index') }}">Kelola status</a>@else<a href="{{ route('home') }}" class="nav-pill">Dashboard</a><a href="{{ route('loans.mine') }}">Peminjaman</a>@endif<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="nav-logout">Keluar</button></form>@else<a href="{{ route('login') }}">Masuk</a><a href="{{ route('register') }}" class="nav-pill">Daftar</a>@endauth</nav></header><main>@if(session('success'))<div class="flash flash-success">{{ session('success') }}</div>@endif @if(session('error'))<div class="flash flash-error">{{ session('error') }}</div>@endif @if($errors->any())<div class="flash flash-error">{{ $errors->first() }}</div>@endif @yield('content')</main><footer class="footer"><span>LEHBOOK</span>@auth @if(Auth::user()->role === 'admin')<a href="{{ route('admin.books.create') }}">Tambah buku</a><a href="{{ route('admin.loans.index') }}">Kelola status</a>@else<a href="{{ route('loans.mine') }}">Peminjaman saya</a>@endif @else<a href="{{ route('login') }}">Peminjaman saya</a>@endauth<span>Ruang kecil untuk ide-ide besar.</span></footer></div><script>const menuToggle = document.querySelector('.menu-toggle'); const mainNavigation = document.querySelector('#main-navigation'); if (menuToggle && mainNavigation) { menuToggle.addEventListener('click', () => { const isOpen = menuToggle.getAttribute('aria-expanded') === 'true'; menuToggle.setAttribute('aria-expanded', String(!isOpen)); menuToggle.setAttribute('aria-label', isOpen ? 'Buka menu' : 'Tutup menu'); mainNavigation.classList.toggle('is-open', !isOpen); }); }</script></body>
</html>