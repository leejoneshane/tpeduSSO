@extends('layouts.dashboard')

@section('page_heading')
學校基本資料
@endsection

@section('section')
<div class="container">
    <div class="row">
	<div class="col-md-6 col-md-offset-2">
		<div>
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
		<form role="form" method="POST" action="{{ route('school.profile') }}">
		    {{ csrf_field() }}
		    <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
			<label>學校全銜</label>
			<input id="description" type="text" class="form-control" name="description" value="{{ array_key_exists('description', $data) ? $data['description'] : old('description') }}">
			@if ($errors->has('description'))
				<p class="help-block">
					<strong>{{ $errors->first('description') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('businessCategory') ? ' has-error' : '' }}">
			<label>學校類別</label>
			<select id="businessCategory" class="form-control" name="businessCategory">
			    <option value="幼兒園" {{ $data['businessCategory'] == '幼兒園' ? 'selected' : '' }}>幼兒園</option>
			    <option value="國民小學" {{ $data['businessCategory'] == '國民小學' ? 'selected' : '' }}>國民小學</option>
			    <option value="國民中學" {{ $data['businessCategory'] == '國民中學' ? 'selected' : '' }}>國民中學</option>
			    <option value="高中" {{ $data['businessCategory'] == '高中' ? 'selected' : '' }}>高中</option>
			    <option value="高職" {{ $data['businessCategory'] == '高職' ? 'selected' : '' }}>高職</option>
			    <option value="大專院校" {{ $data['businessCategory'] == '大專院校' ? 'selected' : '' }}>大專院校</option>
			    <option value="特殊教育" {{ $data['businessCategory'] == '特殊教育' ? 'selected' : '' }}>特殊教育</option>
			    <option value="主管機關" {{ $data['businessCategory'] == '主管機關' ? 'selected' : '' }}>主管機關</option>
			</select>
			@if ($errors->has('businessCategory'))
				<p class="help-block">
					<strong>{{ $errors->first('businessCategory') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('st') ? ' has-error' : '' }}">
			<label>行政區</label>
			<input id="st" type="text" class="form-control" name="st" value="{{ array_key_exists('st', $data) ? $data['st'] : old('st') }}">
			@if ($errors->has('st'))
				<p class="help-block">
					<strong>{{ $errors->first('st') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('fax') ? ' has-error' : '' }}">
			<label>傳真號碼</label>
			<input id="fax" type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" name="fax" value="{{ array_key_exists('fax', $data) ? $data['fax'] : old('fax') }}" placeholder="格式如右：(02)23456789">
			@if ($errors->has('fax'))
				<p class="help-block">
					<strong>{{ $errors->first('fax') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('telephoneNumber') ? ' has-error' : '' }}">
			<label>電話代表號</label>
			<input id="telephoneNumber" type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" name="telephoneNumber" value="{{ array_key_exists('telephoneNumber', $data) ? $data['telephoneNumber'] : old('telephoneNumber') }}" placeholder="格式如右：(02)23456789">
			@if ($errors->has('telephoneNumber'))
				<p class="help-block">
					<strong>{{ $errors->first('telephoneNumber') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('postalCode') ? ' has-error' : '' }}">
			<label>郵遞區號</label>
			<input id="postalCode" type="text" class="form-control" name="postalCode" value="{{ array_key_exists('postalCode', $data) ? $data['postalCode'] : old('postalCode') }}">
			@if ($errors->has('postalCode'))
				<p class="help-block">
					<strong>{{ $errors->first('postalCode') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('street') ? ' has-error' : '' }}">
			<label>地址</label>
			<input id="street" type="text" class="form-control" name="street" value="{{ array_key_exists('street', $data) ? $data['street'] : old('street') }}">
			@if ($errors->has('street'))
				<p class="help-block">
					<strong>{{ $errors->first('street') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('postOfficeBox') ? ' has-error' : '' }}">
			<label>聯絡箱</label>
			<input id="postOfficeBox" type="text" class="form-control" name="postOfficeBox" value="{{ array_key_exists('postOfficeBox', $data) ? $data['postOfficeBox'] : old('postOfficeBox') }}" placeholder="請輸入教育局聯絡箱編號">
			@if ($errors->has('postOfficeBox'))
				<p class="help-block">
					<strong>{{ $errors->first('postOfficeBox') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('wWWHomePage') ? ' has-error' : '' }}">
			<label>官方網址</label>
			<input id="wWWHomePage" type="text" class="form-control" name="wWWHomePage" value="{{ array_key_exists('wWWHomePage', $data) ? $data['wWWHomePage'] : old('wWWHomePage') }}">
			@if ($errors->has('wWWHomePage'))
				<p class="help-block">
					<strong>{{ $errors->first('wWWHomePage') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('tpUniformNumbers') ? ' has-error' : '' }}">
			<label>學校統一編號</label>
			<input id="tpUniformNumbers" type="text" class="form-control" name="tpUniformNumbers" value="{{ array_key_exists('tpUniformNumbers', $data) ? $data['tpUniformNumbers'] : old('tpUniformNumbers') }}" placeholder="請輸入學校會計統一編號">
			@if ($errors->has('tpUniformNumbers'))
				<p class="help-block">
					<strong>{{ $errors->first('tpUniformNumbers') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('tpIpv4') ? ' has-error' : '' }}">
			<label>IPv4 網段</label>
			<input id="tpIpv4" type="text" class="form-control" name="tpIpv4" value="{{ array_key_exists('tpIpv4', $data) ? $data['tpIpv4'] : old('tpIpv4') }}" placeholder="請輸入網路編號及子網路遮罩，例如：163.21.249.0/24">
			@if ($errors->has('tpIpv4'))
				<p class="help-block">
					<strong>{{ $errors->first('tpIpv4') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('tpIpv6') ? ' has-error' : '' }}">
			<label>IPv6 網段</label>
			<input id="tpIpv6" type="text" class="form-control" name="tpIpv6" value="{{ array_key_exists('tpIpv6', $data) ? $data['tpIpv6'] : old('tpIpv6') }}" placeholder="請輸入網路編號及子網路遮罩，例如：2001:288:1200::/48">
			@if ($errors->has('tpIpv6'))
				<p class="help-block">
					<strong>{{ $errors->first('tpIpv6') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group">
			    <button type="submit" class="btn btn-primary">確定</button>
		    </div>
		</form>
	    </div>
	</div>
    </div>
</div>
@endsection
