@extends('layouts.superboard')

@section('page_heading')
匯入教育機構
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
	    <div class="alert alert-info">
	    @foreach (session('success') as $line)
		{{ $line }}<br>
		@endforeach
	    </div>
	@endif
	<div class="col-sm-12">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>為什麼用 JSON?</h4>
		</div>
		<div class="panel-body">
			<div class="form-group">
			跟 EXCEL 比起來，JSON 的優點是格式開源，因此不用買授權。跟 XML 比起來，JSON 輕薄短小不佔磁碟空間，可以節省網路流量。跟 CSV 比起來，JSON 可以表徵陣列和物件，可以交換二進位檔案內容。
			JSON 有人用嗎？有的，事實上絕大多數的網站，只要有交換資料的需求，都會選擇 JSON！不會用 JSON？沒問題！任何格式都可以線上轉成 JSON，請自行 Google 使用。
			JSON 支援標準 UTF-8 格式，不支援微軟 UTF-8 BOM 格式，存檔時請務必挑選正確的儲存格式。
			</div>
		</div>
		</div>
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>機構資訊 JSON 格式範例</h4>
		</div>
		<div class="panel-body">
			<div class="form-group">
			{{ json_encode($sample1, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}
			</div>
		</div>
		</div>
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>移除可省略欄位後之精簡範例</h4>
		</div>
		<div class="panel-body">
			<div class="form-group">
			{{ json_encode($sample2, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}
			</div>
		</div>
		</div>
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>欄位格式說明</h4>
		</div>
		<div class="panel-body">
			<div class="form-group">
				<dl class="dl-horizontal">
					<dt>通則</dt>
					<dd>在 JSON 中，結構化的資料都視為物件，前後需使用 { } 括起來，物件是由多個欄位集合而成，欄位名稱與欄位值之間使用 : 隔開，欄位名稱前後須加 " "。欄位為單一資料時，若資料為數字，前後不加 " "；若為字串，前後需使用 " " 括起來。欄位為多筆資料時，用陣列表示，每筆資料中間用 , 間隔，前後需使用 [ ] 括起來。除了必填欄位外，未包含的欄位將忽略不處理，若要刪除欄位，請賦值空陣列，寫為 [] 。</dd>
					<dt>id</dt>
					<dd>系統代號，是單一資料。為各教育機構英文網域名稱的縮寫。</dd>
					<dt>sid</dt>
					<dd>統ㄧ編號，是單一資料。由教育部編定，固定使用 6 位數字。</dd>
					<dt>name</dt>
					<dd>機構全銜，是單一資料。</dd>
					<dt>category</dt>
					<dd>機構類別，是單一資料。請從右列資料擇一：{{ implode('、', Config::get('app.schoolCategory')) }}。</dd>
					<dt>area</dt>
					<dd>行政區，是單一資料。請從右列資料擇一：{{ implode('、', Config::get('app.areas')) }}。</dd>
					<dt>fax</dt>
					<dd>傳真電話，允許多筆資料，格式如：(02)23093736。可省略。</dd>
					<dt>tel</dt>
					<dd>聯絡電話，允許多筆資料，格式同上。可省略。</dd>
					<dt>postal</dt>
					<dd>郵遞區號，是單一資料。可省略。</dd>
					<dt>address</dt>
					<dd>郵寄地址，是單一資料。可省略。</dd>
					<dt>mbox</dt>
					<dd>教育局聯絡箱編號，是單一資料。由教育局編定，固定使用 3 位數字。可省略。</dd>
					<dt>www</dt>
					<dd>機構官方網址，是單一資料。可省略。</dd>
					<dt>ipv4</dt>
					<dd>機構 IPv4 網路地址及遮罩，允許多筆資料，格式如：163.21.228.0/24。可省略。</dd>
					<dt>ipv6</dt>
					<dd>機構 IPv6 網路地址及遮罩，允許多筆資料，格式如：2001:288:12ce::/64。可省略。</dd>
				</dl>
			</div>
		</div>
		</div>
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>批量匯入</h4>
		</div>
		<div class="panel-body">
			<div class="form-group">
			想要一次匯入多個機構嗎？一個機構的資料是一個物件，機構之間使用 , 間隔，表徵成陣列。例如：
			<p>
			[第一個機構的JSON字串,第二個機構的JSON字串,......省略.....,最後一個機構的JSON字串]
			</p>
			</div>
		</div>
		</div>
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>匯入 JSON</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('bureau.jsonOrg') }}" enctype="multipart/form-data">
		    	@csrf
			    <div class="form-group{{ $errors->has('json') ? ' has-error' : '' }}">
					<input id="json" type="file" class="form-control" name="json" required>
					@if ($errors->has('json'))
						<p class="help-block">
							<strong>{{ $errors->first('json') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
					<button type="submit" class="btn btn-success">匯入</button>
				</div>
			</form>
		</div>
		</div>
	</div>
	</div>
</div>
@endsection
