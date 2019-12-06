/*! Thrive Clever Widgets 2019-09-12
* http://www.thrivethemes.com 
* Copyright (c) 2019 * Thrive Themes */
var tcw_app=tcw_app||{};!function(){"use strict";tcw_app.Options=Backbone.Collection.extend({model:tcw_app.Option,countCheckedOptions:function(){var a=0;return this.each(function(b){a+=b.get("isChecked")?1:0}),a}})}(jQuery);