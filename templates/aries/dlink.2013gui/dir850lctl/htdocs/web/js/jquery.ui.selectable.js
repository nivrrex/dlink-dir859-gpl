/*!
 * jQuery UI Selectable 1.10.4
 * http://jqueryui.com
 *
 * Copyright 2014 jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * http://api.jqueryui.com/selectable/
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.mouse.js
 *	jquery.ui.widget.js
 */
(function( $, undefined ) {

$.widget("ui.selectable", $.ui.mouse, {
	version: "1.10.4",
	options: {
		appendTo: "body",
		autoRefresh: true,
		distance: 0,
		filter: "*",
		tolerance: "touch",
		// callbacks
		selected: null,
		selecting: null,
		start: null,
		stop: null,
		unselected: null,
		unselecting: null
	},
	_create: function() {
		var selectees,
			that = this;
		
		this.element.addClass("ui-selectable");
		this.dragged = false;
		// cache selectee children based on filter
		this.refresh = function() {
			selectees = $(that.options.filter, that.element[0]);
			selectees.addClass("ui-selectee");
			selectees.each(function() {
				var $this = $(this),
					pos = $this.offset();
				$.data(this, "selectable-item", {
					element: this,
					$element: $this,
					left: pos.left,
					top: pos.top,
					right: pos.left + $this.outerWidth(),
					bottom: pos.top + $this.outerHeight(),
					startselected: false,
					selected: $this.hasClass("ui-selected"),
					selecting: $this.hasClass("ui-selecting"),
					unselecting: $this.hasClass("ui-unselecting")
				});
			});
		};
		this.refresh();
		this.selectees = selectees.addClass("ui-selectee");
		this._mouseInit();
		this.helper = $("<div class='ui-selectable-helper'></div>");
	},
	
	_destroy: function() {
		this.selectees
			.removeClass("ui-selectee")
			.removeData("selectable-item");
		this.element
			.removeClass("ui-selectable ui-selectable-disabled");
		this._mouseDestroy();
	},
	
	_mouseStart: function(event) {
		
		var that = this,
			options = this.options;
		
		this.opos = [event.pageX, event.pageY];
		
		if (this.options.disabled) {
			return;
		}
		
		this.selectees = $(options.filter, this.element[0]);
		
		this._trigger("start", event);
		
		$(options.appendTo).append(this.helper);
		// position helper (lasso)
		this.helper.css({
			"left": event.pageX,
			"top": event.pageY,
			"width": 0,
			"height": 0
		});
		
		if (options.autoRefresh) {
			this.refresh();
		}
		
		var target = event.target || event.srcElement;
		if($(target).parent().hasClass("click-selected-locked"))
		{
			return false;
		}
		
		$(this.element[0]).find(".sprite").remove();
		$(this.element[0]).find(".display").remove();
		
		this.selectees.filter(".ui-selected").each(function() {
			var selectee = $.data(this, "selectable-item");
			selectee.startselected = true;
			if (!event.metaKey && !event.ctrlKey) {
				selectee.$element.removeClass("ui-selected");
				selectee.selected = false;
			}
		});
		
		$(event.target).parents().addBack().each(function() {
			var doSelect,
				selectee = $.data(this, "selectable-item");
			if (selectee) {
				return false;
			}
		});
	},
	
	_mouseDrag: function(event) {
		
		this.dragged = true;
		
		if (this.options.disabled) {
			return;
		}
		
		var tmp,
			that = this,
			options = this.options,
			x1 = this.opos[0],
			y1 = this.opos[1],
			x2 = event.pageX,
			y2 = event.pageY;
		
		if (x1 > x2) { tmp = x2; x2 = x1; x1 = tmp; }
		if (y1 > y2) { tmp = y2; y2 = y1; y1 = tmp; }
		this.helper.css({left: x1, top: y1, width: x2-x1, height: y2-y1});
		
		this.selectees.each(function() {
			var selectee = $.data(this, "selectable-item"),
				hit = false;
			
			if (!selectee || selectee.element === that.element[0]) {
				return;
			}
			
			if (options.tolerance === "touch") {
				hit = ( !(selectee.left > x2 || selectee.right < x1 || selectee.top > y2 || selectee.bottom < y1) );
			} else if (options.tolerance === "fit") {
				hit = (selectee.left > x1 && selectee.right < x2 && selectee.top > y1 && selectee.bottom < y2);
			}
			
			if (hit) {
				if (selectee.unselecting) {
					selectee.$element.removeClass("ui-unselecting");
					selectee.unselecting = false;
				}
				if (!selectee.selecting) {
					selectee.$element.addClass("ui-selecting");
					selectee.selecting = true;
					that._trigger("selecting", event, {
						selecting: selectee.element
					});
				}
			} else {
				if (selectee.selecting) {
					if ((event.metaKey || event.ctrlKey) && selectee.startselected) {
						selectee.$element.removeClass("ui-selecting");
						selectee.selecting = false;
						selectee.$element.addClass("ui-selected");
						selectee.selected = true;
					} else {
						selectee.$element.removeClass("ui-selecting");
						selectee.selecting = false;
						if (selectee.startselected) {
							selectee.$element.addClass("ui-unselecting");
							selectee.unselecting = true;
						}
						that._trigger("unselecting", event, {
							unselecting: selectee.element
						});
					}
				}
				if (selectee.selected) {
					if (!event.metaKey && !event.ctrlKey && !selectee.startselected) {
						selectee.$element.removeClass("ui-selected");
						selectee.selected = false;
						selectee.$element.addClass("ui-unselecting");
						selectee.unselecting = true;
						that._trigger("unselecting", event, {
							unselecting: selectee.element
						});
					}
				}
			}
		});
		return false;
	},
	
	_mouseStop: function(event) {
		var that = this;
		this.dragged = false;
		var TimeLine = new Array();
		$(".ui-selecting", this.element[0]).each(function()
		{
			var index = $( this ).text();
			TimeLine.push(parseInt(index));
		});
		$(".ui-selected", this.element[0]).each(function()
		{
			var index = $( this ).text();
			TimeLine.push(parseInt(index));
			
		});
		// Bubble Sort
		var i=TimeLine.length, j;
		var tempExchangVal;
		while(i > 0)
		{
			for(var j = 0; j < i-1; j++)
			{
				if(TimeLine[j] > TimeLine[j+1])
				{
					tempExchangVal = TimeLine[j];
					TimeLine[j]=TimeLine[j+1];
					TimeLine[j+1]=tempExchangVal;
				}
			}
			i--;
		}
		var Line1Min = TimeLine[0];
		var Line1Max = TimeLine[0];
		var Line2Min = 0;
		var Line2Max = 0;
		var CurLen = 0;
		var RuleLen = 0;
		for (var k = 1; k < TimeLine.length; k ++)
		{
			if (Line1Max == TimeLine[k])	{	continue;	}
			else if (Line1Max + 1 == TimeLine[k])	{ Line1Max ++;	}
			else if (Line1Max + 1 < TimeLine[k])	{	CurLen = k;	Line2Min = TimeLine[k];	break;	}
		}
		
		if (CurLen != 0)
		{
			RuleLen = 1;
			Line2Max = TimeLine[CurLen];
			for (var l = CurLen; l < TimeLine.length; l ++)
			{
				if (Line2Max == TimeLine[l])	{	continue;	}
				else if (Line2Max + 1 == TimeLine[l])	{ Line2Max ++;	}
				else if (Line2Max + 1 < TimeLine[l])	{	break;	}
			}
		}
		
		if (RuleLen == 0)
		{
			var sprite1 = new SpriteBtn();
			$( this.element[0].children[Line1Max-1] ).append(sprite1);
			if (Line1Max - Line1Min >= 3)
			{
				var display1 = new DisplayTime();
				$( this.element[0].children[Line1Min-1] ).append(display1);
				display1.innerHTML = (Line1Min-1) + ":00 - " + Line1Max + ":00";
			}
		}
		else
		{
			var sprite1 = new SpriteBtn();
			var sprite2 = new SpriteBtn();
			$( this.element[0].children[Line1Max-1] ).append(sprite1);
			$( this.element[0].children[Line2Max-1] ).append(sprite2);
			if (Line1Max - Line1Min >= 3)
			{
				var display1 = new DisplayTime();
				$( this.element[0].children[Line1Min-1] ).append(display1);
				display1.innerHTML = (Line1Min-1) + ":00 - " + Line1Max + ":00";
			}
			if (Line2Max - Line2Min >= 3)
			{
				var display2 = new DisplayTime();
				$( this.element[0].children[Line2Min-1] ).append(display2);
				display2.innerHTML = (Line2Min-1) + ":00 - " + Line2Max + ":00";
			}
		}
		
		$(".ui-selecting", this.element[0]).each(function() {
			var selectee = $.data(this, "selectable-item");
			selectee.$element.removeClass("ui-selecting").addClass("ui-selected");
			selectee.selecting = false;
			selectee.selected = true;
			selectee.startselected = true;
			that._trigger("selected", event, {
				selected: selectee.element
			});
		});
		this._trigger("stop", event);
		this.helper.remove();
		TimeLine = null;
		return false;
	}

});

})(jQuery);
