@extends('layouts.superboard')

@section('page_heading')
<h1 class="page-header">家長個資與服務條款聲明</h1>
@endsection

@section('section')
<div class="container">
	<div class="row">
	@if (session('error'))
	    <div class="alert alert-danger">
		{{ session('error') }}
	    </div>
	@endif
	@if (session('success'))
	    <div class="alert alert-success">
		{{ session('success') }}
	    </div>
	@endif

		<div class="panel panel-default">	  
			<div style="margin: 15px auto;width: 666px;">
				<pre style="max-height: 370px;overflow: auto;">
{!! $html !!}
				</pre>
				<div style="overflow: hidden;">
					<form name="form1" action="{{ route('bureau.parentspolicies') }}" method="POST" enctype="multipart/form-data" onsubmit="return filecheck()">
						@csrf
						<label for="fileUpload" class="btn btn-default" style="padding: 5px 16px;float: left;margin-right: 10px;">選擇檔案...</label>
						<label id="fileName" style="max-width: 444px;word-break: break-all;float: left;margin-right: 10px;background-color: #f5f5f5;padding: 2px 8px;border: 1px solid #CCC;border-radius: 4px;display: none;"></label>
						<input type="file" id="fileUpload" name="file" accept=".txt,.htm,.html" style="display: none;" onchange="return changeFile(this)"/>
						<button type="submit" class="btn btn-default" style="padding: 5px 16px;float: left;">更新內容</button>
						<div style="clear: both;"></div>
						<div id="errorBlock" class="has-error" style="display: none;"><p class="help-block"><strong>請選擇上傳文件！</strong></p></div>
					</form>
				</div>
			</div>
		</div>
    
		<form id="remove-form" action="" method="POST" style="display: none;">
		@csrf
		</form>
	</div>

	<script type="text/javascript">
		function filecheck(){
			var o = document.getElementById('fileUpload');
			if(o && o.value){
				$("#errorBlock").hide();
				return true;
			}else{
				$("#errorBlock").show();
				return false;
			}
		}

		function changeFile(o){
			if(o.value != ''){
				/*
				var k = $(this).val().toLowerCase().split('.');
				if(k.length != 2 || (k[1] != 'pdf' && k[1] != 'png' && k[1] != 'jpg' && k[1] != 'jpeg')){
					$.showMsg('只能夠上傳pdf、png或jpg格式');
					return false;
				}
				*/
				$("#fileName").text(o.files[0].name).show();
				$("#errorBlock").hide();
			}else{
				$("#fileName").text('').hide();
			}
		}
	</script>
</div>
@endsection