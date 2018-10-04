@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level == 'error')
# 糟糕！
@else
# 嗨！
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    switch ($level) {
        case 'success':
        case 'error':
            $color = $level;
            break;
        default:
            $color = 'primary';
    }
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
{{ config('app.name') }} 敬上
@endif

{{-- Subcopy --}}
@isset($actionText)
@component('mail::subcopy')
如果您無法按下 "{{ $actionText }}" 按鈕, 請將下列網址複製貼到瀏覽器直接連線：
[{{ $actionUrl }}]({!! $actionUrl !!})
@endcomponent
@endisset
@endcomponent
