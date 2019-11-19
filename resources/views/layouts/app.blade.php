<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@section('title') {{ get_option('site_title') }} @show</title>


    <!-- bootstrap css -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-theme.min.css') }}">
    <!-- Font awesome 4.4.0 -->
    <link rel="stylesheet" href="{{ asset('assets/font-awesome-4.4.0/css/font-awesome.min.css') }}">
    <!-- load page specific css -->

    <!-- main select2.css -->
    <link href="{{ asset('assets/select2-4.0.3/css/select2.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">

    <!-- Conditional page load script -->
    @if(request()->segment(1) === 'dashboard')
        <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/metisMenu/dist/metisMenu.min.css') }}">
    @endif

<!-- main style.css -->
    <link rel="stylesheet" href="{{ asset("assets/css/style.css") }}">

    @if(is_rtl())
        <link rel="stylesheet" href="{{ asset("assets/css/rtl.css") }}">
    @endif

    @yield('page-css')

    @if(get_option('additional_css'))
        <style type="text/css">
            {{ get_option('additional_css') }}
        </style>
    @endif

    <script src="{{ asset('assets/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js') }}"></script>

</head>
<body class="@if(is_rtl()) rtl @endif">
<div id="app">

    @if(env('APP_DEMO') == true)
        @include('demobar')
    @endif

    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ route('home') }}">
                    <img src="{{ logo_url() }}" title="{{get_option('site_name')}}" alt="{{get_option('site_name')}}" />
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    &nbsp;<li><a href="{{route('home')}}">@lang('app.home')</a> </li>

                    <?php

                    use App\Post;
                    $header_menu_pages = Post::whereStatus('1')->where('show_in_header_menu', 1)->get();
                    ?>
                    @if($header_menu_pages->count() > 0)
                        @foreach($header_menu_pages as $page)
                            <li><a href="{{ route('single_page', $page->slug) }}">{{ $page->title }} </a></li>
                        @endforeach
                    @endif
                    &nbsp;<li><a href="{{route('create_ad')}}">@lang('app.post_an_ad')</a> </li>
                    &nbsp;<li><a href="{{route('contact_us_page')}}">@lang('app.contact_us')</a> </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->

                    @if(file_exists(public_path('assets/flags/16/'.strtolower($current_country['country_code']).'.png')))
                        <li><a href="{{route('countries')}}" title="{{$current_country['country_name']}}"><img src="{{asset('assets/flags/16/'.strtolower($current_country['country_code']).'.png')}}" /> </a></li>
                    @else
                        <li><a href="{{route('countries')}}"  title="{{$current_country['country_name']}}">{{$current_country['country_code']}} </a></li>
                    @endif

                    @if(get_option('enable_language_switcher') == 1)

                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> @if($current_lang) {{$current_lang->language_name}} @else @lang('app.language') @endif <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ route('switch_language', 'en') }}">English</a></li>
                                @foreach(get_languages() as $lang)
                                    <li><a href="{{ route('switch_language', $lang->language_code) }}">{{ $lang->language_name }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    @endif

                    @if (Auth::guest())
                        <li><a href="{{ route('login') }}">@lang('app.login')</a></li>
                        <li><a href="{{ route('register') }}">@lang('app.register')</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{ auth()->user()->name }} <span class="headerAvatar"> <img src="{{auth()->user()->get_gravatar()}}" /> </span> <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{route('dashboard')}}"> @lang('app.dashboard') </a> </li>
                                <li>
                                    <a href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        @lang('app.logout')
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endif


                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <div id="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php
                    $show_in_footer_menu = Post::whereStatus('1')->where('show_in_footer_menu', 1)->get();
                    ?>

                    <ul class="footer-menu">
                        <li> <a href="{{ route('home') }}"><i class="fa fa-home"></i> @lang('app.home')</a></li>
                        @if($show_in_footer_menu->count() > 0)
                            @foreach($show_in_footer_menu as $page)
                                <li><a href="{{ route('single_page', $page->slug) }}">{{ $page->title }} </a></li>
                            @endforeach
                        @endif
                        <li><a href="{{ route('contact_us_page') }}">@lang('app.contact_us')</a></li>
                    </ul>

                    <div class="footer-heading">
                        <h3>{{get_option('footer_company_name')}}</h3>
                    </div>

                    <div class="footer-copyright">
                        <p>{!! get_text_tpl(get_option('footer_copyright_text')) !!}</p>
                    </div>

                    <div class="footer-social-links">
                        @php
                            $facebook_url = get_option('facebook_url');
                            $twitter_url = get_option('twitter_url');
                            $linked_in_url = get_option('linked_in_url');
                            $dribble_url = get_option('dribble_url');
                            $google_plus_url = get_option('google_plus_url');
                            $youtube_url = get_option('youtube_url');
                        @endphp
                        <ul>
                            @if($facebook_url)
                                <li><a href="{{$facebook_url}}"><i class="fa fa-facebook"></i> </a> </li>
                            @endif
                            @if($twitter_url)
                                <li><a href="{{$twitter_url}}"><i class="fa fa-twitter"></i> </a> </li>
                            @endif
                            @if($google_plus_url)
                                <li><a href="{{$google_plus_url}}"><i class="fa fa-google-plus"></i> </a> </li>
                            @endif
                            @if($youtube_url)
                                <li><a href="{{$youtube_url}}"><i class="fa fa-youtube"></i> </a> </li>
                            @endif
                            @if($linked_in_url)
                                <li><a href="{{$linked_in_url}}"><i class="fa fa-linkedin"></i> </a> </li>
                            @endif
                            @if($dribble_url)
                                <li><a href="{{$dribble_url}}"><i class="fa fa-dribbble"></i> </a> </li>
                            @endif
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>


</div>


<script src="{{ asset('assets/js/vendor/jquery-1.11.2.min.js') }}"></script>
<script src="{{ asset('assets/js/vendor/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('assets/select2-4.0.3/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/twism/jquery.twism.js') }}"></script>

<!-- Conditional page load script -->
@if(request()->segment(1) === 'dashboard')
    <script src="{{ asset('assets/plugins/metisMenu/dist/metisMenu.min.js') }}"></script>
    <script>
        $(function() {
            $('#side-menu').metisMenu();
        });
    </script>
@endif
<script src="{{ asset('assets/js/main.js') }}"></script>
<script>
    var toastr_options = {closeButton : true};
</script>

@if(get_option('additional_js') !== 'additional_js')
    {!! get_option('additional_js') !!}
@endif

@yield('page-js')
</body>
</html>
