jQuery(function ($) {
	function infoTip(div) {
		var tip = $(div).find("option:selected").attr("data-tip");
		$(".select-info-tip").html(tip || "");
	}
	infoTip("select#patch");
	$("select#patch").on("change", function () {
		infoTip($(this));
	});

	function showResponse(msg) {
		var $box = $("#tc-csca-data-response");
		if (!$box.length) {
			$box = $("<div id='tc-csca-data-response' class='tc_response_update'></div>");
			$(".patch-button").first().after($box);
		}
		$box.stop(true, true).show().text(msg);
		setTimeout(function () {
			$box.fadeOut();
		}, 8000);
	}

	function reinstall(force) {
		$.ajax({
			url: tc_csca_auto_ajax.ajax_url,
			type: "post",
			dataType: "json",
			data: {
				action: "tc_csca_reinstall_data",
				nonce_ajax: tc_csca_auto_ajax.nonce,
				force: force ? 1 : 0
			},
			success: function (response) {
				if (response && response.message) {
					showResponse(response.message);
					if (response.counts) {
						window.setTimeout(function () {
							window.location.reload();
						}, 1200);
					}
				}
			},
			error: function () {
				showResponse("Request failed. Please try again.");
			}
		});
	}

	$("#tc-csca-install-data").on("click", function (e) {
		e.preventDefault();
		reinstall(false);
	});

	$("#tc-csca-force-reinstall").on("click", function (e) {
		e.preventDefault();
		if (
			!window.confirm(
				"This will DELETE all countries, states, and cities in the plugin tables and reload the default dataset. Your Contact Form 7 forms/tags are not deleted. Continue?"
			)
		) {
			return;
		}
		reinstall(true);
	});

	$("#update-patch").on("click", function (e) {
		e.preventDefault();
		var optval = $("#patch").val();
		var cnt = $("#patch").find("option:selected").attr("data-country");
		$.ajax({
			url: tc_csca_auto_ajax.ajax_url,
			type: "post",
			dataType: "json",
			data: {
				action: "tc_csca_patch_settings",
				nonce_ajax: tc_csca_auto_ajax.nonce,
				value: optval,
				country: cnt
			},
			success: function (response) {
				if (response && response.message) {
					if ($(".tc_response_update.patch-msg").length) {
						$(".tc_response_update.patch-msg").remove();
					}
					$(".patch-button")
						.last()
						.append(
							"<div class='tc_response_update patch-msg'>" +
								response.message +
								"</div>"
						);
					setTimeout(function () {
						$(".tc_response_update.patch-msg").fadeOut().remove();
					}, 5000);
				}
			}
		});
	});
});
