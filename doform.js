
    // do form! AJAX , JQuery Demo

    $(function() {
        $('form.doajax').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var post_url = form.attr('action') + '?ajax=on';
            var post_data = form.serialize();
            $.ajax({
                type: 'POST',
                url: post_url,
                data: post_data,
                success: function(msg) {
                    $(form).fadeOut(500, function() {
                        form.html(msg).fadeIn();
                    });
                }
            });
        });
    });
