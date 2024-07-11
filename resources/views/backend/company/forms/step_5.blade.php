<div id="step5Section" class="tab-pane fade show active">

    <form id="stepForm5" method="post"  class="step-form" data-step-form-no="5">
        @csrf

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="fs-15">Services</h6>

                        @if(isset($services))
                          @foreach($services as $key=>$service)

                            @php
                              $checkedTag = '';
                              
                              if(isset($user->company)){
                                
                                $serviceArr = $user->company->services()->pluck('id')->toArray();
                                if(in_array($service->id,$serviceArr)){
                                    $checkedTag = 'checked';
                                }

                              }
                            @endphp
                            
                            <div class="form-check form-checkbox-success mb-2">
                                <input type="checkbox" class="form-check-input" name="services[]" id="customCheckcolor-service{{$key}}" value="{{ $service->id }}" {{ $checkedTag }}>
                                <label class="form-check-label" for="customCheckcolor-service{{$key}}">{{ ucwords($service->name) }}</label>
                            </div>

                           @endforeach

                        @endif

                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="fs-15">Industries</h6>

                        @if(isset($industries))
                          @foreach($industries as $key=>$industry)

                          @php
                            $checkedTag = '';
                            
                            if(isset($user->company)){
                                
                                $industryArr = $user->company->industries()->pluck('id')->toArray();
                                if(in_array($industry->id,$industryArr)){
                                    $checkedTag = 'checked';
                                }

                            }
                          @endphp

                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="industries[]" id="customCheckcolor-industry{{$key}}" value="{{ $industry->id }}" {{ $checkedTag }}>
                                <label class="form-check-label" for="customCheckcolor-industry{{$key}}">{{ ucwords($industry->name) }}</label>
                            </div>

                           @endforeach

                        @endif

                    </div>
                </div>
            </div>
        </div>

        <ul class="list-inline wizard mb-0">
            <li class="previous list-inline-item">
                <button type="button" class="btn btn-light back-tab" data-prev-step-route="{{ route('admin.companies.stepForm',4) }}"><i class="ri-arrow-left-line me-1"></i> Back </button>
            </li>
            <li class="next list-inline-item float-end">
                <button type="submit"  class="btn btn-success submitBtn">@lang('global.save')<i class="ri-arrow-right-line ms-1"></i></button>
            </li>
        </ul>
    </form>

</div>
