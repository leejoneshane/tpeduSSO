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

			<div class="panel panel-default">	  
				<div class="panel-heading" style="overflow: hidden;">
					<a href="#addThirdappModal" class="btn btn-success" data-toggle="modal" style="float: right;margin-top: 3px;">
						<i class="glyphicon glyphicon-plus-sign"></i><span style="margin-left: 4px;">新增</span>
					</a>
					<h4 style="float: left;">第三方應用</h4>
					<form action="{{ route('bureau.thirdapp') }}" style="float: left;margin: 4px 0 0 6px;">
						@csrf
						<input type="text" name="entry" value="{{ $entry }}" maxlength="20" style="margin: 0 8px;" placeholder="關鍵字查詢"/>
						<button type="submit" class="btn btn-success">查詢</button>
					</form>
				</div>
				<div class="panel-body">
					<table class="table table-hover" style="margin: 0;">
						<thead>
							<tr>
								<th>應用平臺名稱</th>
								<th>應用平臺網址</th>
								<th>申請單位</th>
								<th>業務聯絡窗口</th>
								<th>管理</th>
							</tr>
						</thead>
						<tbody>
						@if (!empty($apps))
						@foreach ($apps as $app)
							<tr>
								<td style="vertical-align: inherit;">
									<label>{{ $app['entry'] }}</label>
								</td>
								<td style="vertical-align: inherit;">
									<label><a href="{{ $app['url'] }}" target="_blank">{{ $app['url'] }}</a></label>
								</td>
								<td style="vertical-align: inherit;">
									<label>{{ $app['unit'] }}</label>
								</td>
								<td style="vertical-align: inherit;">
									<label>{{ $app['contel'] }}<br/>{{ $app['conman'] }}</label>
								</td>
								<td>
									<button type="button" class="btn btn-primary"
							 	onclick="$('<form></form>').appendTo('body').attr('action','{{ route('bureau.updateThirdapp', $app['id']) }}').submit();">編輯</button>
									<button type="button" class="btn btn-danger"
										onclick="if(confirm('確定要刪除『'+$(this).parent().parent().find('label:first').text()+'』?')){$('#remove-form').attr('action','{{ route('bureau.removeThirdapp', [ 'id' => $app['id'] ]) }}');
												 $('#remove-form').submit();}">刪除</button>
								</td>
							</tr>
						@endforeach
						@endif
						</tbody>
					</table>
				</div>
			</div>
    
		<form id="remove-form" action="" method="POST" style="display: none;">
		@csrf
		</form>
	</div>

	<div id="addThirdappModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<div class="panel-heading">
						<button type="button" data-dismiss="modal" aria-label="Close" class="close" style="padding: 8px 0;">
							<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
						</button>
						<h4>新增第三方應用</h4>
					</div>
					<div class="panel-body">
						<form role="form" method="POST" action="{{ route('bureau.appendThirdapp') }}">
							@csrf
							<div class="form-group{{ $errors->has('unit') ? ' has-error' : '' }}">
								<label>申請單位(機關名稱)</label>
								<input type="text" id="unit" class="form-control" name="unit" value="{{ old('unit') }}" required />
								@if ($errors->has('unit'))
									<p class="help-block">
										<strong>{{ $errors->first('unit') }}</strong>
									</p>
								@endif
							</div>
							<div class="form-group{{ $errors->has('entry') ? ' has-error' : '' }}">
								<label>應用平臺名稱(網站服務入口)</label>
								<input type="text" id="entry" class="form-control" name="entry" value="{{ old('entry') }}" required />
								@if ($errors->has('entry'))
									<p class="help-block">
										<strong>{{ $errors->first('entry') }}</strong>
									</p>
								@endif
							</div>
							<div class="form-group{{ $errors->has('background') ? ' has-error' : '' }}">
								<label>應用平臺申請背景說明</label>
								<input type="text" id="background" class="form-control" name="background" value="{{ old('background') }}" />
							</div>
							<div class="form-group{{ $errors->has('url') ? ' has-error' : '' }}">
								<label>應用平臺網址</label>
								<input type="text" id="url" class="form-control" name="url" value="{{ old('url') }}" required />
								@if ($errors->has('url'))
									<p class="help-block">
										<strong>{{ $errors->first('url') }}</strong>
									</p>
								@endif
							</div>
							<div class="form-group{{ $errors->has('redirect') ? ' has-error' : '' }}">
								<label>SSO認證後重導向URL</label>
								<input type="text" id="redirect" class="form-control" name="redirect" value="{{ old('redirect') }}" required />
								@if ($errors->has('redirect'))
									<p class="help-block">
										<strong>{{ $errors->first('redirect') }}</strong>
									</p>
								@endif
							</div>
							<div class="form-group{{ $errors->has('unittype') ? ' has-error' : '' }}">
								<label>單位別</label>
								<div id="unittype">
									<label style="margin-right: 10px;"><input type="checkbox" name="unittype1" style="margin-right: 3px;" onclick="unittype(this)" value="Y" {{ !empty(old('unittype1')) ? 'checked="checked"' : '' }} />本局</label>
									<label style="margin-right: 10px;"><input type="checkbox" name="unittype2" style="margin-right: 3px;" onclick="unittype(this)" value="Y" {{ !empty(old('unittype2')) ? 'checked="checked"' : '' }} />學校</label>
									<label style="margin-right: 10px;"><input type="checkbox" name="unittype3" style="margin-right: 3px;" onclick="unittype(this)" value="Y" {{ !empty(old('unittype3')) ? 'checked="checked"' : '' }} />第三方</label>
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
											<input type="text" id="conman" class="form-control" name="conman" value="{{ old('conman') }}" required />
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
											<input type="text" id="conmail" class="form-control" name="conmail" value="{{ old('conmail') }}" />
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
											<input type="text" id="conunit" class="form-control" name="conunit" value="{{ old('conunit') }}" />
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
											<input type="text" id="contel" class="form-control" name="contel" value="{{ old('contel') }}" required />
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
													<input type="text" id="recdt" class="form-control" name="recdt" value="{{ old('recdt') }}" placeholder="yyyymmdd" />
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
													<input type="text" id="recno" class="form-control" name="recno" value="{{ old('recno') }}" />
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
												<input type="text" id="key" class="form-control" name="key" value="{{ old('key') }}" />
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
									<label style="margin-right: 12px;"><input type="checkbox" name="scope0" style="margin-right: 3px;" value="Y" {{ !empty(old('scope0')) ? 'checked="checked"' : '' }} />me</label>
									<label style="margin-right: 12px;"><input type="checkbox" name="scope1" style="margin-right: 3px;" value="Y" {{ !empty(old('scope1')) ? 'checked="checked"' : '' }} />email</label>
									<label style="margin-right: 12px;"><input type="checkbox" name="scope2" style="margin-right: 3px;" value="Y" {{ !empty(old('scope2')) ? 'checked="checked"' : '' }} />user</label>
									<label style="margin-right: 12px;"><input type="checkbox" name="scope3" style="margin-right: 3px;" value="Y" {{ !empty(old('scope3')) ? 'checked="checked"' : '' }} />idno</label>
									<label style="margin-right: 12px;"><input type="checkbox" name="scope4" style="margin-right: 3px;" value="Y" {{ !empty(old('scope4')) ? 'checked="checked"' : '' }} />profile</label>
									<label style="margin-right: 12px;"><input type="checkbox" name="scope5" style="margin-right: 3px;" value="Y" {{ !empty(old('scope5')) ? 'checked="checked"' : '' }} />account</label>
									<label style="margin-right: 12px;"><input type="checkbox" name="scope6" style="margin-right: 3px;" value="Y" {{ !empty(old('scope6')) ? 'checked="checked"' : '' }} />school</label>
									<label style="margin-right: 12px;"><input type="checkbox" name="scope7" style="margin-right: 3px;" value="Y" {{ !empty(old('scope7')) ? 'checked="checked"' : '' }} />schoolAdmin</label>
								</div>
								@if ($errors->has('scope'))
									<p class="help-block">
										<strong>{{ $errors->first('scope') }}</strong>
									</p>
								@endif
							</div>
							<div class="form-group">
								<label>
									<input type="checkbox" id="authyn" name="authyn" value="Y" {{ !empty(old('authyn')) ? 'checked="checked"' : '' }} />
									本系統12歲以下學童需要家長同意，始能個資授權
								</label>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label>
											<input type="checkbox" id="stopyn" name="stopyn" value="Y" {{ !empty(old('stopyn')) ? 'checked="checked"' : '' }} />
											撤銷使用許可
										</label>
									</div>
									<div class="col-md-6 col-sm-12">
										<div class="form-group{{ $errors->has('stopdt') ? ' has-error' : '' }}">
											<div class="row">
												<div class="col-md-6 col-sm-3 col-xs-12"><label>撤銷日期</label></div>
												<div class="col-md-6 col-sm-9 col-xs-12">
													<input type="text" id="stopdt" class="form-control" name="stopdt" value="{{ old('stopdt') }}" placeholder="yyyymmdd" />
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
								<button type="submit" class="btn btn-success">新增</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		function unittype(t) {
			if(t.checked)
				$("#unittype").find(":checkbox").not($(t)).prop('checked',false);
		}
	</script>
	@if (sizeof($errors) > 0)
	<script type="text/javascript">
		window.onload = function() { $("#addThirdappModal").modal('show'); };
	</script>
	@endif
</div>
@endsection