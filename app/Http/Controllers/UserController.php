<?php

namespace App\Http\Controllers;

use App\Country;
use App\JobApplication;
use App\State;
use App\User;
use App\Category;
use App\FlagJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{

    public function showLoginForm()
    {
        $title = 'Handiman - Login';
        return view('auth.login');
    }


    public function login(Request $request)
    {

        if (Auth::attempt(['email'=>$request->email, 
                           'password'=>$request->password])) {
                return redirect(route('account')); 
        }
        return redirect()->back()->with('error','Incorrect Login Credentials');
    }

     public function logout()
    {
        if (auth()->logout() ) {
            //$loggedout = 'Logged Out!';
            return redirect(route('login'));
        }

        //Session::flash('flash_msg', 'Can not logout');
        return redirect()->back()->with('error', 'Unable to logout');
    }

    public function index(){
        $title = 'Clients';
        $current_user = Auth::user();
        $users = User::where('id', '!=', $current_user->id)->orderBy('name', 'asc')->paginate(20);
        return view('admin.users', compact('title', 'users'));
    }

    public static function getUsersCount()
    {
        return User::where('id', '!=', auth()->user()->id)->get();
    }


    public function show($id){
        if ($id){
            $title = 'Client Profile';
            $user = User::find($id);

            return view('admin.profile', compact('title', 'user'));
        }
    }

    /**
     * @param $id
     * @param null $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function statusChange($id, $status = null){
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }

        $user = User::find($id);
        if ($user && $status){
            if ($status == 'approve'){
                $user->active_status = 1;
                $user->save();

            }elseif($status == 'block'){
                $user->active_status = 2;
                $user->save();
            }
        }
        return back()->with('success', trans('app.status_updated'));
    }


    public function register(){
        $title = "Quick Signup";
        return view('register', compact('title'));
    }

    public function registerPost(Request $request){
        //dd($request);
        $rules = [
            'name' => ['required', 'string', 'max:190'],
            'phone'=> ['required', 'digits:11'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];

        $this->validate($request, $rules);

        //$user_type = $this->setUserAccountType($data['user_type']);
        $data = $request->input();
        $accountID = $this->generateAccountId();
        $user = $this->saveUser($data, $user_type, $accountID);
        $this->notifyViaMail($user);
        auth()->guard()->login($user);
        return redirect(route('account'))->with('success', 'Registration successful. Your account ID is: '.$accountID);
    }

    /*
        Generate 5 digit account pin
     */
    
    public function notifyViaMail($user)
    {
        $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
        $beautymail->send('emails.new_signup', ['user'=>$user], function($message) use ($user)
        {
            $message
                ->from('info@handiman.com','Handiman Servicesss')
                ->to($user->email, $user->name)
                ->subject('Welcome to Handiman Services');
        });
        return true;
    }

    public function generateAccountId()
    {
        //create a 6 digit random pin
        $x = 5;
        $min = pow(10, $x);
        $max = (pow(10, $x+1)-1);
        $pin = rand($min, $max);

        return $pin;
    }

    /*
        function to add user to db
     */
    
    public function saveUser(array $data, $user_type, $accountID){
        
        return User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'],
            'account_id'    => $accountID,
            'password'      => Hash::make($data['password'])
        ]);

    }

    /*
            add user account type manually
            to avoid malicious access into the system
         */
        public function setUserAccountType($userType){

            switch ($userType) {

            case 'individual':
                return 'individual';
                break;

            case 'admin':
                return 'admin';
                break;
            
            default:
                return 'Nothing';
                break;
            } 
        }

    /*
        function to return user account
        based on user type
     */
    public function getAccount(){
       //return Auth()->user_type == 'individual' ? $this->accountIndividual() : $this->accountCooperate();
       switch (auth()->user()->user_type) {
            case 'individual':
                return $this->accountIndividual();
                break;

            case 'cooperate':
                return $this->accountCooperate();
                break;

            case 'admin':
                return $this->accountAdmin();
                break;
            
            default:
                return $this->accountIndividual();
                break;
        } 
    }

    /*
        function to display individual user account
     */
    private function accountIndividual(){
        $user = Auth()->user();
        $title = $user->name;
        $categories = Category::all();
        $LGAs = $this->getLGAs(); 
        return view('admin.request-new-service', compact('title', 'user', 'categories','LGAs'));
    }

     /*
        function to display cooperate user account
     */
    private function accountCooperate(){
        $user = Auth()->user();
    }

    /*
        function to display cooperate user account
     */
    private function accountAdmin(){
        if (Auth::user()->user_type === 'admin') {
            return redirect(route('dashboard'));
        }
    }

    public function accountInfo(){
        $user = auth()->user();
        $title = 'Account - '.$user->name;
        return view('admin.profile', compact('title', 'user'));
    }

     public function getLGAs(){
        /*
            get LGAs in Cross River using a REST API call
         */
        $cURLConn = curl_init();
        curl_setopt($cURLConn, CURLOPT_URL, 'http://locationsng-api.herokuapp.com/api/v1/states/cross_river/lgas');
        curl_setopt($cURLConn, CURLOPT_RETURNTRANSFER, true);
        $lgas = curl_exec($cURLConn);
        curl_close($cURLConn);
        return $LGAs = json_decode($lgas);
    }

    public function flaggedMessage(){
        $title = "Account Messages";
        $flagged = FlagJob::orderBy('id', 'desc')->paginate(20);
        // foreach ($flagged as $flag) {
        //     dd($flag->service->category);
        // }
        return view('admin.flagged_jobs', compact('title', 'flagged'));
    }

    public function employerProfile(){
        $title = __('app.employer_profile');
        $user = Auth::user();


        $countries = Country::all();
        $old_country = false;
        if ($user->country_id){
            $old_country = Country::find($user->country_id);
        }

        return view('admin.employer-profile', compact('title', 'user', 'countries', 'old_country'));
    }

    public function registerEmployer(){
        $title = __('app.employer_register');
        $countries = Country::all();
        $old_country = false;
        if (old('country')){
            $old_country = Country::find(old('country'));
        }
        return view('employer-register', compact('title', 'countries', 'old_country'));
    }

    public function registerEmployerPost(Request $request){
        $rules = [
            'name'      => ['required', 'string', 'max:190'],
            'company'   => 'required',
            'email'     => ['required', 'string', 'email', 'max:190', 'unique:users'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'phone'     => 'required',
            'address'   => 'required',
            'country'   => 'required',
            'state'     => 'required',
        ];
        $this->validate($request, $rules);

        $company = $request->company;
        $company_slug = unique_slug($company, 'User', 'company_slug');

        $country = Country::find($request->country);
        $state_name = null;
        if ($request->state){
            $state = State::find($request->state);
            $state_name = $state->state_name;
        }

        User::create([
            'name'          => $request->name,
            'company'       => $company,
            'company_slug'  => $company_slug,
            'email'         => $request->email,
            'user_type'     => 'employer',
            'password'      => Hash::make($request->password),

            'phone'         => $request->phone,
            'address'       => $request->address,
            'address_2'     => $request->address_2,
            'country_id'    => $request->country,
            'country_name'  => $country->country_name,
            'state_id'      => $request->state,
            'state_name'    => $state_name,
            'city'          => $request->city,
            'active_status' => 1,
        ]);

        return redirect(route('login'))->with('success', __('app.registration_successful'));
    }


    public function registerAgent(){
        $title = __('app.agent_register');
        $countries = Country::all();
        $old_country = false;
        if (old('country')){
            $old_country = Country::find(old('country'));
        }
        return view('agent-register', compact('title', 'countries', 'old_country'));
    }

    public function registerAgentPost(Request $request){
        $rules = [
            'name'      => ['required', 'string', 'max:190'],
            'company'   => 'required',
            'email'     => ['required', 'string', 'email', 'max:190', 'unique:users'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'phone'     => 'required',
            'address'   => 'required',
            'country'   => 'required',
            'state'     => 'required',
        ];
        $this->validate($request, $rules);

        $company = $request->company;
        $company_slug = unique_slug($company, 'User', 'company_slug');

        $country = Country::find($request->country);
        $state_name = null;
        if ($request->state){
            $state = State::find($request->state);
            $state_name = $state->state_name;
        }

        User::create([
            'name'          => $request->name,
            'company'       => $company,
            'company_slug'  => $company_slug,
            'email'         => $request->email,
            'user_type'     => 'agent',
            'password'      => Hash::make($request->password),

            'phone'         => $request->phone,
            'address'       => $request->address,
            'address_2'     => $request->address_2,
            'country_id'    => $request->country,
            'country_name'  => $country->country_name,
            'state_id'      => $request->state,
            'state_name'    => $state_name,
            'city'          => $request->city,
            'active_status' => 1,
        ]);

        return redirect(route('login'))->with('success', __('app.registration_successful'));
    }


    public function employerProfilePost(Request $request){
        $user = Auth::user();

        $rules = [
            'company_size'   => 'required',
            'phone'     => 'required',
            'address'   => 'required',
            'country'   => 'required',
            'state'     => 'required',
        ];

        $this->validate($request, $rules);


        $logo = null;
        if ($request->hasFile('logo')){
            $image = $request->file('logo');

            $valid_extensions = ['jpg','jpeg','png'];
            if ( ! in_array(strtolower($image->getClientOriginalExtension()), $valid_extensions) ){
                return redirect()->back()->withInput($request->input())->with('error', 'Only .jpg, .jpeg and .png is allowed extension') ;
            }
            $file_base_name = str_replace('.'.$image->getClientOriginalExtension(), '', $image->getClientOriginalName());
            $resized_thumb = Image::make($image)->resize(256, 256)->stream();

            $logo = strtolower(time().str_random(5).'-'.str_slug($file_base_name)).'.' . $image->getClientOriginalExtension();

            $logoPath = 'uploads/images/logos/'.$logo;

            try{
                Storage::disk('public')->put($logoPath, $resized_thumb->__toString());
            } catch (\Exception $e){
                return redirect()->back()->withInput($request->input())->with('error', $e->getMessage()) ;
            }
        }

        $country = Country::find($request->country);
        $state_name = null;
        if ($request->state){
            $state = State::find($request->state);
            $state_name = $state->state_name;
        }

        $data = [
            'company_size'  => $request->company_size,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'address_2'     => $request->address_2,
            'country_id'    => $request->country,
            'country_name'  => $country->country_name,
            'state_id'      => $request->state,
            'state_name'    => $state_name,
            'city'          => $request->city,
            'about_company' => $request->about_company,
            'website'       => $request->website,
        ];

        if ($logo){
            $data['logo'] = $logo;
        }

        $user->update($data);

        return back()->with('success', __('app.updated'));
    }


    public function employerApplicant(){
        $title = __('app.applicant');
        $employer_id = Auth::user()->id;
        $applications = JobApplication::whereEmployerId($employer_id)->orderBy('id', 'desc')->paginate(20);

        return view('admin.applicants', compact('title', 'applications'));
    }

    public function makeShortList($application_id){
        $applicant = JobApplication::find($application_id);
        $applicant->is_shortlisted = 1;
        $applicant->save();
        return back()->with('success', __('app.success'));
    }

    public function shortlistedApplicant(){
        $title = __('app.shortlisted');
        $employer_id = Auth::user()->id;
        $applications = JobApplication::whereEmployerId($employer_id)->whereIsShortlisted(1)->orderBy('id', 'desc')->paginate(20);

        return view('admin.applicants', compact('title', 'applications'));
    }


    public function profile(){
        $title = trans('app.profile');
        $user = Auth::user();
        return view('admin.profile', compact('title', 'user'));
    }

    public function profileEdit($id = null){
        $title = trans('app.profile_edit');
        $user = Auth::user();

        if ($id){
            $user = User::find($id);
        }

        $countries = Country::all();

        return view('admin.profile_edit', compact('title', 'user', 'countries'));
    }

    public function profileEditPost($id = null, Request $request){
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }

        $user = Auth::user();
        if ($id){
            $user = User::find($id);
        }
        //Validating
        $rules = [
            'email'    => 'required|email|unique:users,email,'.$user->id,
        ];
        $this->validate($request, $rules);

        $inputs = array_except($request->input(), ['_token', 'photo']);
        $user->update($inputs);

        return back()->with('success', trans('app.profile_edit_success_msg'));
    }


    public function changePassword()
    {
        $title = trans('app.change_password');
        return view('admin.change_password', compact('title'));
    }

    public function changePasswordPost(Request $request)
    {
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }
        $rules = [
            'old_password'  => 'required',
            'new_password'  => 'required|confirmed',
            'new_password_confirmation'  => 'required',
        ];
        $this->validate($request, $rules);

        $old_password = $request->old_password;
        $new_password = $request->new_password;
        //$new_password_confirmation = $request->new_password_confirmation;

        if(Auth::check())
        {
            $logged_user = Auth::user();

            if(Hash::check($old_password, $logged_user->password))
            {
                $logged_user->password = Hash::make($new_password);
                $logged_user->save();
                return redirect()->back()->with('success', trans('app.password_changed_msg'));
            }
            return redirect()->back()->with('error', trans('app.wrong_old_password'));
        }
    }

}
