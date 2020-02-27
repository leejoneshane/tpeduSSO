@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8" style="margin-left: 20%">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header"><h4>感謝您！</h4></div>
                <div class="card-body">
                    @if ($new)
                        <p class="mb-0">管理人員將會在收到通知信件後，儘快進行介接專案審核。審核結果出來時系統將會主動通知您！</p>
                        <p class="mb-0">如果申請內容有任何異動，您可以使用底下的唯一識別碼進行修改：</p>
                        <p class="mb-0 text-danger">{{ $uuid }}</p>
                        <p class="mb-0">唯一識別碼只會核發一次，在視窗關閉後將不會再次顯示，請務必妥善保存！爾後若專案有任何異動，您都可以自行修改，無需再向承辦人提交申請！</p>
                    @else
                        <p class="mb-0">系統已為您變更介接專案內容，並且已經生效！</p>
                        <p class="mb-0">如果您有修改『授權碼回傳網址』，已經登入的使用者並不會受到影響，但尚未完成登入的使用者將會登入失敗！請您務必儘快修改程式讓大家都能正常登入！</p>
                @endif
                </div>
            </div>
        </div>
	</div>
</div>
@endsection