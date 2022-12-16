{{--$review collection is available here--}}

@component('mail::message')
# A new rating has been given

Comments: {{ $review->comment }}<br>
Ratings: {{ $review->rating }}

@component('mail::button', ['url' => $url ])
    View {{$product->name}}
@endcomponent
Thanks,<br>
{{ config('app.name') }}
@endcomponent
