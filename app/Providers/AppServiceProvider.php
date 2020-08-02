<?php

namespace App\Providers;

use App\Country;
use App\Option;
use App\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(){

	Schema::defaultStringLength(191);
        try {
            DB::connection()->getPdo();

            $options = Option::all()->pluck('option_value', 'option_key')->toArray();

            $allOptions = [];
            $allOptions['options'] = $options;
            config($allOptions);

            /**
             * Set dynamic configuration for third party services
             */
            $amazonS3Config = [
                'filesystems.disks.s3' =>
                    [
                        'driver' => 's3',
                        'key' => get_option('amazon_key'),
                        'secret' => get_option('amazon_secret'),
                        'region' => get_option('amazon_region'),
                        'bucket' => get_option('bucket'),
                    ]
            ];
            $facebookConfig = [
                'services.facebook' =>
                    [
                        'client_id' => get_option('fb_app_id'),
                        'client_secret' => get_option('fb_app_secret'),
                        'redirect' => url('login/facebook-callback'),
                    ]
            ];
            $googleConfig = [
                'services.google' =>
                    [
                        'client_id' => get_option('google_client_id'),
                        'client_secret' => get_option('google_client_secret'),
                        'redirect' => url('login/google-callback'),
                    ]
            ];
            $twitterConfig = [
                'services.twitter' =>
                    [
                        'client_id' => get_option('twitter_consumer_key'),
                        'client_secret' => get_option('twitter_consumer_secret'),
                        'redirect' => url('login/twitter-callback'),
                    ]
            ];
            config($amazonS3Config);
            config($facebookConfig);
            config($googleConfig);
            config($twitterConfig);

            view()->composer('*', function ($view) {
                //$header_menu_pages = Post::whereStatus('1')->where('show_in_header_menu', 1)->get();
                //$show_in_footer_menu = Post::whereStatus('1')->where('show_in_footer_menu', 1)->get();

                $enable_monetize = get_option('enable_monetize');
                $loggedUser = null;
                if (Auth::check()) {
                    $loggedUser = Auth::user();
                }

                /**
                 * Set current country
                 */
                //$current_country = currentCountry();
                $current_country = null;

                if (session('country')) {
                    $current_country = session('country');
                } else {
                    $country = Country::whereCountryCode(oIsoCode())->first();
                    if ($country) {
                        session(['country' => $country->toArray()]);
                        $current_country = $country->toArray();
                    } else {
                        $country = Country::whereCountryCode('US')->first();
                        $current_country = $country->toArray();
                    }
                }

                $current_lang = current_language();

                $view->with(['lUser' => $loggedUser, 'enable_monetize' => $enable_monetize,  'current_country' => $current_country, 'current_lang' => $current_lang]);
            });

        }catch (\Exception $e){
            //echo $e->getMessage();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
