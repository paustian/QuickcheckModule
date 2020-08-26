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
        },

        bindEvents: function () {
            this.$linkButton.on("click", this.linkExam.bind(this));
            this.$unlinkButton.on("click", this.unlinkExam.bind(this));
        },

        linkExam: function (evt) {
            var itemName = evt.target.id;
            var id = itemName.substring(5, itemName.length);

            //send a message to delete that item


            /*
            this.sendAjax(
                "paustianquickcheckmodule_user_getpreviewhtml",
                {"question" : question,
                    "answer" : answer,
                    "type" : type},
                {"success": this.displayPreview.bind(this), method: "POST"}
            );
                */
            evt.stopPropagation();
        },

        /*displayPreview: function(result, textStatus, jqXHR){
            this.$preview.html(result.html);
            this.$preview.dialog({
                title: "Preview Question",
                modal: true,
                show: "blind",
                hide: "blind",
                width: 600,
                dialogClass: "ui-dialog-osx",
                buttons: {
                    "OK": function () {
                        $(this).dialog("close");
                    }
                }
            });
        },*/
        unlinkExam: function (evt) {
            var itemName = evt.target.id;
            var id = itemName.substring(7, itemName.length);
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