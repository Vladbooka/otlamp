// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

define(['jquery'], function($) {

    return {
        screenCheck: function() {
            var notificationpopover = $('#nav-notification-popover-container'),
                notification = notificationpopover.find('.popover-region-container');


            if( notificationpopover.hasClass('position-left') || ! notificationpopover.hasClass('position-right') )
            {
                var notificationleft = notificationpopover.offset().left;
                var cond = (notificationleft < (notification.outerWidth() - notificationpopover.outerWidth()));
                notification.css('left', (cond ? (notificationleft * -1) + 'px' : 'unset'));
            } else
            {
                var notificationright = (($(window).width() - notificationpopover.offset().left +
                        notificationpopover.outerWidth()) * -1) + 'px';
                notification.css('right', ($(window).width() <= 767 ? notificationright : '0'));
            }
        },
        /**
         * Main method.
         * @method init
         */
        init: function() {
            var obj = this;
            obj.screenCheck();
            $(window).on('resize', function() {
                obj.screenCheck();
            });
        }
    };
});
