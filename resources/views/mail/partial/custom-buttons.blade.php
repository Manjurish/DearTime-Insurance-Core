@foreach($buttons as $button)
<p>
    <a href="{{ $button['link'] }}" class="button"
      style="color:#ffffff;text-decoration:none;background-color:#000000;border-radius:30px;padding:8px 20px;display:inline-block;margin:16px 0;">
        {!! $button['text'] !!}
    </a>
</p>
@endforeach
