/*! Thrive Clever Widgets 2019-09-12
* http://www.thrivethemes.com 
* Copyright (c) 2019 * Thrive Themes */
var tcw_app=tcw_app||{};!function(){tcw_app.OptionView=Backbone.View.extend({className:"tcw_optionContainer",events:{"click .tcw_toggle_option":"toggle","click .tcw_removeDirectLink":"removeLink"},initialize:function(){this.listenTo(this.model,"change:isChecked",this.isCheckedChanged)},render:function(){if("direct_url"===this.model.get("type"))var a=_.template(jQuery("#direct-url-template").html())(this.model.toJSON());else var a=_.template(jQuery("#option-template").html())(this.model.toJSON());this.$el.append(a)},toggle:function(){this.model.toggle()},isCheckedChanged:function(){this.$el.find('input[type="checkbox"]').prop("checked",this.model.get("isChecked"))},removeLink:function(){this.model.collection.remove(this.model)}})}(jQuery);