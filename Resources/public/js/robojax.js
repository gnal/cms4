if ( typeof Object.create !== 'function' ) {
    Object.create = function( obj ) {
        function F() {}
        F.prototype = obj;
        return new F();
    };
}

(function($, window, undefined) {
    "use strict";
    var Robojax = {
        init: function(options) {
            var self = this;
            // el could be the modal perhaps?
            self.options = $.extend({}, $.robojax.options, options);
            self.$modal = $('div.modal');
            self.$modalBody = self.$modal.find('.modal-body');
            self.$body = $('body');
            self.ready = true;

            self.listen();
        },

        listen: function()
        {
            var self = this;

            self.$body.on('click', '.robojax', function(e) {
                e.preventDefault();
                self.execute($(this));
            });

            self.$body.on('click', '.robojax_submit', function(e) {
                e.preventDefault();
                self.submitForm($(this));
            });

            self.$body.on('submit', self.$modal.find('form'), function(e) {
                e.preventDefault();
                self.submitForm($(this));
            });
        },

        execute: function($this)
        {
            var self = this;

            if (self.ready === false) {
                return;
            }
            self.ready = false;

            self.$modalBody
                .empty()
                .html('<div class="text-center"><i class="icon-spinner icon-4x icon-spin"></i></div>')
            ;

            self.$modal.find('.modal-title').html($this.data('modal-title'));

            self.$modal.modal('show');

            $.ajax($this.attr('href'), {
                success: function(response) {
                    self.ready = true;
                    self.options.clickSuccess($this, response);
                    self.$modalBody
                        .html($(response).find('form.form-crud'))
                    ;
                }
            });
        },

        submitForm: function($this)
        {
            var self = this;

            if (self.ready === false) {
                return;
            }
            self.ready = false;

            var $form = self.$modal.find('form');

            $form.css('visibility', 'hidden');

            self.$modalBody
                .html('<div class="text-center"><i class="icon-spinner icon-4x icon-spin"></i></div>')
            ;

            $.ajax($form.attr('action'), {
                type: 'POST',
                data: $form.serialize(),
                success: function (response) {
                    self.ready = true;
                    if (response.entity) {
                        self.options.submitSuccess($this, response);
                    } else {
                        self.$modalBody.html($(response).find('form.form-crud'));
                    }
                }
            });
        }
    };

    $.robojax = function(options) {
        var robojax = Object.create(Robojax);
        robojax.init(options);
    };

    $.robojax.options = {
        clickSuccess: null,
        submitSuccess: null
    };
})(jQuery, window);
