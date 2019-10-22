@extends('layouts.userboard')

@section('page_heading')
@endsection

@section('section')
<div class="container">
    <div class="row justify-content-center">
	<div class="col-md-10 col-md-offset-1">
	    <div class="card card-default" style="margin-top: 20px">
		<div class="card-header">親子連結服務</div>
		<div class="card-body">
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
			<p>請輸入您的孩子個人與就讀學校資料！</p>
		<form class="form-horizontal" name='form1' method="POST" action="{{ route('parents.connectChild') }}">
		    {{ csrf_field() }}
		    <div class="form-group{{ $errors->has('idno') ? ' has-error' : '' }}">
			<label for="idno" class="col-md-3 control-label">身分證字號</label>
			<div class="col-md-7">
			    <input id="idno" type="text" class="form-control" name="idno"  placeholder="身分證字號" style="ime-mode:disabled" value="{{ old('idno') ?? $idno }}" required>
			    @if ($errors->has('idno'))
				<span class="help-block">
				<strong>{{ $errors->first('idno') }}</strong>
				</span>
			    @endif
			</div>
		    </div>
		    <div class="form-group">
			<label for="displayName" class="col-md-3 control-label">就讀學校</label>
				<div class="row">
					<div class="col-md-1 text-md-right control-label">行政區</div>
					<div class="col-md-2 text-md-left">
						<select name="area" class="form-control changeData" style="width: auto" >
							@foreach ($areas as $st)
								<option value="{{ $st }}"{{ (old('area') == $st || $area == $st) ? ' selected' : '' }}>{{ $st }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-1 text-md-right control-label">學層</div>
					<div class="col-md-3 text-md-left">
						<select name="schoolCategory" class="form-control changeData" style="width: auto" >
							@foreach ($schoolCategorys as $sc)
								<option value="{{ $sc }}"{{ (old('schoolCategory') == $sc || $schoolCategory == $sc) ? ' selected' : '' }}>{{ $sc }}</option>
							@endforeach
						</select>
					</div>
				</div>
			 </div>	
			 <div class="form-group">
			 <label for="displayName" class="col-md-3 control-label"></label>
				<div class="row">
					<div class="col-md-1 text-md-right control-label">學校</div>
					<div class="col-md-6 text-md-left">
					<select name="dc" class="form-control" style="width: auto" required>
						@foreach ($schools as $sch)
							<option value="{{ $sch->o }}"{{ (old('dc') == $sch->o || $dc == $sch->o) ? ' selected' : '' }}>{{ $sch->description }}</option>
						@endforeach
					</select>
					</div>
				</div>
		    </div>
            <div class="form-group{{ $errors->has('student_id') ? ' has-error' : '' }}">
			<label for="student_id" class="col-md-3 control-label">學號</label>
			<div class="col-md-7">
			    <input id="student_id" type="text" class="form-control" name="student_id" value="{{ old('student_id', $student_id) }}" style="ime-mode:disabled" required> 
			    @if ($errors->has('student_id'))
			    <span class="help-block">
				<strong>{{ $errors->first('student_id') }}</strong>
			    </span>
			    @endif
			</div>
		    </div>
            <div class="form-group{{ $errors->has('student_birthday') ? ' has-error' : '' }}">
			<label for="student_birthday" class="col-md-3 control-label">出生年月日</label>
			<div class="col-md-7">
			    <input id="student_birthday" type="text" class="form-control  calendarNOGOOD"  placeholder="yyyymmdd" name="student_birthday" value="{{ old('student_birthday') ?? $student_birthday }}" style="ime-mode:disabled" > 
			    @if ($errors->has('student_birthday'))
			    <span class="help-block">
				<strong>{{ $errors->first('student_birthday') }}</strong>
			    </span>
			    @endif
			</div>
		    </div>          
		    <div class="form-group">
			<hr style="border: 3px inset #BBB;" />
			</div>		
			<p> 請輸入您的資料！</p>
			<div class="form-group{{ $errors->has('pname') ? ' has-error' : '' }}">
			<div class="row">
					<div class="col-md-3 text-md-right control-label">
					<label><input id="relationType" name="relationType" type="radio" value="監護人" {{ old('relationType') =='監護人' ? 'checked':'' }} {{ $relationType=='監護人' ? 'checked' :'' }}> 監護人  </label>
					<label><input id="relationType" name="relationType" type="radio" value="父親" {{ old('relationType') =='父親' ? 'checked' :'' }} {{ $relationType=='父親' ? 'checked'  :'' }}> 父親  </label>
					<label><input id="relationType" name="relationType" type="radio" value="母親" {{ old('relationType') =='母親' ? 'checked' :'' }} {{ $relationType=='母親' ? 'checked'  :'' }}> 母親  </label>
					</div>
					<div class="col-md-1 text-md-right control-label">姓名</div>
					<div class="col-md-6 text-md-left ">
					    <input id="pname" type="text" class="form-control" name="pname" value="{{ old('pname') ?? '' }}" > 
						@if ($errors->has('pname'))
						<span class="help-block">
						<strong>{{ $errors->first('pname') }}</strong>
						</span>
						@endif
					</div>
			</div>			
		    </div>  			
		    <div class="form-group">
			<div class="col-md-10 col-md-offset-4">
			    <button type="submit" class="btn btn-primary" id='buttonSubmit' name='buttonSubmit' value="send">
				確認送出
			    </button>
			</div>
		    </div>
		</form>
	        </div>
	    </div>
	</div>
    </div>
</div>
@endsection
@section('script')
@parent
<script type="text/javascript">
$(document).ready(function(){
	$('.changeData').change(function(){
		$('form[name=form1]').attr('action','{{ url()->current() }}');
		$('form[name=form1]').submit();
	});
});
</script>
@endsection