<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Ad extends Model
{
    protected $guarded = [];
    
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function city(){
        return $this->belongsTo(City::class);
    }
    public function state(){
        return $this->belongsTo(State::class);
    }
    public function country(){
        return $this->belongsTo(Country::class);
    }
    
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function sub_category(){
        return $this->belongsTo(Sub_Category::class);
    }

    public function brand(){
        return $this->belongsTo(Brand::class);
    }

    public function scopeActivePremium($query){
        return $query->whereStatus('1')->wherePricePlan('premium');
    }

    public function scopeActiveRegular($query){
        return $query->whereStatus('1')->wherePricePlan('regular');
    }
    public function scopeActiveUrgent($query){
        return $query->whereStatus('1')->whereMarkAdUrgent('1');
    }
    public function scopeActive($query){
        return $query->whereStatus('1');
    }
    public function scopeBusiness($query){
        return $query->whereType('business');
    }
    public function scopePersonal($query){
        return $query->whereType('personal');
    }
    public function feature_img(){
        $feature_img = $this->hasOne(Media::class)->whereIsFeature('1');
        if (! $feature_img){
            $feature_img = $this->hasOne(Media::class)->first();
        }
        return $this->hasOne(Media::class);
    }
    public function media_img(){
        return $this->hasMany(Media::class)->whereType('image');
    }

    /**
     * @return bool
     */
    
    public function is_published(){
        if ($this->status == 1)
            return true;
        return false;
    }
    
    public function full_address(){
        $location = '';

        if($this->address != '')
            $location .= $this->address.", ";
        if($this->city != '')
            $location .= '<br />'.$this->city->city_name;
        if($this->state != '')
            $location .= ' '.$this->state->state_name;
        if($this->country != '')
            $location .= '<br />'.$this->country->country_name;
        return safe_output($location);
    }

    public function posting_datetime(){
        $created_date_time = $this->created_at->timezone(get_option('default_timezone'))->format(get_option('date_format_custom').' '.get_option('time_format_custom'));
        return $created_date_time;
    }

    public function posted_date(){
        $created_date_time = $this->created_at->timezone(get_option('default_timezone'))->format(get_option('date_format_custom') );
        return $created_date_time;
    }

    public function status_context(){
        $status = $this->status;
        $html = '';
        switch ($status){
            case 0:
                $html = '<span class="text-muted">'.trans('app.pending').'</span>';
                break;
            case 1:
                $html = '<span class="text-success">'.trans('app.published').'</span>';
                break;
            case 2:
                $html = '<span class="text-warning">'.trans('app.blocked').'</span>';
                break;
        }
        return $html;
    }

    public function is_my_favorite(){
        if (! Auth::check())
            return false;
        $user = Auth::user();

        $favorite = Favorite::whereUserId($user->id)->whereAdId($this->id)->first();
        if ($favorite){
            return true;
        }else{
            return false;
        }
    }

    public function reports(){
        return $this->hasMany(Report_ad::class);
    }

    public function increase_impression(){
        $this->max_impression = $this->max_impression +1;
        $this->save();
    }

    public function job(){
        return $this->hasOne(Job::class);
    }

    public function cars_and_vehicles(){
        return $this->hasOne(CarsVehicle::class);
    }

    public function applicants(){
        return $this->hasMany(JobApplication::class);
    }

    public function bids(){
        return $this->hasMany(Bid::class)->orderBy('id', 'desc');
    }

    public function bid_deadline(){
        if ($this->expired_at){
            $dt = Carbon::createFromTimestamp(strtotime($this->expired_at));
            $deadline = $dt->timezone(get_option('default_timezone'))->format(get_option('date_format_custom'));
            return $deadline;
        }
        return false;
    }
    public function bid_deadline_left(){
        if ($this->expired_at){
            $dt = Carbon::createFromTimestamp(strtotime($this->expired_at));
            $deadline = $dt->diffForHumans();
            return $deadline;
        }
        return false;
    }

    public function current_bid(){
        $last_bid = $this->price;

        $get_last_bid = Bid::whereAdId($this->id)->max('bid_amount');
        if ($get_last_bid && $get_last_bid > $last_bid){
            $last_bid = $get_last_bid;
        }
        return $last_bid;
    }

    public function is_bid_active(){
        $status = true;
        if ($this->category_type == 'auction'){
            $is_accepted_bid = Bid::whereAdId($this->id)->whereIsAccepted(1)->first();
            if ($is_accepted_bid){
                $status = false;
            }

            $expired_date = Carbon::createFromTimestamp(strtotime($this->expired_at));
            if ($expired_date->isPast()){
                $status = false;
            }
        }
        return $status;
    }

    public function is_bid_accepted(){
        $status = false;
        if ($this->category_type == 'auction'){
            $is_accepted_bid = Bid::whereAdId($this->id)->whereIsAccepted(1)->first();
            if ($is_accepted_bid){
                $status = true;
            }
        }
        return $status;
    }

    public function premium_icon(){
        if($this->price_plan == 'premium'){
            $html = '<img src="'.asset('assets/img/premium-icon.png').'" alt="" />';
            return $html;
        }
    }

}
