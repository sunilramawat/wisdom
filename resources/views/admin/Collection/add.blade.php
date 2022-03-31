@extends('admin.mainlayout')
@section('content')
  <meta name="csrf-token" content="{{ csrf_token() }}" />
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark">Manage Knowledge </h1>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
    <!-- /.content-header -->

    <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="box-main">
        <div class="box-main-top">
          <div class="box-main-title">Add Knowledge</div>
          <div class="box-main-top-right">
             <a href="{{URL('admin/collection/view')}}" <button type="button" class="btn btn-primary">Back</button></a>
          </div>
        </div>
          @if ($errors->any())
            <div class="alert alert-danger">
               <ul>
                  @foreach ($errors->all() as $error)
                     <li>{{ $error }}</li>
                  @endforeach
               </ul>
               @if ($errors->has('email'))
               @endif
            </div>
          @endif
        <div class="box-main-content mb-3">
          <div class="row">

            <div class="col-md-12 col-xl-6">
               
              {!!Form::open(['url'=>'admin/collection/save',  'name' => 'orderForm', 'enctype' => 'multipart/form-data', 'method'=>'post','onsubmit'=>"return validateForm()"]) !!} 

                 <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-8">
                      <lable class="control-label">Photo </lable>
                    </div>
                   
                    <div class="col-md-9 col-12">
                      <div class="row align-items-end">
                      </div>
                    
                    <div class="manage-supplierdetail-profile"><img id="preview" src="" /></div>
                     <label class="manage-supplierdetail-browse">
                          <input type="file" name="image" onchange="previewImage(this,this.value)" accept="image/*"/>
                          
                            
                            <button class="btn btn-primary">Browse</button>
                          </label>
                           <span class="admin-error-msg" id="err_music_image" style="display: none;"></span>
                           <br>
                          <span id="redpatner" class="patnerimg">(You can upload with less than or equal to (315X165) width & height)</span>
                    </div>
                  
                </div>
            </div>
            <div class="form-group"  >
              <div class="row">
                <div class="col-md-3 col-8">
                  <!-- {!!Form::label('name','Trade Name') !!} -->
                  <lable class="control-label">Category</lable>
                </div>
                <div class="col-md-9 col-12">
                   <select class="form-control" id="category" name="category">
                        <option  value="">Select Category</option>
                        @foreach ($category as $categorykey => $categoryval)
                        <option value="{{$categorykey}}">{{$categoryval}}</option>
                        @endforeach
                </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Title </lable>
                </div>
                <div class="col-md-8 col-12">
                   <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                    {!!Form::text('title',null,['class' => 'form-control','placeholder' => 'Title','required' => 'required','maxlength' => 255]) !!}
                    <span class="admin-error-msg" id="err_music_title" style="display: none;"></span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <!-- {!!Form::label('name','Trade Name') !!} -->
                  <lable class="control-label">Description</lable>
                </div>
                <div class="col-md-8 col-12">
                 <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                  {!!Form::textarea('desc',null,['class' => 'form-control','placeholder' => 'Enter Description','required' => 'required','cols'=>"50",'rows'=>"5",'maxlength' => 255]) !!}
                  <span class="admin-error-msg" id="err_music_description" style="display: none;"></span>

                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Author </lable>
                </div>
                <div class="col-md-8 col-12">
                   <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                    {!!Form::text('author',null,['class' => 'form-control','placeholder' => 'Author','required' => 'required','maxlength' => 255]) !!}
                    <span class="admin-error-msg" id="err_music_author" style="display: none;"></span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Amazon Link  </lable>
                </div>
                <div class="col-md-8 col-12">
                   <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                    {!!Form::text('amazon_link',null,['class' => 'form-control','placeholder' => 'Amazon Link','maxlength' => 255]) !!}
                    <span class="admin-error-msg" id="err_amazon_link" style="display: none;"></span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Ebay Link  </lable>
                </div>
                <div class="col-md-8 col-12">
                   <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                    {!!Form::text('ebay_link',null,['class' => 'form-control','placeholder' => 'Ebay Link','maxlength' => 255]) !!}
                    <span class="admin-error-msg" id="err_ebay_link" style="display: none;"></span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Wordery Link  </lable>
                </div>
                <div class="col-md-8 col-12">
                   <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                    {!!Form::text('wordery',null,['class' => 'form-control','placeholder' => 'Wordery Link','maxlength' => 255]) !!}
                    <span class="admin-error-msg" id="err_wordery_link" style="display: none;"></span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Other Link  </lable>
                </div>
                <div class="col-md-8 col-12">
                   <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                    {!!Form::text('other_link1',null,['class' => 'form-control','placeholder' => 'Other Link','required' => 'required','maxlength' => 255]) !!}
                    <span class="admin-error-msg" id="err_other_link1" style="display: none;"></span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Other Link  </lable>
                </div>
                <div class="col-md-8 col-12">
                   <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                    {!!Form::text('other_link2',null,['class' => 'form-control','placeholder' => 'Other Link','required' => 'required','maxlength' => 255]) !!}
                    <span class="admin-error-msg" id="err_other_link2" style="display: none;"></span>
                </div>
              </div>
            </div>

                
                
                <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-12">
                      
                    </div>
                    <div class="col-md-8 col-12">
                      <!--  <a href="">{!! Form::button('Cancle',array('class'=>'btn btn-deflaut')) !!} </a> -->
                       {!! Form::submit('Submit',array('class'=>'btn btn-primary')) !!}
                     <!--  <button type="button" class="btn btn-primary">Submit</button> -->
                      
                    </div>
                  </div>
                </div>
              {!!Form::close()!!}
            </div>
          </div>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
  
