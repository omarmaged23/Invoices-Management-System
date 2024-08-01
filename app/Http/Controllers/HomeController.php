<?php

namespace App\Http\Controllers;

use App\Models\invoices;
use Illuminate\Http\Request;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
//        Search Database
        $all=invoices::all();
        $unpaid=invoices::where('Value_Status',2)->get();
        $paid=invoices::where('Value_Status',1)->get();
        $paidPartially=invoices::where('Value_Status',3)->get();
//        Get Number of invoices
        $countAll = $all->count();
        $countUnpaid = $unpaid->count();
        $countPaid=$paid->count();
        $countPartiallyPaid=$paidPartially->count();
//        Get Sum of invoices
        $sumAll = $all->sum('Total');
        $sumUnpaid = $unpaid->sum('Total');
        $sumPaid=$paid->sum('Total');
        $sumPartiallyPaid=$paidPartially->sum('Total');
//        Now Setup Charts
        if($countAll == 0){
            $countAll=1;
        }
        $percentagePaid = $countPaid/$countAll  * 100;
        $percentageUnpaid = $countUnpaid/$countAll  * 100;
        $percentagePartiallyPaid = $countPartiallyPaid/$countAll  * 100;

        $chartjs = app()->chartjs
            ->name('barChartTest')
            ->type('bar')
            ->size(['width' => 350, 'height' => 200])
            ->labels(['الفواتير الغير المدفوعة', 'الفواتير المدفوعة','الفواتير المدفوعة جزئيا'])
            ->datasets([
                [
                    "label" => "الفواتير الغير المدفوعة",
                    'backgroundColor' => ['#ec5858'],
                    'data' => [$percentageUnpaid]
                ],
                [
                    "label" => "الفواتير المدفوعة",
                    'backgroundColor' => ['#81b214'],
                    'data' => [$percentagePaid]
                ],
                [
                    "label" => "الفواتير المدفوعة جزئيا",
                    'backgroundColor' => ['#ff9642'],
                    'data' => [$percentagePartiallyPaid]
                ],
            ])
            ->options([
                'scales' => [
                    'yAxes' => [
                        [
                        'ticks' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ]],
                ]
            ]);


        $chartjs_2 = app()->chartjs
            ->name('pieChartTest')
            ->type('pie')
            ->size(['width' => 340, 'height' => 200])
            ->labels(['الفواتير الغير المدفوعة', 'الفواتير المدفوعة','الفواتير المدفوعة جزئيا'])
            ->datasets([
                [
                    'backgroundColor' => ['#ec5858', '#81b214','#ff9642'],
                    'data' => [$percentageUnpaid, $percentagePaid,$percentagePartiallyPaid]
                ]
            ])
            ->options([]);
        return view('home',compact(['countAll','countPaid','countPartiallyPaid','countUnpaid','sumAll'
                                         ,'sumPaid','sumPartiallyPaid','sumUnpaid','percentagePaid'
                                         ,'percentageUnpaid','percentagePartiallyPaid','chartjs','chartjs_2']));
    }
}
