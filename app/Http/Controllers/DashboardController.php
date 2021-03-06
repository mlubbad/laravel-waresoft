<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\OrderReportService;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function getDashboardInfo(Request $request) {
      
      $start_date = $request->input("start_date");
      $end_date = $request->input("end_date");
      
      $order_report = new OrderReportService($start_date, $end_date);
      
      $data["sales_today"] = $order_report->numberOfOrdersToday();
      $data["fulfillment_rate"] = $order_report->fullfillmentRate();
      $data["pending_orders"] = $order_report->pendingOrders();
      $data["paid_sales"] = $order_report->paidSalesAmount();
      $data["delivered_orders"] = $order_report->deliveredOrders();
      $data["average_basket_size"] = $order_report->averageBasketExVat();
      $data["onlinesales"] = $order_report->onlineSales();
      $data["offlinesales"] = $order_report->offlineSales();
      $data["untaggedsales"] = $order_report->untaggedSales();
      $data["newvsreturning"] = $order_report->breakdownReturningVsNew();
      $data["financial_breakdown"] = $order_report->financialStatusBreakdown();
      $data["fullfillment_breakdown"] = $order_report->fullfillmentStatusBreakdown();
      $data["salesperstaff"] = $order_report->salesExVatPerStaff();
      $data["deliveriesperstaff"] = $order_report->pendingDeliveriesExVatPerStaff();
      $data["untaggedsalesids"] = $order_report->untaggedSalesOrderIds();
      
      $data["report_title"] = $order_report->getReportTitle();
        
      return view("home.home",$data);
      
    }
    
    
    public function index() {
      
      $data = [];
      
      return view("home.homes");
    }
    
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function fullfillmentRate()
    {
      $data = array();
      
      return view("dashboard/fulfillment", $data);
    }
    
    
    public function paidSalesAmount()
    {
      $data = array();
      
      return view("dashboard/paidsalesamount", $data);
    }
    
    public function averageBasketExVat() {
      $data = array();
      
      return view("dashboard/avebasket", $data);
    }
    
    public function deliveredOrders() {
      $data = array();
      
      return view("dashboard/avebasket", $data);
    }
    
    public function revenueDeliveredOrdersExVat() {
      $data = array();
      
      return view("dashboard/deliveredordersrevenue", $data);
    }
    
    public function offlineSales() {
      $data = array();
      
      return view("dashboard/offlinesales", $data);
    }
    
    public function onlineSales() {
      $data = array();
      
      return view("dashboard/onlinesales", $data);
    }
    
    public function pendingOrders() {
      $data = array();
      
      return view("dashboard/pendingorders", $data);
    }
    
    public function pendingDeliveries() {
      $data = array();
      
      return view("dashboard/pendingdeliveries", $data);
    }
    
    public function salesExVatPerStaff() {
      $data = array();
      
      return view("dashboard/salesexvatperstaff", $data);
    }
    
    public function numberOfOrdersToday() {
      $data = array();
      
      return view("dashboard/orderstoday", $data);
    }
    
    public function salesTodayExVat() {
      $data = array();
      
      return view("dashboard/salestodayexvat", $data);
    }
    
    
}
