@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">新增子女</div>
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
				<p>程式將自動比對學籍資料並建立親子關係，如果無法比對成功，將由學校進行人工審核，若家長資料有異動請務必提供證件向註冊組長申請變更學籍資料！</p>
				<form class="form-horizontal" method="POST" action="{{ route('parent.applyLink') }}">
					{{ csrf_field() }}
					<div class="form-group{{ $errors->has('idno') ? ' has-error' : '' }}">
					<label for="idno" class="col-md-4 col-form-label text-md-right">學生的身分證字號</label>
					<div class="col-md-4">
						<input id="idno" type="text" class="form-control" name="idno" required>
						@if ($errors->has('idno'))
						<span class="help-block">
						<strong>{{ $errors->first('idno') }}</strong>
						</span>
						@endif
					</div>
					</div>
					<div class="form-group{{ $errors->has('birthday') ? ' has-error' : '' }}">
						<label for="birthday" class="col-md-4 col-form-label text-md-right">學生的出生日期</label>
						<div class="col-md-4">
							<input id="birthday" type="text" class="form-control" name="birthday" pattern="[0-9]{8}" placeholder="格式如：20120101" required>
							@if ($errors->has('birthday'))
							<span class="help-block">
							<strong>{{ $errors->first('birthday') }}</strong>
							</span>
							@endif
							</div>
					</div>
					<div class="form-group{{ $errors->has('relation') ? ' has-error' : '' }}">
						<label for="relation" class="col-md-4 col-form-label text-md-right">與學生的關係</label>
						<div class="col-md-4">
							<select id="relation" style="width:auto" class="form-control" name="relation">
							@foreach ($relations as $r)
								<option value="{{ $r }}">{{ $r }}</option>
							@endforeach
							</select>
						</div>
					</div>
					<div class="form-group">
					<div class="col-md-8 col-md-offset-4">
						<button type="submit" class="btn btn-primary">
						確定
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
