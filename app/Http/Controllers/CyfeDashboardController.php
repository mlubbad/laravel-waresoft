<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Lineitems;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Mail;

class CyfeDashboardController extends Controller
{
    public $start_date;
    
    public $end_date;
    
    public $today;
    
    public $tags = ["faith","milly","doreen","walter","sharon","Lynn","mahadia","dorcas","beryl","dan"];
    
    public $online_tags = ["faith","milly","doreen","Lynn","walter","sharon","mahadia","beryl"];
    
    public $offline_tags = ["dorcas","dan"];
    
    public $cancelled_tags = ["COOD","NR","DD","DTU","RUD","PLO","IPLO","SO","CNLI"];
    
    public $cancelled_reason_tags = [
          "COOD" => "Change of order details",
          "NR"   => "No Response",
          "DD"   => "Delayed delivery",
          "DTU"  => "Delivery timelines unfeasible",
          "RUD"  => "Reject Upon delivery",
          "PLO"  => "Payment long overdue",
          "IPLO" => "In store pick up long overdue",
          "SO"   => "Stock Out",
          "CNLI" => "Client no longer interested"
         ];
    
    public $fullfillment_status = [
      null,"fulfilled","partial","shipped","unshipped"
    ];
    
    public $financial_status = [
      null, "authorized", "pending","paid","partially_paid","refunded","voided","partially_refunded","unpaid"
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->start_date = Carbon::parse("2019-01-01");
        $this->end_date = Carbon::parse("2019-01-31");
        $this->today = Carbon::now();
    }
    
    public function test()
    {
        $data = "Sales Staff, Revenue(Kes), Sales
              Barbara,100132,213
              Milly,120350,102
              Doreen,103420,54
              Faith,105413,21
              Walter,100200,1";
      
        echo $data;
    }
    
    public function testFunnel($public_token = null, $start_date = null, $end_date = null)
    {
        
        $private_token = "5dfac39f71ad4d35a153ba4fc12d943a0e178e6a"; //env("cyfe_token");
        
        if($public_token != $private_token){
          print_r("Invalid access details");
          exit();
        }
        
        $data = "Type,Count
                 Availability Requests,15654
                 Book Now,4064
                 Book Obligation,1987
                 Booking Confirmation,976";
        
        echo $data;
        
    }
    
