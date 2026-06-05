<div id="alertbanner-root" class="alertbanner-container alertbanner-{{$style|escape:'html'}}" data-banner-id="{{$banner_id|escape:'html'}}" style="--alertbanner-bg: {{$bg_color|escape:'html'}}; --alertbanner-text: {{$text_color|escape:'html'}};">
    <div class="alertbanner-content">
        <span class="alertbanner-icon" aria-hidden="true"></span>
        <span class="alertbanner-text">{{$text|escape:'html'}}</span>
        <button id="alertbanner-close-btn" class="alertbanner-close" type="button" aria-label="{{$close_label|escape:'html'}}">&times;</button>
    </div>
</div>
