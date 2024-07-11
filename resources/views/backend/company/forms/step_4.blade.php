<div id="step4Section" class="tab-pane fade show active">
  
    <form id="stepForm4" method="post"  class="step-form" data-step-form-no="4">
        @csrf

        @php
            $project =  isset($user->company) ? $user->company->project : null;   
        @endphp

        <div class="row">
           
            <div class="mb-3 col-md-6 col-lg-6">
                <label for="minimum_project_size" class="form-label">Minimum Project Size </label>
                <input type="text" id="minimum_project_size" name="minimum_project_size" class="form-control" placeholder="Enter minimum project size" value="{{ $project ? $project->minimum_project_size : ''}}">
            </div>

            <div class="mb-3 col-md-6 col-lg-6">
                <label for="average_hourly_rate" class="form-label">Average Hourly Rate</label>
                <input type="text" id="average_hourly_rate" name="average_hourly_rate" class="form-control" placeholder="Enter average hourly rate" value="{{ $project ? $project->average_hourly_rate : ''}}">
            </div>


        </div>

        <ul class="list-inline wizard mb-0">
            <li class="previous list-inline-item">
                <button type="button" class="btn btn-light back-tab" data-prev-step-route="{{ route('admin.companies.stepForm',3) }}"><i class="ri-arrow-left-line me-1"></i> Back </button>
            </li>
            <li class="next list-inline-item float-end">
                <button type="submit"  class="btn btn-success submitBtn">@lang('global.save')<i class="ri-arrow-right-line ms-1"></i></button>
            </li>
        </ul>

    </form>
</div>