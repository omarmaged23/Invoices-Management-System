<?php

namespace App\Http\Controllers;

use App\Models\invoice_attachments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceAttachmentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:اضافة مرفق', ['only' => ['store']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated_data=$request->validate([
        'pic.*' => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:8192'
    ],
        [
            'pic.*.file' => 'يرجى ادخال ملف صالح '
            ,'pic.*.mimes' => 'صيغة الملف يجب ان تكون واحدة من الصيغ الاتيه:- jpeg,jpg,png,pdf'
            ,'pic.*.max' => 'حجم الملف يجب الا يزيد عن 8 ميجابايت'
        ]
    );

        if ($request->hasFile('pic')) {
            if(count($request->pic) > 3){
                session()->flash('error','أقصى عدد للمرفقات هو 3 برجاء اعادة المحاولة');
                return back();
            }
//            Better Use Queue for this part for performance
            foreach ($request->file('pic') as $item){
                $invoice_id = $request->invoice_id;
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
        session()->flash('Add', 'تم اضافة المرفقات بنجاح');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(invoice_attachments $invoice_attachments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(invoice_attachments $invoice_attachments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, invoice_attachments $invoice_attachments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(invoice_attachments $invoice_attachments)
    {
        //
    }
}
