<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Turbo Drive: swaps page content via AJAX instead of a full browser reload -->
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8/dist/turbo.min.js" data-turbo-track="reload"></script>
    <script>
        // Turbo re-executes <body> scripts on every visit but does not fire a fresh
        // DOMContentLoaded (the JS context is never torn down between navigations).
        // Existing per-page scripts across the app listen for DOMContentLoaded, so we
        // replay a synthetic one after every Turbo navigation (skipping the very first
        // load, since the real DOMContentLoaded already covers it) instead of rewriting
        // every page's script.
        (function () {
            let isFirstTurboLoad = true;
            document.addEventListener('turbo:load', function () {
                if (isFirstTurboLoad) {
                    isFirstTurboLoad = false;
                    return;
                }
                document.dispatchEvent(new Event('DOMContentLoaded', { bubbles: true, cancelable: true }));
            });
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet" data-turbo-track="reload">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" data-turbo-track="reload">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" data-turbo-track="reload">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" data-turbo-track="reload">

    <title>{{ $title ?? 'Dashboard' }} | SISKEN</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Vendor libraries: loaded once in <head> so Turbo keeps them across navigations
         instead of re-downloading/re-executing them on every page click -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" data-turbo-track="reload"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js" data-turbo-track="reload"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js" data-turbo-track="reload"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" data-turbo-track="reload"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js" data-turbo-track="reload"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" data-turbo-track="reload"></script>{{-- calendar --}}

    <!-- Alpine.js -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                // Initialize based on screen size
                isExpanded: window.innerWidth >= 1280, // true for desktop, false for mobile
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    // Only allow hover effects on desktop when sidebar is collapsed
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>
    
</head>

<body
    x-data="{ 'loaded': true}"
    x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    };
    window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="app-main-wrapper flex-1 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="app-content-wrapper p-4 mx-auto max-w-full md:p-6">
                @yield('content')
            </div>
        </div>

        @include('components.dev.impersonate-widget')

        @stack('scripts')
        </body>

        <style>
            html,
            body {
                max-width: 100%;
                overflow-x: hidden;
            }
        
            .app-main-wrapper {
                min-width: 0;
                max-width: 100%;
                overflow-x: hidden;
            }
        
            .app-content-wrapper {
                min-width: 0;
                max-width: 100%;
                overflow-x: hidden;
            }
        
            .dataTables_wrapper {
                max-width: 100%;
                overflow-x: auto;
            }
        
            .dataTables_scroll {
                max-width: 100%;
                overflow-x: auto;
            }
        
            .dataTables_scrollBody {
                overflow-x: auto !important;
            }
        </style>

</html>
