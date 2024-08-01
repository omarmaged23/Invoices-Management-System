<?php

namespace App\Http\Controllers;

use App\Models\products;
use App\Models\sections;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:المنتجات', ['only' => ['index']]);
        $this->middleware('permission:اضافة منتج', ['only' => ['store']]);
        $this->middleware('permission:تعديل منتج', ['only' => ['update']]);
        $this->middleware('permission:حذف منتج', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections = sections::all();
        $products = products::all();
        return view('products.products',compact('sections','products'));
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
        $validated_data = $request->validate([
            'Product_name' => 'required|max:255|unique:products',
            'section_id' => 'required'
        ],[
            'Product_name.required' => 'يرجى ادخال اسم المنتج ',
            'section_id.required' => 'يرجى اختيار القسم ',
            'Product_name.unique' => 'تم اضافة المنتج من قبل '
        ]);
        products::create([
            'Product_name' => $request->Product_name,
            'section_id' =>$request->section_id,
            'description' => $request->description
        ]);
        session()->flash('Add','تم اضافة المنتج بنجاح ');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(products $products)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(products $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated_data = $request->validate([
            'Product_name' => 'required|max:255|unique:products,Product_name'.$request->id
        ],[
            'Product_name.required' => 'يرجى ادخال اسم المنتج ',
            'Product_name.unique' => 'تم اضافة المنتج من قبل '
        ]);
        $id = sections::where('section_name', $request->section_name)->first()->id;
        products::findOrFail($request->pro_id)->update([
            'Product_name' => $request->Product_name,
            'section_id' =>$id,
            'description' => $request->description
        ]);
        session()->flash('Edit','تم تعديل المنتج بنجاح ');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        products::findOrFail($request->pro_id)->delete();
        session()->flash('delete','تم حذف المنتج بنجاح ');
        return redirect()->back();

    }

}
