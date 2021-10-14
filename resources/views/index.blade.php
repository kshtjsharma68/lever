<!doctype html>
<html lang="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="">

    <title>Derrick</title>

    <!-- Scripts -->
    <script src="" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="" rel="stylesheet">
    <style type="text/css">
        .window {
            display: none;
        }
        .intro-window {
            display: none;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">

                <div class="l-window">
                    <form id="wf-form-password-form">
                            <input type="password" name="password"/>
                            <button type="submit">Submit</button>
                    </form>
                </div>
                <div class="intro-window">
                    Container inside
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    var API_ENDPOINT = "http://localhost:8000/";
    var slug = '';
    
        window.onload = function() {
            if( !sessionStorage.hasOwnProperty('session') ) {
                //Fetch slug
                slug = getSlug();
            }
        }; 
        if (sessionStorage.hasOwnProperty('session')) {
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

        $('#wf-form-password-form').submit(function(e) { 
            e.preventDefault();
            var data = $(this).serialize()+"&slug="+getSlug();
            var form = sendForm('check-proposal-password', data);
            form.done(function(response) {
                showInfoContainer();
                createSession();
            })
        })


        function getSlug() {
            return location.pathname.replace('/proposal','').replace('/', '').toLowerCase();
        }

        function showProtectedContainer() {
            $('.l-window').css('display', 'flex');
            $('.intro-window').css('display', 'none');
        }

        function showInfoContainer() {
            $('.l-window').css('display', 'none');
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
            var session = sessionStorage.hasOwnProperty('session') ? JSON.parse(sessionStorage.getItem('session')) : {};
            var session = {
                ...session,
                [getSlug()]: {
                    'start': + new Date()
                }
            }
            sessionStorage.setItem('session',JSON.stringify(session))
        }

        function sessionActive()
        {
            var session = JSON.parse(sessionStorage.getItem('session'));
            let slug  = getSlug();
            //Check if session is for slug
            if (session[slug]) {
                return ( ( new Date - new Date(session[slug].start)) / 1000 ) < ( 4 * 60 * 60 );
            }            
            return false;             
        }
</script>
</html>
