(function ($) {
    $(document).ready(function () {
        $("#tableToSort").DataTable();
        examSort.init();
    });

    var examSort = {
        ajaxSettings: {
            "dataType": "json",
            "error": this.ajaxError,
            "timeout": 10000
        },

        init: function () {
            $("#tableToSort").on("draw.dt", this.cacheDomAndBindEvents.bind(this));
            this.cacheDomAndBindEvents();
        },

        cacheDomAndBindEvents: function () {
            this.cacheDom();
            this.bindEvents();
        },

        cacheDom: function () {
            this.$linkButton = $("span[id^=link_]");
            this.$unlinkButton = $("span[id^=unlink_]");
            this.$dialog = $("#dialog");
            this.$dialog.dialog({
                autoOpen: false,
                show: {
                    effect: "blind",
                    duration: 1000
                },
                hide: {
                    effect: "explode",
                    duration: 1000
                },
                buttons: {
                    Ok: function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
            this.$dialogText = $("#dialog_text");
        },

        bindEvents: function () {
            this.$linkButton.on("click", this.linkExam.bind(this));
            this.$unlinkButton.on("click", this.unlinkExam.bind(this));
        },

        linkExam: function (evt) {
            var itemName = evt.target.id;
            var id = itemName.substring(5, itemName.length);
            var artId = $("#art_id").val();

            this.sendAjax(
                "paustianquickcheckmodule_admin_attach",
                {"exam" : id,
                    "art_id" : artId,
                    "attach": true},
                {"success": this.examAddRemove.bind(this), method: "POST"}
            );
            evt.stopPropagation();
        },

        examAddRemove: function(result, textStatus, jqXHR){
            // right now the dialog is not working. Maybe just get rid of it and
            //update the DOM with the resulting html. Send back the quiz to be added?
            this.$dialogText.html(result.html);
            this.$dialog.dialog("open");
        },
        unlinkExam: function (evt) {
            var itemName = evt.target.id;
            var id = itemName.substring(7, itemName.length);
            var artId = $("#art_id").val();
            this.sendAjax(
                "paustianquickcheckmodule_admin_attach",
                {"exam" : id,
                    "art_id" : artId},
                {"success": this.examAddRemove.bind(this), method: "POST"}
            );
            evt.stopPropagation();
        },

        sendAjax: function (url, data, options) {
            //push the data object into the options
            options.data = data;
            $.extend(options, this.ajaxSettings);
            var theRoute = Routing.generate(url);
            $.ajax(theRoute, options);
        },

        ajaxError: function (jqXHR, textStatus, errorThrown) {
            window.alert(textStatus + "\n" + errorThrown);
        }
    };
})(jQuery);