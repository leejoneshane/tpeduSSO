@extends('layouts.superboard')

@section('page_heading')
<h1 class="page-header">第三方應用管理</h1>
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

		<form role="form" method="POST" action="{{ route('bureau.updateThirdapp', $data['id']) }}">
			<div class="panel panel-default">	  
				<div class="panel-heading">
					<h4>第三方應用</h4>
				</div>
				<div class="panel-body">
					<div class="form-group{{ $errors->has('unit') ? ' has-error' : '' }}">
						<label>申請單位(機關名稱)</label>
						<input type="text" id="unit" class="form-control" name="unit" value="{{ sizeof($errors) > 0 ? old('unit') : $data['unit'] }}" required />
						@if ($errors->has('unit'))
							<p class="help-block">
								<strong>{{ $errors->first('unit') }}</strong>
							</p>
						@endif
					</div>
					<div class="form-group{{ $errors->has('entry') ? ' has-error' : '' }}">
						<label>應用平臺名稱(網站服務入口)</label>
						<input type="text" id="entry" class="form-control" name="entry" value="{{ sizeof($errors) > 0 ? old('entry') : $data['entry'] }}" required />
						@if ($errors->has('entry'))
							<p class="help-block">
								<strong>{{ $errors->first('entry') }}</strong>
							</p>
						@endif
					</div>
					<div class="form-group{{ $errors->has('background') ? ' has-error' : '' }}">
						<label>應用平臺申請背景說明</label>
						<input type="text" id="background" class="form-control" name="background" value="{{ sizeof($errors) > 0 ? old('background') : $data['background'] }}" />
					</div>
					<div class="form-group{{ $errors->has('url') ? ' has-error' : '' }}">
						<label>應用平臺網址</label>
						<input type="text" id="url" class="form-control" name="url" value="{{ sizeof($errors) > 0 ? old('url') : $data['url'] }}" required />
						@if ($errors->has('url'))
							<p class="help-block">
								<strong>{{ $errors->first('url') }}</strong>
							</p>
						@endif
					</div>
					<div class="form-group{{ $errors->has('redirect') ? ' has-error' : '' }}">
						<label>SSO認證後重導向URL</label>
						<input type="text" id="redirect" class="form-control" name="redirect" value="{{ sizeof($errors) > 0 ? old('redirect') : $data['redirect'] }}" required />
						@if ($errors->has('redirect'))
							<p class="help-block">
								<strong>{{ $errors->first('redirect') }}</strong>
							</p>
						@endif
					</div>
					<div class="form-group{{ $errors->has('unittype') ? ' has-error' : '' }}">
						<label>單位別</label>
						<div id="unittype">
							<label style="margin-right: 10px;"><input type="checkbox" name="unittype1" style="margin-right: 3px;" onclick="unittype(this)" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('unittype1')) ? 'checked="checked"' : '' ) : ( $data['unittype1'] == 'Y' ? 'checked="checked"' : '' ) }} />本局</label>
							<label style="margin-right: 10px;"><input type="checkbox" name="unittype2" style="margin-right: 3px;" onclick="unittype(this)" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('unittype2')) ? 'checked="checked"' : '' ) : ( $data['unittype2'] == 'Y' ? 'checked="checked"' : '' ) }} />學校</label>
							<label style="margin-right: 10px;"><input type="checkbox" name="unittype3" style="margin-right: 3px;" onclick="unittype(this)" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('unittype3')) ? 'checked="checked"' : '' ) : ( $data['unittype3'] == 'Y' ? 'checked="checked"' : '' ) }} />第三方</label>
						</div>
						@if ($errors->has('unittype'))
							<p class="help-block">
								<strong>{{ $errors->first('unittype') }}</strong>
							</p>
						@endif
					</div>
					<div class="form-group">
						<label>介接業務聯絡窗口</label>
						<div class="form-group{{ $errors->has('conman') ? ' has-error' : '' }}">
							<div class="row">
								<div class="col-sm-3 col-xs-12">姓名:</div>
								<div class="col-sm-9 col-xs-12">
									<input type="text" id="conman" class="form-control" name="conman" value="{{ sizeof($errors) > 0 ? old('conman') : $data['conman'] }}" required />
								</div>
							</div>
							@if ($errors->has('conman'))
								<p class="help-block">
									<strong>{{ $errors->first('conman') }}</strong>
								</p>
							@endif
						</div>
						<div class="form-group{{ $errors->has('conmail') ? ' has-error' : '' }}">
							<div class="row">
								<div class="col-sm-3 col-xs-12">Email:</div>
								<div class="col-sm-9 col-xs-12">
									<input type="text" id="conmail" class="form-control" name="conmail" value="{{ sizeof($errors) > 0 ? old('conmail') : $data['conmail'] }}" />
								</div>
							</div>
							@if ($errors->has('conmail'))
								<p class="help-block">
									<strong>{{ $errors->first('conmail') }}</strong>
								</p>
							@endif
						</div>
						<div class="form-group{{ $errors->has('conunit') ? ' has-error' : '' }}">
							<div class="row">
								<div class="col-sm-3 col-xs-12">部門/單位:</div>
								<div class="col-sm-9 col-xs-12">
									<input type="text" id="conunit" class="form-control" name="conunit" value="{{ sizeof($errors) > 0 ? old('conunit') : $data['conunit'] }}" />
								</div>
							</div>
							@if ($errors->has('conunit'))
								<p class="help-block">
									<strong>{{ $errors->first('conunit') }}</strong>
								</p>
							@endif
						</div>
						<div class="form-group{{ $errors->has('contel') ? ' has-error' : '' }}">
							<div class="row">
								<div class="col-sm-3 col-xs-12">電話:</div>
								<div class="col-sm-9 col-xs-12">
									<input type="text" id="contel" class="form-control" name="contel" value="{{ sizeof($errors) > 0 ? old('contel') : $data['contel'] }}" required />
								</div>
							</div>
							@if ($errors->has('contel'))
								<p class="help-block">
									<strong>{{ $errors->first('contel') }}</strong>
								</p>
							@endif
						</div>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-md-6 col-sm-12">
								<div class="form-group{{ $errors->has('recdt') ? ' has-error' : '' }}">
									<div class="row" style="margin-bottom: 5px;">
										<div class="col-md-6 col-sm-3 col-xs-12"><label>收件日期</label></div>
										<div class="col-md-6 col-sm-9 col-xs-12">
											<input type="text" id="recdt" class="form-control" name="recdt" value="{{ sizeof($errors) > 0 ? old('recdt') : $data['recdt'] }}" placeholder="yyyymmdd" />
										</div>
									</div>
									@if ($errors->has('recdt'))
										<p class="help-block">
											<strong>{{ $errors->first('recdt') }}</strong>
										</p>
									@endif
								</div>
							</div>
							<div class="col-md-6 col-sm-12">
								<div class="form-group{{ $errors->has('recno') ? ' has-error' : '' }}">
									<div class="row" style="margin-bottom: 5px;">
										<div class="col-md-6 col-sm-3 col-xs-12"><label>案件編號/備註</label></div>
										<div class="col-md-6 col-sm-9 col-xs-12">
											<input type="text" id="recno" class="form-control" name="recno" value="{{ sizeof($errors) > 0 ? old('recno') : $data['recno'] }}" />
										</div>
									</div>
									@if ($errors->has('recno'))
										<p class="help-block">
											<strong>{{ $errors->first('recno') }}</strong>
										</p>
									@endif
								</div>
							</div>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-md-6 col-sm-12">
							<div class="form-group{{ $errors->has('key') ? ' has-error' : '' }}">
								<div class="row">
									<div class="col-md-6 col-sm-3 col-xs-12"><label>系統識別碼</label></div>
									<div class="col-md-6 col-sm-9 col-xs-12">
										<input type="text" id="key" class="form-control" name="key" value="{{ sizeof($errors) > 0 ? old('key') : $data['key'] }}" />
									</div>
								</div>
								@if ($errors->has('key'))
									<p class="help-block">
										<strong>{{ $errors->first('key') }}</strong>
									</p>
								@endif
							</div>
						</div>
					</div>
					<div class="form-group{{ $errors->has('scope') ? ' has-error' : '' }}">
						<label>可調用資料範圍</label>
						<div>
							<label style="margin-right: 12px;"><input type="checkbox" name="scope0" style="margin-right: 3px;" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('scope0')) ? 'checked="checked"' : '' ) : ( $data['scope0'] == 'Y' ? 'checked="checked"' : '' ) }} />me</label>
							<label style="margin-right: 12px;"><input type="checkbox" name="scope1" style="margin-right: 3px;" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('scope1')) ? 'checked="checked"' : '' ) : ( $data['scope1'] == 'Y' ? 'checked="checked"' : '' ) }} />email</label>
							<label style="margin-right: 12px;"><input type="checkbox" name="scope2" style="margin-right: 3px;" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('scope2')) ? 'checked="checked"' : '' ) : ( $data['scope2'] == 'Y' ? 'checked="checked"' : '' ) }} />user</label>
							<label style="margin-right: 12px;"><input type="checkbox" name="scope3" style="margin-right: 3px;" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('scope3')) ? 'checked="checked"' : '' ) : ( $data['scope3'] == 'Y' ? 'checked="checked"' : '' ) }} />idno</label>
							<label style="margin-right: 12px;"><input type="checkbox" name="scope4" style="margin-right: 3px;" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('scope4')) ? 'checked="checked"' : '' ) : ( $data['scope4'] == 'Y' ? 'checked="checked"' : '' ) }} />profile</label>
							<label style="margin-right: 12px;"><input type="checkbox" name="scope5" style="margin-right: 3px;" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('scope5')) ? 'checked="checked"' : '' ) : ( $data['scope5'] == 'Y' ? 'checked="checked"' : '' ) }} />account</label>
							<label style="margin-right: 12px;"><input type="checkbox" name="scope6" style="margin-right: 3px;" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('scope6')) ? 'checked="checked"' : '' ) : ( $data['scope6'] == 'Y' ? 'checked="checked"' : '' ) }} />school</label>
							<label style="margin-right: 12px;"><input type="checkbox" name="scope7" style="margin-right: 3px;" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('scope7')) ? 'checked="checked"' : '' ) : ( $data['scope7'] == 'Y' ? 'checked="checked"' : '' ) }} />schoolAdmin</label>
						</div>
						@if ($errors->has('scope'))
							<p class="help-block">
								<strong>{{ $errors->first('scope') }}</strong>
							</p>
						@endif
					</div>
					<div class="form-group">
						<label>
							<input type="checkbox" id="authyn" name="authyn" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('authyn')) ? 'checked="checked"' : '' ) : ( $data['authyn'] == 'Y' ? 'checked="checked"' : '' ) }} />
							本系統12歲以下學童需要家長同意，始能個資授權
						</label>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-sm-6">
								<label>
									<input type="checkbox" id="stopyn" name="stopyn" value="Y" {{ sizeof($errors) > 0 ? ( !empty(old('stopyn')) ? 'checked="checked"' : '' ) : ( $data['stopyn'] == 'Y' ? 'checked="checked"' : '' ) }} />
									撤銷使用許可
								</label>
							</div>
							<div class="col-md-6 col-sm-12">
								<div class="form-group{{ $errors->has('stopdt') ? ' has-error' : '' }}">
									<div class="row">
										<div class="col-md-6 col-sm-3 col-xs-12"><label>撤銷日期</label></div>
										<div class="col-md-6 col-sm-9 col-xs-12">
											<input type="text" id="stopdt" class="form-control" name="stopdt" value="{{ sizeof($errors) > 0 ? old('stopdt') : $data['stopdt'] }}" placeholder="yyyymmdd" />
										</div>
									</div>
									@if ($errors->has('stopdt'))
										<p class="help-block">
											<strong>{{ $errors->first('stopdt') }}</strong>
										</p>
									@endif
								</div>
							</div>
						</div>
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-success">儲存</button>
						<button type="button" class="btn btn-danger" onclick="window.location.href='/bureau/thirdapp';" style="margin-left: 40px;">返回</button>
					</div>
				</div>
			</div>
		@csrf
		</form>
	</div>
	<script type="text/javascript">
		function unittype(t) {
			if(t.checked)
				$("#unittype").find(":checkbox").not($(t)).prop('checked',false);
		}
	</script>
	@if ($errors->has('addError'))
	<script type="text/javascript">
		window.onload = function() { $("#addThirdappModal").modal('show'); };
	</script>
	@endif
</div>
@endsection