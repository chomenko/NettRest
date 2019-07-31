$(document).ready(function() {
	$(".raw-section").each(function(){
		var content = $(this).text();
		var editor = ace.edit(this, {
			maxLines: Infinity,
			mode: "ace/mode/json",
			selectionStyle: "text",

			readOnly: true,
			highlightActiveLine: false,
			highlightGutterLine: false
		});
		editor.setValue(content);
		editor.clearSelection();
		editor.setOption("displayIndentGuides", false);
		editor.setOption("showPrintMargin", false);
		editor.setTheme("ace/theme/monokai");
	});
});
