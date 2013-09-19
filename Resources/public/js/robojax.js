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
                self.clicky($(this));
            });

            self.$body.on('click', '.robojax-submit', function(e) {
                e.preventDefault();
                self.submitForm($(this));
            });

            self.$body.on('submit', 'div.modal-body form', function(e) {
                e.preventDefault();
                self.submitForm($(this));
            });
        },

        // request the form, create a modal and put the response inside
        clicky: function($this)
        {
            var self = this;

            if (self.ready === false) {
                return;
            }
            self.ready = false;

            // make modal

//             $('body').append('<div class="modal fade">
//     <div class="modal-dialog">
//         <div class="modal-content">
//             <div class="modal-header">
//                 <button type="button" class="close" data-dismiss="modal">&times;</button>
//                 <h4 class="modal-title"></h4>
//             </div>
//             <div class="modal-body">
//             </div>
//             <div class="modal-footer">
//                 <button type="button" class="btn btn-primary"><i class="icon-save icon-large"></i> Save</button>
//             </div>
//         </div>
//     </div>
// </div>');

            if (!$this.hasClass('robojax-delete')) {
                self.$modalBody
                    .empty()
                    .html('<div class="text-center"><i class="icon-spinner icon-4x icon-spin"></i></div>')
                ;
                self.$modal.find('.modal-title').html($this.data('modal-title'));
                self.$modal.modal('show');
            }

            $.ajax($this.attr('href'), {
                success: function(response) {
                    self.ready = true;
                    self.options.clickSuccess($this, response);
                    if ($this.hasClass('robojax-delete')) {
                        return;
                    }
                    self.$modalBody
                        .html($(response).find('form.robojax-form'))
                    ;
                }
            });
        },

        // sends the form and destroys the modal
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
                    if (response.status == 'ok') {
                        self.options.submitSuccess($this, response);
                    } else {
                        self.$modalBody.html($(response).find('form.robojax-form'));
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
        clickSuccess: function() {},
        submitSuccess: function() {}
    };
})(jQuery, window);
