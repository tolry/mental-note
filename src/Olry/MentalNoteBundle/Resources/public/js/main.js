var timeouts = {
    keydown: null
}

var entryFormFields = {
    url: function(){return $('input#entry_url');},
    title: function(){return $('input#entry_title');},
    categories: function(){return $('input[name="entry[category]"]');}
}

var application = {
    getMetaInfo: function($urlElement){
        $.ajax({
            url: mentalNote.route.url_metainfo,
            dataType: 'json',
            data: {
                url: $urlElement.val()
            },
            success: function(data){
                entryFormFields.title().val(data.title);
                var id = entryFormFields.categories().filter('[value=' + data.category + ']').attr('id');
                if (id) {
                    $('[for=' + id + ']').trigger('click');
                }
            }
        });
    },
    getMetaInfoDelayed: function(){
        var $element = $(this);
        clearTimeout(timeouts.keydown);
        timeouts.keydown = setTimeout(function(){application.getMetaInfo($element)}, 500);
    },
    convertRadioButtons: function() {
        $('#entry_category').find('input[type="radio"]').hide();
        var btnGroup = $('<div />').addClass('btn-group').attr('data-toggle', 'buttons-radio');
        $('#entry_category').find('label').each(function(){
            var input = $('#' + $(this).attr('for'));

            var btn = $('<button />')
                        .addClass('btn')
                        .addClass('btn-info')
                        .attr('type', 'button')
                        .attr('for', $(this).attr('for'))
                        .html($(this).html())
                        .appendTo(btnGroup)
                        .click(function(){
                            $('#entry_category').find('input[type="radio"]').removeAttr('checked');
                            input.attr('checked', 'checked');
                        });

            if (input.is(':checked')) {
                btn.addClass('active');
            }

            $(this).remove();
        });

        btnGroup.appendTo($('#entry_category'));
    },
    registerEvents: function($domElement) {

        entryFormFields.url().keydown(application.getMetaInfoDelayed);
        application.convertRadioButtons();

        $domElement.find('.modal-ajax-form').modalAjaxForm({
            onComplete: function($element){
                application.registerEvents($element);
            }
        });

        var tagInput = $("#entry_tagsString");
        if (tagInput.length > 0) {

            tagInput.select2({
                tokenSeperator: [','],
                tags: application.searchTags()
            });
        }

        $('.visit-link').mousedown(function(e){
            $.ajax($(this).data('link'),{
                  type: 'POST'
            });
        });

        $('.deferred-image').imageloader();

    },
    searchTags: function(query) {
        var tags = null;
        $.ajax({
            url: mentalNote.route.tag_search,
            dataType: 'json',
            success: function(data) {
                tags = data;
            },
            async: false
        });

        return tags;
    }
}

$(document).ready(function(){
    application.registerEvents($(document));
});


(function($, jQuery) {
    jQuery.fn.modalAjaxForm = function(options) {
        options = jQuery.extend({}, jQuery.fn.modalAjaxForm.defaults, options);
        return $(this).each(function() {
            $(this).click(function(e) {
                e.preventDefault();

                var url = $(this).attr('href');
                jQuery.fn.modalAjaxForm.showModal(url, {}, options.onComplete, "GET");
            });
        });
    };
    jQuery.fn.modalAjaxForm.defaults = {
        onComplete: function (){}
    };

    jQuery.fn.modalAjaxForm.showModal = function(url, data, complete, method){

        $.ajax(url, {
            dataType: 'html',
            data: data,
            type: method,
            success: function(data, textStatus, jqXHR) {

                if (jqXHR.status == 201) {
                    window.location.reload();
                    return;
                }

                $('#ajax-form-modal').remove();
                var $modal = $('<div class="modal" id="ajax-form-modal" />');

                $modal.appendTo('body');

                $modal.modal();
                $modal.html(data);

                complete($modal);

                $modal.find('form').submit(function(e) {

                    e.preventDefault();

                    jQuery.fn.modalAjaxForm.showModal(
                        $(this).attr('action'),
                        $(this).serialize(),
                        complete,
                        "POST"
                    );
                });
            }
        });
    };
})(jQuery, jQuery);

