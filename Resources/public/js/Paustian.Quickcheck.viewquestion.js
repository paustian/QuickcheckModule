(function ($) {
    $(document).ready(function () {
        previewQ.init();
    });

    var previewQ = {
        ajaxSettings: {
            "dataType": "json",
            "error": this.ajaxError,
            "timeout": 10000
        },

        init: function () {
            $("#tableToSort").on("draw.dt", this.cacheDomAndBindEvents.bind(this));
            this.cacheDomAndBindEvents();
        },

        cacheDomAndBindEvents: function(){
            this.cacheDom();
            this.bindEvents();
        },

        cacheDom: function () {
            this.$previewButton = $("button[id^=preview_button]");
            if(this.$previewButton.length ===    0){
                this.$previewButton = $("span[id^=preview_button]");
            }
            this.$preview = $("#preview_div");
        },

        bindEvents: function () {
            this.$previewButton.on("click",  this.showPreview.bind(this));
        },

        showPreview: function (evt){
            var itemName = evt.target.id;
            var id = itemName.substring(15, itemName.length);
            var question = $("textarea[id*=quickcheckqtext]").val();
            var answer = $("textarea[id*=quickcheckqanswer]").val();
            var type = $("input[id=type]").val();
            if(type === "2") {//True/False type
                answer = $("#tf_quesiton_quickcheckanswer_0").attr("checked");
                if(typeof answer === "undefined"){
                    answer = 0;
                }
            }
                //send a message to preview that item
            this.sendAjax(
                "paustianquickcheckmodule_user_getpreviewhtml",
                {"question" : question,
                "answer" : answer,
                "type" : type},
                {"success": this.displayPreview.bind(this), method: "POST"}
            );

            evt.stopPropagation();
        },

        displayPreview: function(result, textStatus, jqXHR){
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
        },

        sendAjax: function (url, data, options) {
            //push the data object into the options
            options.data = data;
            $.extend(options, this.ajaxSettings);
            var theRoute = Routing.generate(url);
            $.ajax(theRoute, options);
        },

        ajaxError: function(jqXHR, textStatus, errorThrown){
            window.alert(textStatus + "\n" +errorThrown);
        },
    };
})(jQuery);