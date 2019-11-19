@extends('layouts.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection

@section('content')


    <div class="home-top-searchbar" style="background-image: url('{{asset('assets/img/home-search-bg.jpg')}}') ">
        <div class="container">
            <div class="row">

                <div class="col-md-12">
                    <div class="home-search-wrap ">

                        <div class="home-search-section-inner text-center">

                            <h1> @lang('app.find_local_ads') </h1>
                            <p class="sub-text"> @lang('app.search_classified_ads_your_city') </p>

                            <form action="{{route('search_redirect')}}" class="form-inline" method="get" enctype="multipart/form-data">

                                <div class="form-group">
                                    <i class="fa fa-map-marker"></i>

                                    <select class="select2LoadCity form-control" name="city">
                                        <option value="">@lang('app.city')</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <i class="fa fa-bullhorn"></i>
                                    <input type="text" class="form-control" id="searchKeyword" name="q" placeholder="@lang('app.what_are_u_looking')">
                                </div>
                                <div class="form-group">
                                    <i class="fa fa-folder-open-o"></i>
                                    <select class="select2" name="cat">
                                        <option value="">@lang('app.select_a_category')</option>
                                        @if($top_categories->count())
                                            @foreach($top_categories as $top_cat)
                                                <optgroup label="{{$top_cat->category_name}}">
                                                    @foreach($top_cat->sub_categories as $sub_category)
                                                        <option value="{{ $sub_category->id }}" >{{ $sub_category->category_name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-default btn-search"><i class="fa fa-search"></i> @lang('app.search_ads')</button>
                            </form>


                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>


    <div class="home-map-area">
        <div class="container">
            <div class="row">

                <div class="col-md-4">

                    <h2>@lang('app.sell_anything_quickly')</h2>

                    <p>@lang('app.home_short_desc')</p>
                    <ul class="home-features-lists">
                        <li> <i class="fa fa-check-circle-o"></i> @lang('app.spam_free_ads')</li>
                        <li> <i class="fa fa-check-circle-o"></i> @lang('app.low_cost_featured_ads')</li>
                        <li> <i class="fa fa-check-circle-o"></i> @lang('app.sell_anything_quickly')</li>
                        <li> <i class="fa fa-check-circle-o"></i> @lang('app.thousands_buyers_waiting')</li>
                    </ul>

                    <a href="{{route('create_ad')}}" class="btn btn-orange">@lang('app.post_an_ad')</a>
                </div>

                <div class="col-md-4">
                    <div id="oclassifiedMapsStateName"></div>
                    <div id="oclassifiedmaps"></div>
                </div>

                <div class="col-md-4">
                    @if($current_states->count())
                        <div class="front-state-lists">
                            <ul>
                                @foreach($current_states->take(20) as $state)
                                    <li>
                                        <a href="{{route('search', [$current_country['country_code'], "state-".$state->id ] )}}">
                                            <i class="fa fa-map-marker"></i>
                                            {{$state->state_name}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="clearfix"></div>
                            @if($current_states->count() > 20)
                                <a href="{{route('countries', $current_country['country_code'] )}}" class="more-states">@lang('app.all_states')</a>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    @if(get_option('enable_monetize') == 1)
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    {!! get_option('monetize_code_above_categories') !!}
                </div>
            </div>
        </div>
    @endif

    @if($top_categories->count())
        <div class="home-category">

            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="front-ads-head">
                            <h2>@lang('app.categories')</h2>
                        </div>
                    </div>
                </div>
            </div>


            <div class="container">
                <div class="row equal">
                    @foreach($top_categories as $top_cat)
                        <div class="col-md-3">
                            <div class="home-cat-box">
                                <div class="home-cat-box-title">
                                    <h3> <a href="{{ route('search', [ $current_country['country_code'], 'cat-'.$top_cat->id.'-'.$top_cat->category_slug]) }}">
                                            <i class="fa fa-folder"></i>
                                            {{$top_cat->category_name}}
                                            @php $ads_count = $top_cat->ads->count() @endphp
                                            @if($ads_count)
                                                <small>{{$top_cat->ads->count()}}</small>
                                            @endif
                                        </a>
                                    </h3>
                                </div>
                                @if($top_cat->sub_categories->count())
                                    <div class="home-cat-box-content">
                                        <ul>
                                            @foreach($top_cat->sub_categories->take(5) as $sub_cat)
                                                <li><a href="{{ route('search', [ $current_country['country_code'], 'cat-'.$sub_cat->id.'-'.$sub_cat->category_slug]) }}"> <i class="fa fa-folder-o"></i> {{$sub_cat->category_name}}</a> </li>
                                            @endforeach
                                        </ul>
                                        <p>
                                            @if($top_cat->sub_categories->count() > 5)
                                                <a href="{{route('category',$top_cat->id )}}" class="cat-view-more"> <i class="fa fa-arrow-circle-o-right"></i> View {{$top_cat->sub_categories->count()-5}} more</a>
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif


    @if(get_option('enable_monetize') == 1)
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    {!! get_option('monetize_code_below_categories') !!}
                </div>
            </div>
        </div>
    @endif

    @if($premium_ads->count())
        <div id="regular-ads-container">
            <div class="container">
                <div class="row">

                    <div class="col-md-12">
                        <div class="front-ads-head">
                            <h2>@lang('app.new_premium_ads')</h2>
                        </div>
                    </div>

                    @foreach($premium_ads as $ad)
                        <div class="col-md-3">

                            <div class="ad-box">
                                <div class="ads-thumbnail">
                                    <a href="{{ route('single_ad', $ad->slug) }}">
                                        <img itemprop="image"  src="{{ media_url($ad->feature_img) }}" class="img-responsive" alt="{{ $ad->title }}">
                                        <span class="modern-img-indicator">
                                        @if(! empty($ad->video_url))
                                                <i class="fa fa-file-video-o"></i>
                                            @else
                                                <i class="fa fa-file-image-o"> {{ $ad->media_img->count() }}</i>
                                            @endif
                                    </span>
                                    </a>
                                </div>
                                <div class="caption">
                                    <div class="ad-box-caption-title">
                                        <a class="ad-box-title" href="{{ route('single_ad', $ad->slug) }}" title="{{ $ad->title }}">
                                            {{ str_limit($ad->title, 40) }}
                                        </a>
                                    </div>

                                    <div class="ad-box-category">
                                        @if($ad->sub_category)
                                            <a class="price text-muted" href="{{ route('search', [ $ad->country->country_code, 'cat-'.$ad->sub_category->id.'-'.$ad->sub_category->category_slug]) }}"> <i class="fa fa-folder-o"></i> {{ $ad->sub_category->category_name }} </a>
                                        @endif
                                        @if($ad->city)
                                            <a class="location text-muted" href="{{ route('search', [$ad->country->country_code, 'state-'.$ad->state->id, 'city-'.$ad->city->id]) }}"> <i class="fa fa-map-marker"></i> {{ $ad->city->city_name }} </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="ad-box-footer">
                                    <span class="ad-box-price">{!! themeqx_price_ng($ad->price, $ad->is_negotiable) !!}</span>

                                    @if($ad->price_plan == 'premium')
                                        <div class="ad-box-premium" data-toggle="tooltip" title="@lang('app.premium_ad')">
                                            {!! $ad->premium_icon() !!}
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    @else
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="no-ads-wrap">
                        <h2><i class="fa fa-frown-o"></i> @lang('app.no_premium_ads_country') </h2>

                        @if (env('APP_DEMO') == true)
                            <h4>Seems you are checking the demo version, you can check ads preview by switching country to <a href="{{route('set_country', 'US')}}"><img src="{{asset('assets/flags/16/us.png')}}" /> United States </a>  </h4>
                            <p><a  href="{{route('countries')}}"><i class="fa fa-globe"></i> Browse Countries</a> </p>
                        @endif

                    </div>
                </div>
            </div>
        </div>

    @endif


    @if(get_option('enable_monetize') == 1)
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    {!! get_option('monetize_code_below_premium_ads') !!}
                </div>
            </div>
        </div>
    @endif

    @if($regular_ads->count())
        <div id="regular-ads-container">
            <div class="container">
                <div class="row">

                    <div class="col-md-12">
                        <div class="front-ads-head">
                            <h2>@lang('app.new_regular_ads')</h2>
                        </div>
                    </div>

                    @foreach($regular_ads as $ad)
                        <div class="col-md-3">

                            <div class="ad-box">
                                <div class="ads-thumbnail">
                                    <a href="{{ route('single_ad', $ad->slug) }}">
                                        <img itemprop="image"  src="{{ media_url($ad->feature_img) }}" class="img-responsive" alt="{{ $ad->title }}">
                                        <span class="modern-img-indicator">
                                        @if(! empty($ad->video_url))
                                                <i class="fa fa-file-video-o"></i>
                                            @else
                                                <i class="fa fa-file-image-o"> {{ $ad->media_img->count() }}</i>
                                            @endif
                                    </span>
                                    </a>
                                </div>
                                <div class="caption">
                                    <div class="ad-box-caption-title">
                                        <a class="ad-box-title" href="{{ route('single_ad', $ad->slug) }}" title="{{ $ad->title }}">
                                            {{ str_limit($ad->title, 40) }}
                                        </a>
                                    </div>

                                    <div class="ad-box-category">
                                        @if($ad->sub_category)
                                            <a class="price text-muted" href="{{ route('search', [ $ad->country->country_code, 'cat-'.$ad->sub_category->id.'-'.$ad->sub_category->category_slug]) }}"> <i class="fa fa-folder-o"></i> {{ $ad->sub_category->category_name }} </a>
                                        @endif
                                        @if($ad->city)
                                            <a class="location text-muted" href="{{ route('search', [$ad->country->country_code, 'state-'.$ad->state->id, 'city-'.$ad->city->id]) }}"> <i class="fa fa-map-marker"></i> {{ $ad->city->city_name }} </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="ad-box-footer">
                                    <span class="ad-box-price">{!! themeqx_price_ng($ad->price, $ad->is_negotiable) !!}</span>

                                    @if($ad->price_plan == 'premium')
                                        <div class="ad-box-premium"><i class="fa fa-bookmark"></i> </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    @else
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="no-ads-wrap">
                        <h2><i class="fa fa-frown-o"></i> @lang('app.no_regular_ads_country') </h2>

                        @if (env('APP_DEMO') == true)
                            <h4>Seems you are checking the demo version, you can check ads preview by switching country to <a href="{{route('set_country', 'US')}}"><img src="{{asset('assets/flags/16/us.png')}}" /> United States </a> </h4>
                            <p><a  href="{{route('countries')}}"><i class="fa fa-globe"></i> Browse Countries</a> </p>
                        @endif

                    </div>
                </div>
            </div>
        </div>

    @endif


    @if(get_option('enable_monetize') == 1)
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    {!! get_option('monetize_code_below_regular_ads') !!}
                </div>
            </div>
        </div>
    @endif

    <div class="container">
        <div class="section-stats-box">
            <div class="row">
                <div class="col-sm-4">
                    <div class="home-stats-box">
                        <div class="inner">
                            <i class="fa fa-bullhorn"></i>
                            <div class="inner-content">
                                <h3 class="title">@lang('app.ads')</h3>
                                <div class="sub_title">{{$total_ads_count}} @lang('app.ads_available')</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="home-stats-box">
                        <div class="inner">
                            <i class="fa fa-lock"></i>
                            <div class="inner-content">
                                <h3 class="title">@lang('app.secured_payments')</h3>
                                <div class="sub_title">@lang('app.secured_all_your_payments')s</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="no-border home-stats-box">
                        <div class="inner">
                            <i class="fa fa-users"></i>
                            <div class="inner-content">
                                <h3 class="title">@lang('app.trusted_sellers')</h3>
                                <div class="sub_title">{{$user_count}} @lang('app.registered_sellers')</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-features">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <h2>@lang('app.sell_your_items_through')</h2>
                    <p>@lang('app.thousands_of_people_selling')</p>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3">
                    <div class="icon-text-feature">
                        <i class="fa fa-check-circle-o"></i>
                        @lang('app.trusted_buyers')
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="icon-text-feature">
                        <i class="fa fa-check-circle-o"></i>
                        @lang('app.swift_and_secure')
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="icon-text-feature">
                        <i class="fa fa-check-circle-o"></i>
                        @lang('app.spam_free')
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="icon-text-feature">
                        <i class="fa fa-check-circle-o"></i>
                        @lang('app.sell_your_items_quickly')
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <a href="{{route('category')}}" class="btn btn-warning btn-lg"><i class="fa fa-search"></i> @lang('app.browse_ads')</a>
                    <a href="{{route('create_ad')}}" class="btn btn-warning btn-lg"><i class="fa fa-save"></i> @lang('app.post_an_ad')</a>

                </div>
            </div>

        </div>
    </div>


@endsection

@section('page-js')
    <script type="text/javascript">
        $(".select2LoadCity").select2({
            ajax: {
                url: "{{route('searchCityJson')}}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            minimumInputLength: 3,
            templateResult: formatRepo, // omitted for brevity, see the source of this page
            templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
        });
        function formatRepo (repo) {
            if (repo.loading) return repo.city_name;

            var markup = "<div class='clearfix'>"+
                "<div class='select2-result-repository__title'> <i class='fa fa-map-marker'></i> " + repo.city_name + "</div></div>" +
                "</div></div>";

            return markup;
        }
        function formatRepoSelection (repo) {
            return repo.city_name || repo.text;
        }
    </script>


    <script>
        $(document).ready(function () {
            /* SVG Maps */
            $('#oclassifiedmaps').twism("create", {
                map: "custom",
                customMap: '{{asset('assets/maps/'.strtolower($current_country['country_code']).'.svg')}}',
                backgroundColor: 'transparent',
                border: '#c3a29e',
                hoverBorder: "#c3a29e",
                borderWidth: 2,
                color: '#fff4e9',
                width: '250px',
                height: '200px',
                click: function(state) {
                    if (typeof state == "undefined") {
                        return false;
                    }
                    state = rawurlencode(state);
                    var searchPage = '{{route('map_to_search')}}';
                    searchPage = searchPage + '?country={{$current_country['country_code']}}&state=' + state;
                    window.location.replace(searchPage);
                    window.location.href = searchPage;
                },
                hover: function(state_id) {
                    if (typeof state_id == "undefined") {
                        return false;
                    }
                    var selectedState = document.getElementById(state_id);
                    if (typeof selectedState == "undefined") {
                        return false;
                    }
                    if (selectedState.getAttribute("data-name") != null) {
                        $('#oclassifiedMapsStateName').html('<i class="fa fa-map-marker"></i> ' + selectedState.getAttribute("data-name"));
                    }
                    selectedState.style.fill = '#c3a29e';
                    return;
                },
                unhover: function(state_id) {
                    $('#oclassifiedMapsStateName').html('');

                    if (typeof state_id == "undefined") {
                        return false;
                    }
                    var selectedState = document.getElementById(state_id);
                    if (typeof selectedState == "undefined") {
                        return false;
                    }
                    selectedState.style.fill = '#fff4e9';
                    return;
                }
            });
        });
    </script>
@endsection