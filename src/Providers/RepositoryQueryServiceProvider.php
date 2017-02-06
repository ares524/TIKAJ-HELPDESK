<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

		\App\Models\QuickcaseMediation::creating(function($quickcaseMediation)
        {
            $quickcaseMediation->signed_at = date_create();                              
        });
		
		\App\Models\QuickcaseInjured::creating(function($quickcaseInjured)
        {
            $quickcaseInjured->signed_at = date_create();                              
        });
    }
	
	
}
