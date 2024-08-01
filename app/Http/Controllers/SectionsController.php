<?php

namespace App\Http\Controllers;

use App\Models\sections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:الاقسام', ['only' => ['index']]);
        $this->middleware('permission:اضافة قسم', ['only' => ['store']]);
        $this->middleware('permission:تعديل قسم', ['only' => ['update']]);
        $this->middleware('permission:حذف قسم', ['only' => ['destroy']]);

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections = sections::all();
        return view('sections.sections',compact('sections'));
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
            'section_name'=>'required|unique:sections|max:255',
            'description'=>'required'
        ],
        [
            'section_name.required' => 'يرجى ادخال اسم القسم ',
            'description.required' => 'يرجى ادخال الوصف ',
            'section_name.unique' => 'اسم القسم مسجل مسبقا '
        ]);
            sections::create([
                'section_name' => $request->section_name,
                'description' => $request->description,
                'created_by' => Auth::user()->name
            ]);
            session()->flash('success','تم اضافة القسم بنجاح');
            return redirect('sections');
    }

    /**
     * Display the specified resource.
     */
    public function show(sections $sections)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(sections $sections)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated_data = $request->validate([
            'section_name'=>'required|max:255|unique:sections,section_name,'.$request->id,
            'description'=>'required'
        ],
            [
                'section_name.required' => 'يرجى ادخال اسم القسم ',
                'description.required' => 'يرجى ادخال الوصف ',
                'section_name.unique' => 'اسم القسم مسجل مسبقا '
            ]);

        sections::findOrFail($request->id)->update([
            'section_name' => $request->section_name,
            'description' => $request->description
        ]);
        session()->flash('edit','تم التعديل بنجاح');
        return redirect('sections');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        sections::findOrFail($request->id)->delete();
        session()->flash('delete','تم حذف القسم بنجاح');
        return redirect()->back();
    }
}