    /**
     * /fullfillment
     *
     * @return
     */
    public function fullfillmentRate($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
        
        $all_orders = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->count();
      
        $fullfilled_orders = Order::where("fulfillment_status", "fulfilled")
                                  ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                  ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                  ->count();
        
        $paid_fullfilled_orders = Order::where("fulfillment_status", "fulfilled")
                                  ->where("financial_status", "paid")
                                  ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                  ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                  ->count();
        
        $cood_orders = Order::where("tags", "like", "%COOD%")
                                  ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                  ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                  ->count();
        
        $cancelled_orders = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                  ->where("cancelled_at", "!=", null)
                                  ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                  ->count();
        
        $cood_non_cancelled = Order::where("tags", "like", "%COOD%")
                                  ->where("cancelled_at", null)
                                  ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                  ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                  ->count();
        
        $wholesale_unpaid_orders = Order::where("tags", "like", "%wholesale%")
                                  ->where("financial_status", "!=", "paid")
                                  ->where("cancelled_at", null)
                                  ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                  ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                  ->count();
        
        foreach ($this->offline_tags as $key => $offline_tag) {
            $offline_orders[$offline_tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$offline_tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->count();
        }
        
        $offline_order_summation = array_sum($offline_orders);
        
        $aggregate_all_orders = (($all_orders - ($offline_order_summation + $wholesale_unpaid_orders)));
        
        $fullfillment_rate = round((($paid_fullfilled_orders / ($aggregate_all_orders - ($cood_non_cancelled + $cood_orders)))*100), 2);
        
        $data = "All Orders, Cancelled, CooD, CooD Not Cancelled, Offline Sales, Unpaid Wholesale Sales, Paid Fullfilled Orders, Fullfillment Rate (%)
               $all_orders, $cancelled_orders, $cood_orders, $cood_non_cancelled, $offline_order_summation,$wholesale_unpaid_orders, $paid_fullfilled_orders, $fullfillment_rate
               ";
        
        echo $data;
    }
    
    /**
     * 
     */
    public function wholesaleAgentSales($start_date, $end_date) {
      
      $this->start_date = Carbon::parse($start_date); 
        
      $this->end_date = Carbon::parse($end_date);
      
      $sales_count = Order::where("tags", "like", "%wholesale%")
                        ->where("cancelled_at", null)
                        ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                        ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                        ->count();
      
      $paid_orders = Order::where("tags", "like", "%wholesale%")
                        ->where("financial_status", "paid")
                        ->where("cancelled_at", null)
                        ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                        ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                        ->count();
      
      $paid_sales_amount = Order::where("tags", "like", "%wholesale%")
                        ->where("cancelled_at", null)
                        ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                        ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                        ->sum("total_price");
      
      $paid_sales_amount_tax = Order::where("tags", "like", "%wholesale%")
                        ->where("cancelled_at", null)
                        ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                        ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                        ->sum("total_tax");
      
      $ex_vat_total = ($paid_sales_amount - $paid_sales_amount_tax);
      
      $data = "Number of Orders, Paid Orders, Total Sales Inc VAT, Total Sales Ex VAT
                 $sales_count, $paid_orders, $paid_sales_amount ,$ex_vat_total
               ";
        
      echo $data;
    }
    
    /**
     * paidsalesamount
     *
     */
    public function paidSalesAmount($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $paid_sales_count = Order::where("financial_status", "paid")
                                 ->where("cancelled_at", null)
                                 ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                 ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                 ->count();
      
        $paid_sales_amount = Order::where("financial_status", "paid")
                                  ->where("cancelled_at", null)
                                  ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                  ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                  ->sum("total_price");
        
        $paid_sales_amount_tax = Order::where("financial_status", "paid")
                                  ->where("cancelled_at", null)
                                  ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                  ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                  ->sum("total_tax");
      
        $ex_vat_total = ($paid_sales_amount - $paid_sales_amount_tax);
      
        $data = "Number of Orders, Total Sales Inc VAT, Total Sales Ex VAT
                 $paid_sales_count, $paid_sales_amount ,$ex_vat_total
               ";
        
        echo $data;
    }
    
    /**
     *
     * averagebasket
     */
    
    public function averageBasketExVat($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $orders_total = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_price");
        
        $total_tax = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                ->where("cancelled_at", null)
                                ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                ->sum("total_tax");
      
        $orders_count = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                                ->where("cancelled_at", null)
                                ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                                ->count();
      
        $basket_size = round(($orders_total / $orders_count), 2);
        $ex_vat_total = ($orders_total - $total_tax);
        
        $data = "Total Sales ex VAT, Number of Orders, Average Basket size
          $ex_vat_total, $orders_count, $basket_size   
           ";
      
        echo $data;
    }
    
    /**
     *
     * deliveredorders
     *
     */
    
    public function deliveredOrders($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $order_count = Order::where("fulfillment_status", "fulfilled")
                           ->where("cancelled_at", null)
                           ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->count();
      
        $order_total = Order::where("fulfillment_status", "fulfilled")
                            ->where("cancelled_at", null)
                            ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                            ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                            ->sum("total_price");
      
        $order_total_tax = Order::where("fulfillment_status", "fulfilled")
                            ->where("cancelled_at", null)
                            ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                            ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                            ->sum("total_tax");
        
        $order_total = round($order_total, 2);
        
        $order_total_tax = round($order_total_tax, 2);
        
        $total_ex_vat = round(($order_total - $order_total_tax), 2);
        
        $data = "Number of orders, Total Inc VAT, Order Ex Vat Total
               $order_count, $order_total, $total_ex_vat
             ";
      
        echo $data;
    }
    
    /**
     * revenuedeliveredorders
     */
    public function revenueDeliveredOrdersExVat($start_date, $end_date)
    {
        
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);         
      
        $order_count = Order::where("fulfillment_status", "fulfilled")
                           ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->count();
      
        $order_total = Order::where("fulfillment_status", "fulfilled")
                           ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->sum("total_price");
        
        $data = "Order Count, Order Total
              $order_count, $order_total
             ";
      
        echo $data;
    }
    
