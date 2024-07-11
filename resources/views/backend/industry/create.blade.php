@extends('layouts.admin')
@section('title', 'Create Industry')

@section('custom_css')

@endsection

@section('main-content')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Create Industry</h4>
            </div>
           
        </div>
    </div>

    <div class="row">
     
        <div class="col-12">

            <div class="card">
                <div class="card-body">
                    <form id="addIndustryForm" method="post">
                        @csrf
                    
                        @include('backend.industry._form')

                        <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
                    </form>
                </div>

              

            </div>
            
        </div> <!-- end col -->
    </div> 
    <!-- end row -->
   


@endsection

@section('custom_js')

<script>
   
   $(document).on('keyup', '#industry_name', function(e) {
        e.preventDefault();

        var name = $(this).val();

        $.ajax({
            type: 'GET',
            url: "{{ route('admin.industries.generateSlug') }}",
            data:{name:name},
            success: function(response) {

                // console.log(response);

                $('#slug').val(response);

            },
            error: function(xhr, status, error) {
                console.log(xhr);
                if(xhr.responseJSON.error_type == 'something_error'){
                    // toasterAlert('error',response.responseJSON.error);
                } 
            }
        });

   });

   $(document).on('submit', '#addIndustryForm', function(e) {
        e.preventDefault();

        $(".submitBtn").attr('disabled', true);

        $('.validation-error-block').remove();

        $(".loader-div").css('display', 'block');

        var formData = new FormData(this);

        var actionUrl = "{{ route('admin.industries.store') }}";
        
     
        $.ajax({
            type: 'post',
            url: actionUrl,
            dataType: 'json',
            contentType: false,
            processData: false,
            data: formData,
            success: function (response) {
                $(".loader-div").css('display', 'none');

                $(".submitBtn").attr('disabled', false);
                if(response.success) {

                    $('#addIndustryForm')[0].reset();

                    toasterAlert('success',response.message);

                    setTimeout(function() {
                        window.location.href = "{{ route('admin.industries.index') }}";
                    }, 2000);
                }
            },
            error: function (response) {
                // console.log(response);
                $(".loader-div").css('display', 'none');

                $(".submitBtn").attr('disabled', false);
                if(response.responseJSON.error_type == 'something_error'){
                    toasterAlert('error',response.responseJSON.error);
                } else {
                    var errorLabelTitle = '';
                    $.each(response.responseJSON.errors, function (key, item) {
                        errorLabelTitle = '<span class="validation-error-block">'+item[0]+'</sapn>';

                        if (/^\w+\.\d+\.\w+$/.test(key)) {
                            
                            var keys = key.split('.');

                            var transformedKey = keys[0]+'['+keys[1]+']'+'['+keys[2]+']';
                            
                            $(errorLabelTitle).insertAfter("input[name='"+transformedKey+"']");

                        }else{
                            $(errorLabelTitle).insertAfter("input[name='"+key+"']");
                        }

                    });
                }
            },
            complete: function(res){
                $(".submitBtn").attr('disabled', false);
            }
        });
        
        
    });

    
    
</script>

@endsection