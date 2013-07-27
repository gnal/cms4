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
        init: function(el, options) {
            var self = this;

            self.$el = $(el);
            self.options = $.extend({}, $.fn.robojax.options, options);
            self.ready = true;

            self.listen();
        },

        listen: function()
        {
            var self = this;

            $('body').on('click', self.$el, function(e) {
                e.preventDefault();
                if (self.ready === false) {
                    return;
                }
                self.ready = false;

                var $this = $(this);
                self.clickedLink = $this;
                self.execute($this);
            });
        },

        execute: function($this) {
            var self = this;

            self.options.beforeRequest($this);

            $.ajax($this.attr('href'), {
                success: function(response) {
                    self.ready = true;
                    self.options.success($this, response);
                }
            });
        }
    };

    $.fn.robojax = function(options) {
        return this.each(function() {
            var robojax = Object.create(Robojax);
            robojax.init(this, options);
        });
    };

    $.fn.robojax.options = {
        beforeRequest: function($this) {},
        success: function($this, response) {}
    };
})(jQuery, window);
