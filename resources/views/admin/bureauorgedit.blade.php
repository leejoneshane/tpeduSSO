@extends('layouts.superboard')

@section('page_heading')
{{ isset($user) ? '編輯' : '新增' }}教育機構
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
	<div class="col-sm-8">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>{{ isset($user) ? '編輯' : '新增' }}教育機構</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ isset($data) ? route('bureau.updateOrg', [ 'dc' => $data['o'] ]) : route('bureau.createOrg') }}">
		    @csrf
		    <div class="form-group{{ $errors->has('dc') ? ' has-error' : '' }}">
			<label>系統代號</label>
			<input id="dc" type="text" class="form-control" name="dc" value="{{ isset($data) && array_key_exists('o', $data) ? $data['o'] : old('dc') }}" required>
			@if ($errors->has('dc'))
				<p class="help-block">
					<strong>{{ $errors->first('dc') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
			<label>機構全銜</label>
			<input id="description" type="text" class="form-control" name="description" value="{{ isset($data) && array_key_exists('description', $data) ? $data['description'] : old('description') }}" required>
			@if ($errors->has('description'))
				<p class="help-block">
					<strong>{{ $errors->first('description') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('businessCategory') ? ' has-error' : '' }}">
			<label>機構類別</label>
			<select id="businessCategory" class="form-control" name="businessCategory">
			@foreach ($category as $type)
			    <option value="{{ $type }}"{{ isset($data) && $data['businessCategory'] == $type ? ' selected' : '' }}>{{ $type }}</option>
			@endforeach
			</select>
		    </div>
		    <div class="form-group{{ $errors->has('st') ? ' has-error' : '' }}">
			<label>所在行政區</label>
			<select id="st" class="form-control" name="st">
			@foreach ($areas as $area)
			    <option value="{{ $area }}"{{ isset($data) && $data['st'] == $area ? ' selected' : '' }}>{{ $area }}</option>
			@endforeach
			</select>
		    </div>
		    <div class="form-group{{ $errors->has('fax') ? ' has-error' : '' }}">
			<label>傳真號碼</label>
			<input id="fax" type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" name="fax" value="{{ isset($data) && array_key_exists('facsimileTelephoneNumber', $data) ? $data['facsimileTelephoneNumber'] : old('fax') }}" placeholder="格式如右：(02)23456789">
			@if ($errors->has('fax'))
				<p class="help-block">
					<strong>{{ $errors->first('fax') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('telephoneNumber') ? ' has-error' : '' }}">
			<label>電話代表號</label>
			<input id="telephoneNumber" type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" name="telephoneNumber" value="{{ isset($data) && array_key_exists('telephoneNumber', $data) ? $data['telephoneNumber'] : old('telephoneNumber') }}" placeholder="格式如右：(02)23456789">
			@if ($errors->has('telephoneNumber'))
				<p class="help-block">
					<strong>{{ $errors->first('telephoneNumber') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('postalCode') ? ' has-error' : '' }}">
			<label>郵遞區號</label>
			<input id="postalCode" type="text" class="form-control" name="postalCode" value="{{ isset($data) && array_key_exists('postalCode', $data) ? $data['postalCode'] : old('postalCode') }}">
			@if ($errors->has('postalCode'))
				<p class="help-block">
					<strong>{{ $errors->first('postalCode') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('street') ? ' has-error' : '' }}">
			<label>地址</label>
			<input id="street" type="text" class="form-control" name="street" value="{{ isset($data) && array_key_exists('street', $data) ? $data['street'] : old('street') }}">
			@if ($errors->has('street'))
				<p class="help-block">
					<strong>{{ $errors->first('street') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('postOfficeBox') ? ' has-error' : '' }}">
			<label>聯絡箱</label>
			<input id="postOfficeBox" type="text" class="form-control" name="postOfficeBox" value="{{ isset($data) && array_key_exists('postOfficeBox', $data) ? $data['postOfficeBox'] : old('postOfficeBox') }}" placeholder="請輸入教育局聯絡箱編號">
			@if ($errors->has('postOfficeBox'))
				<p class="help-block">
					<strong>{{ $errors->first('postOfficeBox') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('wWWHomePage') ? ' has-error' : '' }}">
			<label>官方網址</label>
			<input id="wWWHomePage" type="text" class="form-control" name="wWWHomePage" value="{{ isset($data) && array_key_exists('wWWHomePage', $data) ? $data['wWWHomePage'] : old('wWWHomePage') }}">
			@if ($errors->has('wWWHomePage'))
				<p class="help-block">
					<strong>{{ $errors->first('wWWHomePage') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('tpUniformNumbers') ? ' has-error' : '' }}">
			<label>機構統一編號</label>
			<input id="tpUniformNumbers" type="text" class="form-control" name="tpUniformNumbers" value="{{ isset($data) && array_key_exists('tpUniformNumbers', $data) ? $data['tpUniformNumbers'] : old('tpUniformNumbers') }}" placeholder="請輸入學校會計統一編號" required>
			@if ($errors->has('tpUniformNumbers'))
				<p class="help-block">
					<strong>{{ $errors->first('tpUniformNumbers') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('tpIpv4') ? ' has-error' : '' }}">
			<label>IPv4 網段</label>
			<input id="tpIpv4" type="text" class="form-control" name="tpIpv4" value="{{ isset($data) && array_key_exists('tpIpv4', $data) ? $data['tpIpv4'] : old('tpIpv4') }}" placeholder="請輸入網路編號及子網路遮罩，例如：163.21.249.0/24">
			@if ($errors->has('tpIpv4'))
				<p class="help-block">
					<strong>{{ $errors->first('tpIpv4') }}</strong>
				</p>
			@endif
		    </div>
		    <div class="form-group{{ $errors->has('tpIpv6') ? ' has-error' : '' }}">
			<label>IPv6 網段</label>
			<input id="tpIpv6" type="text" class="form-control" name="tpIpv6" value="{{ isset($data) && array_key_exists('tpIpv6', $data) ? $data['tpIpv6'] : old('tpIpv6') }}" placeholder="請輸入網路編號及子網路遮罩，例如：2001:288:1200::/48">
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
