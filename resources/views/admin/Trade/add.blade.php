@extends('admin.mainlayout')
@section('content')

  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark">Manage Trade </h1>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
    <!-- /.content-header -->

    <!-- Main content -->
  <section class="content">
     @include('admin.alert_message')
    <div class="container-fluid">
      <div class="box-main">
        <div class="box-main-top">
          <div class="box-main-title">Add New</div>
          <div class="box-main-top-right">
            <a href="{{URL('admin/trade/view')}}"><button type="button" class="btn btn-primary">Back</button></a>
          </div>
        </div>
        <div class="box-main-content mb-3">
          <div class="row">
            <div class="col-md-12 col-xl-6">
              {!!Form::open(['url'=>'admin/trade/save', 'enctype' => 'multipart/form-data', 'method'=>'post']) !!}   
                 <div class="form-group">
                  <div class="row align-items-end">
                    <div class="col-md-3 col-12">
                      <lable class="control-label">Logo:</lable>
                    </div>
                    <div class="col-md-9 col-12">
                      <div class="manage-supplierdetail-profile">
                        <img id="preview" src="{{URL('public/admin/dist/img/avatar.png')}}" /></div>
                      <label class="manage-supplierdetail-browse">
                      
                        <input type="file" name="image" onchange="previewImage(this)" accept="image/*"/>
                        
                        <button class="btn btn-primary">Browse</button>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-12">
                      <!-- {!!Form::label('name','Trade Name') !!} -->
                      <lable class="control-label">Trade Name:</lable>
                    </div>
                    <div class="col-md-9 col-12">
                     <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                      {!!Form::text('trade_name',null,['class' => 'form-control','placeholder' => 'Enter Trade Name','required' => 'required']) !!}
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
    <!-- /.content -->

<script type="text/javascript">      
  function previewImage(input) {
    var preview = document.getElementById('preview');
   /* var ext = input.files[0].split(".");
    ext = ext[ext.length-1].toLowerCase();   
    alert(ext);   
    var arrayExtensions = ["png"];
    if (arrayExtensions.lastIndexOf(ext) == -1) {
        alert("Wrong extension type.");
        $("#image").val("");
    }else{*/
      var fileType = input.files[0].type;
      var validImageTypes = ["image/png"];
      if ($.inArray(fileType, validImageTypes) < 0) {
           // invalid file type code goes here.
           alert('Please upload png image only')
      }else{
       
        if (input.files && input.files[0]) {
          $photo_size = input.files[0].size/1024;
          if($photo_size <= 2048){
            var reader = new FileReader();
            reader.onload = function (e) {
              preview.setAttribute('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
          }else{
            alert('File too Big, please select a file less than 2mb')
          }
        } else {
          preview.setAttribute('src', 'placeholder.png');
        }
      }
    }
  //}
</script>
@stop


