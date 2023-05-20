public function fileupload(Request $request)
{

    try {
        $validate = Validator::make($request->all(), array(
            'file' => 'required',
        ));

        // return response()->json(['error' => true, 'message' => $request->all() ]);

        if($validate->passes()){
            if($request->hasFile('file')) {
                $destinationPath = 'datafiles';
                $extension = $request->file('file')->getClientOriginalExtension();
                $fileName = time().$request->file('file')->getClientOriginalName();
                $request->file('file')->move('/data/', $fileName);

                return response()->json(['error' => false, 'message' => "Successfully uploaded a file.", "file" => $fileName]);

            }
        } else {
            return response()->json(['error' => true, 'message' => $validate->errors()->all()]);
        }
    } catch (\Throwable $th) {
        Log::channel('error_logs')->info($th->getMessage(), [ 'user_id' => \Auth::id() ]);
        return response()->json(['error' => true, 'message' => $th->getMessage()]);
    }
}

public function my_profile()
{
    try {
        if(is_null(\Auth::user()->profile_id)){
            return redirect('/onboarding');
        } else {

            $role = \Auth::user()->getUserProfile();

            $mode = $role->mode;
            $profile = $role->profile;


            $title = ucwords(Auth::user()->name);
            $title_subheading  = ucwords($mode . " : " . $profile);
            $title_icon = 'monitor';

            $active_slug = "my-profile";

            $user_id = \Auth::user()->id;

            $profile_menu = self::getProfileMenuLinks();

            $profile_direct_links = self::getProfileMenuDirectLinks();

            $program_direct_links = self::getProgramMenuDirectLinks();

            $reports_analytics_direct_links = self::getReportsAnalyticsMenuDirectLinks();


            return view('my-profile',
                compact(
                    'mode',
                    'profile',
                    'active_slug',
                    'profile_menu',
                    'profile_direct_links',
                    'program_direct_links',
                    'reports_analytics_direct_links',
                    'title',
                    'title_subheading',
                    'title_icon',
                    'user_id'
                )
            );
        }
    } catch (\Throwable $th) {
        Log::channel('error_logs')->info($th->getMessage(), [ 'user_id' => \Auth::id() ]);
        throw $th;
    }
}

<script>

    $(() => {
        $("#yearpicker").datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years",
            autoclose:true //to close picker once year is selected
        });
    });

    var year_picker = "{{ $year_picker }}";
    var dashboard = "report_nsb_region_rtb_forecast";

    function loadTableDash(year_picker_value, dashboard_value) {
        
        $(".table-performance table").remove();

        var year_picker = year_picker_value;
        var dashboard = dashboard_value;

        $.ajax({
            url: 'get-nsb-dashboard',
            data: {
                year_picker : year_picker,
                dashboard : dashboard
            },
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (resp) {
                if (resp.error) {
                    alert(resp.message);
                } else {
                    const results = resp.message;
                    

                    const pivotGrid = $('#pivotgrid').dxPivotGrid({
                        allowSortingBySummary: true,
                        allowFiltering: true,
                        showBorders: true,
                        showRowTotals: true,
                        showColumnTotals: true,
                        allowExpandAll: true,
                        fieldChooser: {
                            enabled: true,
                            height: 400,
                        },
                        dataSource: {
                            fields: [{
                                caption: 'region',
                                dataField: 'region',
                                width: 150,
                                area: 'row',
                                sortBySummaryField: 'region',
                            }, {
                                caption: 'rtb_month_name',
                                width: 50,
                                dataField: 'rtb_month_name',
                                area: 'column',
                                sortBySummaryField: 'rtb_month_name',
                            }, {
                                caption: 'rtb_count',
                                dataField: 'rtb_count',
                                dataType: 'number',
                                summaryType: 'sum',
                                area: 'data',
                            }],
                            store: results,
                        },
                    }).dxPivotGrid('instance');
                }

                $("#filter_year_btn").removeAttr("disabled");
                $("#filter_year_btn").text("Filter");
            },
            beforeSend: function ( xhr ) {
                $(".table-performance").append("<h1>Processing...</h1>")
            },
            error: function (resp) {
                alert(resp.message);

                $("#filter_year_btn").removeAttr("disabled");
                $("#filter_year_btn").text("Filter");
            },

        });
    }
    window.onload = loadTableDash(year_picker, dashboard);

    

    $("#filter_year_btn").on("click", function () {

        $(this).attr("disabled", "disabled");
        $(this).text("Processing...");

        var year_picker = $("#yearpicker").val();
        var dashboard = "report_nsb_region_rtb_forecast";

        loadTableDash( year_picker, dashboard );
    });
    
</script>
