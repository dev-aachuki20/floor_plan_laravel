<div id="step6Section" class="tab-pane fade show active">
  
    <form id="stepForm6" method="post"  class="step-form" data-step-form-no="6" enctype="multipart/form-data">
        @csrf

        @php
          $portfolios = isset($user->company) ? $user->company->portfolios()->get() : null;
        @endphp

        @if($portfolios->count() > 0)
          @foreach($portfolios as $key=>$portfolio)
          
            <div class="row portfolio-row">
            
                <input type="hidden"  name="portfolios[{{$key}}][portfolio_id]" value="{{ $portfolio->id }}">

                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="client_name" class="form-label">Client Name</label>
                    <input type="text"  name="portfolios[{{$key}}][client_name]" class="form-control" placeholder="Enter client name" value="{{ $portfolio->client_name ?? '' }}">
                </div> 
                
                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="client_website" class="form-label">Client Website</label>
                    <input type="text" name="portfolios[{{$key}}][client_website]" class="form-control" placeholder="Enter client website" value="{{ $portfolio->client_website ?? '' }}">
                </div>

                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="project_title" class="form-label">Project Title</label>
                    <input type="project_title"  name="portfolios[{{$key}}][project_title]" class="form-control" placeholder="Enter project title" value="{{ $portfolio->project_title ?? '' }}">
                </div>

                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="project_industry" class="form-label">Project Industry</label>
                    <input type="text"  name="portfolios[{{$key}}][project_industry]" class="form-control" placeholder="Enter project industry" value="{{ $portfolio->project_industry ?? '' }}"> 
                </div>

                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="timeline" class="form-label">Timeline</label>
                    <input type="text"  name="portfolios[{{$key}}][timeline]" class="form-control" placeholder="Enter timeline" value="{{ $portfolio->timeline ?? '' }}">
                </div>

                <div class="mb-3 col-md-6 col-lg-4">
                    <label for="project_cost" class="form-label">Project Cost</label>
                    <input type="text"  name="portfolios[{{$key}}][project_cost]" class="form-control" placeholder="Enter project cost" value="{{ $portfolio->project_cost ?? '' }}">
                </div>

                <div class="mb-3 col-md-12">
                    <label for="project_description" class="form-label">Project Description</label>
                    <textarea  class="form-control" id="project_description" name="project_description" placeholder="Type Something.." row="5">{{ $portfolio->project_description ?? '' }}</textarea>
                </div>

                <div class="mb-3  col-md-6 col-lg-4">
                    <label class="form-label" for="phone">Screenshot</label>
                    {{-- <input type="file" id="image-input" name="portfolios[{{$key}}][screenshot]" class="form-control fileInputBoth" accept="image/*">
                    
                    <div class="img-prevarea m-1">
                        @php
                         $logoUrl = asset(config('constant.default.no_image'));    
    
                         if(isset($portfolio)){
    
                            if($portfolio->screenshot_url){
                                $logoUrl = $portfolio->screenshot_url;
                            }
                         }
                        @endphp
    
                        <img src="{{ $logoUrl  }}" width="100px" height="100px" >
                    </div> --}}

                    @php
                        $logoUrl = '';    

                        if(isset($portfolio)){

                        if($portfolio->screenshot_url){
                            $logoUrl = $portfolio->screenshot_url;
                        }
                        }
                    @endphp

                    <input name="portfolios[{{$key}}][screenshot]" type="file" class="dropify" id="image-input-{{$key}}" data-height="100"  data-default-file="{{ $logoUrl }}"  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="jpeg png jpg" data-min-file-size-preview="1M" data-max-file-size-preview="3M"  accept="image/jpeg, image/png, image/jpg" />
                </div>

                <div class="mb-3 col-md-6 col-lg-4">
                    @if($portfolios->count() == ($key+1))

                        <button type="button" class="btn btn-primary my-3 p-2 portfolio_btn">+</button>

                    @else

                        <button type="button" class="btn btn-danger my-3 p-2 portfolio_btn">-</button>

                    @endif
                </div>

            </div>
          @endforeach
        @else

        <div class="row portfolio-row">
           
            <div class="mb-3 col-md-6 col-lg-4">
                <label for="client_name" class="form-label">Client Name</label>
                <input type="text"  name="portfolios[0][client_name]" class="form-control" placeholder="Enter client name" >
            </div> 
            
            <div class="mb-3 col-md-6 col-lg-4">
                <label for="client_website" class="form-label">Client Website</label>
                <input type="text" name="portfolios[0][client_website]" class="form-control" placeholder="Enter client website">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="project_title" class="form-label">Project Title</label>
                <input type="project_title"  name="portfolios[0][project_title]" class="form-control" placeholder="Enter project title">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="project_industry" class="form-label">Project Industry</label>
                <input type="text"  name="portfolios[0][project_industry]" class="form-control" placeholder="Enter project industry"> 
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="timeline" class="form-label">Timeline</label>
                <input type="text"  name="portfolios[0][timeline]" class="form-control" placeholder="Enter timeline">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="project_cost" class="form-label">Project Cost</label>
                <input type="text"  name="portfolios[0][project_cost]" class="form-control" placeholder="Enter project cost">
            </div>

            <div class="mb-3 col-md-12">
                <label for="project_description" class="form-label">Project Description</label>
                <textarea  class="form-control" id="project_description" name="portfolios[0][project_description]" placeholder="Type Something.." row="5"></textarea>
            </div>

            <div class="mb-3  col-md-6 col-lg-4">
                <label class="form-label" for="phone">Screenshot</label>
                {{-- <input type="file" id="image-input" name="portfolios[0][screenshot]" class="form-control fileInputBoth" accept="image/*">
                
                <div class="img-prevarea m-1">
                    <img src="{{ asset(config('constant.default.no_image'))  }}" width="100px" height="100px" >
                </div> --}}

                <input name="portfolios[0][screenshot]" type="file" class="dropify" id="image-input-0" data-height="100"  data-default-file=""  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="jpeg png jpg" data-min-file-size-preview="1M" data-max-file-size-preview="3M"  accept="image/jpeg, image/png, image/jpg" />
            </div>


            <div class="mb-3 col-md-6 col-lg-4">
                <button type="button" class="btn btn-primary my-3 p-2 portfolio_btn">+</button>
            </div>

        </div>
        
        @endif

        

        <ul class="list-inline wizard mb-0">
            <li class="previous list-inline-item">
                <button type="button" class="btn btn-light back-tab" data-prev-step-route="{{ route('admin.companies.stepForm',5) }}"><i class="ri-arrow-left-line me-1"></i> Back </button>
            </li>
            <li class="next list-inline-item float-end">
                <button type="submit"  class="btn btn-success submitBtn">@lang('global.save')<i class="ri-arrow-right-line ms-1"></i></button>
            </li>
        </ul>

    </form>
</div>