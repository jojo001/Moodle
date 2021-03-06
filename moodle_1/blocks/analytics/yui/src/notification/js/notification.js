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

/**
 * Notification of level up.
 *
 * @module     moodle-block_analytics-notification
 * @package    block_analytics
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @main       moodle-block_analytics-notification
 */

/**
 * @module moodle-block_analytics-notification
 */

var NAME = 'moodle-block_analytics-notification';
var COMPONENT = 'block_analytics';
var CSS = {
    BADGE: 'level-badge',
    CONTENT: 'level-message-content',
    HEADLINE: 'level-headline',
    PREFIX: 'block_analytics-notification',
    WRAP: 'wrapper',
};

/**
 * Notification.
 *
 * @namespace Y.M.block_analytics
 * @class Notification
 * @constructor
 */
var NOTIFICATION = function() {
    NOTIFICATION.superclass.constructor.apply(this, arguments);
};
Y.namespace('M.block_analytics').Notification = Y.extend(NOTIFICATION, M.core.dialogue, {

    initializer: function() {
        this.display();
    },

    close: function() {
        this.hide();
    },

    display: function() {
        var footerTpl,
            content,
            tpl;

        tpl = Y.Handlebars.compile(
            '<div class="{{CSS.WRAP}}">' +
                '<div class="{{CSS.HEADLINE}}">' +
                    '{{headline}}' +
                '</div>' +
                '<div class="{{CSS.BADGE}}">' +
                    '{{{badge}}}' +
                '</div>' +
                '<div class="{{CSS.CONTENT}}">' +
                    '{{{message}}}' +
                '</div>' +
            '</div>');

        // Set the header.
        this.getStdModNode(Y.WidgetStdMod.HEADER).prepend(Y.Node.create('<h1>' + this.get('title') + '</h1>'));

        // Set the content.
        content = Y.Node.create(
            tpl({
                badge: this.get('badge'),
                CSS: CSS,
                headline: this.get('headline'),
                message: this.get('message')
            })
        );
        this.setStdModContent(Y.WidgetStdMod.BODY, content, Y.WidgetStdMod.REPLACE);

        // Set the footer.
        footerTpl = Y.Handlebars.compile('<button>{{close}}</button>');
        content = Y.Node.create(
            footerTpl({
                close: M.util.get_string('coolthanks', COMPONENT)
            })
        );
        content.on('click', this.close, this);
        this.setStdModContent(Y.WidgetStdMod.FOOTER, content, Y.WidgetStdMod.REPLACE);

        // Use standard dialogue class name. This removes the default styling of the footer.
        this.get('boundingBox').one('.moodle-dialogue-wrap').addClass('moodle-dialogue-content');

        // Change the visibility.
        this.show();
    }

}, {
    NAME: NAME,
    CSS_PREFIX: CSS.PREFIX,
    ATTRS: {

        badge: {
            validator: Y.Lang.isString,
            value: ''
        },

        headline: {
            validator: Y.Lang.isString,
            value: ''
        },

        level: {
            validator: Y.Lang.isNumber,
            value: 0
        },

        message: {
            validator: Y.Lang.isString,
            value: ''
        }

    }
});

Y.Base.modifyAttrs(Y.namespace('M.block_analytics.Notification'), {

    /**
     * List of extra classes.
     *
     * @attribute extraClasses
     * @default [COMPONENT]
     * @type Array
     */
    extraClasses: {
        value: [
            COMPONENT
        ]
    },

    modal: {
        value: true
    },

    render: {
        value: true
    },

    title: {
        valueFn: function() {
            return M.util.get_string('congratulationsyouleveledup', COMPONENT);
        }
    },

    visible: {
        value: false
    }
});

Y.namespace('M.block_analytics.Notification').init = function(config) {
    return new NOTIFICATION(config);
};
