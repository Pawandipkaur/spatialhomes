/*! Thrive Clever Widgets 2019-09-12
* http://www.thrivethemes.com 
* Copyright (c) 2019 * Thrive Themes */
var tcw_app=tcw_app||{};!function(){"use strict";tcw_app.FiltersView=Backbone.View.extend({className:"tcw_filtersContainer",events:{"click .tcw_tabFilter":function(a){this.filterClicked(jQuery(a.target))}},render:function(){var a=this;_.each(this.collection.models,function(b){a.renderFilter(b)})},renderFilter:function(a){var b=_.template(jQuery("#filter-template").html())(a.toJSON());this.$el.append(b)},filterClicked:function(a){_.each(this.$el.parent().find(".tcw_optionContainer"),function(b){var c=jQuery(b);c.children("label").data("type")===a.attr("id")?c.show():c.hide()}),this.renderSelectedFilter(a.text())},renderSelectedFilter:function(a){var b=this.$el.next(".tcw_selectedFilter");b.length&&b.remove();var c=_.template(jQuery("#selected-filter-template").html())({filter:a});this.$el.after(c)}})}(jQuery);