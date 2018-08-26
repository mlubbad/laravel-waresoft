@extends("layouts.main_template")

@section('content')
<!-- Widgets -->
<div class="block-header">
  <h2> Dashboard </h2>


</div>

<div class="row clearfix">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="card">
      <div class="header">
        <h2>
          Select Period
        </h2>
      </div>
      <div class="body">
        <div class="row clearfix">
          
          <div class="col-sm-12">
            <div class="form-group">
              <select class="form-control">
                <option value="Today"> Today </option>
                <option value="Yesterday"> Yesterday </option>
                <option value="Last 7 Days"> Last 7 Days </option>
                <option value="Last 14 Days"> Last 14 Days </option>
                <option value="Last 30 Days"> Last 30 Days </option>
                <option value="This Week"> This Week </option>
                <option value="Last Week"> Last Week </option>
                <option value="This Month"> This Month </option>
                <option value="Last Month"> Last Month </option>
                <option value="Custom Range"> Custom Range </option>
              </select>
            </div>
          </div>
          
          <div class="col-sm-4">
            <div class="form-group">
              <div class="form-line">
                <input type="text" class="datepicker form-control" placeholder="Please choose a date...">
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <div class="form-line">
                <input type="text" class="timepicker form-control" placeholder="Please choose a time...">
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <div class="form-line">
                <input type="text" class="datetimepicker form-control" placeholder="Please choose date & time...">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="row clearfix">
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-pink hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Fullfillment rate </b> </div>
        <div class="number count-to" data-from="0" data-to="12" data-speed="15" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-cyan hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Paid Sales Amount <b/> </div>
        <div class="number count-to" data-from="0" data-to="257" data-speed="1000" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-light-green hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Avr Basket ex VAT </b> </div>
        <div class="number count-to" data-from="0" data-to="243" data-speed="1000" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-orange hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Delivered orders </b> </div>
        <div class="number count-to" data-from="0" data-to="1225" data-speed="1000" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
</div>

<div class="row clearfix">
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-pink hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Delivered orders - Revenue ex VAT </b> </div>
        <div class="number count-to" data-from="0" data-to="12" data-speed="15" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-cyan hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Offline Sales KES <b/> </div>
        <div class="number count-to" data-from="0" data-to="257" data-speed="1000" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-light-green hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Online sales </b> </div>
        <div class="number count-to" data-from="0" data-to="243" data-speed="1000" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-orange hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Pending orders </b> </div>
        <div class="number count-to" data-from="0" data-to="1225" data-speed="1000" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
</div>

<div class="row clearfix">
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-pink hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Pending Deliveries </b> </div>
        <div class="number count-to" data-from="0" data-to="12" data-speed="15" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-cyan hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Sales ex VAT MTD per Staff <b/> </div>
        <div class="number count-to" data-from="0" data-to="257" data-speed="1000" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-light-green hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Number of orders today </b> </div>
        <div class="number count-to" data-from="0" data-to="243" data-speed="1000" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="info-box bg-orange hover-expand-effect">
      <div class="icon">
        <i class="material-icons">playlist_add_check</i>
      </div>
      <div class="content">
        <div class="text"> <b> Sales today ex VAT </b> </div>
        <div class="number count-to" data-from="0" data-to="1225" data-speed="1000" data-fresh-interval="20"></div>
      </div>
    </div>
  </div>
</div>

<legend></legend>

<div class="row clearfix">
  <!-- Task Info -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Fullfillment Rate  <a href="{{url("dashboard/fullfillment")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>

  </div>
  <!-- #END# Task Info -->
  <!-- Browser Usage -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Paid Sales Amount   <a href="{{url("dashboard/paidsalesamount")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>
  </div>
  <!-- #END# Browser Usage -->
</div>

<div class="row clearfix">
  <!-- Task Info -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Avr Basket ex VAT  <a href="{{url("dashboard/averagebasket")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>

  </div>
  <!-- #END# Task Info -->

  <!-- Browser Usage -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Delivered orders   <a href="{{url("dashboard/deliveredorders")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>
  </div>
  <!-- #END# Browser Usage -->
</div>

<div class="row clearfix">
  <!-- Task Info -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Revenue ex VAT for Delivered orders  <a href="{{url("dashboard/revenuedeliveredorders")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>

  </div>
  <!-- #END# Task Info -->
  <!-- Browser Usage -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Offline Sales KES <a href="{{url("dashboard/offlinesales")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>
  </div>
  <!-- #END# Browser Usage -->
</div>

<div class="row clearfix">
  <!-- Task Info -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Online sales KES <a href="{{url("dashboard/onlinesales")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>

  </div>
  <!-- #END# Task Info -->
  <!-- Browser Usage -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Number of Pending orders   <a href="{{url("dashboard/pendingorders")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>
  </div>
  <!-- #END# Browser Usage -->
</div>

<div class="row clearfix">
  <!-- Task Info -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Sales ex VAT MTD per Staff  <a href="{{url("dashboard/salesperstaff")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>

  </div>
  <!-- #END# Task Info -->
  <!-- Browser Usage -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">
      <div class="header">
        <h2> Pending orders ex VAT per Staff  <a href="{{url("dashboard/salesperstaff")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>
      <div class="body">
        <div id="donut_chart" class="dashboard-donut-chart"></div>
      </div>
    </div>
  </div>

  <!-- #END# Browser Usage -->
</div>

<div class="row clearfix">
  <!-- Task Info -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Number of orders today  <a href="{{url("dashboard/orderstoday")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>

  </div>
  <!-- #END# Task Info -->
  <!-- Browser Usage -->
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <div class="card">

      <div class="header">
        <h2> Sales today ex VAT  <a href="{{url("dashboard/salestoday")}}" class="btn btn-primary btn-xs pull-right"> View More </a> </h2>
      </div>

      <div class="body">
        <div class="table-responsive">

        </div>
      </div>
    </div>
  </div>
  <!-- #END# Browser Usage -->
</div>

@endsection
