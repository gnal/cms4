if ( typeof Object.create !== 'function' ) {
    Object.create = function( obj ) {
        function F() {}
        F.prototype = obj;
        return new F();
    };
}

(function($, window, undefined) {
    "use strict";
    var Treezor = {
        init: function(el, options) {
            var self = this;
            self.$el = $(el);
            self.options = $.extend({}, $.fn.treezor.options, options);

            self.listen();
        },

        listen: function()
        {
            var self = this;

            self.$el.sortable({
                items: 'li:not(.dontmove)',
                start: function(e, ui) {
                    self.$el.children().each(function(i, e) {
                        var $e = $(e);
                        if ($e.data('lvl') !== $(ui.item).data('lvl')) {
                            $e.addClass('dontmove');
                        }
                    });

                    self.$el.sortable('refresh');

                    self.array1 = self.$el.sortable('toArray');
                },
                stop: function(e, ui) {
                    self.array2 = self.$el.sortable('toArray');

                    self.$el.children().each(function(i, e) {
                        var $e = $(e);
                        $e.removeClass('dontmove');
                    });

                    self.$el.children().each(function(i, e) {
                        var $m = $(e),
                            result = [];
                        self.$el.children().each(function(i, e) {
                            var $e = $(e);
                            if ($e.data('lft') > $m.data('lft') && $e.data('rgt') < $m.data('rgt')) {
                                result.push(e);
                            }
                        });
                        $(result).insertAfter($m);
                    });

                    $.ajax(self.$el.data('url'), {
                        data: {
                            id: $(ui.item).attr('id'),
                            array1: self.array1,
                            array2: self.array2,
                        }
                    });
                }
            });
        }
    };

    $.fn.treezor = function(options) {
        return this.each(function() {
            var treezor = Object.create(Treezor);
            treezor.init(this, options);
        });
    };

    $.fn.treezor.options = {
    };
})(jQuery, window);

(function($) {
    "use strict";
    $('#sortable1').treezor();
})(jQuery);
