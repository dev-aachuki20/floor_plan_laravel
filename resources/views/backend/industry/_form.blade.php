<div class="row">
    
    <div class="mb-3 col-md-6">
        <label for="industry_name" class="form-label">Name<span class="required"> *</span></label>
        <input type="text" id="industry_name" name="name" class="form-control" placeholder="Enter name" value="{{ isset($industry) ? $industry->name : ''}}">
    </div> 
    
    <div class="mb-3 col-md-6">
        <label for="slug" class="form-label">Slug<span class="required"> *</span></label>
        <input type="text" id="slug" name="slug" class="form-control" placeholder="Enter Slug" value="{{ isset($industry) ? $industry->slug : ''}}">
    </div>

</div>
  