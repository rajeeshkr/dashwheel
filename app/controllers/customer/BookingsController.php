<?php namespace App\Controllers\Customer;

use Notification;
use App\Models\User;
use App\Models\Manufacturer;
use App\Models\Model;
use App\Models\CustomerBooking;
use App\Models\CustomerVehicle;
use App\Models\CustomerProfile;

use App\Services\Validators\CustomerBookingValidator;
use App\Services\Rules\CustomerBookingRule;
use Input, Redirect, Sentry, Str, Session, Event;

class BookingsController extends \BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

    protected $user;
    protected $customerbooking;
    //protected $user;

    /**
    * Inject the models.
    * @param Post $post
    * @param User $user
    */
    public function __construct(User $user, CustomerBooking $customer_booking)
    {
           // parent::__construct();

            $this->user = $user;
            $this->customer_booking = $customer_booking;
            //$this->user = $user;
    }

    public function index()
    {
           $bookings = CustomerBooking::where('user_id', Sentry::getUser()->id)->get();
           $buttons = []; 
           $status_msg = [];


           foreach($bookings as $booking) {

                $customer_booking = CustomerBooking::find($booking->id);

                $booking_status = $customer_booking->customerbookingstatus()
                                     ->where('booking_status.customer_booking_id', '=', $booking->id)->get()->last();

                

                $status_msg["$booking->id"] = $booking_status;                  

                $testrule = new CustomerBookingRule();
            
                $ret_val = ($testrule->findEligibility($customer_booking->servicedate, $booking_status->title));

                if (is_null($ret_val)) {
                    $buttons["$booking->id"] = [];
                }
                else {
                     $buttons["$booking->id"] = $ret_val;
                }
           }
           return \View::make('customer.bookings.index', compact('bookings', 'buttons', 'status_msg'));
    }


    public function getStatus($booking_id)
    {
		$customer_booking = CustomerBooking::find($booking_id);

		$booking_status = $customer_booking->customerbookingstatus()
				->where('booking_status.owner', '=', 's')->orWhere('booking_status.owner', '=', 'c')->get()->last();

				var_dump($booking_status);
		//$rb = new RuleBuilder;
		$testrule = new CustomerBookingRule();
		
		var_dump($testrule->findEligibility($customer_booking->servicedate, $booking_status->title));
		//$testrule->run(); // "Yay!"

		//var_dump($booking_status->title);
        //return \View::make('customer.bookings.status', compact('booking_status'));
    }

	public function getCenter($vehicle_id, $model_id)
	{
		//var_dump($vehicle_id, $model_id);
		//$user = CustomerBooking::where('user_id', Sentry::getUser()->id);
        //$service_centers = Manufacturer::with(array('dealers', 'dealers.service_centers'))->find($man_id->manufacturer_id)->get(array('id', 'title'));
        //$service_centers = Manufacturer::with('service_centers')->find($man_id->manufacturer_id)->get(array('id', 'title'));
		//$service_centers = Manufacturer::where($man_id);
		//var_dump($service_centers);
		//var_dump($man_id);
        //return \View::make('customer.bookings.index')->with('bookings', $user->get());

		//Session::put('service_center_id', $service_center_id);
		Session::put('vehicle_id', $vehicle_id);

		//var_dump($customer_profiles); 

		$man_id =  Model::where('id', $model_id)->first();
		$service_centers = Manufacturer::find($man_id->manufacturer_id)->servicecenters;
		return \View::make('customer.bookings.center', compact('service_centers', 'model_id'));

	}

	public function getBook($service_center_id)
    {
        Session::put('service_center_id', $service_center_id);
		$customer_profiles = CustomerProfile::where('user_id', Sentry::getUser()->id)->get(array('id', 'title'));
        return \View::make('customer.bookings.create', compact('customer_profiles'));
    }

    public function postBook($sc_id) {
	    $user_booking = new CustomerBooking;
        $user_booking->user_id = Sentry::getUser()->id;
        $user_booking->vehicle_id = Session::get('vehicle_id');
        $user_booking->customer_profile_id = Input::get('customer_profile');
        $user_booking->service_center_id = Session::get('service_center_id');
        $user_booking->total_km = Input::get('total_km');
        $user_booking->service_type = Input::get('service_type');
        $user_booking->service_dispatch = Input::get('service_dispatch');
        $user_booking->servicedate = Input::get('servicedate') . " 09:00:00";
        $user_booking->save();
        Session::forget('vehicle_id');
        Session::forget('service_center_id');

        return Redirect::route('customer.vehicles.index');
    }    

     public function edit($id)
        {
                $booking = CustomerBooking::find($id);
                $customer_profiles = CustomerProfile::where('user_id', Sentry::getUser()->id)->get(array('id', 'title'));
                //$model_id = CustomerBooking::find($id)->model_id;
               // foreach (Model::select('id', 'title')->where('manufacturer_id', $man_id)->orderBy('title','asc')->get() as $mod)
               // {
                  //      $model[$mod->id] = $mod->title;
               // }
               
                return \View::make('customer.bookings.edit', compact('booking', 'customer_profiles'));
                //return \View::make('customer.vehicles.edit')->with('vehicle', CustomerBooking::find($id));
                
        }
 
        public function update($id)
        {
               
                $validation = new CustomerBookingValidator;
                if ($validation->passes()) {
                   $customer_booking = CustomerBooking::find($id);
                   $customer_booking->user_id = Sentry::getUser()->id;
                   $customer_booking->customer_profile_id = Input::get('customer_profile');
                   $customer_booking->total_km = Input::get('total_km');
                   $customer_booking->service_type = Input::get('service_type');
                   $customer_booking->service_dispatch = Input::get('service_dispatch');
                   $customer_booking->servicedate = Input::get('servicedate') . " 09:00:00";
                   $customer_booking->save();

                   $customer_booking->customerbookingstatus()->attach(2, array('owner' => 'c', 'user_id' => "$customer_booking->user_id"));
                   
                   Notification::success('The page was saved.');
                 
                   return Redirect::route('customer.bookings.index'); 
                   
                }
                else {
                    return Redirect::back()->withInput()->withErrors($validation->errors);
                }
                
        }       

    public function getConfirm($id)
    {
        $customer_booking = CustomerBooking::find($id);
        $customer_booking->customerbookingstatus()->attach(5, array('owner' => 'c', 'user_id' => Sentry::getUser()->id));
        Event::fire('masterservice.create', array($customer_booking));
        return Redirect::route('customer.bookings.index');
    }    

    public function destroy($id)
    {
        $customer_booking = CustomerBooking::find($id);
        $customer_booking->customerbookingstatus()->attach(3, array('owner' => 'c', 'user_id' => "$customer_booking->user_id"));
        //$customer_booking->delete();

        return Redirect::route('customer.bookings.index');
    }
}