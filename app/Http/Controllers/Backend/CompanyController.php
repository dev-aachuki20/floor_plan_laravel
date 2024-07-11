<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\CompanyDataTable;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Company\StoreRequest;
use App\Http\Requests\Company\UpdateRequest;
use App\Models\User;
use App\Models\Company;
use App\Models\Industry;
use App\Models\Service;
use Illuminate\Support\Facades\File;


class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CompanyDataTable $dataTable)
    {
        abort_if(Gate::denies('company_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            return $dataTable->render('backend.company.index');
        } catch (\Exception $e) {
            return abort(500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('company_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('backend.company.create');
    }

    public function stepForms(Request $request,$step_no){
        try {

            $user = User::where('uuid',$request->user_id)->first();
            if($user){

                //Services & industries
                /*if($step_no == 3){
                            
                    $path = public_path('default/countries_cities.json');
                    $json = File::get($path);
                    $data = json_decode($json, true);
                    
                    $countries = array_keys($data);

                    $html = view('backend.company.forms.step_'.$step_no,compact('user','countries'))->render();


                }else */
                
                if($step_no == 5){

                    $industries = Industry::get();
                    $services   = Service::get();

                    $html = view('backend.company.forms.step_'.$step_no,compact('user','industries','services'))->render();

                }else{

                    $html = view('backend.company.forms.step_'.$step_no,compact('user'))->render();

                }

            }else{
                $html = view('backend.company.forms.step_'.$step_no)->render();
            }

            return response()->json(['html' => $html]);

        }catch (\Exception $e) {
            // dd($e->getMessage().' '.$e->getFile().' '.$e->getLine());
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        abort_if(Gate::denies('company_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      
        try {
            DB::beginTransaction();

            $user_uuid = '';

            $step_no = $request->step_no;

            $flag = false;

            switch ($step_no) {
                case 1:
                    $fullName = null;

                    if($request->first_name){
                        $fullName = $request->first_name;
                    }

                    if($request->last_name){
                        $fullName = ' '.$request->last_name;
                    }

                    $password = generateRandomString(10);
                    $userRecord = [
                        'first_name' => $request->first_name ?? null,
                        'last_name'  => $request->last_name ?? null,
                        'name'       => $fullName ? ucwords($fullName) : null,
                        'email'      => $request->email ?? null,
                        'phone'      => $request->phone ?? null,
                        // 'password'   => $request->password ? Hash::make($request->password) : null,
                        'password'   =>  Hash::make($password),
                        'status'     => 1,
                    ];
                    $createdUser = User::create($userRecord);

                    if($createdUser){

                        $user_uuid = $createdUser->uuid;

                        $createdUser->roles()->sync([config('constant.roles.company')]);

                        $companyRecords = [
                            'user_id'                   => $createdUser->id,
                            'company_name'              => $request->company_name ?? null,
                            'slug'                      => $request->company_name ? generateSlug($request->company_name,'companies') : null,
                            'company_website'           => $request->company_website ?? null, 
                            'total_employees'           => $request->total_employees ?? null, 
                            'founding_year'             => $request->founding_year ?? null, 
                            'tagline'                   => $request->tagline ?? null, 
                            'admin_contact_phone'       => $request->admin_contact_phone ?? null, 
                            'sales_email'               => $request->sales_email ?? null,
                        ];

                        $createdCompany = Company::create($companyRecords);

                        if($createdCompany){
                            if($request->has('company_logo')){
                                $uploadId = null;
                                $actionType = 'save';
                                uploadImage($createdCompany, $request->company_logo, 'companies/logo',"company_logo", 'original', $actionType, $uploadId);
                            }

                            $flag = true;
                        }
                        
                    }

                    break;

                case 2:

                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){
                        $user_uuid = $findUser->uuid;
                        $findUser->company()->update(['description'=>$request->description]);
                        $flag = true;
                    }

                    break;

                case 3:
                   
                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){
                        $user_uuid = $findUser->uuid;
                        //Start Delete exists records
                        if(isset($request->deleted_locations)){
                            $deletedIds = explode(',',$request->deleted_locations);
                            
                            $findUser->company->locations()->whereIn('id',$deletedIds)->delete();
                        }
                        //End Delete exists records

                        if($request->location){

                            foreach($request->location as $locationData){

                                if(isset($locationData['location_id'])){
                                    $locationId = $locationData['location_id'];

                                    $collection = collect($locationData);

                                    $locationData =  $collection->except(['location_id']);

                                    $locationData = $locationData->toArray();
                                  
                                    $findUser->company->locations()->where('id',$locationId)->update($locationData);
                                }else{
                                    $locationData['company_id'] = $findUser->company->id;
                                    $findUser->company->locations()->create($locationData);
                                }

                            }

                            $flag = true;
                        }
                       
                    }
                    break;

                case 4:
                 
                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){
                        $user_uuid = $findUser->uuid;
                        $projectRecords = [
                            'minimum_project_size' => $request->minimum_project_size,
                            'average_hourly_rate' => $request->average_hourly_rate,
                        ];

                        if($findUser->company->project){
                            $findUser->company->project()->update($projectRecords);
                        }else{
                            $findUser->company->project()->create($projectRecords);
                        }

                        $flag = true;
                    }

                    break;
                case 5:
                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){
                        $user_uuid = $findUser->uuid;
                        
                        $company = $findUser->company;

                        if(isset($request->services)){
                            $company->services()->sync($request->services); 
                        }

                        if(isset($request->industries)){
                            $company->industries()->sync($request->industries); 
                        }
                       
                        $flag = true;
                    }
                    
                    break;
                case 6:
                   
                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){
                        $user_uuid = $findUser->uuid;

                        //Start Delete exists records
                        if(isset($request->deleted_portfolios)){
                            $deletedIds = explode(',',$request->deleted_portfolios);
                            
                            $findUser->company->portfolios()->whereIn('id',$deletedIds)->delete();

                            /*$allClients = $findUser->company->clients()->whereIn('id',$deletedIds)->get();
                            foreach($allClients as $client){
                                $uploadImageId = $client->clientProfileImage ? $client->clientProfileImage->id : null;
                                if($uploadImageId){
                                    deleteFile($uploadImageId);
                                }
                                $client->delete();
                            }*/
                        }
                        //End Delete exists records

                        if($request->portfolios){

                            foreach($request->portfolios as $portfolioData){

                                if(isset($portfolioData['portfolio_id'])){
                                    $portfolioId = $portfolioData['portfolio_id'];

                                    $collection = collect($portfolioData);

                                    $portfolioData =  $collection->except(['portfolio_id']);

                                    $portfolioData = $portfolioData->toArray();
                                  
                                    $portfolio = $findUser->company->portfolios()->where('id',$portfolioId)->first();

                                    $portfolio->update($portfolioData);

                                    if(isset($portfolioData['screenshot'])){
                                       
                                        if(!is_null($portfolioData['screenshot'])){

                                            $uploadImageId = $portfolio->screenshot ? $portfolio->screenshot->id : null;
                                            uploadImage($portfolio, $portfolioData['screenshot'], 'portfolios/screenshots',"screenshot", 'original', $portfolio->screenshot ? 'update' : 'save', $uploadImageId ? $uploadImageId : null);

                                        }
                                       
                                    }

                                }else{
                                    $locationData['company_id'] = $findUser->company->id;
                                    $createdPortfolio = $findUser->company->portfolios()->create($portfolioData);

                                    if($createdPortfolio){
                                        if(isset($portfolioData['screenshot'])){
                                       
                                            if(!is_null($portfolioData['screenshot'])){
                                                $uploadId = null;
                                                $actionType = 'save';
                                                uploadImage($createdPortfolio, $portfolioData['screenshot'], 'portfolios/screenshots',"screenshot", 'original', $actionType, $uploadId);
                                            }
                                        }

                                    }
                                }

                                

                            }

                            $flag = true;
                        }
                       
                    }

                    break;
                case 7:
                   
                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){
                        $user_uuid = $findUser->uuid;
                        
                        //Start Delete exists records
                        if(isset($request->deleted_clients)){
                            $deletedIds = explode(',',$request->deleted_clients);
                            
                            $findUser->company->clients()->whereIn('id',$deletedIds)->delete();

                            /*$allClients = $findUser->company->clients()->whereIn('id',$deletedIds)->get();
                            foreach($allClients as $client){
                                $uploadImageId = $client->clientProfileImage ? $client->clientProfileImage->id : null;
                                if($uploadImageId){
                                    deleteFile($uploadImageId);
                                }
                                $client->delete();
                            }*/
                        }
                        //End Delete exists records

                        if($request->clients){

                            foreach($request->clients as $clientData){

                                if(isset($clientData['client_id'])){
                                    $clientId = $clientData['client_id'];

                                    $collection = collect($clientData);

                                    $clientData =  $collection->except(['client_id']);

                                    $clientData = $clientData->toArray();
                                  
                                    $client = $findUser->company->clients()->where('id',$clientId)->first();

                                    $client->update($clientData);

                                    if(isset($clientData['profile_image'])){
                                       
                                        if(!is_null($clientData['profile_image'])){

                                            $uploadImageId = $client->clientProfileImage ? $client->clientProfileImage->id : null;
                                            uploadImage($client, $clientData['profile_image'], 'clients/profile-image',"client_profile_image", 'original', $client->clientProfileImage ? 'update' : 'save', $uploadImageId ? $uploadImageId : null);

                                        }
                                       
                                    }

                                }else{
                                    $clientData['company_id'] = $findUser->company->id;
                                    $createdClient = $findUser->company->clients()->create($clientData);

                                    if($createdClient){
                                        if(isset($clientData['profile_image'])){
                                       
                                            if(!is_null($clientData['profile_image'])){
                                                $uploadId = null;
                                                $actionType = 'save';
                                                uploadImage($createdClient, $clientData['profile_image'], 'clients/profile-image',"client_profile_image", 'original', $actionType, $uploadId);
                                            }
                                        }

                                    }
                                }

                                

                            }

                            $flag = true;
                        }
                       
                    }
                    break;
                case 8:
                    
                    $flag = true;
                    
                    break;
                default:
                    $flag = false;
                    break;
            }
            

            if($flag){
                DB::commit();

                $message = 'Company '.trans('messages.crud.add_record');
                if($step_no != 1){
                    $message = 'Company '.trans('messages.crud.update_record');
                }

                return response()->json([
                    'success'    => true,
                    'nextStep'   => (int)$request->step_no + 1,
                    'user_uuid'  => $user_uuid,
                    'message'    => $message,
                ]);

            }else{
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
           
        }catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage().' '.$e->getFile().' '.$e->getLine());
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $userUuid)
    {
        $user = User::where('uuid',$userUuid)->first();
        if($user){
            
            $industries = Industry::get();
            $services   = Service::get();

            return view('backend.company.show',compact('user','industries','services'));
        }else{
            return abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $userUuid)
    {
        abort_if(Gate::denies('company_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = User::where('uuid',$userUuid)->first();
        if($user){
            $user_uuid = $user->uuid;

            return view('backend.company.edit',compact('user_uuid'));
        }else{
            return abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $userUuid)
    {
        abort_if(Gate::denies('company_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      
        try {
            DB::beginTransaction();

            $user_uuid = $userUuid;

            $step_no = $request->step_no;

            $flag = false;

            switch ($step_no) {
                case 1:
                    $fullName = null;

                    if($request->first_name){
                        $fullName = $request->first_name;
                    }

                    if($request->last_name){
                        $fullName = ' '.$request->last_name;
                    }

                    $password = generateRandomString(10);
                    $userRecord = [
                        'first_name' => $request->first_name ?? null,
                        'last_name'  => $request->last_name ?? null,
                        'name'       => $fullName ? ucwords($fullName) : null,
                        'email'      => $request->email ?? null,
                        'phone'      => $request->phone ?? null,
                        'status'     => 1,
                    ];

                    $finduser = User::where('uuid',$user_uuid)->first();
                    $updatedUser = $finduser->update($userRecord);

                    if($finduser){

                        $companyRecords = [
                            'company_name'              => $request->company_name ?? null,
                            'slug'                      => $request->company_name ? generateSlug($request->company_name,'companies',$finduser->company->id) : null,
                            'company_website'           => $request->company_website ?? null, 
                            'total_employees'           => $request->total_employees ?? null, 
                            'founding_year'             => $request->founding_year ?? null, 
                            'tagline'                   => $request->tagline ?? null, 
                            'admin_contact_phone'       => $request->admin_contact_phone ?? null, 
                            'sales_email'               => $request->sales_email ?? null,
                        ];

                        $updatedCompany = $finduser->company()->update($companyRecords);

                        if($updatedCompany){
                            if($request->has('company_logo')){
                                $uploadId = $finduser->company->companyLogo ? $finduser->company->companyLogo->id : null;
                                $actionType = $uploadId ? 'update' :'save';
                                uploadImage($finduser->company, $request->company_logo, 'companies/logo',"company_logo", 'original', $actionType, $uploadId);
                            }

                            $flag = true;
                        }
                        
                    }

                    break;

                case 2:

                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){
                        $findUser->company()->update(['description'=>$request->description]);
                        $flag = true;
                    }

                    break;

                case 3:
                   
                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){
                        //Start Delete exists records
                        if(isset($request->deleted_locations)){
                            $deletedIds = explode(',',$request->deleted_locations);
                            
                            $findUser->company->locations()->whereIn('id',$deletedIds)->delete();
                        }
                        //End Delete exists records

                        if($request->location){

                            foreach($request->location as $locationData){

                                if(isset($locationData['location_id'])){
                                    $locationId = $locationData['location_id'];

                                    $collection = collect($locationData);

                                    $locationData =  $collection->except(['location_id']);

                                    $locationData = $locationData->toArray();
                                  
                                    $findUser->company->locations()->where('id',$locationId)->update($locationData);
                                }else{
                                    $locationData['company_id'] = $findUser->company->id;
                                    $findUser->company->locations()->create($locationData);
                                }

                            }

                            $flag = true;
                        }
                       
                    }
                    break;

                case 4:
                 
                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){

                        $projectRecords = [
                            'minimum_project_size' => $request->minimum_project_size,
                            'average_hourly_rate' => $request->average_hourly_rate,
                        ];

                        if($findUser->company->project){
                            $findUser->company->project()->update($projectRecords);
                        }else{
                            $findUser->company->project()->create($projectRecords);
                        }

                        $flag = true;
                    }

                    break;
                case 5:

                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){
                        $company = $findUser->company;

                        if(isset($request->services)){
                            $company->services()->sync($request->services); 
                        }

                        if(isset($request->industries)){
                            $company->industries()->sync($request->industries); 
                        }
                       
                        $flag = true;
                    }
                    
                    break;
                case 6:
                   
                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){

                        //Start Delete exists records
                        if(isset($request->deleted_portfolios)){
                            $deletedIds = explode(',',$request->deleted_portfolios);
                            
                            $findUser->company->portfolios()->whereIn('id',$deletedIds)->delete();

                            /*$allClients = $findUser->company->clients()->whereIn('id',$deletedIds)->get();
                            foreach($allClients as $client){
                                $uploadImageId = $client->clientProfileImage ? $client->clientProfileImage->id : null;
                                if($uploadImageId){
                                    deleteFile($uploadImageId);
                                }
                                $client->delete();
                            }*/
                        }
                        //End Delete exists records

                        if($request->portfolios){

                            foreach($request->portfolios as $portfolioData){

                                if(isset($portfolioData['portfolio_id'])){
                                    $portfolioId = $portfolioData['portfolio_id'];

                                    $collection = collect($portfolioData);

                                    $portfolioData =  $collection->except(['portfolio_id']);

                                    $portfolioData = $portfolioData->toArray();
                                  
                                    $portfolio = $findUser->company->portfolios()->where('id',$portfolioId)->first();

                                    $portfolio->update($portfolioData);

                                    if(isset($portfolioData['screenshot'])){
                                       
                                        if(!is_null($portfolioData['screenshot'])){

                                            $uploadImageId = $portfolio->screenshot ? $portfolio->screenshot->id : null;
                                            uploadImage($portfolio, $portfolioData['screenshot'], 'portfolios/screenshots',"screenshot", 'original', $portfolio->screenshot ? 'update' : 'save', $uploadImageId ? $uploadImageId : null);

                                        }
                                       
                                    }

                                }else{
                                    $locationData['company_id'] = $findUser->company->id;
                                    $createdPortfolio = $findUser->company->portfolios()->create($portfolioData);

                                    if($createdPortfolio){
                                        if(isset($portfolioData['screenshot'])){
                                       
                                            if(!is_null($portfolioData['screenshot'])){
                                                $uploadId = null;
                                                $actionType = 'save';
                                                uploadImage($createdPortfolio, $portfolioData['screenshot'], 'portfolios/screenshots',"screenshot", 'original', $actionType, $uploadId);
                                            }
                                        }

                                    }
                                }

                                

                            }

                            $flag = true;
                        }
                       
                    }

                    break;
                case 7:
                   
                    $findUser = User::where('uuid',$request->user_id)->first();
                    if($findUser){

                        //Start Delete exists records
                        if(isset($request->deleted_clients)){
                            $deletedIds = explode(',',$request->deleted_clients);
                            
                            $findUser->company->clients()->whereIn('id',$deletedIds)->delete();

                            /*$allClients = $findUser->company->clients()->whereIn('id',$deletedIds)->get();
                            foreach($allClients as $client){
                                $uploadImageId = $client->clientProfileImage ? $client->clientProfileImage->id : null;
                                if($uploadImageId){
                                    deleteFile($uploadImageId);
                                }
                                $client->delete();
                            }*/
                        }
                        //End Delete exists records

                        if($request->clients){

                            foreach($request->clients as $clientData){

                                if(isset($clientData['client_id'])){
                                    $clientId = $clientData['client_id'];

                                    $collection = collect($clientData);

                                    $clientData =  $collection->except(['client_id']);

                                    $clientData = $clientData->toArray();
                                  
                                    $client = $findUser->company->clients()->where('id',$clientId)->first();

                                    $client->update($clientData);

                                    if(isset($clientData['profile_image'])){
                                       
                                        if(!is_null($clientData['profile_image'])){

                                            $uploadImageId = $client->clientProfileImage ? $client->clientProfileImage->id : null;
                                            uploadImage($client, $clientData['profile_image'], 'clients/profile-image',"client_profile_image", 'original', $client->clientProfileImage ? 'update' : 'save', $uploadImageId ? $uploadImageId : null);

                                        }
                                       
                                    }

                                }else{
                                    $clientData['company_id'] = $findUser->company->id;
                                    $createdClient = $findUser->company->clients()->create($clientData);

                                    if($createdClient){
                                        if(isset($clientData['profile_image'])){
                                       
                                            if(!is_null($clientData['profile_image'])){
                                                $uploadId = null;
                                                $actionType = 'save';
                                                uploadImage($createdClient, $clientData['profile_image'], 'clients/profile-image',"client_profile_image", 'original', $actionType, $uploadId);
                                            }
                                        }

                                    }
                                }

                                

                            }

                            $flag = true;
                        }
                       
                    }
                    break;
                case 8:

                    $flag = true;
                    
                    break;
                default:
                    $flag = false;
                    break;
            }
            

            if($flag){
                DB::commit();

                $message = 'Company '.trans('messages.crud.update_record');
                
                return response()->json([
                    'success'    => true,
                    'nextStep'   => (int)$request->step_no + 1,
                    'user_uuid'  => $user_uuid,
                    'message'    => $message,
                ]);

            }else{
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
           
        }catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage().' '.$e->getFile().' '.$e->getLine());
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $userUuid)
    {
        abort_if(Gate::denies('company_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $finduser = User::where('uuid',$userUuid)->first();

            DB::beginTransaction();
            try {

                $finduser->company->delete();
                $finduser->delete();
                
                DB::commit();
                $response = [
                    'success'    => true,
                    'message'    => 'Company '.trans('messages.crud.delete_record'),
                ];
                return response()->json($response);

            } catch (\Exception $e) {
                DB::rollBack();                
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    }


    public function getCountries()
    {
        $path = public_path('default/countries_cities.json');
        $json = File::get($path);
        $data = json_decode($json, true);
        
        $countries = array_keys($data);

        return response()->json($countries);
    }

    public function getCities(Request $request)
    {
        $path = public_path('default/countries_cities.json');
        $json = File::get($path);
        $data = json_decode($json, true);
        
        $country = $request->input('country');
        $cities = isset($data[$country]) ? $data[$country] : [];

        return response()->json($cities);
    }
}
