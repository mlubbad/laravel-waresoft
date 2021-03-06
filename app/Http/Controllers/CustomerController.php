<?php

namespace App\Http\Controllers;

use App\Models\Customer;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    
    public function getCustomers()
    {
        $data["customers"] = Customer::select()->orderby("id", "desc")->get();
      
        return view("customer/home", $data);
    }
    
    /**
     * Manually Sync Customers from Shopify
     * @return
     */
    public function refresh(Request $request)
    {
        $this->syncCustomers();
        $request->session()->flash("success", "Customer list updated from shopify successfully");

        return redirect(url("customer"));
    }
    
    
    public function syncCustomers()
    {
      /*
        $last_updated_customer = Customer::orderBy("updated_at","desc")->take(1)->get()->first();

        $formatted_date =  Carbon::parse($last_updated_customer->updated_at)->format('Y-m-d\TH:i:s');

        $get_url = "https://f79e3def682b671af1591e83c38ce094:c46734f74bad05ed2a7d9a621ce9cf7b@beautyclickke.myshopify.com/admin/customers.json?updated_at_min=$formatted_date";
        
        dump($get_url);
     */ 
        
        $last_customer = Customer::orderBy("id", "desc")->get()->take(1)->first();
        if($last_customer) {
          $last_customer_id = $last_customer->customerid;
        } else {
          $last_customer_id = 449367507001;
        }
        $get_url = "https://f79e3def682b671af1591e83c38ce094:c46734f74bad05ed2a7d9a621ce9cf7b@beautyclickke.myshopify.com/admin/customers.json?since_id=$last_customer_id&limit=250"; //449367507001
       
        $contents = file_get_contents($get_url);
        
        $shopify_customers = json_decode($contents);
      
        foreach ($shopify_customers->customers as $key => $shopify_customer) {
            
            $customer = Customer::find($shopify_customer->id);
            
            if(!$customer){
              $customer = new Customer;
            }
            
            $customer->customerid = $shopify_customer->id;
            $customer->email = rtrim($shopify_customer->email);
            $customer->first_name = rtrim($shopify_customer->first_name);
            $customer->last_name = rtrim($shopify_customer->last_name);
            $customer->created_at = $shopify_customer->created_at;
            $customer->updated_at = $shopify_customer->updated_at;
            $customer->orders_count = $shopify_customer->orders_count;
            $customer->total_spent = $shopify_customer->total_spent;
            
            if (!empty($shopify_customer->default_address->phone)) {
                $customer->phone = $shopify_customer->default_address->phone;
            }
        
            $customer->admin_graphql_api_id = $shopify_customer->admin_graphql_api_id;
        
            $customer->save();
        }
        
        echo 'Done !';
    }
}
