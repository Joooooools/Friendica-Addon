<div class="settings-block">
	<p>
		<strong>{{$instance_tz_label}}:</strong> {{$instance_tz}} ({{$instance_time}})<br />
		<strong>{{$admin_tz_label}}:</strong> {{$admin_tz}} ({{$admin_time}})
	</p>
	<p class="descriptive-text">
		{{$timezone_note}}
	</p>

	{{include file="field_checkbox.tpl" field=$active}}
	{{include file="field_input.tpl" field=$text}}
	{{include file="field_select.tpl" field=$style}}
	{{include file="field_select.tpl" field=$visibility}}
	{{include file="field_input.tpl" field=$bg_color}}
	{{include file="field_input.tpl" field=$text_color}}

	{{include file="field_input.tpl" field=$starts_at}}
	{{include file="field_input.tpl" field=$ends_at}}

	<div class="submit">
		<button type="submit" class="btn btn-primary">{{$submit}}</button>
	</div>
</div>
