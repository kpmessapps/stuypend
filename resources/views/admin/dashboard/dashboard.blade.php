@extends('layouts.common')

@section('pageTitle','Dashboard')
@section('newCssLoad')

    <link href="{!! asset('asset/css/bootstrap.min.css') !!}" rel="stylesheet">
    <link href="{!! asset('asset/font-awesome/css/font-awesome.css') !!}" rel="stylesheet">

    <!-- Morris -->
    <link href="{!! asset('asset/css/plugins/morris/morris-0.4.3.min.css') !!}" rel="stylesheet">
    
    <!-- chosen -->
    <link href="{!! asset('asset/css/plugins/chosen/chosen.css') !!}" rel="stylesheet">
    
    <link href="{!! asset('asset/css/animate.css') !!}" rel="stylesheet">
    <link href="{!! asset('asset/css/style.css') !!}" rel="stylesheet">

@endsection
@section('mainContainerArea')
    <div class="row">
      <!--         <div class="col-lg-2">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <span class="label label-success pull-right">Today</span>
                        <h5>Non-activated</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">386,200</h1>
                        <div class="stat-percent font-bold text-success">98% <i class="fa fa-bolt"></i></div>
                        <small>Total views</small>
                    </div>
                </div>
            </div>
   <!--         <div class="col-lg-2">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <span class="label label-info pull-right">Monthly</span>
                        <h5>Orders</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">80,800</h1>
                        <div class="stat-percent font-bold text-info">20% <i class="fa fa-level-up"></i></div>
                        <small>New orders</small>
                    </div>
                </div>
            </div>
