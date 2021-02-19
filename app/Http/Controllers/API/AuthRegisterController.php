<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ShopiStores;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class AuthRegisterController extends Controller
{
    /****
     *@params
     *username,email,phone,storename,industry,store_location,lat,lng,agreeterms,wheretosell,salesestimation
     *reg - shopistore,store,user,custshopi,
     */
    public function register(Request $request){
            try{
                $this->validate($request,[
                'username'=>'required|string',
                'email'=>'required|email|unique:users',
                'phone'=>'required',
                'storename'=>'required|unique:shopi_stores,shopistore_name',
                'storelocation'=>'required',
                'lat'=>'required',
                'lng'=>'required',
                'agreeterms'=>'required',
                'industry'=>'required',
                'wheretosell'=>'required',
                'salesestimation'=>'required',
                'password'=>'required|min:6',
            ]);
            $this->insertShopiStore(array(
                'storename'=>$request->storename,
                'slocation'=>$request->storelocation,
                'lat'=>$request->lat,
                'lng'=>$request->lng,
            ));
            $company = $this->getShopistore($request->storename);
            $this->createUser(array(
               'username'=>$request->username,
               'email'=>$request->email,
               'phone'=>$request->phone,
               'password'=>$request->password,
               'company'=>$company->id,
               'ip'=>$request->ip,
               'regfrom'=>$request->regfrom,
            ));

            $theuser = User::where('email',$request->email)->first();
            $this->createCustShopi(array(
               'user'=>$theuser->id,
               'company'=>$company->id,
               'industry'=>$request->industry,
               'wheretosell'=>$request->wheretosell,
               'agreeterms'=>$request->agreeterms,
               'salesestimation'=>$request->salesestimation,
            ));

            $this->createUserGroup($theuser->id,$company->id);
            $this->createBrand($request->username,$company->id);
            $this->createCategories($request->industry,$company->id);
            $this->createUserWebsite($request->storename,$company->id);

            if($request->code != (null || '' )){
               $this->createUserAffliate($request->code,$request->username,$company->id);
            }

            return response(['message'=>'user created successully'],200);
           }
           catch(Exception $e){
               return response(['message'=>$e->getMessage()],203);
           }
    }


    protected function insertShopiStore($storedata){
        $shopi =  DB::table('shopi_stores')->insert([
            'shopistore_name'=>$storedata['storename'],
            'date_created'=>strtotime('now'),
        ]);
        $this->createStore($storedata);
        if(!$shopi){
            throw new Exception('Error Creating Shopistore');
        }
        return true;
    }

    /**
     * GET shopistores DATA
     */
    public function getShopistore($storename){
        return ShopiStores::where('shopistore_name',$storename)->first();
    }


    //create store for ther user
    protected function createStore(array $data){
      $company = $this->getShopistore($data['storename']);
      return DB::table('stores')->insert([
           'name'=>$data['storename'],
           'company_id'=>$company->id,
           'active'=>1,
           'type'=>1,
           'location_name'=>$data['slocation'],
           'location_lat'=>$data['lat'],
           'location_lng'=>$data['lng'],
       ]);
    }

    protected function createUser(array $userdata){
        $newuser = DB::table('users')->insert([
                'username'=>$userdata['username'],
                'firstname'=>$userdata['username'],
                'lastname'=>$userdata['username'],
                'password'=>Hash::make($userdata['password']),
                'email'=>$userdata['email'],
                'phone'=>$userdata['phone'],
                'type'=>'super',
                'gender'=>1,
                'location'=>$userdata['company'],
                'reg_from'=>$userdata['regfrom'],
                'background'=>'default.jpg',
                'company_id'=>$userdata['company'],
                'ip'=>$userdata['ip'],

            ]);
            if(!$newuser){
                throw new Exception('Could not register the user');
            }
            return true;
    }

    protected function createCustShopi(array $custdata){
        return DB::table('custshopi')->insert([
           'user_id'=>$custdata['user'],
           'company_id'=>$custdata['company'],
           'industry'=>$custdata['industry'],
           'wheretosell'=>$custdata['wheretosell'],
           'salesestimation'=>$custdata['salesestimation'],
           'terms_agreement'=>$custdata['agreeterms'],
           'active'=>1,
        ]);
    }

    protected function createUserGroup($userid,$companyid){
         DB::table('groups')->insert([
            'group_name'=>'Administrator',
            'company_id'=>$companyid,
        ]);
        $group = DB::table('groups')->where('company_id','=',$companyid)->first();

        return DB::table('user_group')->insert([
            'user_id'=>$userid,
            'company_id'=>$companyid,
            'group_id'=>$group->id,
        ]);
    }

    protected function createBrand($username,$company){
       return DB::table('brands')->insert([
          'name'=>$username,
          'active'=>1,
          'company_id'=>$company,
      ]);
    }

    protected function createCategories($industry,$company){
        $fashions = ['Bottom wears', 'Top Wears', 'Dresses', 'Jump Suits', 'Foot Wears'];
        $electronics = ['Phones', 'Tablets', 'Computers', 'Phone Accessories'];
        $hardware = ['Finishings', 'Timber', 'Masonry', 'Plumbing', 'Electricals'];
        $cosmetics = ['Facials', 'Hand Care', 'Body Care', 'Manicure', 'Pedicure'];
        $foods = ['grains','fruits','vegetables','sweets','dairy'];
        $beddings = ['beddings','kitchenware'];

        switch ($industry) {
            case '09001':
                for($i =0;$i < count($fashions);$i++){
                    $this->insertCategory($company,$fashions[$i]);
                }
                break;
            case '09002':
                for($i =0;$i < count($electronics);$i++){
                    $this->insertCategory($company,$electronics[$i]);
                }
                break;
            case '09003':
                for($i =0;$i < count($hardware);$i++){
                    $this->insertCategory($company,$hardware[$i]);
                }
                break;
            case '09004':
                for($i =0;$i < count($cosmetics);$i++){
                    $this->insertCategory($company,$cosmetics[$i]);
                }
                break;
            case '09005':
                for($i =0;$i < count($foods);$i++){
                    $this->insertCategory($company,$foods[$i]);
                }
                break;
            case '09006':
                for($i =0;$i < count($beddings);$i++){
                    $this->insertCategory($company,$beddings[$i]);
                }
                break;
            default:
               break;

        }
    }

    protected function insertCategory($company,$category){
        return DB::table('categories')->insert([
            'name'=>$category,
            'company_id'=>$company,
            'active'=>1,
        ]);
    }


    protected function createUserWebsite($storename,$company){
        $web = DB::table('ecomm_base')->where('website_name',$storename)->first();
        $identifier = uniqid().$this->uniqidReal();
        if($web){
           $newweb = $web['website_name'].rand(1000,10000);
           return DB::table('ecomm_base')->insert([
                'website_name'=>$newweb,
                'website_email'=>$newweb.'@shopilyv.com',
                'company_id'=>$company,
                'web_identifier'=>Hash::make($identifier),
            ]);
        }
        else
        {
            return DB::table('ecomm_base')->insert([
                'website_name'=>$storename,
                'website_email'=>$storename.'@shopilyv.com',
                'company_id'=>$company,
                'web_identifier'=>Hash::make($identifier),
            ]);
        }
    }

    protected function  uniqidReal($length = 20) {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $length);
    }

    //create affliate user
    protected function createUserAffliate($code,$username,$company){
       return DB::table('affiliate_customer')->insert([
           'username'=>$username,
           'company_id'=>$company,
           'code'=>$code,
       ]);
    }

    //notifications
    protected function sendMailNotification($email,$mailsubject,$messagetobesent){

        $from = "shopilyvFrom";
        $to = $email;
        $subject = $mailsubject;
        $message = $messagetobesent;
        $headers = "From:" . $from;
        return mail($to,$subject,$message, $headers);
    }

    protected function sendMessageCode($to,$text,$from='ShopiLyv'){
        $username="ShopiLyv";
        $password="WorthBill2030$";
        $auth=base64_encode($username.':'.$password);
        $headers = ['Authorization'=>'Basic '.$auth,'Content-Type'=>' application/json', 'Accept' => 'application/json',];
        $api_url ='https://api.infobip.com/sms/2/text/single';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.infobip.com/sms/2/text/single",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{ \"from\":\"$from\", \"to\":\"$to\", \"text\":\"$text.\" }",
            CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Basic ".json_encode($auth),
            "content-type: application/json"
            ),
        ));
        curl_exec($curl);
    }

}
