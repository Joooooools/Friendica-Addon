<p>{{$description}}</p>
<div class="register-success-preview-wrapper" style="margin-bottom: 20px;">
	<a href="{{$preview_url}}" target="_blank" rel="noopener noreferrer" class="btn btn-default" style="display: inline-flex; align-items: center; gap: 8px;">
		<i class="fa fa-eye"></i> {{$preview_text}}
	</a>
</div>

{{if $status_text}}
<div class="alert alert-{{$status_class}}" role="alert" style="margin-bottom: 20px;">
	<strong>{{$status_label}}:</strong> {{$status_text}}
</div>
{{/if}}

<hr>
<h3>{{$header_open}}</h3>
{{include file="field_input.tpl" field=$title_open}}
{{include file="field_textarea.tpl" field=$message_open}}

<hr>
<h3>{{$header_approve}}</h3>
{{include file="field_input.tpl" field=$title_approve}}
{{include file="field_textarea.tpl" field=$message_approve}}

<div class="settings-submit-wrapper">
	<input type="submit" id="register_success-submit" name="register_success-submit" class="settings-submit" value="{{$submit}}" />
</div>
