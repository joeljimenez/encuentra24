@extends('layouts.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection

@section('content')

    <div class="page-header search-page-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    @if( ! empty($title)) <h2>{{ $title }} </h2> @endif
                    <div class="btn-group btn-breadcrumb">
                        <a href="{{route('home')}}" class="btn btn-warning"><i class="glyphicon glyphicon-home"></i></a>
                        {!! $pagination_output !!}
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="search-box-wrap ">

        <div class="container">
            <div class="row">

                <div class="col-md-12">

                    <div class="home-search-section-inner text-center">
                        <form action="{{route('search_redirect')}}" class="form-inline" method="get" enctype="multipart/form-data">

                        <div class="form-group">
                            <i class="fa fa-map-marker"></i>

                            <select class="select2LoadCity form-control" name="city">
                                <option value="">@lang('app.city')</option>

                                @if($city_id)
                                    <option value="{{$city_id}}" selected="selected">{{$city_name}}</option>
                                @endif

                            </select>
                        </div>
                        <div class="form-group">
                            <i class="fa fa-bullhorn"></i>
                            <input type="text" class="form-control" id="searchKeyword" name="q" value="{{request('q')}}" placeholder="@lang('app.what_are_u_looking')">
                        </div>

                        <div class="form-group">
                            <i class="fa fa-folder-open-o"></i>
                            <select class="select2" name="cat">
                                <option value="">@lang('app.select_a_category')</option>
                                @if($top_categories->count())
                                    @foreach($top_categories as $top_cat)
                                        <optgroup label="{{$top_cat->category_name}}">
                                            @foreach($top_cat->sub_categories as $sub_category)
                                                <option value="{{ $sub_category->id }}" @if($sub_category->id == $category_id) selected="selected" @endif >{{ $sub_category->category_name }}</option>
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

    @if($query_category)
        @php
            $searchable_top_category = $query_category;
            if ($query_category->category_id){
                $searchable_top_category = \App\Category::find($query_category->category_id);
            }
        @endphp

        @if($searchable_top_category->sub_categories->count())
            <div class="search-sub-category">
                <div class="container">
                    <div class="row">

                        <div class="col-md-12">
                            @if($query_category->category_id)
                                <p>@lang('app.also_search_in_similar_categories')</p>
                            @else
                                <p>@lang('app.also_search_in_sub_categories')</p>
                            @endif
                        </div>

                        @foreach($searchable_top_category->sub_categories as $sub_c)
                            <?php
                            $route_params = [];
                            $previous_params = array_diff(request()->route()->parameters, ['cat-'.$query_category->id.'-'.$query_category->category_slug]);
                            if (is_array($previous_params) && count($previous_params)){
                                $route_params[] = implode('/', $previous_params);
                            }
                            $route_params[] = 'cat-'.$sub_c->id.'-'.$sub_c->category_slug;
                            ?>

                            <div class="col-md-3">
                                <div class="search-category-title"><a href="{{ route('search', $route_params) }}"><i class="fa fa-folder-open-o"></i> {{$sub_c->category_name}}</a></div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        @endif

    @else
        @if($top_categories->count())
            <div class="search-sub-category">
                <div class="container">
                    <div class="row">

                        <div class="col-md-12">
                            <p>@lang('app.search_in_categories')</p>
                        </div>

                        @foreach($top_categories as $top_category)
                            <div class="col-md-3">
                                <?php
                                $route_params = [];
                                $previous_params = request()->route()->parameters;
                                if (is_array($previous_params) && count($previous_params)){
                                    $route_params[] = implode('/', $previous_params);
                                }
                                $route_params[] = 'cat-'.$top_category->id.'-'.$top_category->category_slug;
                                ?>
                                <div class="search-category-title"><a href="{{ route('search', $route_params) }}"><i class="fa fa-folder-open-o"></i> {{$top_category->category_name}}</a></div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        @endif
    @endif

    <div class="itemViewFilterWrap">
        <div class="container">
            <div class="row">

                <div class="col-md-12">
                    <div class="listingTopFilterBar">
                        <ul class="filterAdType pull-left">
                            <li class="@if( ! request('adType') || request('adType') === 'all') active @endif"><a href="{{request()->fullUrlWithQuery(['adType' => 'all'])}}">@lang('app.all_ads') <small>({{$business_ads_count+$personal_ads_count}})</small></a> </li>
                            <li class="@if (request('adType') === 'personal') active @endif"><a href="{{request()->fullUrlWithQuery(['adType' => 'personal'])}}">@lang('app.personal') <small>({{$personal_ads_count}})</small></a> </li>
                            <li class="@if (request('adType') === 'business') active @endif"><a href="{{request()->fullUrlWithQuery(['adType' => 'business'])}}">@lang('app.business') <small>({{$business_ads_count}})</small></a> </li>
                        </ul>

                        <ul class="listingViewIcon pull-right">
                            <li class="dropdown shortByListingLi">
                                <a aria-expanded="false" aria-haspopup="true" role="button" data-toggle="dropdown" class="dropdown-toggle" href="#">@lang('app.short_by') <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="{{ request()->fullUrlWithQuery(['shortBy'=>'price_high_to_low']) }}">@lang('app.price_high_to_low')</a></li>
                                    <li><a href="{{ request()->fullUrlWithQuery(['shortBy'=>'price_low_to_high']) }}">@lang('app.price_low_to_high')</a></li>
                                    <li><a href="{{ request()->fullUrlWithQuery(['shortBy'=>'latest']) }}">@lang('app.latest')</a></li>
                                </ul>
                            </li>
                            <li><a href="javascript:;" class="itemListView"><i class="fa fa-bars"></i> </a></li>
                            <li><a href="javascript:;" class="itemImageListView"><i class="fa fa-th-list"></i> </a> </li>
                            <li><a href="javascript:;" class="itemGridView"><i class="fa fa-th-large"></i> </a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @if($ads->count())
        <div id="regular-ads-container">
            <div class="container">
                <div class="row">

                    @foreach($ads as $ad)
                        <div class="item-loop col-md-3">

                            <div class="ad-box ad-type-{{$ad->price_plan}}">
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
                                            {!! $ad->premium_icon() !!} </div>
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
                    <div class="no-content-wrap">
                        <h2> <i class="fa fa-info-circle"></i> @lang('app.there_is_no_ads')</h2>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                {!! $ads->links() !!}
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

        $('.itemListView').click(function (e) {
            e.preventDefault();
            switchItemView('itemListView');
        });

        $('.itemGridView').click(function (e) {
            e.preventDefault();
            switchItemView('itemGridView');
        });

        $('.itemImageListView').click(function (e) {
            e.preventDefault();
            switchItemView('itemImageListView');
        });

        function setInitialItemViewMode() {
            var isSavedViewMode = getCookie("itemViewMode");
            if (isSavedViewMode != "") {
                switchItemView(isSavedViewMode);
            }
        }
        setInitialItemViewMode();

        function switchItemView(mode){
            var item_loop = $('.item-loop');

            if (mode == 'itemListView'){
                item_loop.addClass('item-loop-list').removeClass('col-md-3 item-loop-list-thumb');
                item_loop.find('.ads-thumbnail').hide();
                setCookie('itemViewMode', 'itemListView', 30);
            }else if (mode == 'itemGridView'){
                item_loop.removeClass('item-loop-list item-loop-list-thumb').addClass('col-md-3');
                item_loop.find('.ads-thumbnail').show();
                setCookie('itemViewMode', 'itemGridView', 30);
            }else if(mode == 'itemImageListView'){
                item_loop.addClass('item-loop-list-thumb').removeClass('col-md-3 item-loop-list');
                item_loop.find('.ads-thumbnail').show();
                setCookie('itemViewMode', 'itemImageListView', 30);
            }
        }



    </script>
@endsection