-->
            <div class="col-lg-4">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <span class="label label-primary pull-right">Users</span>
                        <h5>Activated</h5>
                    </div>
                    <div class="ibox-content">

                        <div class="row">
                            <div class="col-md-4">
                                <h1 class="no-margins">{{ $todayNewActivatedUsers or '0'}}</h1>
                                <div class="font-bold text-navy"><small>Today</small></div>
                            </div>
                            <div class="col-md-4">
                                <h1 class="no-margins">{{ $weekNewActivatedUsers or '0'}}</h1>
                                <div class="font-bold text-navy"><small>This Week</small></div>
                            </div>
                            <div class="col-md-4">
                                <h1 class="no-margins">{{ $monthNewActivatedUsers or '0'}}</h1>
                                <div class="font-bold text-navy"><small>This Month</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           <?php /*     
            <div class="col-lg-4">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <span class="label label-primary pull-right">Users</span>
                        <h5>Non Activated</h5>
                    </div>
                    <div class="ibox-content">

                        <div class="row">
                            <div class="col-md-4">
                                <h1 class="no-margins">{{ $todayNewInactivatedUsers or '0'}}</h1>
                                <div class="font-bold text-navy"><small>Today</small></div>
                            </div>
                            <div class="col-md-4">
                                <h1 class="no-margins">{{ $weekNewInactivatedUsers or '0'}}</h1>
                                <div class="font-bold text-navy"><small>This Week</small></div>
                            </div>
                            <div class="col-md-4">
                                <h1 class="no-margins">{{ $monthNewInactivatedUsers or '0'}}</h1>
                                <div class="font-bold text-navy"><small>This Month</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            */ ?>

            <div class="col-lg-4">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <span class="label label-primary pull-right">Answers</span>
                        <h5>Total Answers</h5>
                    </div>
                    <div class="ibox-content">

                        <div class="row">
                            <div class="col-md-4">
                                <h1 class="no-margins">{{ $todayAnswers or '0'}}</h1>
                                <div class="font-bold text-navy"><small>Today</small></div>
                            </div>
                            <div class="col-md-4">
                                <h1 class="no-margins">{{ $weekAnswers or '0'}}</h1>
                                <div class="font-bold text-navy"><small>This Week</small></div>
                            </div>
                            <div class="col-md-4">
                                <h1 class="no-margins">{{ $monthAnswers or '0'}}</h1>
                                <div class="font-bold text-navy"><small>This Month</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!--    <div class="col-lg-4">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Monthly income</h5>
                        <div class="ibox-tools">
                            <span class="label label-primary">Updated 12.2015</span>
                        </div>
                    </div>
                    <div class="ibox-content no-padding">
                        <div class="flot-chart m-t-lg" style="height: 55px;">
                            <div class="flot-chart-content" id="flot-chart1"></div>
                        </div>
                    </div>

                </div>
            </div> -->
        </div> <!-- end of ROW -->
        
        
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content">
                        <div>
                                <h3 class="font-bold no-margins">
                                    Answer Report
                                </h3>
                        </div>
                       <div class="hr-line-dashed"></div>         
                        <div class="row">
                                
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="status" class="control-label">Activities</label>
                                        <select data-placeholder="Choose a Activity..." name="activityId" id="activityId" class="chosen-select" style="width:350px;" tabindex="2">
                                                <option value="">Select</option>
                                                <?php
                                                    foreach($activityList as $key => $value){
                                                        echo "<option value='".$value['act_id']."'>".$value['act_desc']."</option>";
                                                    } 
                                                ?>
                                        </select>
                                    </div>
                                </div>
                            
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="status" class="control-label">Year</label>
                                        <select class="form-control" id="answerYear" name="answerYear">
                                            @foreach ($yearArr as $key => $value)
                                                <option value="{{ $value }}" @if ($value == $currentYear) selected="selected" @endif>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                        
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="status" class="control-label">Month</label>
                                        <select class="form-control" id="answerMonth" name="answerMonth">
                                            <option value="">Select Month</option>
                                            @foreach ($monthArr as $key => $value)
                                                <option value="{{ $key }}" @if ($key == $currentMonth) selected="selected" @endif>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>  
                            
                                <div class="col-sm-1">
                                    <div class="form-group">
                                        <label for="status" class="control-label col-sm-12">&nbsp;</label>
                                        <a href="javascript:void(0);" class="btn btn-primary" onclick="return updateAnswerGraph();" >Filter</a>
                                    </div>
                                </div>  
                        </div>
                

                <div class="m-t-sm">

                    <div class="row">
                        <div class="col-md-12">
                            <div id="lineChartContainer">
                                <canvas id="lineChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                </div>

               <!-- <div class="m-t-md">
                    <small class="pull-right">
                        <i class="fa fa-clock-o"> </i>
                        Update on 16.07.2015
                    </small>
                            <small>
                                <strong>Analysis of sales:</strong> The value has been changed over time, and last month reached a level over $50,000.
                            </small>
                        </div>

                    </div>
                </div> -->
            </div>
       <!--     <div class="col-lg-4">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <span class="label label-warning pull-right">Data has changed</span>
                        <h5>User activity</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-xs-4">
                                <small class="stats-label">Pages / Visit</small>
                                <h4>236 321.80</h4>
                            </div>

                            <div class="col-xs-4">
                                <small class="stats-label">% New Visits</small>
                                <h4>46.11%</h4>
                            </div>
                            <div class="col-xs-4">
                                <small class="stats-label">Last week</small>
                                <h4>432.021</h4>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-xs-4">
                                <small class="stats-label">Pages / Visit</small>
                                <h4>643 321.10</h4>
                            </div>

                            <div class="col-xs-4">
                                <small class="stats-label">% New Visits</small>
                                <h4>92.43%</h4>
                            </div>
                            <div class="col-xs-4">
                                <small class="stats-label">Last week</small>
                                <h4>564.554</h4>
                            </div>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-xs-4">
                                <small class="stats-label">Pages / Visit</small>
                                <h4>436 547.20</h4>
                            </div>

                            <div class="col-xs-4">
                                <small class="stats-label">% New Visits</small>
                                <h4>150.23%</h4>
                            </div>
                            <div class="col-xs-4">
                                <small class="stats-label">Last week</small>
                                <h4>124.990</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

                </div>
            </div>
         </div>

        
        
        
               
                
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content">
                        <div>
                                <h3 class="font-bold no-margins">
                                    User Report
                                </h3>
                        </div>
                       <div class="hr-line-dashed"></div>         
                        <div class="row">
                                
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="userName" class="control-label">Name</label>
                                        <input type="text" class="form-control" id="userName" name="userName" placeholder="Name">
                                    </div>
                                </div>
                                
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="userIsAdmin" class="control-label">Is Admin</label>
                                        <select class="form-control" id="userIsAdmin" name="userIsAdmin">
                                            <option value="" selected="selected">All</option>
                                            <option value="1">Yes</option>
                                            <option value="2">No</option>
                                        </select>
                                    </div>
                                </div>
                            
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="userYear" class="control-label">Year</label>
                                        <select class="form-control" id="userYear" name="userYear">
                                            @foreach ($yearArr as $key => $value)
                                                <option value="{{ $value }}" @if ($value == $currentYear) selected="selected" @endif>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                        
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="userMonth" class="control-label">Month</label>
                                        <select class="form-control" id="userMonth" name="userMonth">
                                            <option value="">Select Month</option>
                                            @foreach ($monthArr as $key => $value)
                                                <option value="{{ $key }}" @if ($key == $currentMonth) selected="selected" @endif>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>  
                                
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="userStatus" class="control-label">Status</label>
                                        <select class="form-control" id="userStatus" name="userStatus">
                                            <option value="" selected="selected">All</option>
                                            <option value="2">Not Activated</option>
                                            <option value="1">Activated</option>
                                        </select>
                                    </div>
                                </div>
                            
                                <div class="col-sm-1">
                                    <div class="form-group">
                                        <label  class="control-label col-sm-12">&nbsp;</label>
                                        <a href="javascript:void(0);" class="btn btn-primary" onclick="return updateUserGraph();" >Filter</a>
                                    </div>
                                </div>  
                        </div>
                

                        <div class="m-t-sm">

                            <div class="row">
                                <div class="col-md-12">
                                    <div id="userLineChartContainer" style="min-height: 100px;">
                                        <canvas id="userLineChart" height="100"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>
        
        
