/* Cascading country/state/city — cache, loading states, optional city. */
jQuery(function ($) {
	var i18n = (tc_csca_auto_ajax && tc_csca_auto_ajax.i18n) ? tc_csca_auto_ajax.i18n : {};
	var cache = { states: {}, cities: {} };

	function t(key, fallback) {
		return i18n[key] || fallback;
	}

	function isEmptyId(id) {
		return !id || id === "0" || id === 0 || id === "0001";
	}

	function emptyLabel($el, key, fallback) {
		return $el.attr("data-placeholder") || t(key, fallback);
	}

	function optionHtml(value, dataId, label) {
		return "<option value='" + value + "' data-id='" + dataId + "'>" + label + "</option>";
	}

	function setLoading($el) {
		$el.prop("disabled", true).attr("aria-busy", "true");
		$el.html(optionHtml("0001", "0001", t("loading", "Loading...")));
	}

	function setEmpty($el, label, disable) {
		$el.attr("aria-busy", "false");
		$el.html(optionHtml("0", "0", label));
		$el.prop("disabled", !!disable);
	}

	function fillOptions($el, rows, emptyLbl) {
		$el.attr("aria-busy", "false").prop("disabled", false);
		$el.html(optionHtml("0", "0", emptyLbl));
		if (!rows || !rows.length) {
			return false;
		}
		for (var i = 0; i < rows.length; i++) {
			$el.append(optionHtml(rows[i].name, rows[i].id, rows[i].name));
		}
		return true;
	}

	/**
	 * Resolve paired select:
	 * 1) data-tc-group
	 * 2) same index in form
	 * 3) first match (legacy)
	 */
	function findPaired($el, typeClass) {
		var $form = $el.closest("form");
		var group = $el.attr("data-tc-group");
		if (group) {
			var $byGroup = $form.find("select." + typeClass + '[data-tc-group="' + group + '"]');
			if ($byGroup.length) {
				return $byGroup.first();
			}
		}

		var fromClass = $el.hasClass("country_auto")
			? "country_auto"
			: $el.hasClass("state_auto")
				? "state_auto"
				: "city_auto";

		var $fromAll = $form.find("select." + fromClass);
		var idx = $fromAll.index($el);
		var $targets = $form.find("select." + typeClass);

		if (idx >= 0 && $targets.eq(idx).length) {
			return $targets.eq(idx);
		}
		return $targets.first();
	}

	function fetchStates(cnt, done) {
		if (cache.states[cnt]) {
			done(cache.states[cnt]);
			return;
		}
		$.ajax({
			url: tc_csca_auto_ajax.ajax_url,
			type: "post",
			dataType: "json",
			data: {
				action: "tc_csca_get_states",
				nonce_ajax: tc_csca_auto_ajax.nonce,
				cnt: cnt
			}
		})
			.done(function (response) {
				var rows = response && response.length ? response : [];
				cache.states[cnt] = rows;
				done(rows);
			})
			.fail(function () {
				done(null);
			});
	}

	function fetchCities(sid, done) {
		if (cache.cities[sid]) {
			done(cache.cities[sid]);
			return;
		}
		$.ajax({
			url: tc_csca_auto_ajax.ajax_url,
			type: "post",
			dataType: "json",
			data: {
				action: "tc_csca_get_cities",
				nonce_ajax: tc_csca_auto_ajax.nonce,
				sid: sid
			}
		})
			.done(function (response) {
				var rows = response && response.length ? response : [];
				cache.cities[sid] = rows;
				done(rows);
			})
			.fail(function () {
				done(null);
			});
	}

	// Country → State (+ reset City if present). City field is optional.
	$(document).on("change", "select.country_auto", function () {
		var $country = $(this);
		var $state = findPaired($country, "state_auto");
		var $city = findPaired($country, "city_auto");

		if (!$state.length) {
			return;
		}

		var cnt = $country.children("option:selected").attr("data-id");
		var stateLbl = emptyLabel($state, "select_state", "Select State");
		var cityLbl = $city.length ? emptyLabel($city, "select_city", "Select City") : "";

		if ($city.length) {
			setEmpty($city, cityLbl, true);
		}

		if (isEmptyId(cnt)) {
			setEmpty($state, stateLbl, true);
			return;
		}

		setLoading($state);
		fetchStates(cnt, function (rows) {
			if (rows === null) {
				setEmpty($state, t("state_not_found", "State list not found"), false);
				return;
			}
			if (!fillOptions($state, rows, stateLbl)) {
				setEmpty($state, t("state_not_found", "State list not found"), false);
			}
		});
	});

	// State → City (no-op if form has country+state only).
	$(document).on("change", "select.state_auto", function () {
		var $state = $(this);
		var $city = findPaired($state, "city_auto");

		if (!$city.length) {
			return;
		}

		var sid = $state.children("option:selected").attr("data-id");
		var cityLbl = emptyLabel($city, "select_city", "Select City");

		if (isEmptyId(sid)) {
			setEmpty($city, cityLbl, true);
			return;
		}

		setLoading($city);
		fetchCities(sid, function (rows) {
			if (rows === null) {
				setEmpty($city, t("city_not_found", "City list not found"), false);
				return;
			}
			if (!fillOptions($city, rows, cityLbl)) {
				setEmpty($city, t("city_not_found", "City list not found"), false);
			}
		});
	});

	// Initial: disable empty dependent fields (city optional).
	$("select.state_auto, select.city_auto").each(function () {
		var $el = $(this);
		if (isEmptyId($el.children("option:selected").attr("data-id"))) {
			$el.prop("disabled", true);
		}
	});

	// Disabled fields are omitted from submit — re-enable so CF7 still receives values.
	function reenableDependent(form) {
		$(form).find("select.state_auto, select.city_auto").prop("disabled", false);
	}
	$(document).on("submit", "form.wpcf7-form", function () {
		reenableDependent(this);
	});
	$(document).on("wpcf7beforesubmit", "form.wpcf7-form", function () {
		reenableDependent(this);
	});
});
