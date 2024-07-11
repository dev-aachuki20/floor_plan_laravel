<script>
    
    let DeletedRow = { 
        
        clients: [],
        locations: [],
        portfolios: [],
    
    };
    
    
    $(document).ready(function() {
        
        renderStepForm("{{ route('admin.companies.stepForm',1) }}",'step1Section',userObj.id);
    
        $('.navTab').on('click', function(e) {
            e.preventDefault();
    
            var $this = $(this);
    
            var tabRoute = $this.attr('href');
            var tabId = $this.attr('data-tab-type');
    
            renderStepForm(tabRoute,tabId,userObj.id);
    
        });

        //Back button
        $(document).on('click',".back-tab",function(e){
            e.preventDefault();

            var $this = $(this);

            var tabPrevRoute = $this.attr('data-prev-step-route');

            var tabNumber = $this.parents('form').attr('data-step-form-no');

            tabNumber = parseInt(tabNumber) - 1;
            
            var prevtabId = 'step'+tabNumber+'Section';

            renderStepForm(tabPrevRoute,prevtabId,userObj.id);

        });
    
    
        $(document).on('change', ".fileInputBoth",function(e){
            var $this = $(this);
            var files = e.target.files;
            for (var i = 0; i < files.length; i++) {
                var reader2 = new FileReader();
                reader2.onload = function(e) {
                    $this.siblings('.img-prevarea').find('img').attr('src', e.target.result);
                };
                reader2.readAsDataURL(files[i]);
            }
        });
    
        $(document).on('click', '.toggle-password', function () {        
            var passwordInput = $(this).prev('input');  
            // console.log(passwordInput);      
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                $(this).addClass('show-password');
            } else {
                passwordInput.attr('type', 'password');
                $(this).removeClass('show-password');
            }
        });
    
        
      
    // Start Step Form 
    @if(isset($user_uuid))

       $(document).on('submit', '.step-form', function(e) {
            e.preventDefault();
    
            $(".submitBtn").attr('disabled', true);
    
            $('.validation-error-block').remove();
    
            $(".card-disabled").css('display', 'block');
    
            var formData = new FormData(this);
            var stepFormNo = $(this).attr('data-step-form-no');
    
            formData.append('step_no',stepFormNo);
            formData.append('user_id',userObj.id);
    
            if(stepFormNo == 2){
                formData.append('description',$('#hidden-input').val());
            }else if(stepFormNo == 3){
                formData.append('deleted_locations',DeletedRow.locations);
            }else if(stepFormNo == 6){
                formData.append('deleted_portfolios',DeletedRow.portfolios);
            }else if(stepFormNo == 7){
                formData.append('deleted_clients',DeletedRow.clients);
            }
    
            var actionUrl = "{{ route('admin.companies.update',$user_uuid) }}";
            
            if(stepFormNo){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type: 'post',
                    url: actionUrl,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    data: formData,
                    headers: {
                        'X-HTTP-Method-Override': 'PUT' // Spoof the HTTP method
                    },
                    success: function (response) {
                        $(".card-disabled").css('display', 'none');
    
                        $(".submitBtn").attr('disabled', false);
                        if(response.success) {
                            toasterAlert('success',response.message);
    
                            $('.step-form')[0].reset();

                            if(response.nextStep > 8){
                                setTimeout(function() {
                                    window.location.href = "{{ route('admin.companies.index') }}";
                                }, 2000);
                            }
    
                            var $nextTab = $('.nav-link[data-step="'+response.nextStep+'"]');
    
                            $nextTab.removeClass('disabled');
                            // Trigger the next tab
                            $nextTab.tab('show');
    
                            var tabRoute = $nextTab.attr('href');
                            var tabId = $nextTab.attr('data-tab-type');
                           
                            if(response.user_uuid){
                                userObj.id = response.user_uuid;
    
                                renderStepForm(tabRoute,tabId,userObj.id);
                            }else{
                                renderStepForm(tabRoute,tabId);
                            }
                           
                        }
                    },
                    error: function (response) {
                        // console.log(response);
                        $(".card-disabled").css('display', 'none');
    
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
    
                                }else if(key == 'password'){
                                    var elementItem = $("input[name='"+key+"']").parent();    
                                    $(errorLabelTitle).insertAfter(elementItem);
    
                                }else if(key == 'total_employees'/*|| key == 'founding_year'*/){
    
                                    var elementItem = $("select[name='"+key+"']").siblings('span');    
                                    
                                    $(errorLabelTitle).insertAfter(elementItem);
    
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
            }
           
        });

    
    @else

        $(document).on('submit', '.step-form', function(e) {
            e.preventDefault();
    
            $(".submitBtn").attr('disabled', true);
    
            $('.validation-error-block').remove();
    
            $(".card-disabled").css('display', 'block');
    
            var formData = new FormData(this);
            var stepFormNo = $(this).attr('data-step-form-no');
    
            formData.append('step_no',stepFormNo);
            formData.append('user_id',userObj.id);
            
            if(stepFormNo == 2){
                formData.append('description',$('#hidden-input').val());
            }else if(stepFormNo == 3){
                formData.append('deleted_locations',DeletedRow.locations);
            }else if(stepFormNo == 6){
                formData.append('deleted_portfolios',DeletedRow.portfolios);
            }else if(stepFormNo == 7){
                formData.append('deleted_clients',DeletedRow.clients);
            }
    
            var actionUrl = "{{ route('admin.companies.store') }}";
            
            if(stepFormNo){

                $.ajax({
                    type: 'post',
                    url: actionUrl,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function (response) {
                        $(".card-disabled").css('display', 'none');
    
                        $(".submitBtn").attr('disabled', false);
                        if(response.success) {
                            toasterAlert('success',response.message);
    
                            $('.step-form')[0].reset();
    
                            if(response.nextStep > 8){
                                setTimeout(function() {
                                    window.location.href = "{{ route('admin.companies.index') }}";
                                }, 2000);
                            }

                            var $nextTab = $('.nav-link[data-step="'+response.nextStep+'"]');
    
                            $nextTab.removeClass('disabled');
                            // Trigger the next tab
                            $nextTab.tab('show');
    
                            var tabRoute = $nextTab.attr('href');
                            var tabId = $nextTab.attr('data-tab-type');
                           
                            // console.log(response);

                            if(response.user_uuid){
                                userObj.id = response.user_uuid;
    
                                renderStepForm(tabRoute,tabId,userObj.id);
                            }else{
                                renderStepForm(tabRoute,tabId);
                            }
                           
                        }
                    },
                    error: function (response) {
                        // console.log(response);
                        $(".card-disabled").css('display', 'none');
    
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
    
                                }else if(key == 'password'){
                                    var elementItem = $("input[name='"+key+"']").parent();    
                                    $(errorLabelTitle).insertAfter(elementItem);
    
                                }else if(key == 'total_employees'/*|| key == 'founding_year'*/){
    
                                    var elementItem = $("select[name='"+key+"']").siblings('span');    
                                    
                                    $(errorLabelTitle).insertAfter(elementItem);
    
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
            }
           
        });

    @endif
    //End Step Form
    
    //Start location clone
        var locationIndex = $(".location-row").length;
    
        $(document).on('click','.location_btn',function(e){
            
            let currentRow = $(this).closest('.row');
    
            if ($(this).hasClass('btn-primary')) {
             
                let newRow = currentRow.clone();
    
                locationIndex++;
    
                newRow.find('input').each(function() {
                    let name = $(this).attr('name');
    
                    let newName = name.replace(/\[\d+\]/g, '[' + locationIndex + ']');
    
                    $(this).attr('name', newName);
                    $(this).val(''); 
                });
    
                $(this).removeClass('btn-primary').addClass('btn-danger').text('-');
    
                newRow.find('.location_btn').removeClass('btn-danger').addClass('btn-primary').text('+');
                newRow.insertAfter(currentRow);
    
            } else {
                var deletedIds = currentRow.find("input[type='hidden']").val();
                if(deletedIds != undefined){
                    DeletedRow.locations.push();
                }
                
                currentRow.remove();
            }
                
        });
    //End location clone
    
    //Start portfolio clone
        var portfolioIndex = $(".portfolio-row").length;
    
        $(document).on('click','.portfolio_btn',function(e){
            
            let currentRow = $(this).closest('.row');
    
            if ($(this).hasClass('btn-primary')) {
             
                let newRow = currentRow.clone();
    
                portfolioIndex++;
    
                newRow.find('input').each(function() {
                    var $this = $(this);
                    let name = $this.attr('name');
    
                    let newName = name.replace(/\[\d+\]/g, '[' + portfolioIndex + ']');
    
                    $this.attr('name', newName);
                    $this.val(''); 
    
                    if($this.attr('type') == 'file'){                    
                        // $this.siblings('.img-prevarea').find('img').attr('src',"{{ asset(config('constant.default.no_image'))  }}");
                    }
                });
    
                $(this).removeClass('btn-primary').addClass('btn-danger').text('-');
    
                // Remove the Dropify wrapper and reinitialize it
                newRow.find('.dropify-wrapper').each(function() {
                    let input = $(this).find('input.dropify');
                    input.removeClass('dropify');
                    $(this).replaceWith(input);
                });

                newRow.find('.portfolio_btn').removeClass('btn-danger').addClass('btn-primary').text('+');
                newRow.insertAfter(currentRow);

                // Reinitialize Dropify for the new input
                newRow.find('input[type="file"]').addClass('dropify').dropify();

                
            } else {
                var deletedIds = currentRow.find("input[type='hidden']").val();
                if(deletedIds != undefined){
                    DeletedRow.portfolios.push();
                }
                currentRow.remove();
            }
                
        });
    //End portfolio clone
    
    //Start client clone
    var portfolioIndex = $(".client-row").length;
    
    $(document).on('click','.client_btn',function(e){
        
        let currentRow = $(this).closest('.row');
    
        if ($(this).hasClass('btn-primary')) {
         
            let newRow = currentRow.clone();
    
            portfolioIndex++;
    
            newRow.find('input').each(function() {
                var $this = $(this);
                let name = $this.attr('name');
    
                let newName = name.replace(/\[\d+\]/g, '[' + portfolioIndex + ']');
    
                $this.attr('name', newName);
                $this.val(''); 
    
                // if($this.attr('type') == 'file'){                    
                //     $this.siblings('.img-prevarea').find('img').attr('src',"{{ asset(config('constant.default.no_image'))  }}");
                // }
            });
    
            // Remove the Dropify wrapper and reinitialize it
            newRow.find('.dropify-wrapper').each(function() {
                let input = $(this).find('input.dropify');
                input.removeClass('dropify');
                $(this).replaceWith(input);
            });

            newRow.find('.client_btn').removeClass('btn-danger').addClass('btn-primary').text('+');
            newRow.insertAfter(currentRow);

            // Reinitialize Dropify for the new input
            newRow.find('input[type="file"]').addClass('dropify').dropify();

            $(this).removeClass('btn-primary').addClass('btn-danger').text('-');
    
    
        } else {
            var deletedIds = currentRow.find("input[type='hidden']").val();
            if(deletedIds != undefined){
                DeletedRow.clients.push();
            }
            currentRow.remove();
    
            // console.log('DeletedRow',DeletedRow);
        }
            
    });
    //End client clone
    
    });
    
    
    function renderStepForm(tabRoute,tabId,user_id=null){
        $('.tab-content').html('');
        
        $(".card-disabled").css('display', 'block');
    
        $.ajax({
            type: 'GET',
            url: tabRoute,
            data:{user_id:user_id},
            success: function(response) {
    
                $(".card-disabled").css('display', 'none');
                
                $('.tab-content').html(response.html);
    
                // Activate the tab pane after loading its content
                $('.navTab[data-tab-type="' + tabId + '"]').tab('show');
    
                $(".select2").select2();
    
                $('#founding_year').datepicker({
                    format: "yyyy",
                    viewMode: "years",
                    minViewMode: "years",
                    autoclose: true,
                    endDate: new Date(),
                });

                if ($('#snow-editor').length > 0) {
                    initializeQuill();
                }

                initializeDropify();
                
            },
            error: function(xhr, status, error) {
                // console.log(xhr,status,error);
                $(".card-disabled").css('display', 'none');
    
                if(xhr.responseJSON.error_type == 'something_error'){
                    toasterAlert('error',xhr.responseJSON.error);
                } 
            }
        });
    }

    function initializeQuill(){
        var quill = new Quill("#snow-editor", {
            theme: "snow",
            modules: {
                toolbar: [
                    [{
                        font: []
                    }, {
                        size: []
                    }],
                    ["bold", "italic", "underline", "strike"],
                    [{
                        header: [!1, 1, 2, 3, 4, 5, 6]
                    }, "blockquote", "code-block"],
                    [{
                        list: "ordered"
                    }, {
                        list: "bullet"
                    }, {
                        indent: "-1"
                    }, {
                        indent: "+1"
                    }],
                    ["direction", {
                        align: []
                    }],
                    ["clean"]
                ]
            }
        });
        
        quill.on('text-change', function() {
            var content = quill.root.innerHTML;
            $('#hidden-input').val(content);
        });
    }

    function initializeDropify(){
        $('.dropify').dropify();
        $('.dropify-errors-container').remove();
        $('.dropify-wrapper').find('.dropify-clear').hide();
        /*$('.dropify-clear').click(function(e) {
            e.preventDefault();
            var elementName = $(this).siblings('input[type=file]').attr('id');
            if (elementName == 'company-logo') {
                
            }
        });*/

    }
    
    
</script>
    