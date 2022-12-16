{{--$question collection is available here--}}

@component('mail::message')
# Q: {{$question->question}}?

A: {{$question->answer}}

@component('mail::button', ['url' => $url ])
    View {{$product->name}}
@endcomponent
Thanks,<br>
{{ config('app.name') }}
@endcomponent
