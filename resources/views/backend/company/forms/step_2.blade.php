<div id="step2Section" class="tab-pane fade show active">
  
    <form id="stepForm2" method="post"  class="step-form" data-step-form-no="2">
        @csrf
        <div class="row">
           
            <div class="mb-3 col-md-12">
                <label for="description" class="form-label">Description</label>
                
                {{-- <textarea  class="form-control" id="snow-editor" name="description" placeholder="Type Something.." row="5">{{ (isset($user->company) && !empty($user->company->description)) ? $user->company->description : ''}}</textarea> --}}

                <div id="snow-editor" style="height: 300px;">
                    {!! (isset($user->company) && !empty($user->company->description)) ? $user->company->description : '' !!}
                </div>

                <input type="hidden" id="hidden-input" name="description">

            </div>

        </div>

        <ul class="pager wizard mb-0 list-inline">
            <li class="previous list-inline-item">
                <button type="button" class="btn btn-light back-tab" data-prev-step-route="{{ route('admin.companies.stepForm',1) }}"><i class="ri-arrow-left-line me-1"></i> Back </button>
            </li>
            <li class="next list-inline-item float-end">
                <button type="submit"  class="btn btn-success submitBtn">@lang('global.save')<i class="ri-arrow-right-line ms-1"></i></button>
            </li>
        </ul>

    </form>
</div>