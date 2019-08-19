(function (){
	function enableAce() {
		$(".raw-section").each(function() {
			var content = $(this).text();
			var editor = ace.edit(this, {
				useWorker: false,
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
	}

	function resizeLastSection() {
		$(".content-section .left").last().height($(window).height());
	}

	function bookmark() {

		var scrollTo = function (element) {
			if (element.length > 0) {
				var scroll = element.offset().top - 70;
				$(document).scrollTop(scroll);
			}
		};

		var active = function (element) {
			if (typeof element !== "undefined") {
				$(document).find(".bookmark a, .bookmark li").removeClass("active");
				element.parents("li").addClass("active");
			}
		};

		if(window.location.hash) {
			active($(document).find(".bookmark a[href=\"" + window.location.hash + "\"]"));
			scrollTo($("body").find("[data-bookmark=\"" + window.location.hash + "\"]"));
		}

		$(document).on("click", ".bookmark a", function () {
			var link = $(this).attr("href");
			if (link[0] === "#") {
				active($(this));
				scrollTo($("body").find("[data-bookmark=\"" + link + "\"]"));
			}
		});

		var elements = [];
		$("body").find("[data-bookmark]").each(function () {
			elements[$(this).offset().top] = $(this);
		});

		$(window).scroll(function(){
			var items = [];
			elements.forEach(function (element, size) {
				if (this.scrollY + 80 > size ) {
					items.push(element);
				}
			});

			var element = items[items.length - 1];
			if (typeof element !== "undefined") {
				var url = element.attr("data-bookmark");
				var link = $(document).find(".bookmark a[href=\"" + url + "\"]");
				active(link);
			}
		});
	}

	$(document).ready(function() {
		setTimeout(function () {
			bookmark();
		}, 500);
		enableAce();

		resizeLastSection();
		$( window ).resize(function() {
			resizeLastSection();
		});
	});
})();


