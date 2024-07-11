<div id="step3Section" class="tab-pane fade show active">
  
    <form id="stepForm3" method="post"  class="step-form" data-step-form-no="3">
        @csrf

        @php
          $locations = isset($user->company) ? $user->company->locations()->get() : null;
        @endphp

        @if($locations->count() > 0)
          @foreach($locations as $key=>$location)
          
            <div class="row location-row">
            
                <input type="hidden"  name="location[{{$key}}][location_id]" value="{{ $location->id }}">

                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="country" class="form-label">Country</label>
                    <input type="text"  name="location[{{$key}}][country]" class="form-control" placeholder="Enter country" value="{{ $location->country ?? '' }}">
                </div> 
                
                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="city" class="form-label">City</label>
                    <input type="text" name="location[{{$key}}][city]" class="form-control" placeholder="Enter city" value="{{ $location->city ?? '' }}">
                </div>

                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="street" class="form-label">Street</label>
                    <input type="street"  name="location[{{$key}}][street]" class="form-control" placeholder="Enter street" value="{{ $location->street ?? '' }}">
                </div>

                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="total_employees" class="form-label">Total employees</label>
                    <input type="text"  name="location[{{$key}}][total_employees]" class="form-control" placeholder="Enter total employees" value="{{ $location->total_employees ?? '' }}"> 
                </div>

                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="contact_number" class="form-label">Contact Number</label>
                    <input type="text"  name="location[{{$key}}][contact_number]" class="form-control" placeholder="Enter contact number" value="{{ $location->contact_number ?? '' }}">
                </div>

                <div class="mb-3 col-md-6 col-lg-4">

                    @if($locations->count() == ($key+1))

                        <button type="button" class="btn btn-primary my-3 p-2 location_btn">+</button>

                    @else

                        <button type="button" class="btn btn-danger my-3 p-2 location_btn">-</button>

                    @endif
                </div>

            </div>
          @endforeach
        @else

        <div class="row location-row">
           
            <div class="mb-3 col-md-6 col-lg-4">
                <label for="country" class="form-label">Country</label>
                <input type="text"  name="location[0][country]" class="form-control" placeholder="Enter country">
            </div> 
            
            <div class="mb-3 col-md-6 col-lg-4">
                <label for="city" class="form-label">City</label>
                <input type="text" name="location[0][city]" class="form-control" placeholder="Enter city">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="street" class="form-label">Street</label>
                <input type="street"  name="location[0][street]" class="form-control" placeholder="Enter street">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="total_employees" class="form-label">Total employees</label>
                <input type="text"  name="location[0][total_employees]" class="form-control" placeholder="Enter total employees">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="contact_number" class="form-label">Contact Number</label>
                <input type="text"  name="location[0][contact_number]" class="form-control" placeholder="Enter contact number">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <button type="button" class="btn btn-primary my-3 p-2 location_btn">+</button>
            </div>

        </div>
        
        @endif

        

        <ul class="list-inline wizard mb-0">
            <li class="previous list-inline-item">
                <button type="button" class="btn btn-light back-tab" data-prev-step-route="{{ route('admin.companies.stepForm',2) }}"><i class="ri-arrow-left-line me-1"></i> Back </button>
            </li>
            <li class="next list-inline-item float-end">
                <button type="submit"  class="btn btn-success submitBtn">@lang('global.save')<i class="ri-arrow-right-line ms-1"></i></button>
            </li>
        </ul>

    </form>
</div>