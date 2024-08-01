<?php

namespace App\Http\Controllers;

use App\Models\invoices;
use Illuminate\Http\Request;

class Invoices_Report extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:تقرير الفواتير', ['only' => ['index','Search_invoices']]);
    }

    public function index(){

        return view('reports.invoices_report');

    }

    public function Search_invoices(Request $request){

        $rdio = $request->rdio;
        $type_name='حدد نوع الفواتير';
        if(isset($request->type)){
            switch($request->type){
                case 'مدفوعة':
                    $type_name='الفواتير المدفوعة';
                    break;

                case 'غير مدفوعة':
                    $type_name='الفواتير الغير مدفوعة';
                    break;

                case 'مدفوعة جزئيا':
                    $type_name='الفواتير المدفوعة جزئيا';
                    break;

                default:
                    $type_name=$request->type;
                    break;
            }
        }
        // في حالة البحث بنوع الفاتورة

        if ($rdio == 1) {


            // في حالة عدم تحديد تاريخ
            if ($request->type && $request->start_at =='' && $request->end_at =='') {

                $invoices = invoices::select('*')->where('Status','=',$request->type)->get();
                $type = $request->type;
                return view('reports.invoices_report',compact('type_name'))->withDetails($invoices);
            }

            // في حالة تحديد تاريخ استحقاق
            else {

                $start_at = date($request->start_at);
                $end_at = date($request->end_at);
                $type = $request->type;

                $invoices = invoices::whereBetween('invoice_Date',[$start_at,$end_at])->where('Status','=',$request->type)->get();
                return view('reports.invoices_report',compact('type_name','start_at','end_at'))->withDetails($invoices);

            }



        }

//====================================================================

// في البحث برقم الفاتورة
        else {

            $invoices = invoices::select('*')->where('invoice_number','=',$request->invoice_number)->get();
            return view('reports.invoices_report')->withDetails($invoices);

        }



    }
}
