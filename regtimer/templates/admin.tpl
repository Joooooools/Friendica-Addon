<div class="settings-block">
	<div class="alert alert-info" role="alert">
		<strong>{{$worker_note_title}}</strong><br />
		{{$worker_note}}
	</div>

	<p>
		<strong>{{$instance_tz_label}}:</strong> {{$instance_tz}} ({{$instance_time}})<br />
		<strong>{{$admin_tz_label}}:</strong> {{$admin_tz}} ({{$admin_time}})
	</p>
	<p class="descriptive-text">
		{{$timezone_note}}
	</p>

	{{include file="field_checkbox.tpl" field=$enabled}}
	{{include file="field_input.tpl" field=$start_time}}
	{{include file="field_input.tpl" field=$end_time}}
	{{include file="field_select.tpl" field=$policy}}
	{{include file="field_select.tpl" field=$day_policy}}

	<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>
</div>
