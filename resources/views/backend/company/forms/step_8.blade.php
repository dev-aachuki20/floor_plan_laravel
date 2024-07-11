<div id="step8Section" class="tab-pane fade show active">
  
    <form id="stepForm8" method="post"  class="step-form" data-step-form-no="8">
        @csrf
        <div class="row">
           
            <div class="mb-3 col-md-12">

                <h3>Reviews</h3>
                <p>This is the Reviews section.</p>

            </div>

        </div>

        <ul class="pager wizard mb-0 list-inline">
            <li class="previous list-inline-item">
                <button type="button" class="btn btn-light back-tab" data-prev-step-route="{{ route('admin.companies.stepForm',7) }}"><i class="ri-arrow-left-line me-1"></i> Back </button>
            </li>
            <li class="next list-inline-item float-end">
                <button type="submit"  class="btn btn-success submitBtn">@lang('global.save')<i class="ri-arrow-right-line ms-1"></i></button>
            </li>
        </ul>

    </form>
</div>