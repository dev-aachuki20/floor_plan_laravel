<div id="step7Section" class="tab-pane fade show active">
  
    <form id="stepForm7" method="post"  class="step-form" data-step-form-no="7" enctype="multipart/form-data">
        @csrf

        @php
            $clients = isset($user->company) ? $user->company->clients()->get() : null;
        @endphp

        @if($clients->count() > 0)
        @foreach($clients as $key=>$client)

        <div class="row client-row">
        
            <input type="hidden"  name="clients[{{$key}}][client_id]" value="{{ $client->id }}">

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="client_name" class="form-label">Client Name</label>
                <input type="text"  name="clients[{{$key}}][name]" class="form-control" placeholder="Enter client name" value="{{ $client->name ?? '' }}">
            </div> 
            
            <div class="mb-3  col-md-6 col-lg-4">
                <label class="form-label" for="phone">Profile Image</label>
                {{-- <input type="file" id="image-input" name="clients[{{$key}}][profile_image]" class="form-control fileInputBoth" accept="image/*">
                
                <div class="img-prevarea m-1">
                    @php
                    $logoUrl = asset(config('constant.default.no_image'));    

                    if(isset($client)){

                        if($client->client_profile_image_url){
                            $logoUrl = $client->client_profile_image_url;
                        }
                    }
                    @endphp

                    <img src="{{ $logoUrl  }}" width="100px" height="100px" >
                </div> --}}

                @php
                    $logoUrl = '';    

                    if(isset($client)){

                        if($client->client_profile_image_url){
                            $logoUrl = $client->client_profile_image_url;
                        }
                    }
                @endphp

                <input name="clients[{{$key}}][profile_image]" type="file" class="dropify" id="image-input-{{$key}}" data-height="100"  data-default-file="{{ $logoUrl }}"  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="jpeg png jpg" data-min-file-size-preview="1M" data-max-file-size-preview="3M"  accept="image/jpeg, image/png, image/jpg" />
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                @if($clients->count() == ($key+1))

                    <button type="button" class="btn btn-primary my-3 p-2 client_btn">+</button>

                @else

                    <button type="button" class="btn btn-danger my-3 p-2 client_btn">-</button>

                @endif
            </div>

        </div>
        @endforeach
        @else

        <div class="row client-row">
        
            <div class="mb-3 col-md-6 col-lg-4">
                <label for="name" class="form-label">Client Name</label>
                <input type="text"  name="clients[0][name]" class="form-control" placeholder="Enter client name" >
            </div> 
            
            <div class="mb-3  col-md-6 col-lg-4">
                <label class="form-label" for="image-input">Profile Image</label>
                {{-- <input type="file" id="image-input" name="clients[0][profile_image]" class="form-control fileInputBoth" accept="image/*">
                
                <div class="img-prevarea m-1">
                    <img src="{{ asset(config('constant.default.no_image'))  }}" width="100px" height="100px" >
                </div> --}}

                <input name="clients[0][profile_image]" type="file" class="dropify" id="image-input-0" data-height="100"  data-default-file=""  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="jpeg png jpg" data-min-file-size-preview="1M" data-max-file-size-preview="3M"  accept="image/jpeg, image/png, image/jpg" />
            </div>


            <div class="mb-3 col-md-6 col-lg-4">
                <button type="button" class="btn btn-primary my-3 p-2 client_btn">+</button>
            </div>

        </div>

        @endif


        <ul class="list-inline wizard mb-0">
            <li class="previous list-inline-item">
                <button type="button" class="btn btn-light back-tab" data-prev-step-route="{{ route('admin.companies.stepForm',6) }}"><i class="ri-arrow-left-line me-1"></i> Back </button>
            </li>
            <li class="next list-inline-item float-end">
                <button type="submit"  class="btn btn-success submitBtn">@lang('global.save')<i class="ri-arrow-right-line ms-1"></i></button>
            </li>
        </ul>

    </form>
</div>