    /**
     * offlinesales
     *
     */
    public function offlineSales($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        foreach ($this->offline_tags as $key => $tag) {
            $order_count[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->count();

            $order_total[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_price");
            
            $order_total_tax[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_tax");
        }
        
        $data = "Number of orders, Total Inc Vat, Total ex Vat"."<br>";
        
        $order_total_summation = (array_sum($order_total));
        $order_total_tax_summation = (array_sum($order_total_tax));
        $order_count_summation = (array_sum($order_count));
        
        $ex_vat_total = ($order_total_summation - $order_total_tax_summation);
        
        $data .= "$order_count_summation, $order_total_summation, $ex_vat_total"."<br>";
        
        echo $data;
    }
    
    /**
     * onlinesales
     */
    public function onlineSales($start_date, $end_date)
    {
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);      
      
        foreach ($this->online_tags as $key => $tag) {
            $order_count[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->count();

            $order_total[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_price");
            
            $order_total_tax[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_tax");
        }
        
        $data = "Number of orders, Total, Total ex Vat"."<br>";
        
        $order_total_summation = (array_sum($order_total));
        $order_total_tax_summation = (array_sum($order_total_tax));
        $order_count_summation = (array_sum($order_count));
        
        $ex_vat_total = ($order_total_summation - $order_total_tax_summation);
        
        $data .= "$order_count_summation, $order_total_summation, $ex_vat_total"."<br>";
        
        echo $data;
    }
    
    /**
     * untaggedsales
     *
     */
    
    public function untaggedSales($start_date, $end_date)
    {
        //all numbers
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
        
        $all_order_count = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                               ->where("cancelled_at", null)
                               ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                               ->count();

        $all_order_total = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                         ->where("cancelled_at", null)
                         ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                         ->sum("total_price");

        $all_order_total_tax = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                         ->where("cancelled_at", null)
                         ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                         ->sum("total_tax");
        
        foreach ($this->online_tags as $key => $tag) {
            $online_order_count[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->count();

            $online_order_total[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_price");
            
            $online_order_total_tax[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_tax");
        }
        
        foreach ($this->offline_tags as $key => $tag) {
            $offline_order_count[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->count();

            $offline_order_total[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_price");
            
            $offline_order_total_tax[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_tax");
        }
        
        $data = "Number of orders, Total, Total ex Vat"."<br>";
        
        $offline_order_total_summation = (array_sum($offline_order_total));
        $offline_order_total_tax_summation = (array_sum($offline_order_total_tax));
        $offline_order_count_summation = (array_sum($offline_order_count));
        
        $online_order_total_summation = (array_sum($online_order_total));
        $online_order_total_tax_summation = (array_sum($online_order_total_tax));
        $online_order_count_summation = (array_sum($online_order_count));
        
        $order_total_summation = round(($all_order_total - ($offline_order_total_summation + $online_order_total_summation)),2);
        $order_total_tax_summation = $all_order_total_tax - ($offline_order_total_tax_summation + $online_order_total_tax_summation);
        $order_count_summation = round($all_order_count - ($offline_order_count_summation + $online_order_count_summation));
        
        $ex_vat_total = round(($order_total_summation - $order_total_tax_summation),2);
        
        $data .= "$order_count_summation, $order_total_summation, $ex_vat_total"."<br>";
        
        echo $data;
    }
    
    /**
     *
     * pendingorders
     */
    
    public function pendingOrders($start_date, $end_date)
    {
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);      
      
        $order_count = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("financial_status", "pending")
                           ->where("cancelled_at", null)
                           ->count();
      
        $order_total = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("financial_status", "pending")
                           ->where("cancelled_at", null)
                           ->sum("total_price");
        
        $tax = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("financial_status", "pending")
                           ->where("cancelled_at", null)
                           ->sum("total_tax");
        
        $ex_vat_order_total = round(($order_total - $tax), 2);
        
        $as_at = $this->start_date->format("d/m/y").":".$this->end_date->format("d/m/y");
        
        $data = "Number of Orders, Total Inc VAT, Total Ex VAT
                 $order_count, $order_total, $ex_vat_order_total
             ";
        
        echo $data;
    }
    
    /**
     *
     * pendingdeliveries
     */
    
    public function pendingDeliveries($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $order_count = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("cancelled_at", null)
                           ->count();
      
        $order_total = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("cancelled_at", null)
                           ->sum("total_price");
      
        $data = "Number of Orders, Total Inc VAT
                 $order_count, $order_total";
                 
        echo $data;
    }
    
    /**
     *
     * salesperstaff
     */
    
    public function salesExVatPerStaff($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $all_order_count = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("fulfillment_status", "fulfilled")
                           ->where("financial_status", "paid")
                           ->where("cancelled_at", null)
                           ->count();
      
        $all_order_total = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                       ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                       ->where("fulfillment_status", "fulfilled")
                       ->where("financial_status", "paid")
                       ->where("cancelled_at", null)
                       ->sum("total_price");

        $all_order_total_tax = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                        ->where("fulfillment_status", "fulfilled")
                        ->where("financial_status", "paid")
                        ->where("cancelled_at", null)
                        ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                        ->sum("total_tax");
        
        $all_order_total_ex_vat = ($all_order_total - $all_order_total_tax);
        
        foreach ($this->tags as $tag) {
            $order_count[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("fulfillment_status", "fulfilled")
                           ->where("financial_status", "paid")
                           ->where("tags", "like", "%$tag%")
                           ->where("cancelled_at", null)
                           ->count();
      
            $order_total[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("fulfillment_status", "fulfilled")
                           ->where("financial_status", "paid")
                           ->where("tags", "like", "%$tag%")
                           ->where("cancelled_at", null)
                           ->sum("total_price");
        
            $order_total_tax[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                            ->where("tags", "like", "%$tag%")
                            ->where("fulfillment_status", "fulfilled")
                            ->where("financial_status", "paid")
                            ->where("cancelled_at", null)
                            ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                            ->sum("total_tax");
        }
      
        $datax = "Staff, Number of Orders, Total ex VAT"."<br>";
      
        foreach ($this->tags as $key => $tag) {
            $ex_vat_amount[$tag] = round(($order_total[$tag] - $order_total_tax[$tag]), 2);
            $data[$tag]["name"] = ucfirst($tag);
            $data[$tag]["order_count"] = $order_count[$tag];
            $data[$tag]["total_ex_vat"] = $ex_vat_amount[$tag];
        }
        
        usort($data, function ($a, $b) {
            return $a["total_ex_vat"] < $b["total_ex_vat"];
        });
        
        $combined_orders = 0;
        $combined_sales = 0;
        
        foreach ($data as $key => $data_item) {
            $combined_orders += $data_item["order_count"];
            $combined_sales += $data_item["total_ex_vat"];
            $datax .= $data_item["name"].",".$data_item["order_count"].",".$data_item["total_ex_vat"]."<br>";
        }
        
        $untagged_order_count = ($all_order_count - $combined_orders);
        $untagged_order_total = ($all_order_total_ex_vat - $combined_sales);
        
        $datax .= "Untaged, $untagged_order_count, - "."<br>";
        
        $datax .= "Total, $all_order_count, $all_order_total_ex_vat ";
        
        echo $datax;
    }
    
    
   /**
    *  cancelled sales per staff
   */
    
    public function salesCancelledExVatPerStaff($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $all_order_count = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("cancelled_at",  "<>", '')
                           ->count();
      
        $all_order_total = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                            ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                            ->where("cancelled_at",  "<>", '')
                            ->sum("total_price");

        $all_order_total_tax = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                            ->where("cancelled_at",  "<>", '')
                            ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                            ->sum("total_tax");
        
        $all_order_total_ex_vat = ($all_order_total - $all_order_total_tax);
        
        foreach ($this->tags as $tag) {
            $order_count[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("tags", "like", "%$tag%")
                           ->where("cancelled_at",  "<>", '')
                           ->count();
            
            $order_total[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("tags", "like", "%$tag%")
                           ->where("cancelled_at",  "<>", '')
                           ->sum("total_price");
            
            $order_total_tax[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("tags", "like", "%$tag%")
                           ->where("cancelled_at",  "<>", '')
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->sum("total_tax");
        }
      
        $datax = "Staff, Number of Orders, Total ex VAT"."<br>";
      
        foreach ($this->tags as $key => $tag) {
            $ex_vat_amount[$tag] = round(($order_total[$tag] - $order_total_tax[$tag]), 2);
            $data[$tag]["name"] = ucfirst($tag);
            $data[$tag]["order_count"] = $order_count[$tag];
            $data[$tag]["total_ex_vat"] = $ex_vat_amount[$tag];
        }
        
        usort($data, function ($a, $b) {
            return $a["total_ex_vat"] < $b["total_ex_vat"];
        });
        
        $combined_orders = 0;
        $combined_sales = 0;
        
        foreach ($data as $key => $data_item) {
            $combined_orders += $data_item["order_count"];
            $combined_sales += $data_item["total_ex_vat"];
            $datax .= $data_item["name"].",".$data_item["order_count"].",".$data_item["total_ex_vat"]."<br>";
        }
        
        $untagged_order_count = ($all_order_count - $combined_orders);
        $untagged_order_total = ($all_order_total_ex_vat - $combined_sales);
        
        $datax .= "Untaged, $untagged_order_count, - "."<br>";
        
        $datax .= "Total, $all_order_count, $all_order_total_ex_vat ";
        
        echo $datax;
    }
    
    
    /**
     * orderstoday
     *
     */
    
    public function pendingDeliveriesExVatPerStaff($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $all_order_count = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("cancelled_at", null)
                           ->where("financial_status", "pending")
                           ->count();
      
        $all_order_total = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                       ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                       ->where("cancelled_at", null)
                       ->where("financial_status", "pending")
                       ->sum("total_price");

        $all_order_total_tax = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                          ->where("cancelled_at", null)
                          ->where("financial_status", "pending")
                          ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                          ->sum("total_tax");
        
        $all_order_total_ex_vat = ($all_order_total - $all_order_total_tax);
        
        foreach ($this->tags as $tag) {
            $order_count[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("tags", "like", "%$tag%")
                           ->where("cancelled_at", null)
                           ->where("financial_status", "pending")
                           ->count();
      
            $order_total[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("tags", "like", "%$tag%")
                           ->where("cancelled_at", null)
                           ->where("financial_status", "pending")
                           ->sum("total_price");
        
            $order_total_tax[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                              ->where("tags", "like", "%$tag%")
                              ->where("cancelled_at", null)
                              ->where("financial_status", "pending")
                              ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                              ->sum("total_tax");
        }
        
        $datax = "Staff, Number of Orders, Total ex VAT"."<br>";
      
        foreach ($this->tags as $key => $tag) {
            $ex_vat_amount[$tag] = round(($order_total[$tag] - $order_total_tax[$tag]), 2);
            $data[$tag]["name"] = ucfirst($tag);
            $data[$tag]["order_count"] = $order_count[$tag];
            $data[$tag]["total_ex_vat"] = $ex_vat_amount[$tag];
        }
        
        usort($data, function ($a, $b) {
            return $a["total_ex_vat"] < $b["total_ex_vat"];
        });
        
        $combined_orders = 0;
        $combined_sales = 0;
        
        foreach ($data as $key => $data_item) {
            $combined_orders += $data_item["order_count"];
            $combined_sales += $data_item["total_ex_vat"];
            $datax .= $data_item["name"].",".$data_item["order_count"].",".$data_item["total_ex_vat"]."<br>";
        }
        
        $untagged_order_count = ($all_order_count - $combined_orders);
        $untagged_order_total = ($all_order_total_ex_vat - $combined_sales);
        
        $datax .= "Untaged, $untagged_order_count, - "."<br>";
        
        $datax .= "Total, $all_order_count, $all_order_total_ex_vat ";
        
        echo $datax;
    }
    
    /**
     * paid unfullfilled
     */
    
    public function pendingPaidUnfullfilledExVatPerStaff($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $all_order_count = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("cancelled_at", null)
                           ->where("financial_status", "paid")
                           ->where("fulfillment_status", null)
                           ->count();
      
        $all_order_total = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                       ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                       ->where("cancelled_at", null)
                       ->where("financial_status", "paid")
                       ->where("fulfillment_status", null)
                       ->sum("total_price");

        $all_order_total_tax = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                          ->where("cancelled_at", null)
                          ->where("financial_status", "paid")
                          ->where("fulfillment_status", null)
                          ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                          ->sum("total_tax");
        
        $all_order_total_ex_vat = ($all_order_total - $all_order_total_tax);
        
        foreach ($this->tags as $tag) {
            $order_count[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("tags", "like", "%$tag%")
                           ->where("cancelled_at", null)
                           ->where("financial_status", "paid")
                           ->where("fulfillment_status", null)
                           ->count();
      
            $order_total[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("tags", "like", "%$tag%")
                           ->where("cancelled_at", null)
                           ->where("financial_status", "paid")
                           ->where("fulfillment_status", null)
                           ->sum("total_price");
        
            $order_total_tax[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                              ->where("tags", "like", "%$tag%")
                              ->where("cancelled_at", null)
                              ->where("financial_status", "paid")
                              ->where("fulfillment_status", null)
                              ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                              ->sum("total_tax");
        }
        
        $datax = "Staff, Number of Orders, Total ex VAT"."<br>";
      
        foreach ($this->tags as $key => $tag) {
            $ex_vat_amount[$tag] = round(($order_total[$tag] - $order_total_tax[$tag]), 2);
            $data[$tag]["name"] = ucfirst($tag);
            $data[$tag]["order_count"] = $order_count[$tag];
            $data[$tag]["total_ex_vat"] = $ex_vat_amount[$tag];
        }
        
        usort($data, function ($a, $b) {
            return $a["total_ex_vat"] < $b["total_ex_vat"];
        });
        
        $combined_orders = 0;
        $combined_sales = 0;
        
        foreach ($data as $key => $data_item) {
            $combined_orders += $data_item["order_count"];
            $combined_sales += $data_item["total_ex_vat"];
            $datax .= $data_item["name"].",".$data_item["order_count"].",".$data_item["total_ex_vat"]."<br>";
        }
        
        $untagged_order_count = ($all_order_count - $combined_orders);
        $untagged_order_total = ($all_order_total_ex_vat - $combined_sales);
        
        $datax .= "Untaged, $untagged_order_count, - "."<br>";
        
        $datax .= "Total, $all_order_count, $all_order_total_ex_vat ";
        
        echo $datax;
    }
    
    
    /**
     * cancelledorders
     *
     */
    public function cancelledOrders($start_date, $end_date)
    {
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);    
      
        foreach ($this->cancelled_tags as $tag) {
          
            $order_count[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("tags", "like", "%$tag%")
                           ->count();
      
            $order_total[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("tags", "like", "%$tag%")
                           ->sum("total_price");
        
            $order_total_tax[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                              ->where("tags", "like", "%$tag%")
                              ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                              ->sum("total_tax");
        }
      
        $data = " Cancelled Reason, Number of Orders, Total ex VAT"."<br>";
        
        foreach ($this->cancelled_tags as $key => $tag) {
            $reason = $this->cancelled_reason_tags[$tag];
            $ex_vat_amount = round(($order_total[$tag] - $order_total_tax[$tag]), 2);
            $data .= "$reason, $order_count[$tag], $ex_vat_amount"."<br>";
        }
      
        echo $data;
    }
    
    /**
     *
     * orderstoday
     */
    
    public function numberOfOrdersToday()
    {
      
        $order_count = Order::where("shopify_created_at", "like", $this->today->format("Y-m-d")."%")->count();
        $order_total = Order::where("shopify_created_at", "like", $this->today->format("Y-m-d")."%")->sum("total_price");
        $order_count_paid = Order::where("financial_status", "paid")->where("shopify_created_at", "like", $this->today->format("Y-m-d")."%")->count();
        $paid_order_total = Order::where("financial_status", "paid")->where("shopify_created_at", "like", $this->today->format("Y-m-d")."%")->sum("total_price");
        $paid_tax = Order::where("financial_status", "paid")->where("shopify_created_at", "like", $this->today->format("Y-m-d")."%")->sum("total_tax");
        $discounts = Order::where("shopify_created_at", "like", $this->today->format("Y-m-d")."%")->sum("total_discounts");
          
        $ex_vat_order_total = round(($paid_order_total - $paid_tax), 2);
        
        $data = "All Orders, Gross Amount, Paid Orders,  Paid Total Inc Vat, Paid Total ex Vat
              $order_count, $order_total, $order_count_paid, $paid_order_total, $ex_vat_order_total 
             ";
        
        echo $data;
    }
    
    /**
     * salestoday
     */
    
    public function salesTodayExVat($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $order_count = Order::where("shopify_created_at", "like", Carbon::now()->format("Y-m-d")."%")->count();
        $order_total = Order::where("shopify_created_at", "like", Carbon::now()->format("Y-m-d")."%")->sum("total_price");
        $order_total_tax = Order::where("shopify_created_at", "like", Carbon::now()->format("Y-m-d")."%")->sum("total_tax");
        $total_ex_vat = ($order_total - $order_total_tax);
      
        $data = "Number of Orders, Total ex Vat
              $order_count, $total_ex_vat ";
      
        echo $data;
    }
    
    /**
     *
     * dailytransactionbreakdown
     */
    
    public function dailyTransactionBreakdown($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $date_range = $this->generateDateRange($this->start_date, $this->end_date);
        
        foreach ($date_range as $key => $date) {
            $order_count[$date] = Order::where("shopify_created_at", "like", Carbon::parse($date)->format("Y-m-d")."%")
                                       ->where("cancelled_at", null)
                                       ->count();
        
            $order_total[$date] = Order::where("shopify_created_at", "like", Carbon::parse($date)->format("Y-m-d")."%")
                                        ->where("cancelled_at", null)
                                        ->sum("total_price");
          
            $order_total_tax[$date] = Order::where("shopify_created_at", "like", Carbon::parse($date)->format("Y-m-d")."%")
                                        ->where("cancelled_at", null)
                                        ->sum("total_tax");
        }
        
        $data = "Date, Number of Orders, Total Inc VAT, Total Ex VAT"."<br>";
        
        foreach ($date_range as $key => $date) {
            $ex_vat_total[$date] = round(($order_total[$date] - $order_total_tax[$date]), 2);
            $data .= "$date, $order_count[$date], $order_total[$date], $ex_vat_total[$date]"."<br>";
        }
        
        echo $data;
    }
    
    /**
     *
     * onlinesalesdailytransactionbreakdown
     */
    
    public function onelineSalesDailyTransactionBreakdown($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $date_range = $this->generateDateRange($this->start_date, $this->end_date);
        
        foreach ($date_range as $key => $date) {
            foreach ($this->online_tags as $tag) {
                $order_count[$date][$tag] = Order::where("shopify_created_at", "like", Carbon::parse($date)->format("Y-m-d")."%")
                                         ->where("tags", "like", "%$tag%")
                                         ->where("cancelled_at", null)
                                         ->count();
            
                $order_total[$date][$tag] = Order::where("shopify_created_at", "like", Carbon::parse($date)->format("Y-m-d")."%")
                                          ->where("tags", "like", "%$tag%")
                                          ->where("cancelled_at", null)
                                          ->sum("total_price");
            
                $order_total_tax[$date][$tag] = Order::where("shopify_created_at", "like", Carbon::parse($date)->format("Y-m-d")."%")
                                            ->where("tags", "like", "%$tag%")
                                            ->where("cancelled_at", null)
                                            ->sum("total_tax");
            }
        }
        
        foreach ($date_range as $key => $date) {
            $aggregate_order_count[$date] = array_sum($order_count[$date]);
            $aggregate_order_total[$date] = array_sum($order_total[$date]);
            $aggregate_order_total_tax[$date] = array_sum($order_total_tax[$date]);
        }
        
        $data = "Date, Number of Orders, Total Inc VAT, Total Ex VAT"."<br>";
        
        foreach ($date_range as $key => $date) {
            $ex_vat_total[$date] = round(($aggregate_order_total[$date] - $aggregate_order_total_tax[$date]), 2);
            $data .= "$date, $aggregate_order_count[$date], $aggregate_order_total[$date], $ex_vat_total[$date]"."<br>";
        }
        
        echo $data;
    }
    
    /**
     *
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param type $minimal
     * @return type
     *
     */
    
    private function generateDateRange(Carbon $start_date, Carbon $end_date, $minimal = false)
    {
        $dates = [];
      
        if ($minimal) {
            for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
                $dates[] = $date->format("d");
            }
        } else {
            for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
                $dates[] = $date->format("Y-m-d");
            }
        }
        return $dates;
    }
    
    /**
     *
     * breakdownbyvendor
     */
    
    public function breakdownByVendor($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $products = Lineitems::join("orders", "orders.id", "=", "line_items.order_id")
                            ->select("line_items.vendor", DB::raw('sum(line_items.quantity) as total'), DB::raw('sum(line_items.price*line_items.quantity) as item_price'))
                            ->groupBy("line_items.vendor")
                            ->where("orders.shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                            ->where("orders.shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                            ->orderBy("total", "desc")
                            ->get();
      
        $data = "Vendor, Number of Items, Total Item Sales"."<br>";
      
        foreach ($products as $key => $product) {
            $data .= "$product->vendor, $product->total, $product->item_price"."<br>";
        }
      
        echo $data;
    }
    
    /**
     * dailybreakdownbyvendor
     */
    
    public function dailyBreakdownByVendor($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $products = Lineitems::join("orders", "orders.id", "=", "line_items.order_id")
                            ->select("line_items.vendor", DB::raw('sum(line_items.quantity) as total'), DB::raw('sum(line_items.price*line_items.quantity) as item_price'))
                            ->groupBy("line_items.vendor")
                            ->where("orders.shopify_created_at", "like", $this->today->format("Y-m-d")."%")
                            ->orderBy("total", "desc")
                            ->get();
      
        $data = "Vendor, Number of Items, Total Item Sales"."<br>";
      
        foreach ($products as $key => $product) {
            $data .= "$product->vendor, $product->total, $product->item_price"."<br>";
        }
      
        echo $data;
    }
    
    /**
     * breakdownbyproduct
     *
     */
    
    public function breakdownByProduct($start_date, $end_date)
    {
      
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $products = Lineitems::join("orders", "orders.id", "=", "line_items.order_id")
                            ->select("line_items.title", DB::raw('count(*) as total'), DB::raw('sum(line_items.price*line_items.quantity) as item_price'))
                            ->groupBy("line_items.title")
                            ->where("orders.shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                            ->where("orders.shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                            ->orderBy("total", "desc")
                            ->limit(30)
                            ->get();
      
        $data = "Product, Number of Products, Total Sales"."<br>";
      
        foreach ($products as $key => $product) {
            $data .= " $product->title, $product->total, $product->item_price"."<br>";
        }
      
        echo $data;
    }
    
    /**
     * dailybreakdownbyproduct
     *
     */
    
    public function dailyBreakdownByProduct($start_date, $end_date)
    {
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $products = Lineitems::join("orders", "orders.id", "=", "line_items.order_id")
                            ->select("line_items.title", DB::raw('count(*) as total'), DB::raw('sum(line_items.price*line_items.quantity) as item_price'))
                            ->groupBy("line_items.title")
                            ->where("orders.shopify_created_at", "like", $this->today->format("Y-m-d")."%")
                            ->orderBy("total", "desc")
                            ->limit(30)
                            ->get();
      
        $data = "Product, Number of Products, Total Sales"."<br>";
      
        foreach ($products as $key => $product) {
            $data .= " $product->title, $product->total, $product->item_price"."<br>";
        }
      
        echo $data;
    }
    
    /**
     * breakdownbysku
     *
     */
    
    public function breakdownBySku($start_date, $end_date)
    {
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $products = Lineitems::join("orders", "orders.id", "=", "line_items.order_id")
                            ->select("line_items.sku", DB::raw('count(*) as total'), DB::raw('sum(line_items.price*line_items.quantity) as item_price'))
                            ->groupBy("line_items.sku")
                            ->where("orders.shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                            ->where("orders.shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                            ->orderBy("total", "desc")
                            ->limit(30)
                            ->get();
      
        $data = "SKU, Number of Items, Total Item Sales"."<br>";
      
        foreach ($products as $key => $product) {
            $data .= "$product->sku,  $product->total, $product->item_price"."<br>";
        }
      
        echo $data;
    }
    
    /**
     *
     * dailybreakdownbysku
     */
    
    public function dailyBreakdownBySku()
    {
        $products = Lineitems::join("orders", "orders.id", "=", "line_items.order_id")
                            ->select("line_items.sku", DB::raw('count(*) as total'), DB::raw('sum(line_items.price*line_items.quantity) as item_price'))
                            ->groupBy("line_items.sku")
                            ->where("orders.shopify_created_at", "like", $this->today->format("Y-m-d")."%")
                            ->orderBy("total", "desc")
                            ->limit(30)
                            ->get();
      
        $data = "SKU, Number of Items, Total Sales"."<br>";
      
        foreach ($products as $key => $product) {
            $data .= "$product->sku, $product->total, $product->item_price"."<br>";
        }
      
        echo $data;
    }
    
    /**
     *
     * returningvsnew
     */
    
    public function breakdownReturningVsNew($start_date, $end_date)
    {
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        $all_customer_orders = Order::select("customer_id")
                           ->where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                           ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                           ->where("financial_status", "paid")
                           ->groupBy("customer_id")
                           ->get();
      
        foreach ($all_customer_orders as $key => $customer) {
            $orders_made[$customer->customer_id] = Order::where("financial_status", "paid")->where("customer_id", $customer->customer_id)->count();
        }
      
        $new_customer_array = [];
        $returning_customer_array = [];
      
        foreach ($orders_made as $customer_id => $order_made) {
            if ($order_made == 1) {
                array_push($new_customer_array, $customer_id);
            } else {
                array_push($returning_customer_array, $customer_id);
            }
        }
      
        $new_customers = count($new_customer_array);
        $returning_customers = count($returning_customer_array);
        $all_customers = count($all_customer_orders);
      
        $data = "All Customers, New Customers, Returning Customers"."<br>";
      
        $data .= "$all_customers, $new_customers, $returning_customers"."<br>";
      
        echo $data;
    }
    
    /**
     *
     * breakdownbyfullfillmentstatus
     */
    
    public function fullfillmentStatusBreakdown($start_date, $end_date)
    {
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        foreach ($this->fullfillment_status as $key => $status) {
            $order_count[$status] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("fulfillment_status", $status)
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->count();

            $order_total[$status] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("fulfillment_status", $status)
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_price");
            
            $order_total_tax[$status] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("fulfillment_status", $status)
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_tax");
        }
        
        $datax = "Fulfillment, Number of Orders, Total ex VAT"."<br>";
      
        foreach ($this->fullfillment_status as $key => $status) {
            if (($order_count[$status]) > 0) {
                $ex_vat_amount[$status] = round(($order_total[$status] - $order_total_tax[$status]), 2);
                $data[$status]["name"] = ucfirst($status);
                if (empty($data[$status]["name"])) {
                    $data[$status]["name"] = "Not Fulfilled";
                }
                $data[$status]["order_count"] = $order_count[$status];
                $data[$status]["total_ex_vat"] = $ex_vat_amount[$status];
            }
        }
        
        usort($data, function ($a, $b) {
            return $a["total_ex_vat"] < $b["total_ex_vat"];
        });
        
        $combined_orders = 0;
        $combined_sales = 0;
        
        foreach ($data as $key => $data_item) {
            $combined_orders += $data_item["order_count"];
            $combined_sales += $data_item["total_ex_vat"];
            $datax .= $data_item["name"].",".$data_item["order_count"].",".$data_item["total_ex_vat"]."<br>";
        }
        
        $datax .= "Total Sales, $combined_orders, $combined_sales ";
        
        echo $datax;
    }
    
    /**
     *
     * breakdownbyfinancialstatus
     */
    
    public function financialStatusBreakdown($start_date, $end_date)
    {
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        foreach ($this->financial_status as $key => $status) {
            $order_count[$status] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("financial_status", $status)
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->count();

            $order_total[$status] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("financial_status", $status)
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_price");
            
            $order_total_tax[$status] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("financial_status", $status)
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->sum("total_tax");
        }
        
        $datax = "Financial, Number of Orders, Total ex VAT"."<br>";
        
        foreach ($this->financial_status as $key => $status) {
            if (($order_count[$status]) > 0) {
                $ex_vat_amount[$status] = round(($order_total[$status] - $order_total_tax[$status]), 2);
                $data[$status]["name"] = ucfirst($status);
                if (empty($data[$status]["name"])) {
                    $data[$status]["name"] = "Null";
                }
                $data[$status]["order_count"] = $order_count[$status];
                $data[$status]["total_ex_vat"] = $ex_vat_amount[$status];
            }
        }
        
        usort($data, function ($a, $b) {
            return $a["total_ex_vat"] < $b["total_ex_vat"];
        });
        
        $combined_orders = 0;
        $combined_sales = 0;
        
        foreach ($data as $key => $data_item) {
            $combined_orders += $data_item["order_count"];
            $combined_sales += $data_item["total_ex_vat"];
            $datax .= $data_item["name"].",".$data_item["order_count"].",".$data_item["total_ex_vat"]."<br>";
        }
        
        $datax .= "All Sales, $combined_orders, $combined_sales ";
        
        echo $datax;
    }
    
    /*
     * untaggedsalesorderids
     */
    
    public function untaggedSalesOrderIds($start_date, $end_date)
    {
        $this->start_date = Carbon::parse($start_date); 
        
        $this->end_date = Carbon::parse($end_date);
      
        //all numbers        
        $all_orders = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                               ->where("cancelled_at", null)
                               ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                               ->select(["name"])
                               ->orderBy("name", "desc")
                               ->get();
        $all_order_numbers = [];
        
        foreach ($all_orders as $order) {
            array_push($all_order_numbers, $order->name);
        }
        
        foreach ($this->online_tags as $key => $tag) {
            $online_orders[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->select(["name"])
                             ->orderBy("name", "desc")
                             ->get();
        }
        
        $online_order_numbers = [];
        
        foreach ($online_orders as $tag => $online_order_array) {
            foreach ($online_order_array as $key => $online_order) {
                array_push($online_order_numbers, $online_order->name);
            }
        }
        
        $offline_order_numbers = [];
        
        foreach ($this->offline_tags as $key => $tag) {
            $offline_orders[$tag] = Order::where("shopify_created_at", ">=", $this->start_date->format("Y-m-d"))
                             ->where("tags", "like", "%$tag%")
                             ->where("cancelled_at", null)
                             ->where("shopify_created_at", "<=", $this->end_date->endOfDay()->format("Y-m-d H:i"))
                             ->select(["name"])
                             ->orderBy("name", "desc")
                             ->get();
        }
        
        foreach ($offline_orders as $tag => $offline_order_array) {
            foreach ($offline_order_array as $key => $offline_order) {
                array_push($offline_order_numbers, $offline_order->name);
            }
        }
        
        $combined_offline_online = array_merge($online_order_numbers, $offline_order_numbers);
        
        $difference = array_diff($all_order_numbers, $combined_offline_online);
        
        $datax = "Order Ids"."<br>";
        
        foreach ($difference as $key => $diff) {
            $datax .= $diff."<br>";
        }
        
        echo $datax;
    }
    
    public function getPaymentPost($postdata)
    {
        mail("akulad19@gmail.com", "Test", $postdata);
    }
}