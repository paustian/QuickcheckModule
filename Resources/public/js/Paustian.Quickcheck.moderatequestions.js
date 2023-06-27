(function ($) {
    $(document).ready(function () {
        $("#tableToSort").DataTable();
        tablesorter.init();
    });

    var tablesorter = {
        currentId:0,
        currentButtons:[],

        ajaxSettings: {
            'dataType': 'json',
            'error': this.ajaxError,
            'timeout': 10000
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
            this.$deleteButtons = $("span[id^=delete_]");
            this.$editButtons = $("span[id^=edit_]");
            this.$previewButtons = $("span[id^=preview_button_]");
            this.$table = $("#tableToSort");
            var divDeleteRowsExists = $("#delete_rows");
            this.$deleteRows = (divDeleteRowsExists.length !== 0);
        },

        bindEvents: function () {
            this.$deleteButtons.on('click',  this.deleteQuestion.bind(this));
            this.$editButtons.on('click', this.editQuestion.bind(this));
            this.$previewButtons.on('click', this.showPreview.bind(this));

        },

        deleteQuestion: function (evt){
            var itemName = evt.target.id;
            this.currentId = itemName.substring(7, itemName.length);
            //send a message to delete that item
            this.sendAjax(
                'paustianquickcheckmodule_admin_deletequestion',
                {'id' : this.currentId},
                {'success': this.itemDeleted.bind(this), method: 'POST'}
            );
        },

        itemDeleted: function(result, textStatus, jqXHR){
            if(result.success === true){
                var rowToDelete = this.$table.find("tr[id=" + this.currentId + "]");
                rowToDelete.remove();
            }
        },

        editQuestion: function(evt){
            var itemName = evt.target.id;
            this.currentId = itemName.substring(5, itemName.length);
            //send a message to delete that item
            this.sendAjax(
                'paustianquickcheckmodule_admin_javaedit',
                {'id' : this.currentId},
                {'success': this.doEdit.bind(this), method: 'POST'}
            );
        },

        doEdit: function(result, textStatus, jqXHR){
            var rowToEdit = this.$table.find("tr[id=" + this.currentId + "]");
            var qText = rowToEdit.find("td[id=quickcheckqtext_" +  this.currentId + "]");
            qText.empty();
            qText.html("<textarea id='quickcheckqtext_" + this.currentId + "' rows='10' cols='20'>" + result.qText + "</textarea>");
            var qAnswer = rowToEdit.find("td[id=quickcheckqanswer_" +  this.currentId + "]");
            qAnswer.empty();
            qAnswer.html("<textarea id='quickcheckqanswer_" + this.currentId + "' rows='10' cols='20'>" + result.qAnswer + "</textarea>");
            var qExpan = rowToEdit.find("td[id=qExpan_" +  this.currentId + "]");
            qExpan.empty();
            qExpan.html("<textarea id='qExpan_" + this.currentId + "' rows='10' cols='20'>" + result.qExpan + "</textarea>");
            var buttons = rowToEdit.find("td[id=actions]");
            this.currentButtons[this.currentId] = buttons.html();
            buttons.empty();
            buttons.html("<span id='submit_" + this.currentId + "' class='fa fa-save'></span>");
            $("#submit_" + this.currentId).on("click", this.saveItem.bind(this));
        },

        showPreview: function (evt){
            var itemName = evt.target.id;
            var id = itemName.substring(15, itemName.length);
            var question;
            var answer;
            var type;
            question=$("#quickcheckqtext_" + id).html();
            answer=$("#quickcheckqanswer_" + id).html();
            type = $("#type_" + id).val();

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

        saveItem: function(evt){
            var itemName = evt.target.id;
            this.currentId = itemName.substring(7, itemName.length);
            //we need to update the cached dom because it has changed upon save
            this.$table = $("#tableToSort");
            var rowToEdit = this.$table.find("tr[id=" + this.currentId + "]");
            var qText = rowToEdit.find("textarea[id=quickcheckqtext_" +  this.currentId + "]").val();
            var qAnswer = rowToEdit.find("textarea[id=quickcheckqanswer_" +  this.currentId + "]").val();
            var qExpan = rowToEdit.find("textarea[id=qExpan_" +  this.currentId + "]").val();
            var qStatus = rowToEdit.find("select[id=qStatus_" + this.currentId + "]").val();

            this.sendAjax(
                'paustianquickcheckmodule_admin_setquestion',
                {'id' : this.currentId, 'qText' : qText, 'qAnswer': qAnswer, 'qExpan': qExpan, 'qStatus': qStatus},
                {'success': this.doSave.bind(this), method: 'POST'}
            );
        },

        doSave: function(result, textStatus, jqXHR){
            var rowToEdit = this.$table.find("tr[id=" + this.currentId + "]");
            if(!result.cansave){
                return;
            }
            var qStatus = rowToEdit.find("select[id=qStatus_" + this.currentId + "]");
            //the number 0 signifies its a public question. If it is a public
            //questions, we remove it. Since this is in the moderation or hidden question interface.
            if((result.qStatus === "0") && this.$deleteRows){
                var rowToDelete = this.$table.find("tr[id=" + this.currentId + "]");
                rowToDelete.remove();
                //we are done with this row so we can just leave.
                return;
            } else {
                qStatus.find("option").removeAttr("selected");
                var option = qStatus.find("option[value=" + result.qStatus + "]");
                option.attr("selected", "selected");
            }
            var qText = rowToEdit.find("td[id^=quickcheckqtext_]");
            qText.empty();
            qText.text(result.qText);
            var qAnswer = rowToEdit.find("td[id^=quickcheckqanswer_]");
            qAnswer.empty();
            qAnswer.text(result.qAnswer);
            var qExpan = rowToEdit.find("td[id^=qExpan_]");
            qExpan.empty();
            qExpan.text(result.qExpan);

            var buttons = rowToEdit.find("td[id=actions]");
            buttons.empty();
            buttons.html(this.currentButtons[this.currentId]);
            $("#delete_" + result.id).on('click',  this.deleteQuestion.bind(this));
            $("#edit_"+ result.id).on('click', this.editQuestion.bind(this));
            delete this.currentButtons[this.currentId];
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