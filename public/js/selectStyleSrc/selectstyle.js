
/****************************************************************
* Selector plug that made select tag in to custome select style *
*****************************************************************/
(function($){
	$.fn.selectstyle = function(option){
		var defaults = {
			width  : 250,
			height : 300,
			theme  : 'light'
		},
		setting = $.extend({}, defaults, option);
		this.each(function(){
			var $this = $(this),
				parent = $(this).parent(),
				html = '',
				html_op = '',
				search = $this.attr('data-search'),
				name = $this.attr('name'),
				style = $this.attr('style'),
				placeholder = $this.attr('placeholder'),
				id = $this.attr('id');
			setting.width = (parseInt($this.attr('width') == null ? $this.width() : $this.attr('width') ) + 10 )+'px';
			setting.theme = $this.attr('theme') != null ? $this.attr('theme') : setting.theme;

			$this.find('option').each(function (e) {
				var $this_a = $(this),
					val = $this_a.val(),
					image = $this_a.attr('data-image'),
					text = $this_a.html();
				if(val == null){
					val = text;
				}
				html_op += '<li data-title="'+text+'" value="'+val+'"';
				if($this_a.attr('font-family') != null){
					html_op += ' style="font-family'+$this_a.attr('font-family')+'"';
				}
				html_op += '>';
				if(image != null){
					html_op += '<div class="ssli_image"><img src="'+image+'"></div>';
				}
				html_op += '<div class="ssli_text">'+text+'</div></li>';
			});
			$this.hide();

			html = 
			'<div class="selectstyle ss_dib '+setting.theme+'" style="width:'+parseInt(setting.width)+'px;">'+
				'<div id="select_style" class="ss_button" style="width:'+parseInt(setting.width)+'px;'+style+'">'+
					'<div class="ss_dib ss_text" id="select_style_text" style="margin-right:15px;width:'+(parseInt(setting.width) - 20)+'px;position:relative;">'+placeholder+'</div>'+
					'<div class="ss_dib ss_image"></div>'+
				'</div>';
			if(search == "true"){
				html += '<ul id="select_style_ul" sid="'+id+'" class="ss_ulsearch" style="max-height:'+setting.height+'px;width:'+(parseInt(setting.width) + 20)+'px;"><div class="search" id="ss_search"><input type="text" placeholder="Search"></div><ul style="max-height:'+(parseInt(setting.height) - 53)+'px;width:'+(parseInt(setting.width) + 20)+'px;" class="ss_ul">'+html_op+'</ul></ul>';
			}
			else{
				html += '<ul id="select_style_ul" sid="'+id+'" style="max-height:'+setting.height+'px;width:'+(parseInt(setting.width) + 20)+'px;" class="ss_ul">'+html_op+'</ul>';
			}
			
			html += '</div>';
			$(html).insertAfter($this);
			
		});

		$("body").delegate( "div#ss_search input", "keyup", function(e) {
			var val = $(this).val(), flag=false;
			$('#nosearch').remove();
			$(this).parent().parent().find('li').each(function(index, el) {
				if($(el).text().indexOf(val) > -1){
					$(el).show();
					flag=true;
				}
				else{
					$(el).hide();
				}
			});
			if (!flag) {$(this).parent().parent().append('<div class="nosearch" id="nosearch">Nothing Found</div>')};
		});
		$("body").delegate( "div#select_style", "click", function(e) {
			$('ul#select_style_ul').hide();
			var ul = $(this).parent('div').find('ul#select_style_ul');
			ul.show();
			var height = ul.height();
			var offset = $(this).offset();
			if(offset.top+height > $(window).height()){
				ul.css({
					marginTop: -(((offset.top+height) - $(window).height()) + 100)
				});
			}
		});
		$("body").delegate("ul#select_style_ul li", "click", function(e) {
			var txt = $(this).data('title'),
				vl = $(this).attr('value'),
				sid = $(this).parent('ul').attr('sid');
			$(this).parents('ul#select_style_ul').hide();
			$(this).parents('ul#select_style_ul').parent('div').find('div#select_style_text').html(txt);
			$('#'+sid).children('option').filter(function(){return $(this).val()==vl}).prop('selected',true).change();
		});
		$(document).delegate("body", "click", function(e) {
			var clickedOn=$(e.target);
			if(!clickedOn.parents().andSelf().is('ul#select_style_ul, div#select_style')){
				$('ul#select_style_ul').fadeOut(400);
				$('div#ss_search').children('input').val('').trigger('keyup');
			}
		});
	}
})(jQuery);
