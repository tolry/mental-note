var timeouts = {
    keydown: null
}

var entryFormFields = {
    url: function(){return $('input#entry_url');},
    title: function(){return $('input#entry_title');},
    categories: function(){return $('input[name="entry[category]"]');},
    notificationSpan: function(){return $('span#metainfo-notification');}
}

var application = {
    getMetaInfo: function($urlElement){
        entryFormFields.notificationSpan().html('trying to retrieve URL meta data ...');
        $.ajax({
            url: mentalNote.route.url_metainfo,
            dataType: 'json',
            data: {
                url: $urlElement.val()
            },
            success: function(data){
                entryFormFields.notificationSpan().html('');

                if (entryFormFields.title().val() == '') {
                    entryFormFields.title().val(data.title);
                }

                var id = entryFormFields.categories().filter('[value=' + data.category + ']').attr('id');
                if (id) {
                    $('[for=' + id + ']').trigger('click');
                }
            },
            error: function() {
                entryFormFields.notificationSpan().html('error retrieving URL meta data');
            }
        });
    },
    getMetaInfoDelayed: function(){
        var $element = $(this);
        clearTimeout(timeouts.keydown);
        timeouts.keydown = setTimeout(function(){application.getMetaInfo($element)}, 500);
    },
    registerEvents: function($domElement) {

        entryFormFields.url().keydown(application.getMetaInfoDelayed);

        $domElement.find('.modal-ajax-form').modalAjaxForm({
            onComplete: function($element){
                application.registerEvents($element);
                $element.find(':text').first().focus();
            }
        });

        var tagInput = $("#entry_tags");
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

// all JS is already loading defered
// therefore document.ready und window.load already happened
// and won't be triggered again

application.registerEvents($(document));
$('.deferred-image').imageloader();

