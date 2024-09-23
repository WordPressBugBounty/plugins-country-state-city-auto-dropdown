jQuery(function (t) {
    t("select.country_auto").change(function () {
        if (t("select.state_auto").length > 0) {
            var a = t("select.country_auto").children("option:selected").attr("data-id");
            t("select.state_auto").html('<option value="0">Select State</option>'),
                t("select.city_auto").html('<option value="0">Select City</option>'),
                jQuery.ajax({
                    url: tc_csca_auto_ajax.ajax_url,
                    type: "post", dataType: "json",
                    data: { action: "tc_csca_get_states", nonce_ajax: tc_csca_auto_ajax.nonce, cnt: a },
                    success: function (a) {
                        for (i = 0; i < a.length; i++) {
                            var e = a[i].id, c = a[i].name,
                                o = "<option data-id='" + e + "' value='" + c + "'>" + c + "</option>";
                            t("select.state_auto").append(o)
                        }
                    }
                });
        }
    });
    
    t("select.state_auto").change(function () {
        if (t("select.city_auto").length > 0) {
            var a = t(this).children("option:selected").attr("data-id");
            t("select.city_auto").html('<option value="0">Select City</option>'),
                jQuery.ajax({
                    url: tc_csca_auto_ajax.ajax_url,
                    type: "post", dataType: "json",
                    data: { action: "tc_csca_get_cities", nonce_ajax: tc_csca_auto_ajax.nonce, sid: a },
                    success: function (a) {
                        for (i = 0; i < a.length; i++) {
                            var e = a[i].id, c = a[i].name,
                                o = "<option value='" + c + "' data-id='" + e + "'>" + c + "</option>";
                            t("select.city_auto").append(o);
                        }
                    }
                });
        }
    });
});