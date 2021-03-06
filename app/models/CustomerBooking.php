<?php namespace App\Models;

use App\Models\User;
use App\Models\ServiceCenter;
use App\Models\CustomerVehicle;
use App\Models\CustomerProfile;
use Carbon\Carbon;
use App\Models\CustomerBookingStatus;
use App\Models\ServiceMaster;
 
class CustomerBooking extends \Eloquent {
 
    protected $table = 'customer_bookings';

    public static function boot()
    {
        parent::boot();

        static::created(function($booking)
        {
            $booking->customerbookingstatus()->attach(1, array('owner' => 's', 'user_id' => "$booking->user_id"));
           // $post->created_by = Auth::user()->id;
           // $post->updated_by = Auth::user()->id;
        });
        static::updated(function($booking)
        {
            //$booking->customerbookingstatus()->attach(1, array('owner' => 's', 'user_id' => "$booking->user_id"));
           // $post->created_by = Auth::user()->id;
           // $post->updated_by = Auth::user()->id;
        });
    }
    
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
 
 	public function servicecenter()
    {
        return $this->belongsTo('App\Models\ServiceCenter', 'service_center_id', 'id');
    }

    public function customervehicle()
    {
        return $this->belongsTo('App\Models\CustomerVehicle', 'vehicle_id', 'id');
    }

    public function customerprofile()
    {
        return $this->belongsTo('App\Models\CustomerProfile', 'customer_profile_id', 'id');
    }

    public function setServiceDateAttribute($value)
    {
        $this->attributes['servicedate'] = Carbon::createFromFormat('d-m-Y H:i:s', $value)->toDateTimeString();
    }

    public function getServiceDateAttribute($value) {
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('d-m-Y H:i:s');
    }

    //This one resolve the writing to another Table with the correct MySQL fromat.
    public function raw_servicedate() {
        return Carbon::createFromFormat('d-m-Y H:i:s', $this->servicedate)->format('Y-m-d H:i:s');
        //return Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('d-m-Y H:i:s')
    }

    public function customerbookingstatus()
    {
        return $this->belongsToMany('App\Models\CustomerBookingStatus', 
            'booking_status', 'customer_booking_id', 'customer_booking_status_id')
                    ->withPivot('owner')->withPivot('user_id')->withTimestamps();
    }

    public function servicemaster()
    {
        return $this->hasOne('App\Models\ServiceMaster', 'booking_id', 'id');
    }
}