var timeouts = {
    keydown: null
}

var entryForm = {
    form: function(){return $("#entry_form");},
    fields: {
        url: function(){return $('input#entry_url');},
        title: function(){return $('input#entry_title');},
        categories: function(){return $('input[name="entry[category]"]');},
        notificationSpan: function(){return $('span#metainfo-notification');}
    }
}

var application = {
    getMetaInfo: function($urlElement){
        entryForm.fields.notificationSpan().html('trying to retrieve URL meta data ...');
        $.ajax({
            url: mentalNote.route.url_metainfo,
            dataType: 'json',
            data: {
                url: $urlElement.val()
            },
            success: function(data){
                entryForm.fields.notificationSpan().html('');

                if (entryForm.fields.title().val() == '') {
                    entryForm.fields.title().val(data.title);
                }

                var id = entryForm.fields.categories().filter('[value=' + data.category + ']').attr('id');
                if (id) {
                    $('[for=' + id + ']').trigger('click');
                }
            },
            error: function() {
                entryForm.fields.notificationSpan().html('error retrieving URL meta data');
            }
        });
    },
    getMetaInfoDelayed: function(){
        var $element = $(this);
        clearTimeout(timeouts.keydown);
        timeouts.keydown = setTimeout(function(){application.getMetaInfo($element)}, 500);
    },
    registerEvents: function($domElement) {

        entryForm.fields.url().keydown(application.getMetaInfoDelayed);
        entryForm.form().keydown(function(event) {
            if (event.ctrlKey && event.keyCode == 13) {
                entryForm.form().submit();
            }
        });

        $domElement.find('.modal-ajax-form').modalAjaxForm({
            onComplete: function($element){
                application.registerEvents($element);
                $element.find(':text').first().focus();
                if (mentalNote.addUrl.length > 0) {
                    entryForm.fields.url()
                        .val(mentalNote.addUrl)
                        .trigger('keydown')
                    ;
                    mentalNote.addUrl = undefined;
                }
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

        $('div.entry-list').jscroll({
            loadingHtml: '<div class="text-center"><div class="loader-inner ball-pulse"><div></div><div></div><div></div></div></div>',
            padding: 20,
            nextSelector: 'ul.pagination li.next a',
            contentSelector: 'div.entry-list',
            callback: function() {
                application.registerEvents($(this));
            }
        });

        $domElement.find('.deferred-image').imageloader();
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
    },
    checkForAddURL: function() {
        var $button = $("#add-url");
        if (mentalNote.addUrl.length > 0) {
            $button.trigger('click');
        }
    }
}

// all JS is already loading defered
// therefore document.ready und window.load already happened
// and won't be triggered again

application.registerEvents($(document));

application.checkForAddURL();

