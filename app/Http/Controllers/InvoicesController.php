<?php

namespace App\Http\Controllers;

use App\Models\invoice_attachments;
use App\Models\invoices;
use App\Models\invoices_details;
use App\Models\sections;
use App\Models\User;
use App\Notifications\Add_new_invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Exports\InvoicesExport;
use Maatwebsite\Excel\Facades\Excel;
class InvoicesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:قائمة الفواتير', ['only' => ['index']]);
        $this->middleware('permission:اضافة فاتورة', ['only' => ['create','store']]);
        $this->middleware('permission:تعديل الفاتورة', ['only' => ['edit','update']]);
        $this->middleware('permission:حذف الفاتورة', ['only' => ['destroy']]);
        $this->middleware('permission:ارشفة الفاتورة', ['only' => ['destroy']]);
        $this->middleware('permission:تصدير EXCEL', ['only' => ['export']]);
        $this->middleware('permission:تغير حالة الدفع', ['only' => ['Status_Update']]);
        $this->middleware('permission:طباعةالفاتورة', ['only' => ['Print_invoice']]);
        $this->middleware('permission:الفواتير المدفوعة', ['only' => ['Invoice_Paid']]);
        $this->middleware('permission:الفواتير الغير مدفوعة', ['only' => ['Invoice_unPaid']]);
        $this->middleware('permission:الفواتير المدفوعة جزئيا', ['only' => ['Invoice_Partial']]);
        $this->middleware('permission:الاشعارات', ['only' => ['MarkAsRead_all']]);

    }

    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $invoices = invoices::all();
        return view('invoices.invoices', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $sections = sections::all();
        return view('invoices.add_invoice', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated_data=$request->validate([
            'invoice_number' => 'required|unique:invoices|max:50',
            'Section' => 'required',
            'product' => 'required',
            'Amount_collection' =>'required|Numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
            'Amount_Commission' =>'required|Numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
            'Discount' =>'required|Numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
            'Rate_VAT' => 'required',
            'pic.*' => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:8192'
        ],
            [
                 'invoice_number.required' => 'يرجى ادخال رقم الفاتورة'
                ,'invoice_number.unique' => 'رقم الفاتورة مسجل بالفعل'
                ,'invoice_number.max' => 'رقم الفاتورة يجب الا يزيد عن 50 حرف'
                ,'Section.required' => 'يرجى تحديد القسم'
                ,'product.required' => 'يرجى تحديد المنتج'
                ,'Amount_collection.required' => 'يرجى تحديد مبلغ التحصيل'
                ,'Amount_collection.numeric' => 'مبلغ التحصيل يجب ان يكون رقما'
                ,'Amount_collection.regex' => 'مبلغ التحصيل يجب الا يزيد عن 8 ارقام صحيحة ورقمين عشريين'
                ,'Amount_Commission.required' => 'يرجى تحديد مبلغ العمولة'
                ,'Amount_Commission.numeric' => 'مبلغ العمولة يجب ان يكون رقما'
                ,'Amount_Commission.regex' => 'مبلغ العمولة يجب الا يزيد عن 8 ارقام صحيحة ورقمين عشريين'
                ,'Discount.required' => 'يرجى تحديد مبلغ الخصم (ادخل 0 في حالة عدم وجود خصم)'
                ,'Discount.numeric' => 'مبلغ الخصم يجب ان يكون رقما'
                ,'Discount.regex' => 'مبلغ الخصم يجب الا يزيد عن 8 ارقام صحيحة ورقمين عشريين'
                ,'Rate_VAT.required' => 'يرجى تحديد نسبة الضريبة'
                ,'pic.*.file' => 'يرجى ادخال ملف صالح '
                ,'pic.*.mimes' => 'صيغة الملف يجب ان تكون واحدة من الصيغ الاتيه:- jpeg,jpg,png,pdf'
                ,'pic.*.max' => 'حجم الملف يجب الا يزيد عن 8 ميجابايت'
            ]
        );
        invoices::create([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
        ]);

        $invoice_id = invoices::latest()->first()->id;
        invoices_details::create([
            'id_Invoice' => $invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
            'user' => (Auth::user()->name),
        ]);

        if ($request->hasFile('pic')) {
            if(count($request->pic) > 3){
                session()->flash('error','أقصى عدد للمرفقات هو 3 برجاء اعادة المحاولة');
                return back();
            }
//            Better Use Queue for this part for performance
            foreach ($request->file('pic') as $item){
                $invoice_id = Invoices::latest()->first()->id;
                $image = $item;
                $file_name = $image->getClientOriginalName();
                $invoice_number = $request->invoice_number;

                $attachments = new invoice_attachments();
                $attachments->file_name = $file_name;
                $attachments->invoice_number = $invoice_number;
                $attachments->Created_by = Auth::user()->name;
                $attachments->invoice_id = $invoice_id;
                $attachments->save();

                // move pic
                $imageName = $item->getClientOriginalName();
                $item->move(public_path('Attachments/' . $invoice_number), $imageName);
            }

        }
        $user = User::All();
        Notification::send($user, new Add_new_invoice($invoice_id));
        session()->flash('Add', 'تم اضافة الفاتورة بنجاح');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $invoices = invoices::where('id', $id)->first();
        return view('invoices.status_update', compact('invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $invoices = invoices::where('id', $id)->first();
        $sections = sections::all();
        return view('invoices.edit_invoice', compact('sections', 'invoices'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated_data=$request->validate([
            'invoice_number' => 'required|max:50|unique:invoices,invoice_number,'.$request->invoice_id,
            'Section' => 'required',
            'product' => 'required',
            'Amount_collection' =>'required|Numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
            'Amount_Commission' =>'required|Numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
            'Discount' =>'required|Numeric|regex:/^\d{1,8}(\.\d{1,2})?$/',
            'Rate_VAT' => 'required',
        ],
            [
                'invoice_number.required' => 'يرجى ادخال رقم الفاتورة'
                ,'invoice_number.unique' => 'رقم الفاتورة مسجل بالفعل'
                ,'invoice_number.max' => 'رقم الفاتورة يجب الا يزيد عن 50 حرف'

                ,'Section.required' => 'يرجى تحديد القسم'
                ,'product.required' => 'يرجى تحديد المنتج'
                ,'Amount_collection.required' => 'يرجى تحديد مبلغ التحصيل'
                ,'Amount_collection.numeric' => 'مبلغ التحصيل يجب ان يكون رقما'
                ,'Amount_collection.regex' => 'مبلغ التحصيل يجب الا يزيد عن 8 ارقام صحيحة ورقمين عشريين'

                ,'Amount_Commission.required' => 'يرجى تحديد مبلغ العمولة'
                ,'Amount_Commission.numeric' => 'مبلغ العمولة يجب ان يكون رقما'
                ,'Amount_Commission.regex' => 'مبلغ العمولة يجب الا يزيد عن 8 ارقام صحيحة ورقمين عشريين'

                ,'Discount.required' => 'يرجى تحديد مبلغ الخصم (ادخل 0 في حالة عدم وجود خصم)'
                ,'Discount.numeric' => 'مبلغ الخصم يجب ان يكون رقما'
                ,'Discount.regex' => 'مبلغ الخصم يجب الا يزيد عن 8 ارقام صحيحة ورقمين عشريين'

                ,'Rate_VAT.required' => 'يرجى تحديد نسبة الضريبة'
            ]
        );
        $invoices = invoices::findOrFail($request->invoice_id);
        $invoices->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'note' => $request->note,
        ]);

        $invoice_detail = invoices_details::where('id_invoice',$request->invoice_id);
        $invoice_detail->update([
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'note' => $request->note,
            'user' => (Auth::user()->name),
        ]);
//        Update Attachment Column and move the included files to a new folder
        $inv_old = $request->invoice_old_number;
        $inv_new = $request->invoice_number;
        if(File::isDirectory(public_path('attachments/'.$request->invoice_old_number)) && ($inv_old != $inv_new)) {

            $attachments = invoice_attachments::where('invoice_id', $request->invoice_id)->get();
            if (!$attachments->count()) {
                $newPath = public_path('attachments/' . $request->invoice_number);
                if (!File::isDirectory($newPath)) {
                    File::makeDirectory($newPath, 0777, true, true);
                }
                $attachmentsPath = public_path('attachments/' . $inv_old);
                File::deleteDirectory($attachmentsPath);

            } else
            {
                foreach ($attachments as $attachment) {
                    $attachment->update([
                        'invoice_number' => $request->invoice_number
                    ]);
                }
                $attachmentsPath = public_path('attachments/' . $inv_old);
                $newPath = public_path('attachments/' . $request->invoice_number);
                $files = File::files($attachmentsPath . '/');

                if (!File::isDirectory($newPath)) {
                    File::makeDirectory($newPath, 0777, true, true);
                }
                foreach ($files as $file) {
                    $fileName = $file->getFilename();
                    File::move($attachmentsPath . '/' . $fileName, $newPath . '/' . $fileName);
                }
                File::deleteDirectory($attachmentsPath);
            }
        }
        session()->flash('edit', 'تم تعديل الفاتورة بنجاح');
        return redirect()->route('invoices.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->invoice_id;
        $invoices = invoices::where('id', $id)->first();
        $Details = invoice_attachments::where('invoice_id', $id)->first();

        $id_page =$request->id_page;

        if (!$id_page==2) {
            DB::table('notifications')->where('data->id','=',$request->invoice_id)->delete();

            if (!empty($Details->invoice_number)) {

                Storage::disk('public_uploads')->deleteDirectory($Details->invoice_number);
            }

            $invoices->forceDelete();
            session()->flash('delete_invoice');
            return redirect('/invoices');

        }

        else {

            $invoices->delete();
            session()->flash('archive_invoice');
            return redirect('/Archive');
        }
    }

    public function getproducts($id)
    {
        $products = DB::table("products")->where("section_id", $id)->pluck("Product_name", "id");
        return json_encode($products);
    }
    public function Status_Update($id, Request $request)
    {
        $request->validate([
            'Status' => 'required',
            'Payment_Date' => 'required|date'
        ],[
            'Status.required' => 'يرجى ادخال حالة الدفع',
            'Payment_Date.required' => 'يرجى ادخال تاريخ الدفع',
            'Payment_Date.date' => 'يرجى ادخال تاريخ صحيح'
        ]);
        $invoices = invoices::findOrFail($id);

        if ($request->Status === 'مدفوعة') {

            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            invoices_Details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }

        else {
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            invoices_Details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invoices');

    }
    public function Invoice_Paid()
    {
        $invoices = Invoices::where('Value_Status', 1)->get();
        return view('invoices.invoices_paid',compact('invoices'));
    }

    public function Invoice_unPaid()
    {
        $invoices = Invoices::where('Value_Status',2)->get();
        return view('invoices.invoices_unpaid',compact('invoices'));
    }

    public function Invoice_Partial()
    {
        $invoices = Invoices::where('Value_Status',3)->get();
        return view('invoices.invoices_Partial',compact('invoices'));
    }
    public function Print_invoice($id)
    {
        $invoices = invoices::where('id', $id)->first();
        return view('invoices.Print_invoice',compact('invoices'));
    }
    public function export()
    {
        return Excel::download(new InvoicesExport, 'قائمة الفواتير.xlsx');
    }
    public function MarkAsRead_all ()
    {
        $userUnreadNotification = auth()->user()->unreadNotifications;
        if ($userUnreadNotification) {
            $userUnreadNotification->markAsRead();
            return back();
        }
    }
    public function markAsRead($notify_id,$inv_id){
        auth()->user()->unreadNotifications->where('id',$notify_id)->markAsRead();
        return to_route('details',$inv_id);
    }
}