<script type="text/javascript">
  $('#catdiv').hide(); 
  $(function () {
  
      //Timepicker
    $('#opening').datetimepicker({
      format: 'LT'
    })
    
      //Timepicker
    $('#closing').datetimepicker({
      format: 'LT'
    })

  });

  function previewImage(input,fileName) {
    var preview = document.getElementById('preview');
    var allowed_extensions = new Array("jpeg","jpg",'png','bmp');
    var file_extension = fileName.split('.').pop().toLowerCase(); 

    if(allowed_extensions.indexOf(file_extension) > -1){
      $("#err_music_image").css("display", "none");
      $(':input[type="submit"]').prop('disabled', false);
    }else{
      $("#err_music_image").html("*Please select image.");
      $("#err_music_image").css("display", "block");
      $(':input[type="submit"]').prop('disabled', true);
    }
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.setAttribute('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    } else {
      preview.setAttribute('src', 'placeholder.png');
    }
  }     


  </script> 

  <script type="text/javascript">
    $(document).ready(function () {
     
        $('#category').on('change',function(e) {
         var cat_id = e.target.value;
         $.ajax({
               url:"{{URL('admin/collection/subcat')}}",
               type:"POST",
               data: {
                   cat_id: cat_id
                },
               headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
               success:function (data) {
                console.log(data);
                $('#subcategory').empty();

               $.each(data.subcategories,function(index,subcategory){
                $('#subcategory').append('<option value="'+subcategory.sc_id+'">'+subcategory.sc_name+'</option>');
                })
               }
           })
        });
    });


    ////
    function validateForm() {
      console.log(document.forms["orderForm"]);
      var image = document.forms["orderForm"]["image"].value;
      if(image == ''){
        //alert('Please select image.');
        alert("Please select product image");
        $("#err_music_image").html("*Please select image");
        $("#err_music_image").css("display", "block");
        return false;
      }else{
        $("#err_music_image").css("display", "none");
      }
      if($('#category').val()){
           
      } else {
        alert("Please Select Category");
          return false;
          // do something else
      }
      var title = document.forms["orderForm"]["title"].value;
      var title = title.trim();
      if (title == ""){
          //alert('Please enter title.');
          $("#err_music_title").html("*Please enter title");
          $("#err_music_title").css("display", "block");
          return false;
      }else{
          $("#err_music_title").css("display", "none");
      }
      var description = document.forms["orderForm"]["desc"].value;
      var description = description.trim();
      if (description == ""){
          //alert('Please enter name.');
          $("#err_music_description").html("*Please enter description");
          $("#err_music_description").css("display", "block");
          return false;
      }else{
          $("#err_music_description").css("display", "none");
      }
      var author = document.forms["orderForm"]["author"].value;
      var author = author.trim();
      if (author == ""){
          //alert('Please enter author.');
          $("#err_music_author").html("*Please enter author");
          $("#err_music_author").css("display", "block");
          return false;
      }else{
          $("#err_music_author").css("display", "none");
      }
      /*if (from_date != "" || to_date != "") {
      if (from_date == ""){
      //alert(from_date);
        document.getElementById('error-from_date').innerHTML = "Please Enter From Date"
        //alert("Please Select From Date");
        return false;
      }
      if(to_date == ""){
          $('#to_date').val(from_date);
        //document.getElementById('error-to_date').innerHTML = "Please Enter To Date"
        //return false;
      }
      
    }*/
    if (opening == "" ) {
      document.getElementById('error-from_date').innerHTML = "Please Enter From Date";
      return false;
    }
  } 
  </script>

        
@stop

