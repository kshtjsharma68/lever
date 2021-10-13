<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Derrick</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('auth.login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('auth.register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
<script>
    var API_ENDPOINT = "http://localhost:8000/";
    var slug = '';
    
        window.onload = function() {
            if( !localStorage.hasOwnProperty('session') ) {
                //Fetch slug
                slug = getSlug();
            }
        };
        if (localStorage.hasOwnProperty('session')) {
            //Check if session is still active
            if ( sessionActive() ) {
                showInfoContainer();
            } else {
                showProtectedContainer();
            }
        } else {
            //Session is not created and will have to show authentication
            showProtectedContainer();
        }

        $('#password-form').submit(function(e) { 
            e.preventDefault();
            var data = $(this).serialize()+"&slug="+slug;
            console.log("=====form submitting=====")
            var form = sendForm('/check-proposal-password', data);
            console.log('=====form submitted======')
            form.done(function(response) {
                showInfoContainer();
                createSession();
            })
        })


        function getSlug() {
            return location.pathname.replace('/proposal','').replace('/', '').toLowerCase();
        }

        function showProtectedContainer() {
            $('.window').css('display', 'flex');
            $('.intro-window').css('display', 'none');
        }

        function showInfoContainer() {
            $('.window').css('display', 'none');
            $('.intro-window').css('display', 'flex');
        }

        function sendForm(url, data) {
            var deffered = $.Deferred();
            var object = {
                url: url.indexOf('http') === -1 ? `${API_ENDPOINT}${url}` : url,
                data: data ? data : {},
                type: data ?  'POST' : 'GET',
                dataType: 'json',
                success: function(res) {
                    deffered.resolve(res)
                },
                error: function(err) {
                    console.log('something went wrong', err)
                    deffered.reject(err)
                }
            }
            $.ajax(object);
            return deffered.promise();
        }

        function createSession()
        {
            var session = {
                'start': + new Date()
            }
            localStorage.setItem('session',JSON.stringify(session))
        }

        function sessionActive()
        {
            var sessionTime = JSON.parse(localStorage.getItem('session'))
            return ( ( new Date - new Date(sessionTime.start)) / 1000 ) < ( 4 * 60 *60 ); 
        }
</script>
</html>
