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
            this.cacheDom();
            this.bindEvents();
        },

        cacheDom: function () {
            this.$previewButton = $("button[id^=preview]");
            this.$questionText = $("textarea[id=mc_question_quickcheckqtext]");
            this.$answerText = $("textarea[id=mc_question_quickcheckqanswer]");
            this.$type = $("input[id=type]");
        },

        bindEvents: function () {
            this.$previewButton.on("click",  this.showPreview.bind(this));
        },

        showPreview: function (evt){
            //send a message to delete that item
            var question = this.$questionText.val();
            var answer = this.$answerText.val();
            var type = this.$type.val();

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
            var htmlText = result.htmlText;
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