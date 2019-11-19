@extends('layouts.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection


@section('page-css')
    <link href="{{asset('assets/plugins/bootstrap-datepicker-1.6.4/css/bootstrap-datepicker3.css')}}" rel="stylesheet">
@endsection

@section('content')

    <div class="container">

        <div id="wrapper">

            @include('admin.sidebar_menu')

            <div id="page-wrapper">
                @if( ! empty($title))
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header"> {{ $title }}  </h1>
                        </div> <!-- /.col-lg-12 -->
                    </div> <!-- /.row -->
                @endif

                @include('admin.flash_msg')

                <div class="row">
                    <div class="col-md-10 col-xs-12">

                        <form action="" id="adsPostForm" class="form-horizontal" method="post" enctype="multipart/form-data"> @csrf

                        <legend> <span class="ad_text"> @lang('app.ad') </span> @lang('app.info')</legend>

                        <div class="form-group {{ $errors->has('ad_title')? 'has-error':'' }}">
                            <label for="ad_title" class="col-sm-4 control-label"><span class="ad_text"> @lang('app.ad') </span> @lang('app.title')</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="ad_title" value="{{ old('ad_title') ? old('ad_title') : $ad->title }}" name="ad_title" placeholder="@lang('app.ad_title')">
                                {!! $errors->has('ad_title')? '<p class="help-block">'.$errors->first('ad_title').'</p>':'' !!}
                                <p class="text-info"> @lang('app.great_title_info')</p>
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('ad_description')? 'has-error':'' }}">
                            <label class="col-sm-4 control-label"><span class="ad_text"> @lang('app.ad') </span> @lang('app.description')</label>
                            <div class="col-sm-8">
                                <textarea name="ad_description" class="form-control" id="content_editor" rows="8">{{ old('ad_description')?  old('ad_description') : $ad->description }}</textarea>
                                {!! $errors->has('ad_description')? '<p class="help-block">'.$errors->first('ad_description').'</p>':'' !!}
                                <p class="text-info"> @lang('app.ad_description_info_text')</p>
                            </div>
                        </div>

                        <div class="classified_field">

                            <div class="form-group {{ $errors->has('condition')? 'has-error':'' }}">
                                <label for="condition" class="col-sm-4 control-label">@lang('app.condition')</label>
                                <div class="col-sm-8">
                                    <select class="form-control select2NoSearch" name="condition" id="condition">
                                        <option value="new" {{ $ad->ad_condition == 'new' ? 'selected':'' }}>@lang('app.new')</option>
                                        <option value="used" {{ $ad->ad_condition == 'used' ? 'selected':'' }}>@lang('app.used')</option>
                                    </select>
                                    {!! $errors->has('condition')? '<p class="help-block">'.$errors->first('condition').'</p>':'' !!}
                                </div>
                            </div>
                        </div>


                        <div class="form-group  {{ $errors->has('price')? 'has-error':'' }}">
                            <label for="price" class="col-md-4 control-label"><span class="price_text">@lang('app.price')</label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-addon">{{ get_option('currency_sign') }}</span>
                                    <input type="text" placeholder="@lang('app.ex_price')" class="form-control" name="price" id="price" value="{{ old('price')? old('price') : $ad->price }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" value="1" name="negotiable" id="negotiable" {{ $ad->is_negotiable == 1 ? 'checked':'' }}>
                                        @lang('app.negotiable')
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-8 col-md-offset-4">
                                {!! $errors->has('price')? '<p class="help-block">'.$errors->first('price').'</p>':'' !!}
                                <p class="text-info">Pick a good price. </p>
                            </div>

                        </div>


                        @if($ad->category_type == 'jobs' && $ad->job)
                            <div class="job_field specific-fields">

                                <hr />

                                <div class="form-group {{ $errors->has('salary_will_be')? 'has-error':'' }}">
                                    <label for="salary_will_be" class="col-sm-4 control-label">@lang('app.mentioned_salary_will_be')</label>
                                    <div class="col-sm-8">

                                        <div class="price_input_group">

                                            <label><input type="radio" value="monthly" name="salary_will_be" @if($ad->job->salary_will_be == 'monthly') checked="checked" @endif />@lang('app.monthly') </label> <br />
                                            <label><input type="radio" value="yearly" name="salary_will_be" @if($ad->job->salary_will_be == 'yearly') checked="checked" @endif />@lang('app.yearly') </label>
                                            {!! $errors->has('salary_will_be')? '<p class="help-block">'.$errors->first('salary_will_be').'</p>':'' !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('job_nature')? 'has-error':'' }}">
                                    <label for="job_nature" class="col-sm-4 control-label">@lang('app.job_nature')</label>
                                    <div class="col-sm-8">
                                        <select class="form-control select2NoSearch" name="job_nature" id="job_nature">
                                            <option value="fulltime" @if($ad->job->job_nature == 'fulltime') selected="selected" @endif >@lang('app.fulltime')</option>
                                            <option value="internship" @if($ad->job->job_nature == 'internship') selected="selected" @endif >@lang('app.internship')</option>
                                            <option value="parttime" @if($ad->job->job_nature == 'parttime') selected="selected" @endif  >@lang('app.parttime')</option>
                                        </select>
                                        {!! $errors->has('job_nature')? '<p class="help-block">'.$errors->first('job_nature').'</p>':'' !!}
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('job_validity')? 'has-error':'' }}">
                                    <label for="job_validity" class="col-sm-4 control-label">@lang('app.job_validity')</label>
                                    <div class="col-sm-8">

                                        <div class="price_input_group">
                                            <label><input type="radio" value="permanent" name="job_validity" @if($ad->job->job_validity == 'permanent') checked="checked" @endif  />@lang('app.permanent') </label> <br />
                                            <label><input type="radio" value="contractual" name="job_validity" @if($ad->job->job_validity == 'contractual') checked="checked" @endif />@lang('app.contractual') </label>
                                            {!! $errors->has('job_validity')? '<p class="help-block">'.$errors->first('job_validity').'</p>':'' !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('apply_instruction')? 'has-error':'' }}">
                                    <label class="col-sm-4 control-label"> @lang('app.apply_instruction')</label>
                                    <div class="col-sm-8">
                                        <textarea name="apply_instruction" class="form-control summerNoteEditor" rows="3">{{ $ad->job->apply_instruction }}</textarea>
                                        {!! $errors->has('apply_instruction')? '<p class="help-block">'.$errors->first('apply_instruction').'</p>':'' !!}
                                        <p class="text-info"> @lang('app.apply_instruction_info_text')</p>
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('application_deadline')? 'has-error':'' }}">
                                    <label for="application_deadline" class="col-sm-4 control-label"> @lang('app.application_deadline')</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="application_deadline" value="{{ $ad->job->application_deadline }}" name="application_deadline" placeholder="@lang('app.application_deadline')">
                                        {!! $errors->has('application_deadline')? '<p class="help-block">'.$errors->first('application_deadline').'</p>':'' !!}
                                    </div>
                                </div>

                            </div>

                        @endif




                        @if($ad->category_type == 'cars_and_vehicles' && $ad->cars_and_vehicles)

                        <div class="cars_and_vehicles_fields specific-fields">

                            <hr />

                            <div class="form-group {{ $errors->has('transmission')? 'has-error':'' }}">
                                <label for="transmission" class="col-sm-4 control-label">@lang('app.transmission')</label>
                                <div class="col-sm-8">
                                    <label class="radio-inline">
                                        <input type="radio" value="manual" name="transmission"  {{ $ad->cars_and_vehicles->transmission == 'manual'? 'checked="checked"' : '' }}>
                                        @lang('app.manual')
                                    </label>

                                    <label class="radio-inline">
                                        <input type="radio" value="automatic" name="transmission"  {{ $ad->cars_and_vehicles->transmission == 'automatic'? 'checked="checked"' : '' }}>
                                        @lang('app.automatic')
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" value="others" name="transmission"  {{ $ad->cars_and_vehicles->transmission == 'others'? 'checked="checked"' : '' }}>
                                        @lang('app.others')
                                    </label>
                                    {!! $errors->has('transmission')? '<p class="help-block">'.$errors->first('transmission').'</p>':'' !!}
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('fuel_type')? 'has-error':'' }}">
                                <label for="fuel_type" class="col-sm-4 control-label">@lang('app.fuel_type')</label>
                                <div class="col-sm-8">
                                    <label class="radio-inline">
                                        <input type="radio" value="diesel" name="fuel_type"  {{ $ad->cars_and_vehicles->fuel_type == 'diesel'? 'checked="checked"' : '' }}>
                                        @lang('app.diesel')
                                    </label>

                                    <label class="radio-inline">
                                        <input type="radio" value="petrol" name="fuel_type"  {{ $ad->cars_and_vehicles->fuel_type == 'petrol'? 'checked="checked"' : '' }}>
                                        @lang('app.petrol')
                                    </label>

                                    <label class="radio-inline">
                                        <input type="radio" value="cng" name="fuel_type"  {{ $ad->cars_and_vehicles->fuel_type == 'cng'? 'checked="checked"' : '' }}>
                                        @lang('app.cng')
                                    </label>

                                    <label class="radio-inline">
                                        <input type="radio" value="octane" name="fuel_type"  {{ $ad->cars_and_vehicles->fuel_type == 'octane'? 'checked="checked"' : '' }}>
                                        @lang('app.octane')
                                    </label>

                                    <label class="radio-inline">
                                        <input type="radio" value="others" name="fuel_type"  {{ $ad->cars_and_vehicles->fuel_type == 'others'? 'checked="checked"' : '' }}>
                                        @lang('app.others')
                                    </label>
                                    {!! $errors->has('fuel_type')? '<p class="help-block">'.$errors->first('fuel_type').'</p>':'' !!}
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('engine_cc')? 'has-error':'' }}">
                                <label for="engine_cc" class="col-sm-4 control-label">@lang('app.engine_cc')</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="engine_cc" value="{{ $ad->cars_and_vehicles->engine_cc }}" name="engine_cc" placeholder="@lang('app.engine_cc')">
                                    {!! $errors->has('engine_cc')? '<p class="help-block">'.$errors->first('engine_cc').'</p>':'' !!}
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('mileage')? 'has-error':'' }}">
                                <label for="mileage" class="col-sm-4 control-label">@lang('app.mileage')</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="mileage" value="{{ $ad->cars_and_vehicles->mileage }}" name="mileage" placeholder="@lang('app.mileage')">
                                    {!! $errors->has('mileage')? '<p class="help-block">'.$errors->first('mileage').'</p>':'' !!}
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('build_year')? 'has-error':'' }}">
                                <label for="build_year" class="col-sm-4 control-label">@lang('app.build_year')</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="build_year" value="{{ $ad->cars_and_vehicles->build_year }}" name="build_year" placeholder="@lang('app.build_year')">
                                    {!! $errors->has('build_year')? '<p class="help-block">'.$errors->first('build_year').'</p>':'' !!}
                                </div>
                            </div>

                        </div>

                        @endif


                        <legend>@lang('app.image')</legend>

                        <div class="form-group {{ $errors->has('images')? 'has-error':'' }}">
                            <div class="col-sm-12">

                                <div id="uploaded-ads-image-wrap">
                                    @if($ad->media_img->count() > 0)
                                        @foreach($ad->media_img as $img)
                                            <div class="creating-ads-img-wrap">
                                                <img src="{{ media_url($img, false) }}" class="img-responsive" />
                                                <div class="img-action-wrap" id="{{ $img->id }}">
                                                    <a href="javascript:;" class="imgDeleteBtn"><i class="fa fa-trash-o"></i> </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>


                                <div class="col-sm-8 col-sm-offset-4">
                                    <div class="upload-images-input-wrap">
                                        <input type="file" name="images[]" class="form-control" />
                                        <input type="file" name="images[]" class="form-control" />
                                    </div>

                                    <div class="image-ad-more-wrap">
                                        <a href="javascript:;" class="image-add-more"><i class="fa fa-plus-circle"></i> @lang('app.add_more')</a>
                                    </div>
                                </div>
                                {!! $errors->has('images')? '<p class="help-block">'.$errors->first('images').'</p>':'' !!}
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('video_url')? 'has-error':'' }}">
                            <label for="ad_title" class="col-sm-4 control-label">@lang('app.video_url')</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="video_url" value="{{ old('video_url')? old('video_url') : $ad->video_url }}" name="video_url" placeholder="@lang('app.video_url')">
                                {!! $errors->has('video_url')? '<p class="help-block">'.$errors->first('video_url').'</p>':'' !!}
                                <p class="help-block">@lang('app.video_url_help')</p>
                                <p class="text-info">@lang('app.video_url_help_for_modern_theme')</p>
                            </div>
                        </div>


                        <legend>@lang('app.location_info')</legend>

                        <div class="form-group  {{ $errors->has('country')? 'has-error':'' }}">
                            <label for="category_name" class="col-sm-4 control-label">@lang('app.country')</label>
                            <div class="col-sm-8">
                                <select class="form-control select2" name="country">
                                    <option value="">@lang('app.select_a_country')</option>

                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $ad->country_id == $country->id ? 'selected' :'' }}>{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                {!! $errors->has('country')? '<p class="help-block">'.$errors->first('country').'</p>':'' !!}
                            </div>
                        </div>

                        <div class="form-group  {{ $errors->has('state')? 'has-error':'' }}">
                            <label for="category_name" class="col-sm-4 control-label">@lang('app.state')</label>
                            <div class="col-sm-8">
                                <select class="form-control select2" id="state_select" name="state">
                                    @if($previous_states->count() > 0)
                                        @foreach($previous_states as $state)
                                        <option value="{{ $state->id }}" {{ $ad->state_id == $state->id ? 'selected' :'' }}>{{ $state->state_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <p class="text-info">
                                    <span id="state_loader" style="display: none;"><i class="fa fa-spin fa-spinner"></i> </span>
                                </p>
                            </div>
                        </div>

                        <div class="form-group  {{ $errors->has('city')? 'has-error':'' }}">
                            <label for="category_name" class="col-sm-4 control-label">@lang('app.city')</label>
                            <div class="col-sm-8">
                                <select class="form-control select2" id="city_select" name="city">
                                    @if($previous_cities->count() > 0)
                                        @foreach($previous_cities as $city)
                                        <option value="{{ $city->id }}" {{ $ad->city_id == $city->id ? 'selected':'' }}>{{ $city->city_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <p class="text-info">
                                    <span id="city_loader" style="display: none;"><i class="fa fa-spin fa-spinner"></i> </span>
                                </p>
                            </div>
                        </div>

                        @if($ad->category_type == 'jobs')
                            <div class="job_field">
                                <div class="form-group  {{ $errors->has('is_any_where')? 'has-error':'' }}">
                                    <label for="is_any_where_select" class="col-sm-4 control-label"></label>
                                    <div class="col-sm-8">

                                        <label>
                                            <input type="checkbox" name="is_any_where" value="1" @if($ad->job->is_any_where == '1') checked="checked" @endif /> @lang('app.is_any_where')
                                        </label>
                                        <p class="text-info">
                                            @lang('app.is_any_where_in_country')
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <legend><span class="seller_text"> @lang('app.seller') </span> @lang('app.info')</legend>

                        <div class="form-group {{ $errors->has('seller_name')? 'has-error':'' }}">
                            <label for="seller_name" class="col-sm-4 control-label"> <span class="seller_text"> @lang('app.seller') </span> @lang('app.name')</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="seller_name" value="{{ old('seller_name')? old('seller_name') : $ad->seller_name }}" name="seller_name" placeholder="@lang('app.seller_name')">
                                {!! $errors->has('seller_name')? '<p class="help-block">'.$errors->first('seller_name').'</p>':'' !!}
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('seller_email')? 'has-error':'' }}">
                            <label for="seller_email" class="col-sm-4 control-label">  <span class="seller_text"> @lang('app.seller') </span> @lang('app.email')</label>
                            <div class="col-sm-8">
                                <input type="email" class="form-control" id="seller_email" value="{{ old('seller_email')? old('seller_email') : $ad->seller_email }}" name="seller_email" placeholder="@lang('app.seller_email')">
                                {!! $errors->has('seller_email')? '<p class="help-block">'.$errors->first('seller_email').'</p>':'' !!}
                            </div>
                        </div>


                        <div class="form-group {{ $errors->has('seller_phone')? 'has-error':'' }}">
                            <label for="seller_phone" class="col-sm-4 control-label">  <span class="seller_text"> @lang('app.seller') </span> @lang('app.phone')</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="seller_phone" value="{{ old('seller_phone') ? old('seller_phone') : $ad->seller_phone }}" name="seller_phone" placeholder="@lang('app.seller_phone')">
                                {!! $errors->has('seller_phone')? '<p class="help-block">'.$errors->first('seller_phone').'</p>':'' !!}
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('address')? 'has-error':'' }}">
                            <label for="address" class="col-sm-4 control-label">@lang('app.address')</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="address" value="{{ old('address')? old('address') : $ad->address }}" name="address" placeholder="@lang('app.address')">
                                {!! $errors->has('address')? '<p class="help-block">'.$errors->first('address').'</p>':'' !!}
                                <p class="text-info">@lang('app.address_line_help_text')</p>
                            </div>
                        </div>

                        @if(get_option('enable_google_maps') == '1')
                            <div class="alert alert-info">
                                <p><i class="fa fa-info-circle"></i> @lang('app.map_click_help') </p>
                            </div>
                            <input id="pac-input" class="controls" type="text" placeholder="Search Box">
                            <div id="dvMap" style="width: 100%; height: 400px; margin: 20px 0;"></div>
                        @endif
                        <input type="hidden" name="latitude" id="latitude" value="{{defaultLatLon()->lat}}" />
                        <input type="hidden" name="longitude" id="longitude" value="{{defaultLatLon()->lon}}" />


                        <hr />

                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-8">
                                <button type="submit" class="btn btn-primary">@lang('app.edit_ad')</button>
                            </div>
                        </div>
                        </form>

                    </div>

                </div>

            </div>   <!-- /#page-wrapper -->

        </div>   <!-- /#wrapper -->


    </div> <!-- /#container -->

@endsection

@section('page-js')

    <script src="{{ asset('assets/plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        // Replace the <textarea id="editor1"> with a CKEditor
        // instance, using default configuration.
        CKEDITOR.replace( 'content_editor' );
    </script>

    @if(get_option('enable_google_maps') == '1')
        <script src="https://maps.googleapis.com/maps/api/js?key={{get_option('google_map_api_key')}}&libraries=places&callback=initAutocomplete" async defer></script>

        <script>
            // This example adds a search box to a map, using the Google Place Autocomplete
            // feature. People can enter geographical searches. The search box will return a
            // pick list containing a mix of places and predicted search terms.

            function initAutocomplete() {

                var myLatLng = {lat: {{ $ad->latitude }}, lng: {{ $ad->longitude }} };

                var map = new google.maps.Map(document.getElementById('dvMap'), {
                    center: myLatLng,
                    zoom: 15,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                });

                // Create the search box and link it to the UI element.
                var input = document.getElementById('pac-input');
                var searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

                // Bias the SearchBox results towards current map's viewport.
                map.addListener('bounds_changed', function() {
                    searchBox.setBounds(map.getBounds());
                });

                //Click event for getting lat lng
                google.maps.event.addListener(map, 'click', function (e) {
                    $('input#latitude').val(e.latLng.lat());
                    $('input#longitude').val(e.latLng.lng());
                });

                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                    draggable:true,
                    title: 'Ad Title'
                });
                marker.setMap(map);

                var markers = [];
                // [START region_getplaces]
                // Listen for the event fired when the user selects a prediction and retrieve
                // more details for that place.
                searchBox.addListener('places_changed', function() {
                    var places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }

                    // Clear out the old markers.
                    markers.forEach(function(marker) {
                        marker.setMap(null);
                    });
                    markers = [];

                    // For each place, get the icon, name and location.
                    var bounds = new google.maps.LatLngBounds();
                    places.forEach(function(place) {
                        var icon = {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25)
                        };

                        // Create a marker for each place.
                        markers.push(new google.maps.Marker({
                            map: map,
                            icon: icon,
                            title: place.name,
                            position: place.geometry.location
                        }));

                        if (place.geometry.viewport) {
                            // Only geocodes have viewport.
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
                // [END region_getplaces]
            }

        </script>
    @endif

    <script src="{{asset('assets/plugins/bootstrap-datepicker-1.6.4/js/bootstrap-datepicker.js')}}"></script>
    <script type="text/javascript">
        $('#application_deadline').datepicker({
            format: "yyyy-mm-dd",
            todayHighlight: true,
            startDate: new Date(),
            autoclose: true
        });
        $('#build_year').datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years",
            autoclose: true
        });
    </script>

    <script>

        function generate_option_from_json(jsonData, fromLoad){

            //Load Category Json Data To Brand Select
            if (fromLoad === 'category_to_brand'){
                var option = '';
                if (jsonData.length > 0) {
                    option += '<option value="0" selected> <?php echo trans('app.select_a_brand') ?> </option>';
                    for ( i in jsonData){
                        option += '<option value="'+jsonData[i].id+'"> '+jsonData[i].brand_name +' </option>';
                    }
                    $('#brand_select').html(option);
                    $('#brand_select').select2();
                }else {
                    $('#brand_select').html('');
                    $('#brand_select').select2();
                }
                $('#brand_loader').hide('slow');
            }else if(fromLoad === 'country_to_state'){
                var option = '';
                if (jsonData.length > 0) {
                    option += '<option value="0" selected> @lang('app.select_state') </option>';
                    for ( i in jsonData){
                        option += '<option value="'+jsonData[i].id+'"> '+jsonData[i].state_name +' </option>';
                    }
                    $('#state_select').html(option);
                    $('#state_select').select2();
                }else {
                    $('#state_select').html('');
                    $('#state_select').select2();
                }
                $('#state_loader').hide('slow');

            }else if(fromLoad === 'state_to_city'){
                var option = '';
                if (jsonData.length > 0) {
                    option += '<option value="0" selected> @lang('app.select_city') </option>';
                    for ( i in jsonData){
                        option += '<option value="'+jsonData[i].id+'"> '+jsonData[i].city_name +' </option>';
                    }
                    $('#city_select').html(option);
                    $('#city_select').select2();
                }else {
                    $('#city_select').html('');
                    $('#city_select').select2();
                }
                $('#city_loader').hide('slow');
            }
        }


        $(document).ready(function(){
            $('[name="category"]').change(function(e){
                e.preventDefault();

                var category_id = $(this).val();
                $('#brand_loader').show();

                //Changing text first
                var ad_type = $('option:selected', this).attr('data-category-type');

                change_text_by_ads_category(ad_type);
                //Request to ajax for band load
                $.ajax({
                    type : 'POST',
                    url : '{{ route('get_brand_by_category') }}',
                    data : { category_id : category_id, ad_type:ad_type, _token : '{{ csrf_token() }}' },
                    success : function (data) {
                        generate_option_from_json(data, 'category_to_brand');
                    }
                });
            });

            change_text_by_ads_category('{{$ad->category_type}}');

            function change_text_by_ads_category(ad_type) {
                var classified_fields = $('.classified_field');
                var cars_and_vehicles_fields = $('.cars_and_vehicles_fields');
                var job_field = $('.job_field');

                var job_text = '@lang('app.job')';
                var ad_text = '@lang('app.ad')';
                var seller_text = '@lang('app.seller')';
                var employer_text = '@lang('app.employer')';
                var price_text = '@lang('app.price')';
                var salary_text = '@lang('app.salary')';

                $('.specific-fields').hide();

                classified_fields.show();
                cars_and_vehicles_fields.hide();
                job_field.hide();

                //Change label text
                $('.ad_text').html(ad_text);
                $('.seller_text').html(seller_text);
                $('.price_text').html(price_text);

                if (ad_type === 'jobs'){
                    $('.ad_text').html(job_text);
                    $('.seller_text').html(employer_text);
                    $('.price_text').html(salary_text);

                    classified_fields.hide();
                    job_field.show();
                }else if(ad_type === 'cars_and_vehicles'){
                    cars_and_vehicles_fields.show();
                } else {
                    //
                }
            }


            $('[name="country"]').change(function(){
                var country_id = $(this).val();
                $('#state_loader').show();
                $.ajax({
                    type : 'POST',
                    url : '{{ route('get_state_by_country') }}',
                    data : { country_id : country_id,  _token : '{{ csrf_token() }}' },
                    success : function (data) {
                        generate_option_from_json(data, 'country_to_state');
                    }
                });
            });

            $('[name="state"]').change(function(){
                var state_id = $(this).val();
                $('#city_loader').show();
                $.ajax({
                    type : 'POST',
                    url : '{{ route('get_city_by_state') }}',
                    data : { state_id : state_id,  _token : '{{ csrf_token() }}' },
                    success : function (data) {
                        generate_option_from_json(data, 'state_to_city');
                    }
                });
            });

            $('body').on('click', '.imgDeleteBtn', function(){
                //Get confirm from user
                if ( ! confirm('{{ trans('app.are_you_sure') }}')){
                    return '';
                }

                var current_selector = $(this);
                var img_id = $(this).closest('.img-action-wrap').attr('id');
                $.ajax({
                    url : '{{ route('delete_media') }}',
                    type: "POST",
                    data: { media_id : img_id, _token : '{{ csrf_token() }}' },
                    success : function (data) {
                        if (data.success == 1){
                            current_selector.closest('.creating-ads-img-wrap').hide('slow');
                            toastr.success(data.msg, '@lang('app.success')', toastr_options);
                        }
                    }
                });
            });

            $(document).on('click', '.image-add-more', function (e) {
                e.preventDefault();
                $('.upload-images-input-wrap').append('<input type="file" name="images[]" class="form-control" />');
            });

        });
    </script>


    <script>
        @if(session('success'))
            toastr.success('{{ session('success') }}', '<?php echo trans('app.success') ?>', toastr_options);
        @endif
    </script>
@endsection