@endsection

@section('newJsLoad')
    
    <!-- Custom and plugin javascript -->
    <script src="{!! asset('asset/js/inspinia.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/pace/pace.min.js') !!}"></script>
        
    <!-- jQuery UI -->
    <script src="{!! asset('asset/js/plugins/jquery-ui/jquery-ui.min.js') !!}"></script>
     
    <!-- Chosen -->
    <script src="{!! asset('asset/js/plugins/chosen/chosen.jquery.js') !!}"></script>

    
    <!-- Flot -->
 <!--   <script src="{!! asset('asset/js/plugins/flot/jquery.flot.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/flot/jquery.flot.tooltip.min.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/flot/jquery.flot.spline.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/flot/jquery.flot.resize.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/flot/jquery.flot.pie.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/flot/jquery.flot.symbol.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/flot/curvedLines.js') !!}"></script>
-->
    <!-- Jvectormap -->
 <!--   <script src="{!! asset('asset/js/plugins/jvectormap/jquery-jvectormap-2.0.2.min.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') !!}"></script>
-->
    <!-- Sparkline -->
    <!--     <script src="{!! asset('asset/js/plugins/sparkline/jquery.sparkline.min.js') !!}"></script> -->

    <!-- Sparkline demo data  -->
    <!--     <script src="{!! asset('asset/js/demo/sparkline-demo.js') !!}"></script>

    <!-- ChartJS-->
    <script src="{!! asset('asset/js/plugins/chartJs/Chart.min.js') !!}"></script> 
        
@endsection

