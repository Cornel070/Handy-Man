<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{!! !empty($title) ? $title : 'Handy Man' !!}</title>

    {{-- Fav Icon --}}
    <link rel="icon" href="{{asset('assets/images/fav.png')}}">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" {{ ! request()->is('payment*')? 'defer' : ''}}></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="{{ asset('assets/js/vendor/jquery-1.11.2.min.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/font-awesome-5/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
   

    <script type='text/javascript'>
        /* <![CDATA[ */
        var page_data = {!! pageJsonData() !!};
        /* ]]> */
    </script>

</head>
<body class="{{request()->routeIs('home') ? ' home ' : ''}} {{request()->routeIs('job_view') ? ' job-view-page ' : ''}}">
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light navbar-laravel {{request()->routeIs('home') ? 'transparent-navbar' : ''}}">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{asset('assets/images/logo.jpeg')}}" />
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="{{route('home')}}"><i class="la la-home"></i> @lang('app.home')</a> </li>

                    


                    {{-- <li class="nav-item"><a class="nav-link" href="{{route('pricing')}}"><i class="la la-dollar"></i> @lang('app.pricing')</a> </li> --}}
                    {{-- <li class="nav-item"><a class="nav-link" href="{{route('jobs_listing')}}"><i class="la la-briefcase"></i> Services</a> </li> --}}
                    {{-- <li class="nav-item"><a class="nav-link" href="#categories"><i class="la la-briefcase"></i> Services</a> </li> --}}
                    {{-- <li class="nav-item"><a class="nav-link" href="{{route('blog_index')}}"><i class="la la-file-o"></i> @lang('app.blog')</a> </li> --}}
                    <?php
                    $header_menu_pages = config('header_menu_pages');
                    ?>
                    @if($header_menu_pages->count() > 0)
                        @foreach($header_menu_pages as $page)
                            <li class="nav-item"><a class="nav-link" href="{{ route('single_page', $page->slug) }}"><i class="la la-link"></i>{{ $page->title }} </a></li>
                        @endforeach
                    @endif
                    
                    <li class="nav-item"><a class="nav-link" href="{{route('contact_us')}}"><i class="la la-envelope-o"></i> Contact Us</a> </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">

                    <li class="nav-item">
                        <a class="nav-link btn btn-success text-white" style="background-color: #ffbf00; border: 0 solid #ffbf00;" href="{{ route('request') }}"><i class="la la-save"></i>Request Service </a>
                    </li>

                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}"><i class="la la-sign-in"></i> {{ __('app.login') }}</a>
                        </li>
                        <li class="nav-item">
                            @if (Route::has('new_register'))
                                <a class="nav-link" href="{{ route('new_register') }}"><i class="la la-user-plus"></i>Sign Up</a>
                            @endif
                        </li>
                    @else
                        <li class="nav-item dropdown">

                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <i class="la la-user"></i> {{ Auth::user()->name }}
                                <span class="badge badge-warning"><i class="la la-briefcase"></i>{{auth()->user()->premium_jobs_balance}}</span>
                                <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{route('account')}}">{{__('app.dashboard')}} </a>


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

    <div class="main-container">
        @yield('content')
    </div>

    <div id="main-footer" class="main-footer bg-dark py-5">

        <div class="container" style=" align-content: center;">
            <div class="row">
                <div class="col-md-4">

                    <div class="footer-logo-wrap mb-3">
                        <a class="navbar-brand" href="{{ url('/') }}">
                            <img src="{{asset('assets/images/logo.jpeg')}}" style="opacity: 1px;" />
                        </a>
                    </div>

                    <div class="footer-menu-wrap">
                        <ul class="list-unstyled">
                            <?php
                            $show_in_footer_menu = config('footer_menu_pages');
                            ?>
                            @if($show_in_footer_menu->count() > 0)
                                @foreach($show_in_footer_menu as $page)
                                    <li><a href="{{ route('single_page', $page->slug) }}">{{ $page->title }} </a></li>
                                @endforeach
                            @endif
                            <li><a href="{{route('contact_us')}}">@lang('app.contact_us')</a> </li>
                        </ul>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="footer-menu-wrap  mt-2">
                        <h4 class="mb-3">Quick Links</h4>

                        <ul class="list-unstyled">
                            <li><a href="{{ route('request') }}">Request Service</a> </li>
                            <li><a href="{{ route('new_register') }}">@lang('app.create_account')</a> </li>
                            <li><a href="{{route('login')}}">Sign In</a> </li>
                        </ul>

                    </div>

                </div>


            </div>


            <div class="row">
                <div class="col-md-12">
                    <div class="footer-copyright-text-wrap text-center mt-4">
                        <p>{!! get_text_tpl(get_option('copyright_text')) !!}</p>
                    </div>
                </div>
            </div>

        </div>

    </div>


</div>



<!-- Scripts -->
@yield('page-js')
<script src="{{ asset('assets/js/main.js') }}" defer></script>




</body>
</html>
