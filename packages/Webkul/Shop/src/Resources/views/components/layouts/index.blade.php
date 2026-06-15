@props([
    'hasHeader'  => true,
    'hasFeature' => true,
    'hasFooter'  => true,
])

<!DOCTYPE html>

<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>
        <script>
            (function() {
                function showError(msg) {
                    var showErrorFn = function() {
                        var container = document.getElementById('js-debug-errors');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'js-debug-errors';
                            container.style.position = 'fixed';
                            container.style.top = '0';
                            container.style.left = '0';
                            container.style.width = '100%';
                            container.style.zIndex = '999999';
                            container.style.fontFamily = 'monospace';
                            container.style.fontSize = '14px';
                            document.body.appendChild(container);
                        }
                        var errorDiv = document.createElement('div');
                        errorDiv.style.backgroundColor = '#f43f5e';
                        errorDiv.style.color = '#ffffff';
                        errorDiv.style.padding = '15px';
                        errorDiv.style.borderBottom = '2px solid #be123c';
                        errorDiv.style.wordBreak = 'break-all';
                        errorDiv.innerHTML = msg;
                        container.appendChild(errorDiv);
                    };
                    if (document.body) {
                        showErrorFn();
                    } else {
                        document.addEventListener('DOMContentLoaded', showErrorFn);
                    }
                }
                window.addEventListener('error', function(event) {
                    showError('<strong>JS Error:</strong> ' + event.message + ' <br>Dosya: ' + event.filename + ' <br>Satır: ' + event.lineno);
                });
                window.addEventListener('unhandledrejection', function(event) {
                    var reason = event.reason;
                    if (reason && reason.stack) {
                        reason = reason.message + '<br>' + reason.stack.replace(/\n/g, '<br>');
                    }
                    showError('<strong>Promise Rejection:</strong> ' + reason);
                });
                window.addEventListener('load', function() {
                    setInterval(function() {
                        var appEl = document.getElementById('app');
                        var isVueMounted = appEl && appEl.__vue_app__ ? 'EVET (Yes)' : 'HAYIR (No)';
                        var debugDiv = document.getElementById('js-debug-status');
                        if (!debugDiv) {
                            debugDiv = document.createElement('div');
                            debugDiv.id = 'js-debug-status';
                            debugDiv.style.position = 'fixed';
                            debugDiv.style.bottom = '10px';
                            debugDiv.style.right = '10px';
                            debugDiv.style.backgroundColor = '#1e293b';
                            debugDiv.style.color = '#ffffff';
                            debugDiv.style.padding = '12px';
                            debugDiv.style.borderRadius = '8px';
                            debugDiv.style.zIndex = '999999';
                            debugDiv.style.fontFamily = 'monospace';
                            debugDiv.style.fontSize = '11px';
                            debugDiv.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.3)';
                            debugDiv.style.maxHeight = '250px';
                            debugDiv.style.overflowY = 'auto';
                            debugDiv.style.width = '320px';
                            debugDiv.style.lineHeight = '1.4';
                            document.body.appendChild(debugDiv);
                        }
                        var logsHtml = (window.vueDebugLogs || []).map(function(log) {
                            var color = log.indexOf('Success') !== -1 ? '#4ade80' : (log.indexOf('Error') !== -1 ? '#f87171' : '#e2e8f0');
                            return '<span style="color:' + color + '">• ' + log + '</span>';
                        }).join('<br>');
                        debugDiv.innerHTML = '<strong>Debug Status:</strong><br>' +
                            'Vue Mounted: ' + isVueMounted + '<br>' +
                            'window.app: ' + (window.app ? 'Defined' : 'Undefined') + '<br>' +
                            '<strong style="margin-top: 5px; display: inline-block;">API Logs:</strong><br>' + (logsHtml || 'No logs yet');
                    }, 1000);
                });
            })();
        </script>

        {!! view_render_event('bagisto.shop.layout.head.before') !!}

        <title>{{ $title ?? '' }}</title>

        <meta charset="UTF-8">

        <meta
            http-equiv="X-UA-Compatible"
            content="IE=edge"
        >
        <meta
            http-equiv="content-language"
            content="{{ app()->getLocale() }}"
        >

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >
        <meta
            name="base-url"
            content="{{ url()->to('/') }}"
        >
        <meta
            name="currency"
            content="{{ core()->getCurrentCurrency()->toJson() }}"
        >
        <meta 
            name="generator" 
            content="Bagisto"
        >

        @stack('meta')

        <link
            rel="icon"
            sizes="16x16"
            href="{{ core()->getCurrentChannel()->favicon_url ?? bagisto_asset('images/favicon.ico') }}"
        />

        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
            crossorigin
        />

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        />

        <link
            rel="preload" as="style"
            href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap"
        />

        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap"
        />

        @stack('styles')

        <style>
            {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        </style>

        @if(core()->getConfigData('general.content.speculation_rules.enabled'))
            <script type="speculationrules">
                @json(core()->getSpeculationRules(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            </script>
        @endif

        {!! view_render_event('bagisto.shop.layout.head.after') !!}

    </head>

    <body>
        {!! view_render_event('bagisto.shop.layout.body.before') !!}

        <a
            href="#main"
            class="skip-to-main-content-link"
        >
            Skip to main content
        </a>

        <!-- Built With Bagisto -->
        <div id="app">
            <!-- Flash Message Blade Component -->
            <x-shop::flash-group />

            <!-- Confirm Modal Blade Component -->
            <x-shop::modal.confirm />

            <!-- Page Header Blade Component -->
            @if ($hasHeader)
                <x-shop::layouts.header />
            @endif

            @if(
                core()->getConfigData('general.gdpr.settings.enabled')
                && core()->getConfigData('general.gdpr.cookie.enabled')
            )
                <x-shop::layouts.cookie />
            @endif

            {!! view_render_event('bagisto.shop.layout.content.before') !!}

            <!-- Page Content Blade Component -->
            <main id="main" class="bg-white">
                {{ $slot }}
            </main>

            {!! view_render_event('bagisto.shop.layout.content.after') !!}


            <!-- Page Services Blade Component -->
            @if ($hasFeature)
                <x-shop::layouts.services />
            @endif

            <!-- Page Footer Blade Component -->
            @if ($hasFooter)
                <x-shop::layouts.footer />
            @endif
        </div>

        {!! view_render_event('bagisto.shop.layout.body.after') !!}

        @stack('scripts')

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.before') !!}
        <script>
            /**
             * Mount the application as soon as the DOM is ready instead of waiting
             * for the `load` event. All `Vue` components are registered through
             * deferred `type="module"` scripts, which always finish executing
             * before `DOMContentLoaded` fires, so every component is available
             * by the time `app.mount()` runs. Mounting on `DOMContentLoaded`
             * avoids blocking the storefront behind every image/font download.
             */
            function mountApp() {
                app.mount("#app");
            }

            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", mountApp);
            } else {
                mountApp();
            }
        </script>

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.after') !!}

        <script type="text/javascript">
            {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
        </script>
    </body>
</html>
