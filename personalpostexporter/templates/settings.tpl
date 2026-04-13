<div class="ppex-settings-intro">
    {{$description}}
</div>
<br>

<div class="alert alert-warning">
    <strong>{{$important_label}}</strong>
    {{$importance_warning}}
</div>

<div class="alert alert-info">
    <h4>{{$info_title}}</h4>
    <ul class="ppex-info-list">
        <li><strong>{{$label_exported}}</strong> {{$text_exported}}</li>
        <li><strong>{{$label_excluded}}</strong> {{$text_excluded_1}} <u>{{$text_excluded_not}}</u> {{$text_excluded_2}}</li>
        <li><strong>{{$label_privacy}}</strong> {{$text_privacy}}</li>
        <li><strong>{{$label_images}}</strong> {{$text_images_1}} <strong>{{$text_images_never}}</strong> {{$text_images_2}}</li>
        <li><strong>{{$label_how}}</strong> {{$text_how_1}} <code>{{$text_how_code}}</code> {{$text_how_2}}</li>
    </ul>
</div>

{{if $busy_alert}}
    <div class="alert alert-warning">
        <strong>{{$busy_label}}</strong> {{$busy_alert}}
    </div>
    <br>
{{/if}}

<div class="ppex-status-bar">
    {{$member_since}} <strong>{{$reg_date}}</strong>
</div>
<br>

{{include file="field_select.tpl" field=$media_config}}
{{include file="field_select.tpl" field=$theme_config}}
{{include file="field_select.tpl" field=$year_config}}
<br>

<div class="alert alert-info">
    {{$export_notice}}
</div>
