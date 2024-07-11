<div class="row">
    
    <div class="mb-3 col-md-6">
        <label for="service_name" class="form-label">Name<span class="required"> *</span></label>
        <input type="text" id="service_name" name="name" class="form-control" placeholder="Enter name" value="{{ isset($service) ? $service->name : ''}}">
    </div> 
    
    <div class="mb-3 col-md-6">
        <label for="slug" class="form-label">Slug<span class="required"> *</span></label>
        <input type="text" id="slug" name="slug" class="form-control" placeholder="Enter Slug" value="{{ isset($service) ? $service->slug : ''}}">
    </div>

</div>
  