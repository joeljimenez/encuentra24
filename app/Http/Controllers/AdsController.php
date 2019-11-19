<?php

namespace App\Http\Controllers;

use App\Ad;
use App\Brand;
use App\CarsVehicle;
use App\Category;
use App\City;
use App\Comment;
use App\Country;
use App\Job;
use App\JobApplication;
use App\Media;
use App\Payment;
use App\Report_ad;
use App\State;
use App\Sub_Category;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class AdsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('app.all_ads');
        $ads = Ad::with('city', 'country', 'state')->whereStatus('1')->orderBy('id', 'desc')->paginate(20);

        return view('admin.all_ads', compact('title', 'ads'));
    }

    public function adminPendingAds()
    {
        $title = trans('app.pending_ads');
        $ads = Ad::with('city', 'country', 'state')->whereStatus('0')->orderBy('id', 'desc')->paginate(20);

        return view('admin.all_ads', compact('title', 'ads'));
    }
    public function adminBlockedAds()
    {
        $title = trans('app.blocked_ads');
        $ads = Ad::with('city', 'country', 'state')->whereStatus('2')->orderBy('id', 'desc')->paginate(20);

        return view('admin.all_ads', compact('title', 'ads'));
    }

    public function myAds(){
        $title = trans('app.my_ads');

        $user = Auth::user();
        $ads = $user->ads()->with('city', 'country', 'state')->orderBy('id', 'desc')->paginate(20);

        return view('admin.my_ads', compact('title', 'ads'));
    }

    public function pendingAds(){
        $title = trans('app.my_ads');

        $user = Auth::user();
        $ads = $user->ads()->whereStatus('0')->with('city', 'country', 'state')->orderBy('id', 'desc')->paginate(20);

        return view('admin.pending_ads', compact('title', 'ads'));
    }

    public function favoriteAds(){
        $title = trans('app.favourite_ads');

        $user = Auth::user();
        $ads = $user->favourite_ads()->with('city', 'country', 'state')->orderBy('id', 'desc')->paginate(20);

        return view('admin.favourite_ads', compact('title', 'ads'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('app.post_an_ad');
        $categories = Category::where('category_id', 0)->orderBy('category_name', 'asc')->get();
        $countries = Country::all();

        $previous_brands = Brand::where('category_id', old('category'))->get();
        $previous_states = State::where('country_id', old('country'))->get();
        $previous_cities = City::where('state_id', old('state'))->get();

        return view('create_ad', compact('title', 'categories', 'countries', 'previous_brands', 'previous_states', 'previous_cities'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $user_id = 0;
        if (Auth::check()){
            $user_id = Auth::user()->id;
        }

        $ads_price_plan = get_option('ads_price_plan');

        if ($request->category){
            $sub_category = Category::find($request->category);
        }

        $rules = [
            'category'          => 'required',
            'ad_title'          => 'required',
            'ad_description'    => 'required',
            'condition'         => 'required',
            'country'           => 'required',
            'seller_name'       => 'required',
            'seller_email'      => 'required',
            'seller_phone'      => 'required',
            'address'           => 'required',
        ];

        if( $ads_price_plan != 'all_ads_free'){
            $rules['price_plan'] = 'required';
        }

        if ($request->category) {
            if ($sub_category->category_type == 'jobs') {
                $rules['salary_will_be'] = 'required';
                $rules['job_nature'] = 'required';
                $rules['job_validity'] = 'required';
                $rules['application_deadline'] = 'required';

                unset($rules['type']);
                unset($rules['condition']);
            }

            if ($sub_category->category_type == 'cars_and_vehicles') {
                $rules['transmission'] = 'required';
                $rules['fuel_type'] = 'required';
                $rules['engine_cc'] = 'required';
                $rules['mileage'] = 'required';
                $rules['build_year'] = 'required';
            }
            if ($sub_category->category_type == 'auction') {
                $rules['bid_deadline'] = 'required';
            }
        }

        //reCaptcha
        if(get_option('enable_recaptcha_post_ad') == 1){
            $rules['g-recaptcha-response'] = 'required';
        }

        $this->validate($request, $rules);

        if (get_option('enable_recaptcha_post_ad') == 1){
            $secret = get_option('recaptcha_secret_key');
            $gRecaptchaResponse = $request->input('g-recaptcha-response');
            $remoteIp = $request->ip();

            $recaptcha = new \ReCaptcha\ReCaptcha($secret);
            $resp = $recaptcha->verify($gRecaptchaResponse, $remoteIp);
            if ( ! $resp->isSuccess()) {
                return redirect()->back()->with('error', 'reCAPTCHA is not verified');
            }
        }

        $title = $request->ad_title;
        $slug = unique_slug($title);

        $is_negotialble = $request->negotiable ? $request->negotiable : '0';
        $brand_id = $request->brand ? $request->brand : 0;
        $mark_ad_urgent = $request->mark_ad_urgent ? $request->mark_ad_urgent : '0';
        $video_url = $request->video_url ? $request->video_url : '';

        $data = [
            'title'             => $request->ad_title,
            'slug'              => $slug,
            'description'       => $request->ad_description,
            'category_id'       => $sub_category->category_id,
            'sub_category_id'   => $request->category,
            'brand_id'          => $brand_id,
            'type'              => $request->type,
            'ad_condition'      => $request->condition,
            'price'             => $request->price,
            'is_negotiable'     => $is_negotialble,

            'seller_name'       => $request->seller_name,
            'seller_email'      => $request->seller_email,
            'seller_phone'      => $request->seller_phone,
            'country_id'        => $request->country,
            'state_id'          => $request->state,
            'city_id'           => $request->city,
            'address'           => $request->address,
            'video_url'         => $video_url,
            'category_type'     => 'classifieds',
            'price_plan'        => $request->price_plan,
            'mark_ad_urgent'    => $mark_ad_urgent,
            'status'            => '0',
            'user_id'           => $user_id,
            'latitude'          => $request->latitude,
            'longitude'         => $request->longitude,
        ];

        if ($sub_category->category_type == 'jobs') {
            $data['category_type'] = 'jobs';
        }

        if ($sub_category->category_type == 'cars_and_vehicles') {
            $data['category_type'] = 'cars_and_vehicles';
        }
        if ($sub_category->category_type == 'auction') {
            $data['category_type']  = 'auction';
            $data['expired_at']     = $request->bid_deadline;
        }
        //Check ads moderation settings
        if (get_option('ads_moderation') == 'direct_publish'){
            $data['status'] = '1';
        }

        //if price_plan not in post data, then set a default value, although mysql will save it as enum first value
        if ( ! $request->price_plan){
            $data['price_plan'] = 'regular';
        }

        $created_ad = Ad::create($data);

        /**
         * iF add created
         */
        if ($created_ad){
            //If job
            if ($sub_category->category_type == 'jobs'){
                $job_data = [
                    'ad_id'                 => $created_ad->id,
                    'job_nature'            => $request->job_nature,
                    'job_validity'          => $request->job_validity,
                    'apply_instruction'     => $request->apply_instruction,
                    'application_deadline'  => $request->application_deadline,
                    'is_any_where'          => $request->is_any_where,
                    'salary_will_be'        => $request->salary_will_be,
                ];
                Job::create($job_data);
            }
            //If cars or vehicle
            if ($sub_category->category_type == 'cars_and_vehicles') {
                $cars_data = [
                    'ad_id'                 => $created_ad->id,
                    'transmission'            => $request->transmission,
                    'fuel_type'            => $request->fuel_type,
                    'engine_cc'            => $request->engine_cc,
                    'mileage'            => $request->mileage,
                    'build_year'            => $request->build_year.'-01-01',
                ];
                CarsVehicle::create($cars_data);
            }
            //Attach all unused media with this ad
            $this->uploadAdsImage($request, $created_ad->id);
            /**
             * Payment transaction login here
             */
            $ads_price = get_ads_price($created_ad->price_plan);
            if ($mark_ad_urgent){
                $ads_price = $ads_price + get_option('urgent_ads_price');
            }

            if ($ads_price){
                //Insert checkout Logic

                $transaction_id = 'tran_'.time().str_random(6);
                // get unique recharge transaction id
                while( ( Payment::whereLocalTransactionId($transaction_id)->count() ) > 0) {
                    $transaction_id = 'reid'.time().str_random(5);
                }
                $transaction_id = strtoupper($transaction_id);

                $currency = get_option('currency_sign');
                $payments_data = [
                    'ad_id'     => $created_ad->id,
                    'user_id'   => $user_id,
                    'amount'    => $ads_price,
                    'payment_method'    => $request->payment_method,
                    'status'    => 'initial',
                    'currency'  => $currency,
                    'local_transaction_id'  => $transaction_id
                ];
                $created_payment = Payment::create($payments_data);

                return redirect(route('payment_checkout', $created_payment->local_transaction_id));
            }

            if ( Auth::check()){
                return redirect(route('pending_ads'))->with('success', trans('app.ad_created_msg'));
            }
            return back()->with('success', trans('app.ad_created_msg'));
        }


        //dd($request->input());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Auth::user();
        $user_id = $user->id;

        $title = trans('app.edit_ad');
        $ad = Ad::find($id);

        if (!$ad)
            return view('admin.error.error_404');

        if (! $user->is_admin()){
            if ($ad->user_id != $user_id){
                return view('admin.error.error_404');
            }
        }

        $countries = Country::all();

        $previous_states = State::where('country_id', $ad->country_id)->get();
        $previous_cities = City::where('state_id', $ad->state_id)->get();

        return view('admin.edit_ad', compact('title', 'countries', 'ad', 'previous_states', 'previous_cities'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $ad = Ad::find($id);
        $user = Auth::user();
        $user_id = $user->id;

        if (! $user->is_admin()){
            if ($ad->user_id != $user_id){
                return view('admin.error.error_404');
            }
        }

        $sub_category = Category::find($ad->sub_category_id);

        $rules = [
            'ad_title'          => 'required',
            'ad_description'    => 'required',
            'country'           => 'required',
            'seller_name'       => 'required',
            'seller_email'      => 'required',
            'seller_phone'      => 'required',
            'address'           => 'required',
        ];

        if ($sub_category->category_type == 'jobs'){
            $rules['salary_will_be']        = 'required';
            $rules['job_nature']            = 'required';
            $rules['job_validity']          = 'required';
            $rules['application_deadline']  = 'required';

            unset($rules['type']);
            unset($rules['condition']);
        }

        if ($sub_category->category_type == 'cars_and_vehicles') {
            $rules['transmission'] = 'required';
            $rules['fuel_type'] = 'required';
            $rules['engine_cc'] = 'required';
            $rules['mileage'] = 'required';
            $rules['build_year'] = 'required';
        }

        $this->validate($request, $rules);

        $title = $request->ad_title;
        //$slug = unique_slug($title);

        $is_negotialble = $request->negotiable ? $request->negotiable : '0';
        $video_url = $request->video_url ? $request->video_url : '';

        $data = [
            'title'             => $request->ad_title,
            'description'       => $request->ad_description,
            'price'             => $request->price,
            'is_negotiable'     => $is_negotialble,

            'seller_name'       => $request->seller_name,
            'seller_email'      => $request->seller_email,
            'seller_phone'      => $request->seller_phone,
            'country_id'        => $request->country,
            'state_id'          => $request->state,
            'city_id'           => $request->city,
            'address'           => $request->address,
            'video_url'         => $video_url,
            'latitude'          => $request->latitude,
            'longitude'         => $request->longitude,
        ];

        $updated_ad = $ad->update($data);

        /**
         * iF add created
         */
        if ($updated_ad){
            if ($sub_category->category_type == 'jobs'){
                $job_data = [
                    'job_nature'            => $request->job_nature,
                    'job_validity'          => $request->job_validity,
                    'apply_instruction'     => $request->apply_instruction,
                    'application_deadline'  => $request->application_deadline,
                    'is_any_where'          => $request->is_any_where,
                    'salary_will_be'        => $request->salary_will_be,
                ];
                $ad->job->update($job_data);
            }


            //If cars or vehicle
            if ($sub_category->category_type == 'cars_and_vehicles') {
                $cars_data = [
                    'transmission'      => $request->transmission,
                    'fuel_type'         => $request->fuel_type,
                    'engine_cc'         => $request->engine_cc,
                    'mileage'           => $request->mileage,
                    'build_year'        => $request->build_year.'-01-01',
                ];
                $ad->cars_and_vehicles->update($cars_data);
            }

            //Upload new image
            $this->uploadAdsImage($request, $ad->id);
        }

        return redirect()->back()->with('success', trans('app.ad_updated'));
    }


    public function adStatusChange(Request $request){
        $slug = $request->slug;
        $ad = Ad::whereSlug($slug)->first();
        if ($ad){
            $value = $request->value;

            $ad->status = $value;
            $ad->save();

            if ($value ==1){
                return ['success'=>1, 'msg' => trans('app.ad_approved_msg')];
            }elseif($value ==2){
                return ['success'=>1, 'msg' => trans('app.ad_blocked_msg')];
            }
            elseif($value ==3){
                return ['success'=>1, 'msg' => trans('app.ad_archived_msg')];
            }
        }
        return ['success'=>0, 'msg' => trans('app.error_msg')];

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $slug = $request->slug;
        $ad = Ad::whereSlug($slug)->first();
        if ($ad){
            $media = Media::whereAdId($ad->id)->get();
            if ($media->count() > 0){
                foreach($media as $m){
                    $storage = Storage::disk($m->storage);
                    if ($storage->has('uploads/images/'.$m->media_name)){
                        $storage->delete('uploads/images/'.$m->media_name);
                    }
                    if ($m->type == 'image'){
                        if ($storage->has('uploads/images/thumbs/'.$m->media_name)){
                            $storage->delete('uploads/images/thumbs/'.$m->media_name);
                        }
                    }
                    $m->delete();
                }
            }
            $ad->delete();
            return ['success'=>1, 'msg' => trans('app.media_deleted_msg')];
        }
        return ['success'=>0, 'msg' => trans('app.error_msg')];
    }

    public function getSubCategoryByCategory(Request $request){
        $category_id = $request->category_id;
        $brands = Sub_Category::whereCategoryId($category_id)->select('id', 'category_name', 'category_slug')->get();
        return $brands;
    }

    public function getBrandByCategory(Request $request){
        $category_id = $request->category_id;
        $brands = Brand::whereCategoryId($category_id)->select('id', 'brand_name')->get();

        //Save into session about last category choice
        session(['last_category_choice' => $request->ad_type ]);
        return $brands;
    }

    public function getStateByCountry(Request $request){
        $country_id = $request->country_id;
        $states = State::whereCountryId($country_id)->select('id', 'state_name')->get();
        return $states;
    }

    public function getCityByState(Request $request){
        $state_id = $request->state_id;
        $cities = City::whereStateId($state_id)->select('id', 'city_name')->get();
        return $cities;
    }

    public function getParentCategoryInfo(Request $request){
        $category_id = $request->category_id;
        $sub_category = Category::find($category_id);
        $category = Category::find($sub_category->category_id);
        return $category;
    }

    public function uploadAdsImage(Request $request, $ad_id = 0){
        $user_id = 0;

        if (Auth::check()){
            $user_id = Auth::user()->id;
        }

        if ($request->hasFile('images')){
            $images = $request->file('images');
            foreach ($images as $image){
                $valid_extensions = ['jpg','jpeg','png'];
                if ( ! in_array(strtolower($image->getClientOriginalExtension()), $valid_extensions) ){
                    return redirect()->back()->withInput($request->input())->with('error', 'Only .jpg, .jpeg and .png is allowed extension') ;
                }

                $file_base_name = str_replace('.'.$image->getClientOriginalExtension(), '', $image->getClientOriginalName());
                $resized = Image::make($image)->resize(640, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->stream();
                $resized_thumb = Image::make($image)->resize(320, 213)->stream();

                $image_name = strtolower(time().str_random(5).'-'.str_slug($file_base_name)).'.' . $image->getClientOriginalExtension();

                $imageFileName = 'uploads/images/'.$image_name;
                $imageThumbName = 'uploads/images/thumbs/'.$image_name;

                try{
                    //Upload original image
                    $is_uploaded = current_disk()->put($imageFileName, $resized->__toString(), 'public');

                    if ($is_uploaded) {
                        //Save image name into db
                        $created_img_db = Media::create(['user_id' => $user_id, 'ad_id' => $ad_id, 'media_name'=>$image_name, 'type'=>'image', 'storage' => get_option('default_storage'), 'ref'=>'ad']);

                        //upload thumb image
                        current_disk()->put($imageThumbName, $resized_thumb->__toString(), 'public');
                        $img_url = media_url($created_img_db, false);
                    }
                } catch (\Exception $e){
                    return redirect()->back()->withInput($request->input())->with('error', $e->getMessage()) ;
                }
            }
        }
    }
    /**
     * @param Request $request
     * @return array
     */

    public function deleteMedia(Request $request){
        $media_id = $request->media_id;
        $media = Media::find($media_id);

        $storage = Storage::disk($media->storage);
        if ($storage->has('uploads/images/'.$media->media_name)){
            $storage->delete('uploads/images/'.$media->media_name);
        }

        if ($media->type == 'image'){
            if ($storage->has('uploads/images/thumbs/'.$media->media_name)){
                $storage->delete('uploads/images/thumbs/'.$media->media_name);
            }
        }

        $media->delete();
        return ['success'=>1, 'msg'=>trans('app.media_deleted_msg')];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function featureMediaCreatingAds(Request $request){
        $user_id = Auth::user()->id;
        $media_id = $request->media_id;

        Media::whereUserId($user_id)->whereAdId(0)->whereRef('ad')->update(['is_feature'=>'0']);
        Media::whereId($media_id)->update(['is_feature'=>'1']);

        return ['success'=>1, 'msg'=>trans('app.media_featured_msg')];
    }

    /**
     * @return mixed
     */
    public function appendMediaImage(){
        $user_id = Auth::user()->id;
        $ads_images = Media::whereUserId($user_id)->whereAdId(0)->whereRef('ad')->get();

        return view('admin.append_media', compact('ads_images'));
    }

    /**
     * Listing
     */

    public function listing(Request $request){
        $ads = Ad::active();
        $business_ads_count = Ad::active()->business();
        $personal_ads_count = Ad::active()->personal();

        $premium_ads = Ad::activePremium();

        if ($request->q){
            $ads = $ads->where(function($ads) use($request){
                $ads->where('title','like', "%{$request->q}%")->orWhere('description','like', "%{$request->q}%");
            });

            $business_ads_count = $business_ads_count->where(function($business_ads_count) use($request){
                $business_ads_count->where('title','like', "%{$request->q}%")->orWhere('description','like', "%{$request->q}%");
            });

            $personal_ads_count = $personal_ads_count->where(function($personal_ads_count) use($request){
                $personal_ads_count->where('title','like', "%{$request->q}%")->orWhere('description','like', "%{$request->q}%");
            });
        }
        if ($request->category){
            $ads = $ads->whereCategoryId($request->category);
            $business_ads_count = $business_ads_count->whereCategoryId($request->category);
            $personal_ads_count = $personal_ads_count->whereCategoryId($request->category);

            $premium_ads = $premium_ads->whereCategoryId($request->category);
        }
        if ($request->sub_category){
            $ads = $ads->whereSubCategoryId($request->sub_category);
            $business_ads_count = $business_ads_count->whereSubCategoryId($request->sub_category);
            $personal_ads_count = $personal_ads_count->whereSubCategoryId($request->sub_category);

            $premium_ads = $premium_ads->whereSubCategoryId($request->sub_category);
        }
        if ($request->brand){
            $ads = $ads->whereBrandId($request->brand);
            $business_ads_count = $business_ads_count->whereBrandId($request->brand);
            $personal_ads_count = $personal_ads_count->whereBrandId($request->brand);
        }
        if ($request->condition){
            $ads = $ads->whereAdCondition($request->condition);
            $business_ads_count = $business_ads_count->whereAdCondition($request->condition);
            $personal_ads_count = $personal_ads_count->whereAdCondition($request->condition);
        }
        if ($request->type){
            $ads = $ads->whereType($request->type);
            $business_ads_count = $business_ads_count->whereType($request->type);
            $personal_ads_count = $personal_ads_count->whereType($request->type);
        }
        if ($request->country){
            $ads = $ads->whereCountryId($request->country);
            $business_ads_count = $business_ads_count->whereCountryId($request->country);
            $personal_ads_count = $personal_ads_count->whereCountryId($request->country);
        }
        if ($request->state){
            $ads = $ads->whereStateId($request->state);
            $business_ads_count = $business_ads_count->whereStateId($request->state);
            $personal_ads_count = $personal_ads_count->whereStateId($request->state);
        }
        if ($request->city){
            $ads = $ads->whereCityId($request->city);
            $business_ads_count = $business_ads_count->whereCityId($request->city);
            $personal_ads_count = $personal_ads_count->whereCityId($request->city);
        }
        if ($request->min_price){
            $ads = $ads->where('price', '>=', $request->min_price);
            $business_ads_count = $business_ads_count->where('price', '>=', $request->min_price);
            $personal_ads_count = $personal_ads_count->where('price', '>=', $request->min_price);
        }
        if ($request->max_price){
            $ads = $ads->where('price', '<=', $request->max_price);
            $business_ads_count = $business_ads_count->where('price', '<=', $request->max_price);
            $personal_ads_count = $personal_ads_count->where('price', '<=', $request->max_price);
        }
        if ($request->adType){
            if ($request->adType == 'business') {
                $ads = $ads->business();
            }elseif ($request->adType == 'personal'){
                $ads = $ads->personal();
            }
        }
        if ($request->user_id){
            $ads = $ads->whereUserId($request->user_id);
            $business_ads_count = $business_ads_count->whereUserId($request->user_id);
            $personal_ads_count = $personal_ads_count->whereUserId($request->user_id);
        }
        if ($request->shortBy){
            switch ($request->shortBy){
                case 'price_high_to_low':
                    $ads = $ads->orderBy('price', 'desc');
                    break;
                case 'price_low_to_height':
                    $ads = $ads->orderBy('price', 'asc');
                    break;
                case 'latest':
                    $ads = $ads->orderBy('id', 'desc');
                    break;
            }
        }else{
            $ads = $ads->orderBy('id', 'desc');
        }


        $ads_per_page = get_option('ads_per_page');
        $ads = $ads->with('feature_img', 'country', 'state', 'city', 'category');
        $ads = $ads->paginate($ads_per_page);


        //Check max impressions
        $max_impressions = get_option('premium_ads_max_impressions');
        $premium_ads = $premium_ads->where('max_impression', '<', $max_impressions);
        $take_premium_ads = get_option('number_of_premium_ads_in_listing');
        if ($take_premium_ads > 0){
            $premium_order_by = get_option('order_by_premium_ads_in_listing');
            $premium_ads = $premium_ads->take($take_premium_ads);
            $last_days_premium_ads = get_option('number_of_last_days_premium_ads');

            $premium_ads = $premium_ads->where('created_at', '>=', Carbon::now()->timezone(get_option('default_timezone'))->subDays($last_days_premium_ads));

            if ($premium_order_by == 'latest'){
                $premium_ads = $premium_ads->orderBy('id', 'desc');
            }elseif ($premium_order_by == 'random'){
                $premium_ads = $premium_ads->orderByRaw('RAND()');
            }

            $premium_ads = $premium_ads->get();

        }else{
            $premium_ads = false;
        }

        $business_ads_count = $business_ads_count->count();
        $personal_ads_count = $personal_ads_count->count();

        $title = trans('app.post_an_ad');
        $categories = Category::where('category_id', 0)->get();
        $countries = Country::all();

        $selected_categories = Category::find($request->category);
        $selected_sub_categories = Category::find($request->sub_category);

        $selected_countries = Country::find($request->country);
        $selected_states = State::find($request->state);
        //dd($selected_countries->states);

        return view('listing', compact('top_categories', 'ads', 'title', 'categories', 'countries', 'selected_categories', 'selected_sub_categories', 'selected_countries', 'selected_states', 'personal_ads_count', 'business_ads_count', 'premium_ads'));
    }

    /**
     * @param null $segment_one
     * @param null $segment_two
     * @param null $segment_three
     * @param null $segment_four
     * @param null $segment_five
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * Search ads
     */

    public function search($segment_one = null, $segment_two = null, $segment_three = null, $segment_four = null, $segment_five = null){
        $top_categories = Category::whereCategoryId(0)->with('sub_categories', 'ads')->orderBy('category_name', 'asc')->get();
        $query_category = null;

        $title = null;
        $pagination_output = null;
        $pagination_params = [];

        $ads = Ad::active()->with('category','sub_category', 'city', 'media_img', 'country', 'feature_img', 'state');
        $business_ads = Ad::active()->business()->with('category','sub_category', 'city', 'media_img', 'country', 'feature_img', 'state');
        $personal_ads = Ad::active()->personal()->with('category','sub_category', 'city', 'media_img', 'country', 'feature_img', 'state');

        //Search Keyword
        $search_terms = request('q');

        //Search by keyword
        if ($search_terms){
            $ads = $ads->where('title', 'like', "%{$search_terms}%")->orWhere('description', 'like', "%{$search_terms}%");
            $business_ads = $business_ads->where('title', 'like', "%{$search_terms}%")->orWhere('description', 'like', "%{$search_terms}%");
            $personal_ads = $personal_ads->where('title', 'like', "%{$search_terms}%")->orWhere('description', 'like', "%{$search_terms}%");
        }

        $country_id = null;
        $state_id = null;
        $city_id = null;
        $city_name = null;
        $category_id = null;
        $brand_id = null;

        //get first url segment, generally it will be country code
        $country = Country::whereCountryCode($segment_one)->first();
        if ($country){
            $country_id = $country->id;
            $pagination_params[] = $country->country_code;
            $pagination_output .= "<a href='".route('search', $pagination_params)."' class='btn btn-warning'>{$country->iso_3166_3}</a>";
        }

        $segment_one = explode('-', $segment_one);
        if ( ! empty($segment_one[0])){
            switch (strtolower($segment_one[0])){
                case 'state':
                    if ( ! empty($segment_one[1]))
                        $state_id = $segment_one[1];
                    break;
                case 'city':
                    if ( ! empty($segment_one[1]))
                        $city_id = $segment_one[1];
                    break;
                case 'cat':
                    if ( ! empty($segment_one[1]))
                        $category_id = $segment_one[1];
                    break;
                case 'brand':
                    if ( ! empty($segment_one[1]))
                        $brand_id = $segment_one[1];
                    break;
            }
        }

        $segment_two = explode('-', $segment_two);
        if ( ! empty($segment_two[0])){
            switch (strtolower($segment_two[0])){
                case 'state':
                    if ( ! empty($segment_two[1]))
                        $state_id = $segment_two[1];
                    break;
                case 'city':
                    if ( ! empty($segment_two[1]))
                        $city_id = $segment_two[1];
                    break;
                case 'cat':
                    if ( ! empty($segment_two[1]))
                        $category_id = $segment_two[1];
                    break;
                case 'brand':
                    if ( ! empty($segment_two[1]))
                        $brand_id = $segment_two[1];
                    break;
            }
        }

        $segment_three = explode('-', $segment_three);
        if ( ! empty($segment_three[0])){
            switch (strtolower($segment_three[0])){
                case 'state':
                    if ( ! empty($segment_three[1]))
                        $state_id = $segment_three[1];
                    break;
                case 'city':
                    if ( ! empty($segment_three[1]))
                        $city_id = $segment_three[1];
                    break;
                case 'cat':
                    if ( ! empty($segment_three[1]))
                        $category_id = $segment_three[1];
                    break;
                case 'brand':
                    if ( ! empty($segment_three[1]))
                        $brand_id = $segment_three[1];
                    break;
            }
        }

        $segment_four = explode('-', $segment_four);
        if ( ! empty($segment_four[0])){
            switch (strtolower($segment_four[0])){
                case 'state':
                    if ( ! empty($segment_four[1]))
                        $state_id = $segment_four[1];
                    break;
                case 'city':
                    if ( ! empty($segment_four[1]))
                        $city_id = $segment_four[1];
                    break;
                case 'cat':
                    if ( ! empty($segment_four[1]))
                        $category_id = $segment_four[1];
                    break;
                case 'brand':
                    if ( ! empty($segment_four[1]))
                        $brand_id = $segment_four[1];
                    break;
            }
        }

        $segment_five = explode('-', $segment_five);
        if ( ! empty($segment_five[0])){
            switch (strtolower($segment_five[0])){
                case 'state':
                    if ( ! empty($segment_five[1]))
                        $state_id = $segment_five[1];
                    break;
                case 'city':
                    if ( ! empty($segment_five[1]))
                        $city_id = $segment_five[1];
                    break;
                case 'cat':
                    if ( ! empty($segment_five[1]))
                        $category_id = $segment_five[1];
                    break;
                case 'brand':
                    if ( ! empty($segment_five[1]))
                        $brand_id = $segment_five[1];
                    break;
            }
        }

        //dd('Country = '.$country_id.', State = '.$state_id.', City = '.$city_id. ', Cat = '.$category_id.', Brand = '.$brand_id);
        if ($country_id){
            $ads = $ads->whereCountryId($country->id);
            $business_ads = $business_ads->whereCountryId($country->id);
            $personal_ads = $personal_ads->whereCountryId($country->id);
        }
        if ($state_id){
            $ads = $ads->whereStateId($state_id);
            $query_state = State::find($state_id);
            if ($query_state){
                $pagination_params[] = 'state-'.$state_id;
                $pagination_output .= "<a href='".route('search', $pagination_params)."' class='btn btn-warning'>{$query_state->state_name}</a>";
            }
        }
        if ($city_id){
            $ads = $ads->whereCityId($city_id);
            $business_ads = $business_ads->whereCityId($city_id);
            $personal_ads = $personal_ads->whereCityId($city_id);

            $query_city = City::find($city_id);
            if ($query_city){
                $pagination_params[] = 'city-'.$state_id;
                $pagination_output .= "<a href='".route('search', $pagination_params)."' class='btn btn-warning'>{$query_city->city_name}</a>";

                $city_name = $query_city->city_name;
            }
        }
        if ($category_id){
            $query_category = Category::find($category_id);
            if ($query_category){
                if ($query_category->category_id){
                    //This is subcategory
                    $ads = $ads->whereSubCategoryId($category_id);
                    $business_ads = $business_ads->whereSubCategoryId($category_id);
                    $personal_ads = $personal_ads->whereSubCategoryId($category_id);
                }else{
                    //This is main category
                    $ads = $ads->whereCategoryId($category_id);
                    $business_ads = $business_ads->whereCategoryId($category_id);
                    $personal_ads = $personal_ads->whereCategoryId($category_id);
                }

                $pagination_params[] = 'cat-'.$category_id.'-'.$query_category->category_slug;
                $pagination_output .= "<a href='".route('search', $pagination_params)."' class='btn btn-warning'>{$query_category->category_name}</a>";

                $title .= ' '.$query_category->category_name.' '.trans('app.in');
            }
        }
        if ($brand_id){
            $ads = $ads->whereBrandId($brand_id);
            $business_ads = $business_ads->whereBrandId($brand_id);
            $personal_ads = $personal_ads->whereBrandId($brand_id);

            $brand = Brand::find($brand_id);
            $pagination_params[] = 'brand-'.$brand_id;
            $pagination_output .= "<a href='".route('search', $pagination_params)."' class='btn btn-warning'>{$brand->brand_name}</a>";
        }

        if ($city_id){
            if ($query_city){
                if ($title) {
                    $title .= ' ' . $query_city->city_name;
                }else{
                    $title .= trans('app.ads').' '.trans('app.in').' '. $query_city->city_name;
                }
            }
        }

        if ($state_id){
            if ($query_state){
                if ($title){
                    $title .= ', '.$query_state->state_name;
                }else{
                    $title .= trans('app.ads').' '.trans('app.in').' '. $query_state->state_name;
                }
            }
        }

        if ($country){
            if ($title){
                $title .= ', '.$country->country_name;
            }else{
                $title .= trans('app.ads').' '.trans('app.in').' '. $country->country_name;
            }
        }
        if ( ! $title){
            $title .= trans('app.ads');
        }

        //Determine if ad type filter active
        if (request('adType') === 'personal'){
            $ads = $ads->personal();
        }elseif (request('adType') === 'business'){
            $ads = $ads->business();
        }

        $business_ads = $business_ads->business();
        $personal_ads = $personal_ads->personal();


        //Sort by filter
        if (request('shortBy') ){
            switch (request('shortBy')){
                case 'price_high_to_low':
                    $ads = $ads->orderBy('price', 'desc');
                    break;
                case 'price_low_to_high':
                    $ads = $ads->orderBy('price', 'asc');
                    break;
                case 'latest':
                    $ads = $ads->orderBy('id', 'desc');
                    break;
            }
        }else{
            $ads = $ads->orderBy('id', 'desc');
        }

        $ads = $ads->paginate(20);
        //dd($ads);

        $business_ads_count = $business_ads->count();
        $personal_ads_count = $personal_ads->count();


        return view('search', compact('ads', 'title', 'personal_ads_count', 'business_ads_count', 'pagination_output', 'top_categories', 'category_id', 'city_id', 'city_name', 'query_category'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * Redirect map state to search route
     */
    public function mapToSearch(Request $request){
        if ( ! $request->country){
            return redirect(route('search'));
        }
        if ( $request->country && ! $request->state){
            $country = Country::whereCountryCode(strtoupper($request->country))->first();
            if ($country){
                return redirect(route('search', [$country->country_code]));
            }
        }
        if ( $request->country && $request->state) {
            $country = Country::whereCountryCode(strtoupper($request->country))->first();
            if ($country){
                $state = State::where('state_name', 'like', "%{$request->state}%")->first();
                if ($state){
                    return redirect(route('search', [$country->country_code, 'state-'.$state->id]));
                }
                return redirect(route('search', [$country->country_code]));
            }
        }
        return redirect(route('search'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * Redirect to search route
     */
    public function searchRedirect(Request $request){
        $current_country = currentCountry();

        $city = $cat = null;
        if ($request->city){
            $city = 'city-'.$request->city;
        }
        if ($request->cat){
            $cat = 'cat-'.$request->cat;
        }
        $search_url = route('search', [$current_country['country_code'], $city, $cat] );
        $search_url = $search_url.'?'.http_build_query(['q' => $request->q]);

        return redirect($search_url);
    }


    public function adsByUser($user_id = 0){
        $current_country = currentCountry();
        $user = User::find($user_id);

        if ( ! $user_id || ! $user ){
            return redirect(route('search', [$current_country['country_code']] ));
        }

        $title = trans('app.ads_by').' '.$user->name;
        $ads = Ad::active()->whereUserId($user_id)->paginate(40);

        return view('ads_by_user', compact('ads', 'title', 'user'));
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function singleAd($slug){
        $limit_regular_ads = get_option('number_of_free_ads_in_home');
        $ad = Ad::whereSlug($slug)->first();

        if (! $ad){
            return view('error_404');
        }

        if ( ! $ad->is_published()){
            if (Auth::check()){
                $user_id = Auth::user()->id;
                if ($user_id != $ad->user_id){
                    return view('error_404');
                }
            }else{
                return view('error_404');
            }
        }else{
            $ad->view = $ad->view+1;
            $ad->save();
        }

        $title = $ad->title;

        //Get Related Ads, add [->whereCountryId($ad->country_id)] for more specific results
        $related_ads = Ad::active()->whereCategoryId($ad->category_id)->where('id', '!=',$ad->id)->with('category', 'sub_category', 'feature_img','media_img', 'country', 'state', 'city')->limit($limit_regular_ads)->orderByRaw('RAND()')->get();

        return view('single_ad', compact('ad', 'title', 'related_ads'));
    }

    public function switchGridListView(Request $request){
        session(['grid_list_view' => $request->grid_list_view]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function reportAds(Request $request){
        $ad = Ad::whereSlug($request->slug)->first();
        if ($ad) {
            $data = [
                'ad_id' => $ad->id,
                'reason' => $request->reason,
                'email' => $request->email,
                'message' => $request->message,
            ];
            Report_ad::create($data);
            return ['status'=>1, 'msg'=>trans('app.ad_reported_msg')];
        }
        return ['status'=>0, 'msg'=>trans('app.error_msg')];
    }


    public function reports(){
        $reports = Report_ad::orderBy('id', 'desc')->with('ad')->paginate(20);
        $title = trans('app.ad_reports');

        return view('admin.ad_reports', compact('title', 'reports'));
    }

    public function deleteReports(Request $request){
        Report_ad::find($request->id)->delete();
        return ['success'=>1, 'msg' => trans('app.report_deleted_success')];
    }

    public function reportsByAds($slug){
        $user = Auth::user();

        if ($user->is_admin()){
            $ad = Ad::whereSlug($slug)->first();
        }else{
            $ad = Ad::whereSlug($slug)->whereUserId($user->id)->first();
        }

        if (! $ad){
            return view('admin.error.error_404');
        }

        $reports = $ad->reports()->paginate(20);

        $title = trans('app.ad_reports');
        return view('admin.reports_by_ads', compact('title', 'ad', 'reports'));

    }


    /**
     * Apply to job
     */
    public function applyJob(Request $request){
        $rules = [
            'name'              => 'required',
            'email'             => 'required',
            'phone_number'      => 'required',
            'message'           => 'required',
            'resume'            => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        $user_id = 0;
        if (Auth::check()){
            $user_id = Auth::user()->id;
        }

        $request->session()->flash('job_validation_fails', true);
        if ($validator->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($validator);
        }

        if ($request->hasFile('resume')){
            $image = $request->file('resume');
            $valid_extensions = ['pdf','doc','docx'];
            if ( ! in_array(strtolower($image->getClientOriginalExtension()), $valid_extensions) ){
                return redirect()->back()->withInput($request->input())->with('error', trans('app.resume_file_type_allowed_msg') ) ;
            }

            $file_base_name = str_replace('.'.$image->getClientOriginalExtension(), '', $image->getClientOriginalName());

            $image_name = strtolower(time().str_random(5).'-'.str_slug($file_base_name)).'.' . $image->getClientOriginalExtension();

            $imageFileName = 'uploads/resume/'.$image_name;
            try{
                //Upload original image
                $is_uploaded = current_disk()->put($imageFileName, file_get_contents($image), 'public');

                $application_data = [
                    'ad_id'                 => $request->ad_id,
                    'job_id'                => $request->job_id,
                    'user_id'               => $user_id,
                    'name'                  => $request->name,
                    'email'                 => $request->email,
                    'phone_number'          => $request->phone_number,
                    'message'               => $request->message,
                    'resume'                => $image_name,
                    'application_type'      => 'job_applied',
                ];
                JobApplication::create($application_data);

                $request->session()->forget('job_validation_fails');
                return redirect()->back()->withInput($request->input())->with('success', trans('app.job_applied_success_msg')) ;

            } catch (\Exception $e){
                return redirect()->back()->withInput($request->input())->with('error', $e->getMessage()) ;
            }
        }

        return redirect()->back()->withInput($request->input())->with('error', trans('app.error_msg')) ;
    }

    /**
     * See all applicants by your jobs
     */
    public function jobApplicants($id){
        $ad = Ad::find($id);
        $user = Auth::user();
        $user_id = $user->id;

        $title = trans('app.applicants_for').' '.$ad->title.' '.trans('app.position');
        if (! $user->is_admin()){
            if ($ad->user_id != $user_id){
                return view('admin.error.error_404');
            }
        }
        $applicants = $ad->applicants()->orderBy('id', 'desc')->paginate(50);
        return view('admin.job_applicants', compact('title','ad', 'applicants'));
    }

    public function jobApplicantView($applicant_id){
        $user = Auth::user();
        $user_id = $user->id;

        $applicant = JobApplication::find($applicant_id);
        $title = trans('app.applicant_view');

        $ad = Ad::find($applicant->ad_id);

        if (! $user->is_admin()){
            if ($ad->user_id != $user_id){
                return view('admin.error.error_404');
            }
        }

        return view('admin.job_applicant_view', compact('title','ad', 'applicant'));
    }

}
