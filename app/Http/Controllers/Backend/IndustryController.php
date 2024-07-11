<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Industry;
use App\DataTables\IndustryDataTable;
use App\Http\Requests\Industry\StoreRequest;
use App\Http\Requests\Industry\UpdateRequest;

use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class IndustryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndustryDataTable $dataTable)
    {
        abort_if(Gate::denies('industry_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            return $dataTable->render('backend.industry.index');
        } catch (\Exception $e) {
            return abort(500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('industry_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('backend.industry.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        abort_if(Gate::denies('industry_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            DB::beginTransaction();

            // $slug = generateSlug($request->name,'services');

            $industry = new Industry();
            $industry->name = $request->name;
            $industry->slug = $request->slug;
        
            $industry->save();

            DB::commit();
          
            return response()->json([
                'success'    => true,
                'message'    => 'Industry '.trans('messages.crud.add_record'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage().' '.$e->getFile().' '.$e->getLine());
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_if(Gate::denies('industry_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      
        $industry = Industry::find($id);
        if($industry){
            return view('backend.industry.edit',compact('industry'));
        }

        return abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        abort_if(Gate::denies('industry_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            DB::beginTransaction();

            // $slug = generateSlug($request->name,'services');

            $industry = Industry::find($id);
            $industry->name = $request->name;
            $industry->slug = $request->slug;
        
            $industry->save();

            DB::commit();
          
            return response()->json([
                'success'    => true,
                'message'    => 'Industry '.trans('messages.crud.update_record'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage().' '.$e->getFile().' '.$e->getLine());
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        abort_if(Gate::denies('industry_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $industry = Industry::where('id', $id)->first();

            DB::beginTransaction();
            try {

                $industry->delete();
                
                DB::commit();
                $response = [
                    'success'    => true,
                    'message'    => 'Industry '.trans('messages.crud.delete_record'),
                ];
                return response()->json($response);

            } catch (\Exception $e) {
                DB::rollBack();                
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    }

    public function generateSlug(Request $request){
        $slug = generateSlug($request->name, 'industries');

        return $slug;
    }
}
