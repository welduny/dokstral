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
        .handoff-qr { --handoff-qr-size:160px; display:grid; grid-template-columns:var(--handoff-qr-size) minmax(0, 1fr); gap:12px; align-items:center; width:100%; min-width:0; margin-top:12px; padding:10px; background:#fffefa; border:1px solid #e3e7df; }
        .handoff-qr canvas { display:block; flex:none; width:var(--handoff-qr-size)!important; height:var(--handoff-qr-size)!important; max-width:var(--handoff-qr-size); aspect-ratio:1; }
        .handoff-qr > div { min-width:0; overflow-wrap:anywhere; }
        .handoff-qr .handoff-token { grid-column:1 / -1; display:grid; grid-template-columns:minmax(0, 1fr) auto; gap:6px 10px; align-items:center; padding-top:9px; border-top:1px solid #e3e7df; }
        .handoff-token small { grid-column:1 / -1; margin:0; color:#718078; font-size:10px; }
        .handoff-token code { min-width:0; color:#18352d; font:11px/1.5 monospace; overflow-wrap:anywhere; user-select:all; }
        .handoff-token button { border:1px solid #d8e0d9; padding:7px 9px; color:#18352d; background:#fffefa; font-size:10px; font-weight:700; cursor:pointer; }
        .handoff-token button[data-copied="true"] { color:#315f43; border-color:#b9d8be; }
        .handoff-qr strong { display:block; margin-bottom:5px; font-size:12px; }
        .handoff-qr small { display:block; color:#718078; font-size:10px; line-height:1.5; }
        .member-fine { display:grid; gap:5px; margin-top:10px; padding:10px 12px; border-left:3px solid #d96b44; background:#fff4e8; }
        .member-fine strong { color:#8b3f30; font-size:12px; }
        .member-fine small { color:#715f52; font-size:11px; line-height:1.45; }
        .fine-paid-form { margin:0; }
        .fine-paid-form .button { padding:8px 10px; font-size:10px; white-space:nowrap; }
        .fine-paid-label { padding:6px 8px; color:#315f43; background:#edf7ee; font-size:10px; font-weight:700; white-space:nowrap; }
        .loan-delete-form { margin:0; }
        .loan-delete-button { padding:8px 10px; border:1px solid #edc2b7; color:#8b3f30; background:#fff0eb; font-size:10px; font-weight:700; white-space:nowrap; cursor:pointer; transition:background-color var(--motion-fast) ease, border-color var(--motion-fast) ease, transform var(--motion-fast) ease; }
        .loan-delete-button:hover { border-color:#c5533b; background:#fbe0d8; }
        .loan-delete-button:active { transform:scale(.97); }
        .scan-panel { max-width:720px; margin:0 0 80px; padding:28px; background:#fffefa; border:1px solid #e3e7df; }
        #handoff-reader { width:100%; min-height:300px; border:0!important; }
        #handoff-reader video { object-fit:cover; }
        #handoff-reader button { border:0; padding:10px 14px; color:white; background:#18352d; cursor:pointer; }
        #handoff-reader select { padding:8px; border:1px solid #d8e0d9; }
        .scan-manual { display:grid; gap:9px; margin-top:18px; }
        .scan-manual label { color:#18352d; font-size:13px; font-weight:700; }
        .scan-manual-controls { display:flex; gap:10px; }
        .scan-manual-controls input { flex:1; min-width:0; padding:11px 12px; border:1px solid #d8e0d9; color:#18352d; font:inherit; font-size:14px; letter-spacing:2px; }
        .scan-manual-controls input:focus { outline:3px solid rgba(24,53,45,.16); border-color:#18352d; }
        .scan-manual-controls button { border:0; padding:10px 16px; color:white; background:#18352d; font:inherit; font-size:13px; font-weight:700; cursor:pointer; }
        .scan-manual small { color:#718078; font-size:11px; }
        .scan-result { min-height:48px; margin-top:16px; padding:12px; background:#f7f6f0; color:#18352d; font-size:13px; }
        .scan-result[data-state="error"] { color:#8b3f30; background:#fff0eb; }
        .catalog-filters { display:grid; grid-template-columns:minmax(220px,1fr) minmax(160px,.55fr) auto auto auto; gap:10px; align-items:center; margin:26px 0 24px; }
        .catalog-search { display:flex; align-items:center; gap:10px; min-width:0; padding:0 13px; background:#fffefa; border:1px solid #d8e0d9; }
        .catalog-search > span { color:#d96b44; font-size:22px; }
        .catalog-search input, .filter-select select { width:100%; min-width:0; height:44px; border:0; outline:0; color:#18352d; background:transparent; font:inherit; font-size:13px; }
        .filter-select { min-width:0; padding:0 12px; background:#fffefa; border:1px solid #d8e0d9; }
        .filter-availability { display:flex; align-items:center; gap:7px; white-space:nowrap; color:#718078; font-size:12px; }
        .filter-availability input { accent-color:#18352d; }
        .catalog-filters > .button { height:44px; justify-content:space-between; padding:10px 13px; font-size:11px; }
        .catalog-reset { color:#d96b44; font-size:12px; font-weight:700; }
        .book-detail-trigger { display:flex; width:100%; min-width:0; flex:1 1 100%; gap:15px; padding:0; border:0; text-align:left; color:inherit; background:transparent; cursor:pointer; }
        .book-info { display:flex; flex-direction:column; align-items:flex-start; gap:5px; }
        .book-info .book-title { color:#18352d; font:700 17px/1.2 'Playfair Display',serif; }
        .book-author { color:#718078; font-size:12px; }
        .book-info .availability { margin-top:5px; }
        .book-rating { color:#718078; font-size:11px; }
        .book-rating > span { color:#d39b50; }
        .book-rating small { color:#89938c; }
        .book-cover { position:relative; overflow:hidden; }
        .book-cover img { width:100%; height:100%; object-fit:cover; }
        .book-cover .cover-fallback { display:grid; position:absolute; inset:0; place-items:center; color:rgba(255,255,255,.85); font:700 32px Georgia,serif; }
        .book-actions { display:flex; align-items:center; gap:10px; width:100%; margin-top:10px; }
        .favorite-form { margin:0; }
        .favorite-toggle { display:grid; place-items:center; width:38px; height:38px; border:1px solid #e3e7df; color:#d96b44; background:#fffefa; font-size:20px; cursor:pointer; }
        .favorite-toggle.is-favorite { color:#fffefa; background:#d96b44; border-color:#d96b44; }
        .book-detail-dialog { width:min(760px,calc(100vw - 32px)); max-width:none; max-height:min(86dvh,800px); padding:0; overflow:auto; border:1px solid #e3e7df; color:#18352d; background:#fffefa; box-shadow:0 28px 90px rgba(0,0,0,.28); }
        .book-detail-dialog::backdrop { background:rgba(16,34,29,.62); backdrop-filter:blur(3px); }
        .book-detail-close { position:sticky; z-index:2; top:12px; float:right; display:grid; place-items:center; width:38px; height:38px; margin:12px 12px -50px 0; border:1px solid #e3e7df; color:#18352d; background:#fffefa; font-size:24px; cursor:pointer; }
        .book-detail-layout { display:grid; grid-template-columns:minmax(200px,.72fr) minmax(0,1.28fr); gap:30px; padding:36px; }
        .book-detail-cover { position:relative; min-height:350px; overflow:hidden; background:#dce8db; }
        .book-detail-cover img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
        .book-detail-cover > span { display:grid; position:absolute; inset:0; place-items:center; color:rgba(255,255,255,.85); font:700 64px Georgia,serif; }
        .book-detail-copy { min-width:0; padding-top:8px; }
        .book-detail-copy .eyebrow { margin-bottom:12px; }
        .book-detail-copy h2 { max-width:100%; padding-right:22px; font-size:32px; overflow-wrap:anywhere; }
        .book-detail-author { margin:8px 0; color:#718078; }
        .book-detail-availability { color:#4d795b; font-size:12px; font-weight:700; }
        .book-description { margin:18px 0 12px; color:#52645a; font-size:14px; line-height:1.7; }
        .book-isbn { color:#89938c; font-size:11px; }
        .book-detail-reviews { display:grid; gap:8px; margin:20px 0; padding-top:16px; border-top:1px solid #e3e7df; }
        .book-detail-reviews > span { color:#8c6a2f; font-size:12px; }
        .book-detail-reviews blockquote { margin:0; padding:10px 12px; background:#f7f6f0; }
        .book-detail-reviews blockquote > span { color:#c58a29; font-size:12px; }
        .book-detail-reviews blockquote p { margin:5px 0; color:#52645a; font-size:12px; line-height:1.5; }
        .book-detail-reviews blockquote small { color:#89938c; font-size:10px; }
        .book-review-form { display:grid; gap:8px; padding-top:14px; border-top:1px solid #e3e7df; }
        .book-review-form label { color:#18352d; font-size:11px; font-weight:700; }
        .book-review-form select, .book-review-form textarea { width:100%; padding:10px; border:1px solid #d8e0d9; color:#18352d; background:#fffefa; font:inherit; font-size:12px; }
        .book-review-form .button { justify-content:space-between; margin-top:4px; }
        .catalog-empty { grid-column:1/-1; padding:28px; background:#fffefa; border:1px solid #e3e7df; }
        .catalog-pagination { margin-top:26px; }
        .reading-challenge { padding:27px; color:#fffefa; background:#18352d; }
        .reading-challenge .eyebrow { color:#f7c879; }
        .reading-challenge h2 { margin-bottom:15px; color:#fffefa; font-size:28px; }
        .reading-challenge h2 span { color:#b6c7bd; font:500 15px 'DM Sans',sans-serif; }
        .reading-challenge .muted { color:#b6c7bd; }
        .challenge-track { height:8px; margin-bottom:12px; overflow:hidden; background:rgba(255,255,255,.16); }
        .challenge-track span { display:block; height:100%; background:#f7c879; transition:width .5s ease; }
        .challenge-form { display:grid; gap:7px; margin-top:18px; }
        .challenge-form label { color:#dce8db; font-size:11px; font-weight:700; }
        .challenge-form > div { display:flex; gap:8px; }
        .challenge-form input { width:78px; padding:9px; border:1px solid rgba(255,255,255,.25); color:#fffefa; background:rgba(255,255,255,.08); }
        .challenge-form .button { justify-content:space-between; padding:9px 12px; font-size:11px; }
        .due-reminders { margin:0 0 32px; padding:22px; background:#fffefa; border:1px solid #e3e7df; }
        .reminder-list { display:grid; }
        .reminder-item { display:flex; align-items:center; gap:12px; padding:13px 0; border-top:1px solid #e3e7df; }
        .reminder-item > span:nth-child(2) { flex:1; min-width:0; }
        .reminder-item strong, .reminder-item small { display:block; overflow-wrap:anywhere; }
        .reminder-item strong { font-size:13px; }
        .reminder-item small { margin-top:4px; color:#718078; font-size:11px; }
        .reminder-mark { display:grid; flex:none; place-items:center; width:32px; height:32px; color:#8b3f30; background:#fff0eb; font-weight:700; }
        .loan-success-eyebrow { color:#2e8b57; font-size:9px; font-weight:700; letter-spacing:1.4px; }
        .loan-success-progress { position:absolute; right:0; bottom:0; left:0; height:3px; background:#d96b44; transform:scaleX(1); transform-origin:left; animation:success-progress 2s linear forwards; }
        .loan-success-notice { position:fixed; z-index:100; inset:0; display:grid; place-items:center; padding:20px; background:rgba(18,38,32,.48); backdrop-filter:blur(3px); animation:notice-fade-in .22s ease-out both; }
        .loan-success-notice[hidden] { display:none; }
        .loan-success-dialog { position:relative; width:min(370px,100%); padding:32px 28px 30px; overflow:hidden; text-align:center; background:#fffefa; border:1px solid #e3e7df; box-shadow:0 25px 80px rgba(0,0,0,.2); animation:toast-enter .38s cubic-bezier(.2,.8,.2,1) both; }
        .loan-success-close { position:absolute; top:11px; right:14px; display:grid; place-items:center; width:30px; height:30px; border:0; color:#718078; background:#f3f5f0; font-size:21px; cursor:pointer; }
        .loan-success-check { display:grid; place-items:center; width:66px; height:66px; margin:0 auto 16px; border-radius:50%; color:#25824f; background:#e8f5eb; animation:success-icon-in .44s .08s cubic-bezier(.2,.8,.2,1) both; }
        .loan-success-check svg { width:43px; height:43px; fill:none; stroke:currentColor; stroke-width:3.5; stroke-linecap:round; stroke-linejoin:round; }
        .loan-success-check path { stroke-dasharray:40; stroke-dashoffset:40; animation:check-draw .38s .32s ease-out forwards; }
        .loan-success-eyebrow { margin:0 0 8px; }
        .loan-success-dialog h2 { margin:0 0 13px; font:700 25px/1.15 'Playfair Display',serif; }
        .loan-success-book { margin:0 auto 7px; max-width:290px; color:#18352d; font-size:14px; font-weight:700; }
        .loan-success-due { margin:0; color:#718078; font-size:12px; }
        .loan-success-shelf { margin:10px 0 0; padding:10px 12px; color:#315f43; background:#edf7ee; font-size:12px; font-weight:700; line-height:1.45; }
        .loan-book .loan-shelf-location { display:block; margin-top:9px; padding:8px 10px; color:#315f43; background:#edf7ee; font-size:11px; line-height:1.5; }
        .admin-handoff-notice { position:fixed; z-index:110; inset:0; display:grid; place-items:center; padding:20px; background:rgba(18,38,32,.48); backdrop-filter:blur(3px); animation:notice-fade-in .22s ease-out both; }
        .admin-handoff-notice[hidden] { display:none; }
        .admin-handoff-dialog { position:relative; width:min(410px,100%); padding:32px 28px 30px; overflow:hidden; text-align:center; background:#fffefa; border:1px solid #e3e7df; box-shadow:0 25px 80px rgba(0,0,0,.2); animation:toast-enter .38s cubic-bezier(.2,.8,.2,1) both; }
        .admin-handoff-close { position:absolute; top:11px; right:14px; display:grid; place-items:center; width:30px; height:30px; border:0; color:#718078; background:#f3f5f0; font-size:21px; cursor:pointer; }
        .admin-handoff-dialog h2 { margin:0 0 13px; font:700 25px/1.15 'Playfair Display',serif; }
        .admin-handoff-book { margin:0 auto 7px; max-width:330px; color:#18352d; font-size:14px; font-weight:700; overflow-wrap:anywhere; }
        .admin-handoff-copy { margin:5px 0 0; color:#718078; font-size:12px; line-height:1.5; }
        .admin-handoff-progress { position:absolute; right:0; bottom:0; left:0; height:3px; background:#d96b44; transform:scaleX(1); transform-origin:left; animation:success-progress 4s linear forwards; }
        @keyframes notice-fade-in { from { opacity:0; } to { opacity:1; } }
        @keyframes notice-pop-in { from { opacity:0; transform:translateY(18px) scale(.94); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toast-enter { from { opacity:0; transform:translateY(15px) scale(.97); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes success-icon-in { from { opacity:0; transform:scale(.55) rotate(-12deg); } to { opacity:1; transform:scale(1) rotate(0); } }
        @keyframes check-draw { to { stroke-dashoffset:0; } }
        @keyframes success-progress { to { transform:scaleX(0); } }
        @media(prefers-reduced-motion:reduce) { .loan-success-notice, .loan-success-dialog, .loan-success-check, .loan-success-check path, .loan-success-progress, .admin-handoff-notice, .admin-handoff-dialog, .admin-handoff-progress { animation-duration:.01ms!important; animation-delay:0ms!important; } }
        @media(max-width:640px) { .handoff-qr { --handoff-qr-size:140px; }.handoff-token { grid-template-columns:minmax(0, 1fr); }.handoff-token button { justify-self:start; }.scan-panel { padding:20px 14px; }.scan-manual-controls { flex-direction:column; }.scan-manual-controls button { min-height:44px; }.loan-success-dialog { padding:28px 22px 26px; }.admin-handoff-notice { padding:14px; }.admin-handoff-dialog { width:100%; max-width:370px; padding:28px 20px 26px; }.admin-handoff-dialog h2 { font-size:23px; } }
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
            .loan-form { position: fixed; z-index: 60; top: 50%; left: 50%; width: min(360px, calc(100vw - 28px)); max-height: min(78dvh, 620px); overflow-y: auto; padding: 17px; background: #fffefa; border: 1px solid #e3e7df; box-shadow: 0 14px 30px rgba(24,53,45,.15); transform: translate(-50%, -50%); }
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
            .admin-field input[type="file"] { padding:8px; }
            .admin-field .field-hint { display:block; margin-top:6px; color:#718078; font-size:11px; line-height:1.45; }
            .field .field-hint { display:block; margin-top:7px; color:var(--muted); font-size:11px; line-height:1.5; }
            .admin-cover-preview { display:flex; align-items:center; gap:12px; margin-bottom:10px; color:#718078; font-size:11px; }
            .admin-cover-preview img { width:52px; height:72px; object-fit:cover; border:1px solid #e3e7df; background:#f7f6f0; }
            .admin-check { grid-column: 1 / -1; display: flex; gap: 9px; align-items: center; color: #718078; font-size: 12px; }.admin-check input { accent-color: #18352d; }.book-create-form > .button { grid-column: 1 / -1; justify-content: space-between; width: max-content; min-width: 150px; }
            .book-login-link { display: block; margin-top: 12px; color: #d96b44; font-size: 11px; font-weight: 700; }
            .book-admin-link { display:inline-flex; align-items:center; gap:8px; padding:9px 12px; border:1px solid #d8e0d9; color:#18352d; background:#fffefa; font-size:11px; font-weight:700; transition:border-color var(--motion-fast) ease, background-color var(--motion-fast) ease; }
            .book-admin-link:hover { border-color:#18352d; background:#f7f6f0; }
            .book-admin-link span { color:var(--accent); }
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
        <style>
            html { min-width:0; overflow-x:hidden; }
            body { min-width:0; overflow-x:hidden; }
            .shell { --responsive-gutter:48px; width:100%; min-width:0; }
            .quote-band { margin-right:calc(var(--responsive-gutter) * -1); margin-left:calc(var(--responsive-gutter) * -1); padding-right:var(--responsive-gutter); padding-left:var(--responsive-gutter); }
            main, main > *, .book-card, .book-info, .panel, .tip-panel, .loan-table-panel, .loan-row, .loan-book { min-width:0; }
            img, video, canvas, svg { max-width:100%; }
            .button, button, input, select, textarea { max-width:100%; }
            .dashboard-head > div, .section-heading > div, .panel-heading > div { min-width:0; }
            .dashboard-head h1, .section-heading h2, .loan-table-head h2, .loan-book strong, .mini-book strong { overflow-wrap:anywhere; }
            .book-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
            .book-card { align-items:flex-start; }
            .book-cover { flex:0 0 78px; }
            .book-info { flex:1 1 0; }
            .book-info h3, .book-info p { overflow-wrap:anywhere; }
            .dashboard-content { grid-template-columns:minmax(0,1.5fr) minmax(0,1fr); }
            .stats-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
            .stat-card { min-width:0; }
            .topnav { min-width:0; }
            .loan-row { grid-template-columns:36px minmax(120px,1fr) 88px 88px minmax(170px,220px); gap:10px; }
            .loan-admin-actions, .loan-admin-controls { flex-wrap:wrap; }
            .loan-admin-controls > *, .loan-admin-actions > * { min-width:0; }
            .fine-paid-form .button, .admin-return summary { white-space:normal; }
            .admin-page-head > div:last-child { display:flex; flex-wrap:wrap; gap:10px; }
            .admin-page-head > div:last-child .button { justify-content:space-between; }
            .loan-table-panel { max-width:100%; }
            .loan-table-head { gap:14px; }
            .auth-card, .auth-form-wrap, .admin-form-panel { min-width:0; }
            .auth-form-wrap { overflow-wrap:anywhere; }

            @media (max-width: 980px) {
                .shell { --responsive-gutter:28px; padding-right:28px; padding-left:28px; }
                .topbar { gap:18px; }
                .topnav { gap:14px; font-size:12px; }
                .hero { grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr); gap:24px; min-height:450px; }
                .hero-art { height:330px; }
                .book-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
                .dashboard-content { grid-template-columns:minmax(0,1.2fr) minmax(0,1fr); }
                .stats-grid { gap:12px; }
                .stat-card { gap:12px; padding:17px; }
                .loan-table-panel { padding:24px 20px; }
                .loan-row { grid-template-columns:34px minmax(100px,1fr) 78px 78px minmax(150px,190px); gap:8px; }
                .loan-date strong { overflow-wrap:anywhere; }
            }

            @media (max-width: 821px) {
                .menu-toggle { display:block; flex:0 0 40px; }
                .topbar { position:relative; z-index:40; }
                .topnav { display:none; position:absolute; z-index:30; top:calc(100% - 5px); left:0; right:0; flex-direction:column; align-items:stretch; gap:0; padding:8px; background:#fffefa; border:1px solid #e3e7df; box-shadow:0 14px 30px rgba(24,53,45,.14); }
                .topnav.is-open { display:flex; }
                .topnav > a:first-child { display:block; }
                .topnav a, .topnav form { width:100%; padding:13px 14px; }
                .topnav form { margin:0; }
                .topnav .nav-pill { text-align:left; }
                .topnav .nav-logout { width:100%; text-align:left; }
                .dashboard-content { grid-template-columns:minmax(0,1fr); }
                .stats-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
                .loan-row { grid-template-columns:34px minmax(0,1fr) 78px 78px minmax(130px,170px); }
                .loan-admin-controls { align-items:flex-start; }
            }

            @media (max-width: 640px) {
                .shell { --responsive-gutter:18px; padding-right:18px; padding-left:18px; }
                .shell .quote-band { margin-right:-18px; margin-left:-18px; padding-right:18px; padding-left:18px; }
                .topbar { height:72px; }
                .brand { min-width:0; font-size:15px; }
                .hero { display:flex; flex-direction:column; align-items:stretch; gap:22px; min-height:0; padding:34px 0 42px; }
                .hero-copy h1 { font-size:clamp(40px,12vw,56px); overflow-wrap:anywhere; }
                .hero-actions { align-items:stretch; flex-direction:column; gap:16px; }
                .hero-actions .button { justify-content:space-between; }
                .hero-art { height:260px; }
                .art-sun { width:130px; height:130px; right:22px; }
                .art-book { transform:scale(.68) rotate(-18deg); transform-origin:bottom right; }
                .book-two { transform:scale(.68) rotate(9deg); }
                .book-three { transform:scale(.68) rotate(-2deg); }
                .catalog-section { padding:46px 0 54px; }
                .section-heading, .panel-heading, .dashboard-head { align-items:flex-start; flex-direction:column; }
                .section-heading { gap:10px; }
                .section-heading h2, .panel-heading h2 { font-size:26px; overflow-wrap:anywhere; }
                .book-grid, .stats-grid { grid-template-columns:minmax(0,1fr); gap:12px; }
                .book-card { gap:14px; padding:14px; }
                .book-cover { flex-basis:68px; height:100px; }
                .book-info h3 { font-size:16px; }
                .loan-confirm summary { max-width:100%; }
                .quote-band { margin:0 -18px; padding:38px 18px; }
                .quote-band p { font-size:21px; }
                .footer { align-items:flex-start; flex-direction:column; gap:10px; padding:22px 0; }
                .dashboard-head { padding:36px 0 26px; }
                .dashboard-head h1 { font-size:36px; }
                .dashboard-head .button { width:100%; justify-content:space-between; }
                .dashboard-head > div:last-child { width:100%; }
                .dashboard-head > div:last-child .button { flex:1 1 100%; }
                .panel, .tip-panel { padding:21px 17px; }
                .panel-heading { gap:10px; }
                .mini-book { align-items:flex-start; flex-wrap:wrap; }
                .mini-book > div { flex:1 1 calc(100% - 56px); min-width:0; }
                .mini-book .availability { margin-left:52px; }
                .auth-page { min-height:0; padding:18px 0 48px; }
                .auth-card { display:flex; flex-direction:column; }
                .auth-intro { min-height:0; padding:28px 23px; }
                .auth-intro .eyebrow { margin-top:24px; }
                .auth-intro h1 { font-size:34px; }
                .auth-form-wrap { padding:30px 21px; }
                .auth-form-wrap h2 { font-size:30px; }
                .auth-switch { line-height:1.6; }
                .loan-page-head { align-items:stretch; }
                .loan-table-panel { padding:20px 14px; margin-bottom:50px; }
                .loan-table-head { align-items:flex-start; flex-direction:column; gap:10px; }
                .loan-row { grid-template-columns:36px minmax(0,1fr); gap:10px 11px; padding:16px 0; }
                .loan-book { grid-column:2; }
                .loan-date, .loan-status, .status-form, .loan-admin-actions { grid-column:2; }
                .loan-date { display:block; margin:0; }
                .loan-date strong { font-size:12px; }
                .loan-admin-actions { align-items:flex-start; flex-direction:column; gap:8px; }
                .loan-admin-controls { width:100%; flex-direction:column; align-items:stretch; gap:8px; }
                .loan-admin-controls > *, .loan-admin-controls form, .loan-admin-controls .status-form { width:100%; }
                .status-form select { width:100%; max-width:none; }
                .fine-paid-form .button { width:100%; justify-content:center; }
                .loan-delete-button { width:100%; }
                .admin-return { width:100%; }
                .admin-return summary { text-align:center; }
                .admin-return form { position:fixed; z-index:70; top:50%; right:auto; left:50%; width:min(340px,calc(100vw - 28px)); max-height:80dvh; overflow-y:auto; transform:translate(-50%,-50%); }
                .handoff-qr { --handoff-qr-size:132px; gap:10px; padding:9px; }
                .handoff-token { grid-template-columns:minmax(0,1fr); }
                .handoff-token button { justify-self:start; }
                .loan-table-panel .loan-row .loan-status { width:max-content; }
                .scan-panel { padding:18px 12px; margin-bottom:48px; }
                #handoff-reader { min-height:220px; }
                #handoff-reader video { max-height:55dvh; }
                .loan-success-notice { padding:14px; }
                .loan-success-dialog { width:100%; max-width:350px; padding:28px 20px 26px; }
                .loan-success-dialog h2 { font-size:23px; }
                .admin-form-panel { padding:22px 16px; margin-bottom:50px; }
                .admin-form-heading h2 { font-size:26px; }
                .book-create-form { grid-template-columns:minmax(0,1fr); gap:15px; }
                .admin-check, .book-create-form > .button { grid-column:auto; }
                .book-create-form > .button { width:100%; }
                .flash { overflow-wrap:anywhere; }
            }

            @media (max-width: 380px) {
                .shell { --responsive-gutter:14px; padding-right:14px; padding-left:14px; }
                .shell .quote-band { margin-right:-14px; margin-left:-14px; padding-right:14px; padding-left:14px; }
                .brand > span:last-child::after { font-size:14px; }
                .book-cover { flex-basis:60px; }
                .book-card { gap:11px; padding:12px; }
                .loan-table-panel { padding-right:11px; padding-left:11px; }
                .loan-row { grid-template-columns:32px minmax(0,1fr); gap:9px; }
                .handoff-qr { --handoff-qr-size:120px; gap:8px; }
                .loan-success-check { width:58px; height:58px; }
            }
        </style>
        <style>
            :root { --motion-fast:160ms; --motion-medium:240ms; --motion-page:440ms; --focus-ring:rgba(217,107,68,.32); }
            @view-transition { navigation:auto; }
            .topbar { view-transition-name:site-header; }
            main { view-transition-name:page-content; }
            .footer { view-transition-name:site-footer; }
            ::view-transition-group(root), ::view-transition-group(site-header), ::view-transition-group(page-content), ::view-transition-group(site-footer) { animation:none; }
            ::view-transition-old(root), ::view-transition-new(root) { animation:none; }
            ::view-transition-old(page-content) { animation:page-content-out 150ms ease-in both; }
            ::view-transition-new(page-content) { animation:page-content-in var(--motion-page) cubic-bezier(.16,1,.3,1) both; }
            ::view-transition-old(site-header) { animation:header-transition-out 120ms ease-in both; }
            ::view-transition-new(site-header) { animation:header-transition-in 180ms ease-out both; }
            ::view-transition-old(site-footer) { animation:header-transition-out 120ms ease-in both; }
            ::view-transition-new(site-footer) { animation:header-transition-in 180ms ease-out both; }
            @keyframes page-content-out { to { opacity:0; transform:translateY(-4px); } }
            @keyframes page-content-in { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
            @keyframes header-transition-out { to { opacity:0; } }
            @keyframes header-transition-in { from { opacity:0; } to { opacity:1; } }
            @media (hover:none) { .book-card:hover { transform:none; box-shadow:none; } }
            @supports not (view-transition-name:page-content) {
                @media (prefers-reduced-motion:no-preference) {
                    main { animation:page-content-fallback var(--motion-page) cubic-bezier(.16,1,.3,1) backwards; }
                }
            }
            @keyframes page-content-fallback { from { opacity:0; } }
            :where(a, button, summary, input, select, textarea):focus-visible { outline:3px solid var(--focus-ring); outline-offset:3px; }
            .button, .nav-pill, .admin-return summary, #handoff-reader button, .handoff-token button, .loan-success-close, .menu-toggle { transition:transform var(--motion-fast) ease, box-shadow var(--motion-fast) ease, background-color var(--motion-fast) ease, border-color var(--motion-fast) ease, color var(--motion-fast) ease; }
            .button-primary { position:relative; isolation:isolate; overflow:hidden; box-shadow:0 2px 0 rgba(12,35,29,.18); }
            .button-primary::before { position:absolute; z-index:-1; inset:0; content:''; background:linear-gradient(110deg, transparent 20%, rgba(255,255,255,.16) 48%, transparent 76%); transform:translateX(-130%); transition:transform 480ms cubic-bezier(.2,.7,.2,1); }
            .button-primary:hover::before, .button-primary:focus-visible::before { transform:translateX(130%); }
            .button-primary:hover { box-shadow:0 5px 12px rgba(24,53,45,.16); transform:translateY(-2px); }
            .button-primary:active { box-shadow:0 1px 2px rgba(24,53,45,.16); transform:translateY(1px) scale(.985); }
            .button-primary span { display:inline-block; transition:transform var(--motion-medium) cubic-bezier(.2,.7,.2,1); }
            .button-primary:hover span { transform:translate(2px,-2px); }
            .monthly-report-head { align-items:flex-start; padding-bottom:24px; }
            .monthly-report-head h1 { margin-bottom:10px; font-size:clamp(34px,5vw,48px); }
            .monthly-report-toolbar { display:flex; align-items:flex-end; justify-content:space-between; gap:20px; margin:0 0 18px; padding:18px 20px; background:var(--paper); border:1px solid var(--line); }
            .monthly-report-period { min-width:0; flex:1; }
            .monthly-report-filter label { display:block; margin-bottom:7px; color:var(--ink); font-size:12px; font-weight:700; }
            .monthly-report-controls { display:flex; align-items:center; flex-wrap:wrap; gap:10px; }
            .monthly-report-controls input { min-height:44px; padding:9px 11px; border:1px solid #d8e0d9; color:var(--ink); background:#fbfcf8; font:inherit; font-size:14px; }
            .monthly-report-controls .button { min-height:44px; padding:10px 14px; }
            .monthly-report-hint { display:block; margin-top:7px; color:#8b3f30; font-size:12px; }
            .monthly-report-hint[hidden] { display:none; }
            .monthly-report-period-label { margin:9px 0 0; color:var(--muted); font-size:13px; }
            .monthly-report-download { flex:none; min-height:44px; border:1px solid var(--line); color:var(--ink); background:var(--cream); }
            .monthly-report-download:hover { border-color:#aab9ae; background:var(--paper); }
            .monthly-report-download span { color:var(--accent); font-size:17px; }
            .monthly-report-sheet { margin:0 0 76px; padding:30px 34px; background:var(--paper); border:1px solid var(--line); }
            .monthly-report-empty { margin:0 0 20px; padding:11px 13px; color:#52645a; background:var(--cream); font-size:13px; }
            .monthly-report-section { padding:24px 0 22px; border-top:1px solid var(--line); }
            .monthly-report-section:first-of-type { padding-top:0; border-top:0; }
            .monthly-report-section:last-child { padding-bottom:0; }
            .monthly-report-section-heading { display:flex; align-items:baseline; justify-content:space-between; gap:20px; margin-bottom:12px; }
            .monthly-report-section-heading h2 { flex:none; font-size:25px; }
            .monthly-report-section-heading p { max-width:620px; margin:0; color:var(--muted); font-size:12px; line-height:1.55; }
            .monthly-report-metrics { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; margin:18px 0 10px; }
            .monthly-report-metric dt, .monthly-report-ledger dt { color:var(--muted); font-size:13px; }
            .monthly-report-metric dd { margin:4px 0 0; color:var(--ink); font-size:25px; font-weight:700; font-variant-numeric:tabular-nums; }
            .monthly-report-metric-primary dd { font:700 36px/1.1 'Playfair Display',serif; }
            .monthly-report-ledger { margin:0; }
            .monthly-report-ledger > div { display:grid; grid-template-columns:minmax(0,1fr) minmax(180px,auto); gap:12px; align-items:center; padding:12px 0; border-top:1px solid var(--line); }
            .monthly-report-ledger > div:first-child { border-top:0; }
            .monthly-report-ledger dt small { display:block; margin-top:4px; color:var(--muted); font-size:11px; font-weight:400; line-height:1.45; }
            .monthly-report-ledger dd { display:flex; align-items:baseline; justify-content:flex-end; gap:16px; margin:0; color:var(--ink); font-size:12px; font-variant-numeric:tabular-nums; text-align:right; }
            .monthly-report-ledger dd > span { color:var(--muted); white-space:nowrap; }
            .monthly-report-ledger dd strong { min-width:118px; font-size:16px; }
            .monthly-report-note { margin:10px 0 0; padding:10px 12px; color:#52645a; background:var(--cream); font-size:12px; line-height:1.55; }
            .monthly-report-table-wrap { max-width:100%; overflow-x:auto; border:1px solid var(--line); }
            .monthly-report-table-wrap:focus-visible { outline:3px solid var(--focus-ring); outline-offset:3px; }
            .monthly-report-table { width:100%; min-width:790px; border-collapse:collapse; color:var(--ink); font-size:12px; text-align:left; }
            .monthly-report-table caption { padding:11px 13px; color:var(--muted); background:var(--paper); font-size:11px; text-align:left; }
            .monthly-report-table th { padding:11px 12px; color:var(--muted); background:var(--cream); font-size:10px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; }
            .monthly-report-table td { padding:12px; border-top:1px solid var(--line); vertical-align:top; line-height:1.5; }
            .monthly-report-table tbody tr:hover { background:#fbfaf6; }
            .monthly-report-table td strong, .monthly-report-table td small { display:block; }
            .monthly-report-table td strong { font-size:12px; font-weight:700; }
            .monthly-report-table td small { margin-top:3px; color:var(--muted); font-size:11px; }
            .monthly-report-table th:nth-child(1) { min-width:155px; }
            .monthly-report-table th:nth-child(2) { min-width:190px; }
            .monthly-report-table th:nth-child(3) { min-width:115px; }
            .monthly-report-table th:nth-child(4) { min-width:145px; }
            .monthly-report-table th:nth-child(5) { min-width:190px; }
            .monthly-report-table .money { font-variant-numeric:tabular-nums; white-space:nowrap; }
            .monthly-report-table-hint { margin:8px 0 0; color:var(--muted); font-size:11px; }
            .monthly-report-download:focus-visible, .monthly-report-controls input:focus-visible { outline:3px solid var(--focus-ring); outline-offset:3px; }
            @media(max-width:640px) {
                .monthly-report-head { padding:34px 0 22px; }
                .monthly-report-head h1 { font-size:36px; }
                .monthly-report-toolbar { align-items:stretch; flex-direction:column; gap:13px; padding:15px; }
                .monthly-report-controls { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:8px; }
                .monthly-report-controls input { width:100%; min-width:0; }
                .monthly-report-controls .button { justify-content:center; padding:9px 11px; font-size:11px; }
                .monthly-report-download { align-self:flex-start; }
                .monthly-report-sheet { margin-bottom:50px; padding:22px 17px; }
                .monthly-report-section { padding:20px 0 18px; }
                .monthly-report-section-heading { align-items:flex-start; flex-direction:column; gap:6px; }
                .monthly-report-section-heading h2 { font-size:23px; }
                .monthly-report-section-heading p { font-size:12px; }
                .monthly-report-metrics { gap:12px; }
                .monthly-report-metric dd { font-size:22px; }
                .monthly-report-metric-primary dd { font-size:30px; }
                .monthly-report-ledger > div { grid-template-columns:minmax(0,1fr) auto; gap:8px; }
                .monthly-report-ledger dd { align-items:flex-end; flex-direction:column; gap:2px; }
                .monthly-report-ledger dd strong { min-width:0; font-size:14px; }
                .monthly-report-ledger dt small { font-size:10px; }
            }
            .nav-pill:hover { box-shadow:0 5px 12px rgba(24,53,45,.16); transform:translateY(-1px); }
            .nav-logout:hover { color:#c5533b; }
            .brand-mark { transition:transform var(--motion-medium) cubic-bezier(.2,.7,.2,1), color var(--motion-fast) ease, background-color var(--motion-fast) ease; }
            .brand:hover .brand-mark { color:#18352d; background:#f7c879; transform:rotate(-7deg) scale(1.06); }
            .topnav a:not(.nav-pill) { transition:color var(--motion-fast) ease, transform var(--motion-fast) ease; }
            .topnav a:not(.nav-pill):hover { transform:translateY(-1px); }
            .text-link { transition:color var(--motion-fast) ease, border-color var(--motion-fast) ease; }
            .text-link:hover { color:var(--accent); border-color:var(--accent); }
            .book-card { transition:transform var(--motion-medium) cubic-bezier(.2,.7,.2,1), box-shadow var(--motion-medium) ease, border-color var(--motion-fast) ease; }
            .book-card:hover { border-color:#cbd8cc; }
            .book-cover { transition:transform var(--motion-medium) cubic-bezier(.2,.7,.2,1); }
            .book-card:hover .book-cover { transform:translateY(-2px) rotate(-1deg); }
            .loan-confirm summary, .admin-return summary { transition:transform var(--motion-fast) ease, background-color var(--motion-fast) ease, box-shadow var(--motion-fast) ease; }
            .loan-confirm summary:hover, .admin-return summary:hover { box-shadow:0 4px 10px rgba(24,53,45,.14); transform:translateY(-1px); }
            .loan-confirm summary:active, .admin-return summary:active { transform:scale(.98); }
            .loan-status { transition:box-shadow var(--motion-fast) ease, transform var(--motion-fast) ease; }
            .loan-status:hover { box-shadow:0 2px 8px rgba(24,53,45,.1); transform:translateY(-1px); }
            .handoff-token button:hover, #handoff-reader button:hover { border-color:#18352d; box-shadow:0 3px 8px rgba(24,53,45,.12); transform:translateY(-1px); }
            .handoff-token button:active, #handoff-reader button:active { transform:scale(.97); }
            .check-row input, .loan-agreement input, .admin-check input { transition:transform var(--motion-fast) cubic-bezier(.2,.7,.2,1), filter var(--motion-fast) ease; }
            .check-row input:checked, .loan-agreement input:checked, .admin-check input:checked { transform:scale(1.16); filter:saturate(1.25); }
            .status-form select, .admin-return input, .admin-return select, .admin-return textarea, .field input, .admin-field input { transition:border-color var(--motion-fast) ease, box-shadow var(--motion-fast) ease, background-color var(--motion-fast) ease; }
            .status-form select:hover, .admin-return input:hover, .admin-return select:hover, .admin-return textarea:hover { border-color:#aab9ae; }
            .menu-toggle:hover { border-color:#aab9ae; box-shadow:0 3px 9px rgba(24,53,45,.1); }
            .menu-toggle:active { transform:scale(.94); }
            .availability { transition:color var(--motion-fast) ease; }
            .book-card:hover .availability { color:#315f43; }
            .member-fine { transition:transform var(--motion-fast) ease, box-shadow var(--motion-fast) ease; }
            .member-fine:hover { box-shadow:0 5px 14px rgba(139,63,48,.09); transform:translateX(2px); }
            .book-card { flex-direction:column; }
            .book-detail-trigger { flex:1 1 auto; }
            .catalog-filters { scroll-margin-top:100px; }
            .book-detail-dialog { border-radius:2px; }
            .book-detail-trigger:focus-visible { outline:3px solid var(--focus-ring); outline-offset:4px; }
            .favorite-toggle { transition:transform .16s ease, color .16s ease, background-color .16s ease; }
            .favorite-toggle:hover { transform:scale(1.08); }
            .favorite-toggle:active { transform:scale(.92); }
            .book-detail-close:hover { color:#fffefa; background:#18352d; }
            .admin-description-field { grid-column:1/-1; }
            .admin-field textarea { width:100%; min-height:105px; padding:12px; border:1px solid #d8e0d9; color:#18352d; background:#f7f6f0; font:inherit; resize:vertical; }
            .admin-book-list { margin-bottom:80px; }
            .admin-book-list-items { display:grid; }
            .admin-book-item { display:grid; grid-template-columns:48px minmax(0,1fr) auto; gap:14px; align-items:center; padding:13px 0; border-top:1px solid #e3e7df; }
            .admin-book-item img { width:48px; height:64px; object-fit:cover; }
            .admin-book-item > div { min-width:0; }
            .admin-book-item strong, .admin-book-item small { display:block; overflow-wrap:anywhere; }
            .admin-book-item strong { font-size:13px; }
            .admin-book-item small { margin-top:4px; color:#718078; font-size:11px; }
            .admin-book-item .button { padding:9px 11px; font-size:11px; }
            .reading-challenge, .due-reminders { min-width:0; }
            @media(max-width:980px) { .catalog-filters { grid-template-columns:minmax(0,1fr) minmax(150px,.6fr); }.book-detail-layout { gap:22px; padding:28px; } }
            @media(max-width:640px) {
                .catalog-filters { grid-template-columns:minmax(0,1fr); }
                .catalog-filters > * { width:100%; }
                .filter-availability { min-height:40px; }
                .book-card { display:flex; flex-direction:column; }
                .book-detail-trigger { display:grid; grid-template-columns:68px minmax(0,1fr); width:100%; gap:12px; }
                .book-detail-trigger .book-cover { grid-row:span 1; width:68px; min-width:68px; height:100px; }
                .book-detail-trigger .book-info { gap:4px; }
                .book-info .book-title { overflow-wrap:anywhere; }
                .book-actions { margin-top:4px; }
                .book-detail-layout { grid-template-columns:minmax(0,1fr); gap:16px; padding:20px; }
                .book-detail-cover { width:min(170px,65%); min-height:220px; margin:0 auto; }
                .book-detail-copy { padding-top:0; }
                .book-detail-copy h2 { padding-right:28px; font-size:25px; }
                .book-detail-close { position:sticky; top:8px; margin:8px 8px -46px 0; }
                .reading-challenge { padding:21px 18px; }
                .challenge-form > div { flex-wrap:wrap; }
                .challenge-form .button { flex:1; justify-content:space-between; }
                .due-reminders { padding:18px 14px; }
                .reminder-item { gap:9px; }
                .admin-description-field { grid-column:auto; }
                .admin-book-item { grid-template-columns:42px minmax(0,1fr); gap:10px; }
                .admin-book-item img { width:42px; height:58px; }
                .admin-book-item .button { grid-column:2; justify-content:space-between; width:100%; }
            }
            .book-card { cursor:pointer; flex-wrap:wrap; }
            .book-detail-trigger { transition:opacity .18s ease; }
            .book-detail-trigger:hover .book-title { color:#d96b44; }
            .book-actions { position:relative; z-index:1; }
            .book-detail-dialog[open] { animation:toast-enter .24s ease-out both; }
            .book-detail-layout { align-items:start; }
            .book-card:has(.loan-confirm[open]) { transform:none; }
            .book-card:has(.loan-confirm[open]) .book-actions { z-index:60; }
            .catalog-pagination nav { display:flex; justify-content:center; }
            .catalog-pagination nav > div:first-child { display:none; }
            .catalog-pagination nav > div:last-child > span { display:flex; flex-wrap:wrap; gap:5px; }
            .catalog-pagination nav a, .catalog-pagination nav span[aria-current] span, .catalog-pagination nav span[aria-disabled] span { display:grid; min-width:38px; min-height:38px; place-items:center; padding:7px 10px; border:1px solid #e3e7df; background:#fffefa; font-size:12px; }
            .catalog-pagination nav span[aria-current] span { color:white; background:#18352d; border-color:#18352d; }
            .catalog-pagination nav a:hover { border-color:#18352d; }
            .reading-challenge { min-width:0; }
            .challenge-form input { min-width:0; }
            .reminder-item { min-width:0; }
            .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
            .admin-user-panel, .admin-user-reset-panel, .admin-user-delete-panel { margin-bottom:22px; }
            .admin-user-search { display:flex; flex-wrap:wrap; align-items:center; gap:10px; margin-bottom:22px; }
            .admin-user-search input { min-width:min(100%,260px); min-height:44px; flex:1 1 260px; padding:10px 12px; border:1px solid var(--line); color:var(--ink); background:var(--cream); font:inherit; }
            .admin-user-search .button { min-height:44px; padding:10px 16px; }
            .admin-user-list { display:grid; }
            .admin-user-item { display:grid; grid-template-columns:42px minmax(0,1fr) auto auto; gap:14px; align-items:center; padding:14px 0; border-top:1px solid var(--line); }
            .admin-user-avatar { display:grid; place-items:center; width:42px; height:42px; color:var(--ink); background:var(--sage); font:700 19px 'Playfair Display',serif; }
            .admin-user-copy { display:grid; min-width:0; gap:4px; }
            .admin-user-copy strong, .admin-user-copy small { overflow-wrap:anywhere; }
            .admin-user-copy strong { font-size:13px; }
            .admin-user-copy small { color:var(--muted); font-size:11px; }
            .admin-user-status { padding:6px 9px; font-size:10px; font-weight:700; }
            .admin-user-status.is-active { color:#315f43; background:#edf7ee; }
            .admin-user-status.is-inactive { color:#8b3f30; background:#fff0eb; }
            .admin-user-status.is-pending { color:#80551b; background:#fff4dc; }
            .admin-user-item .button { justify-content:space-between; padding:10px 12px; font-size:11px; }
            .admin-user-filters { display:flex; flex-wrap:wrap; gap:8px; margin:18px 0; }
            .admin-user-filters a { display:inline-flex; align-items:center; gap:8px; min-height:40px; padding:8px 12px; border:1px solid var(--line); color:var(--muted); background:var(--paper); font-size:12px; font-weight:700; }
            .admin-user-filters a.is-selected { color:white; border-color:var(--ink); background:var(--ink); }
            .admin-user-filters span { font-size:10px; opacity:.8; }
            .admin-user-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; align-items:center; gap:8px; }
            .admin-user-actions .text-link { font-size:11px; }
            .admin-user-actions .button { min-height:38px; }
            .admin-user-reject { color:#8b3f30; border:1px solid #edc2b7; background:#fff0eb; }
            .admin-field select { width:100%; min-height:44px; padding:10px 11px; border:1px solid var(--line); color:var(--ink); background:var(--cream); font:inherit; }
            .admin-issued-code { display:grid; gap:8px; margin:16px 0; padding:17px; border:1px solid #b9d8be; color:#315f43; background:#edf7ee; }
            .admin-issued-code strong { font-size:13px; }
            .admin-issued-code code { width:max-content; max-width:100%; padding:8px 12px; color:var(--ink); background:var(--paper); font:700 28px/1.2 monospace; letter-spacing:.12em; overflow-wrap:anywhere; }
            .admin-issued-code small { color:#52645a; font-size:11px; line-height:1.5; }
            .admin-user-delete-panel { border-color:#edc2b7; }
            .admin-user-delete-panel .loan-delete-button { padding:11px 14px; }
            @media(max-width:980px) { .catalog-filters { grid-template-columns:minmax(0,1fr) minmax(150px,.6fr); }.filter-availability { min-height:42px; }.catalog-filters > .button { width:100%; }.catalog-reset { justify-self:start; } }
            @media(max-width:640px) {
                .admin-user-search { display:grid; grid-template-columns:minmax(0,1fr) auto; }
                .admin-user-search input { grid-column:1/-1; width:100%; }
                .admin-user-item { grid-template-columns:38px minmax(0,1fr) auto; gap:10px; }
                .admin-user-avatar { width:38px; height:38px; }
                .admin-user-status { grid-column:2; justify-self:start; }
                .admin-user-item .button { grid-column:3; grid-row:1/3; }
                .admin-user-item:has(.admin-user-actions) { grid-template-columns:38px minmax(0,1fr); }
                .admin-user-item:has(.admin-user-actions) .admin-user-status { grid-column:2; }
                .admin-user-actions { grid-column:1/-1; justify-content:flex-start; }
                .admin-user-actions > form { flex:1 1 90px; }
                .admin-user-actions > form .button { width:100%; justify-content:center; }
                .admin-user-filters { gap:6px; }
                .admin-user-filters a { flex:1 1 calc(50% - 6px); justify-content:space-between; }
                .admin-issued-code { padding:14px; }
                .admin-issued-code code { font-size:24px; }
                .admin-user-delete-panel .loan-delete-button { width:100%; }
                .catalog-filters { grid-template-columns:minmax(0,1fr); gap:9px; margin-top:18px; }
                .catalog-search, .filter-select { width:100%; }
                .catalog-filters > .button { width:100%; }
                .catalog-reset { justify-self:center; padding:5px; }
                .book-detail-trigger { gap:12px; }
                .book-info .book-title { font-size:15px; }
                .book-detail-dialog { width:calc(100vw - 20px); max-height:90dvh; }
                .book-detail-layout { grid-template-columns:minmax(0,1fr); gap:18px; padding:20px; }
                .book-detail-cover { width:min(180px,65%); min-height:230px; margin:0 auto; }
                .book-detail-copy { padding-top:0; }
                .book-detail-copy h2 { padding-right:30px; font-size:26px; }
                .book-detail-close { position:sticky; top:8px; margin:8px 8px -46px 0; }
                .reading-challenge { padding:21px 18px; }
                .due-reminders { padding:18px 14px; }
                .reminder-item { gap:9px; }
                .reminder-item small { line-height:1.45; }
                .challenge-form > div { flex-wrap:wrap; }
                .challenge-form .button { flex:1; justify-content:space-between; }
            }
            @media (prefers-reduced-motion:reduce) {
                *, *::before, *::after { scroll-behavior:auto!important; animation-duration:.01ms!important; animation-delay:0ms!important; animation-iteration-count:1!important; transition-duration:.01ms!important; }
                ::view-transition-group(*), ::view-transition-old(*), ::view-transition-new(*) { animation:none!important; }
            }
        </style>
    </head>
<body><div class="shell"><header class="topbar"><a class="brand" href="{{ url('/') }}"><span class="brand-mark">L</span><span>Lentera<span class="brand-muted"> Library</span></span></a><button class="menu-toggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="main-navigation"><span></span><span></span><span></span></button><nav class="topnav" id="main-navigation"><a href="{{ url('/') }}">Katalog</a>@auth @if(Auth::user()->role === 'admin')<a href="{{ route('admin.books.create') }}" class="nav-pill">Tambah buku</a><a href="{{ route('admin.users.index') }}">Pengguna</a><a href="{{ route('admin.loans.index') }}">Kelola status</a><a href="{{ route('admin.reports.monthly') }}">Laporan bulanan</a>@else<a href="{{ route('guide') }}">Panduan</a><a href="{{ route('home') }}" class="nav-pill">Dashboard</a><a href="{{ route('loans.mine') }}">Peminjaman</a>@endif<form method="POST" action="{{ route('logout', [], false) }}">
@csrf<button type="submit" class="nav-logout">Keluar</button></form>@else<a href="{{ route('guide') }}">Panduan</a><a href="{{ route('login') }}">Masuk</a><a href="{{ route('register') }}" class="nav-pill">Daftar</a>@endauth</nav></header><main>@if(session('success'))<div class="flash flash-success">{{ session('success') }}</div>@endif @if(session('error'))<div class="flash flash-error">{{ session('error') }}</div>@endif @if($errors->any())<div class="flash flash-error">{{ $errors->first() }}</div>@endif @yield('content')</main><footer class="footer"><span>LEHBOOK</span>@auth @if(Auth::user()->role === 'admin')<a href="{{ route('admin.books.create') }}">Tambah buku</a><a href="{{ route('admin.loans.index') }}">Kelola status</a><a href="{{ route('admin.reports.monthly') }}">Laporan bulanan</a>@else<a href="{{ route('guide') }}">Panduan</a><a href="{{ route('loans.mine') }}">Peminjaman saya</a>@endif @else<a href="{{ route('guide') }}">Panduan</a>@endauth<span>Ruang kecil untuk ide-ide besar.</span></footer></div><script>const menuToggle = document.querySelector('.menu-toggle'); const mainNavigation = document.querySelector('#main-navigation'); if (menuToggle && mainNavigation) { menuToggle.addEventListener('click', () => { const isOpen = menuToggle.getAttribute('aria-expanded') === 'true'; menuToggle.setAttribute('aria-expanded', String(!isOpen)); menuToggle.setAttribute('aria-label', isOpen ? 'Buka menu' : 'Tutup menu'); mainNavigation.classList.toggle('is-open', !isOpen); }); }</script></body>
</html>