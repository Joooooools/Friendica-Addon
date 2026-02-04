{{include file="field_checkbox.tpl" field=$enabled}}	
	<input type="text" name="header_img" id="header_img"/> <button id="coverphoto_browse" type="button" class="btn btn-default" onclick="javascript:Dialog.doImageBrowser('banner','header_img');">{{$browse}}</button>
	<span class="help-block" id="header_img_tip" role="tooltip" style="display:block;font-size:small;">
	{{$instructions}}
	</span>
{{if $error}}
	<p id="error_msg" style="border:2px solid red;background-color:black;color:yellow;padding:5px;text-align:center;"><strong>{{$error}}</strong></p>
{{/if}}
<p>{{$prevhead}}:
<div id="preview_area">
{{if $preview }}
<img id="preview" style="max-width:100%;" src="{{$preview}}"></p>
{{/if}}
</div>
<script>

$(document).ready(function () {
	$("body").on("fbrowser.photo.banner", function(e, filename, embedcode, id, img) {
		if (img){
			var img_url = img;
		} else {
			/* 	filename is useless because it is the name of the image when it was uploaded
				what we need is the valid URL to the largest version of the image and we
				need to extract that from the embedcode the modal sends
			*/
			var regex = /\[url=.*\]\[img=|\].*\[\/img\]\[\/url\]/gi;
			var img_url = embedcode.replace(regex, "");
		}
 		// close colorbox
 		$.colorbox.close();
 		// replace textarea text with filename
 		$("#"+id+"").val(img_url);
 		coverphoto_preview(img_url);
 	});
 	$("#coverphoto_browse, #header_img").on("click", function(e){
 		if ($("#error_msg").length != 0){
 			$("#error_msg").hide();
 		}
 	});
 	// we probably need to add ajaxupload.js on the backend but check
	var ispresent = false; // assume it was not loaded
	var scripts = $("script");
	for(let s=0; s < scripts.length; s++){
		if (scripts[s].src.match(/ajaxupload/)){
			ispresent = true;
		}
	}
	if (ispresent){
		return;
	} else {
		var ajs = document.createElement('script');
			ajs.type = "text/javascript";
			ajs.src = baseurl + "/view/js/ajaxupload.js?v=1.0coverphoto";
		$('head').append(ajs);
	}
			
 });
 
function coverphoto_preview(url){
	// if preview img tag exists use it
	if($("#preview").length != 0){
		$("#preview").attr('src',url);
	} else {
		// preview img tag does not exist so create it
		var prev = document.createElement('img');
			prev.id = "preview";
			prev.src = url;
		$('#preview_area').append(prev);
	}
}
</script>
