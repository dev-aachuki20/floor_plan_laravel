@extends('layouts.admin')
@section('title', 'Companies')

@section('custom_css')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">

@endsection

@section('main-content')

    <div class="row">
        <div class="col-12 d-flex justify-content-between">
            <div class="page-title-box">
                <h4 class="page-title">Companies</h4>
            </div>
            <div class="my-3">
                <a href="{{ route('admin.companies.create') }}"  class="btn btn-primary">Create</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive-sm">
                       
                        {{$dataTable->table(['class' => 'table mb-0', 'style' => 'width:100%;'])}}
                           
                    </div> 
                </div>
            </div> 
        </div> 
    </div>
   


@endsection

@section('custom_js')
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

{!! $dataTable->scripts() !!}

<script>

@can('company_delete')
    $(document).on("click",".deleteCompanyBtn", function() {
        var url = $(this).data('href');
        Swal.fire({
            title: "{{ trans('global.areYouSure') }}",
            text: "{{ trans('global.onceClickedRecordDeleted') }}",
            icon: "warning",
            showDenyButton: true,  
            //   showCancelButton: true,  
            confirmButtonText: "{{ trans('global.swl_confirm_button_text') }}",  
            denyButtonText: "{{ trans('global.swl_deny_button_text') }}",
        })
        .then(function(result) {
            if (result.isConfirmed) {  
                $('.loader-div').show();
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    dataType: 'json',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        if(response.success) {
                            $('.loader-div').hide();

                            $('#company-table').DataTable().ajax.reload(null, false);
                            toasterAlert('success',response.message);
                        }
                        else {
                            toasterAlert('error',response.error);
                            $('.loader-div').hide();
                        }
                    },
                    error: function(res){
                        toasterAlert('error',res.responseJSON.error);
                    }
                });
            }
        });
    });
@endcan

</script>

@endsection