@section("customPageJs")
    <script>
        function drawAnswerGraph(x_graph,y_graph){
            // This below step needed because over right graph is still contain old graph in background 
            // //when you hover below horizontal axis than its show some time old graph lines values
            $('#lineChartContainer').html('<canvas id="lineChart" height="100"></canvas>');
     
            var lineData = {
                labels: x_graph,
                datasets: [
                 /*   {
                        label: "Example dataset",
                        fillColor: "rgba(220,220,220,0.5)",
                        strokeColor: "rgba(220,220,220,1)",
                        pointColor: "rgba(220,220,220,1)",
                        pointStrokeColor: "#fff",
                        pointHighlightFill: "#fff",
                        pointHighlightStroke: "rgba(220,220,220,1)",
                        data: y_graph
                    },*/
                    {
                        label: "Example dataset",
                        fillColor: "rgba(26,179,148,0.5)",
                        strokeColor: "rgba(26,179,148,0.7)",
                        pointColor: "rgba(26,179,148,1)",
                        pointStrokeColor: "#fff",
                        pointHighlightFill: "#fff",
                        pointHighlightStroke: "rgba(26,179,148,1)",
                        data: y_graph //[48, 48, 60, 39, 56, 37, 30, 48, 48, 60, 39, 56, 37, 30]
                    } 
                ]
            };

            var lineOptions = {
                scaleShowGridLines: true,
                scaleGridLineColor: "rgba(0,0,0,.05)",
                scaleGridLineWidth: 1,
                bezierCurve: true,
                bezierCurveTension: 0.4,
                pointDot: true,
                pointDotRadius: 4,
                pointDotStrokeWidth: 1,
                pointHitDetectionRadius: 20,
                datasetStroke: true,
                datasetStrokeWidth: 2,
                datasetFill: true,
                responsive: true,
            };


            var ctx = document.getElementById("lineChart").getContext("2d");
            var myNewChart = new Chart(ctx).Line(lineData, lineOptions);
        }
        
        function drawUserGraph(x_graph,y_graph){
            // This below step needed because over right graph is still contain old graph in background 
            // //when you hover below horizontal axis than its show some time old graph lines values
            $('#userLineChartContainer').html('<canvas id="userLineChart" height="100"></canvas>');
     
            var lineData = {
                labels: x_graph,
                datasets: [
                    {
                        label: "Users",
                        fillColor: "rgba(26,179,148,0.5)",
                        strokeColor: "rgba(26,179,148,0.7)",
                        pointColor: "rgba(26,179,148,1)",
                        pointStrokeColor: "#fff",
                        pointHighlightFill: "#fff",
                        pointHighlightStroke: "rgba(26,179,148,1)",
                        data: y_graph //[48, 48, 60, 39, 56, 37, 30, 48, 48, 60, 39, 56, 37, 30]
                    } 
                ]
            };

            var lineOptions = {
                scaleShowGridLines: true,
                scaleGridLineColor: "rgba(0,0,0,.05)",
                scaleGridLineWidth: 1,
                bezierCurve: true,
                bezierCurveTension: 0.4,
                pointDot: true,
                pointDotRadius: 4,
                pointDotStrokeWidth: 1,
                pointHitDetectionRadius: 20,
                datasetStroke: true,
                datasetStrokeWidth: 2,
                datasetFill: true,
                responsive: true,
            };


            var ctx = document.getElementById("userLineChart").getContext("2d");
            var myNewChart = new Chart(ctx).Line(lineData, lineOptions);
        }
        
        function updateAnswerGraph(){
            var activityId = $('#activityId').val();
            var answerYear = $('#answerYear').val();
            var answerMonth = $('#answerMonth').val();
            
            $.ajax({
                url: '{{route('admin::ajaxAnswerGraphReport')}}',
                type: 'POST',
                data:{activityId:activityId,answerYear:answerYear,answerMonth:answerMonth,_token:'{{ csrf_token() }}'},
                async: false,
                cache: false,
                timeout: 30000,
                error: function(){
                    return true;
                },
                success: function(data){
                    if (data.statusCode == "1") {
                        drawAnswerGraph(data.x_graph,data.y_graph);
                    }
                }
            });
            return true;
        }
        
        function updateUserGraph(){
            var name        = $('#userName').val();
            var campusId    = $('#userCampusId').val();
            var userIsAdmin   = $('#userIsAdmin').val();
            var regType     = $('#userRegType').val();
            var memType     = $('#userMemberType').val();
            var year        = $('#userYear').val();
            var month       = $('#userMonth').val();
            var status      = $('#userStatus').val();
            
            $.ajax({
                url: '{{route('admin::ajaxUserGraphReport')}}',
                type: 'POST',
                data:{name: name,campusId:campusId,isAdmin:userIsAdmin,regType:regType,memType:memType,year:year,month:month,status:status,_token:'{{ csrf_token() }}'},
                async: false,
                cache: false,
                timeout: 30000,
                error: function(){
                    return true;
                },
                success: function(data){
                    if (data.statusCode == "1") {
                        drawUserGraph(data.x_graph,data.y_graph);
                    }
                }
            });
            return true;
        }
        
        $(document).ready(function() {
                var config = {
                    '.chosen-select'           : {width:"100%"},
                    '.chosen-select-deselect'  : {allow_single_deselect:true},
                    '.chosen-select-no-single' : {disable_search_threshold:10},
                    '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
                    '.chosen-select-width'     : {width:"95%"}
                }
                for (var selector in config) {
                    $(selector).chosen(config[selector]);
                }
                
                updateAnswerGraph();
                updateUserGraph();
        
        });
    </script>
    
@endsection