/*! Noatice v0.1.3 * https://noatice.com/ * Copyright (c) 2020 Robert Noakes * License: GNU General Public License v3.0 */
 
(function ($)
{
	'use strict';
	
	if (!$.noatice)
	{
		$.fn.extend(
		{
			/**
			 * Add a message element to the provided elements.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.fn.noatice_message
			 * @this   object         Elements to add the message element to.
			 * @param  string message Message displayed in the added element.
			 * @return object         Updated elements.
			 */
			"noatice_message": function (message)
			{
				this
				.each(function ()
				{
					$('<div class="noatice-message" />').html(message).appendTo($(this));
				});
				
				return this;
			}
		});
		
		/**
		 * Main Noatice object.
		 * 
		 * @since 0.1.3 Changed variable name.
		 * @since 0.1.0
		 * 
		 * @access jQuery.noatice
		 * @var    object
		 */
		var PLUGIN = $.noatice = $.noatice || {};
		
		$.extend(PLUGIN,
		{
			/**
			 * Body element.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.body
			 * @var    object
			 */
			"body": $(document.body),

			/**
			 * Data and event name for dismissing noatices.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.dismiss
			 * @var    string
			 */
			"dismiss": 'noatice-dismiss',

			/**
			 * Noatice queue.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.queue
			 * @var    array
			 */
			"queue": [],

			/**
			 * True if Noatice is setup and ready to start displaying noatices.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.ready
			 * @var    array
			 */
			"ready": false,

			/**
			 * True if Noatice is currently displaying noatices.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.running
			 * @var    array
			 */
			"running": false,
			
			/**
			 * Wrapper element for noatices.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.wrapper
			 * @var    array
			 */
			"wrapper": $('<div id="noatifications" />'),

			/**
			 * Initialize Noatice functionality.
			 * 
			 * @since 0.1.3 Removed deprecated functionality.
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.init
			 * @return void
			 */
			"init": function ()
			{
				if (!PLUGIN.ready)
				{
					PLUGIN.ready = true;

					$(window)
					.on('resize', function ()
					{
						$('.noatification').find(':animated').stop(true, true);
					});
					
					if (PLUGIN.body.hasClass(OPTIONS.rtl_class))
					{
						PLUGIN.wrapper.addClass('noatifications-rtl');
					}

					PLUGIN.enter();
					PLUGIN.tooltips();
				}
			},

			/**
			 * Show the next noatice in the queue.
			 * 
			 * @since 0.1.3 Removed deprecated functionality.
			 * @since 0.1.2 Improved conditions.
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.enter
			 * @return void
			 */
			"enter": function ()
			{
				if
				(
					PLUGIN.ready
					&&
					PLUGIN.queue.length > 0
				)
				{
					PLUGIN.running = true;
					
					if (PLUGIN.wrapper.closest(document.documentElement).length === 0)
					{
						PLUGIN.wrapper.appendTo(PLUGIN.body);
					}

					var options = PLUGIN.queue.shift();

					var noatice = (options.id)
					? $('#' + options.id)
					: '';

					if (noatice.length === 0)
					{
						noatice = $('<div class="noatice" />').attr('id', options.id).addClass(options.css_class);

						var inner = $('<div class="noatice-inner" />').css('width', PLUGIN.wrapper.width()).noatice_message(options.message).appendTo(noatice);

						if (options.dismissable)
						{
							noatice.addClass('noatice-dismissable');

							var dismiss = $('<div class="noatice-dismiss" />').appendTo(inner)
							.on('click', function ()
							{
								var existing = $(this).closest('.noatice-inner').css('width', PLUGIN.wrapper.width()).closest('.noatice').stop(true, true).css('z-index', '0');

								existing
								.animate(
								{
									"margin-top": '-' + existing.height() + 'px'
								},
								{
									"duration": options.duration.down,
									"easing": options.easing.down,
									"queue": false
								})
								.animate(
								{
									"width": '0px'
								},
								{
									"duration": options.duration.exit,
									"easing": options.easing.exit,
									"queue": false,

									"complete": function ()
									{
										$(this).remove();

										if (PLUGIN.wrapper.children().length === 0)
										{
											PLUGIN.wrapper.detach();
										}
									}
								});
							});

							if
							(
								typeof options.dismissable === 'number'
								&&
								options.dismissable > 0
							)
							{
								noatice
								.on(PLUGIN.dismiss, function ()
								{
									var current = $(this);
									var timeout = current.data(PLUGIN.dismiss);

									if (timeout)
									{
										clearTimeout(timeout);
									}

									current
									.data(PLUGIN.dismiss, setTimeout(function ()
									{
										dismiss.triggerHandler('click');
									},
									options.dismissable));
								})
								.triggerHandler(PLUGIN.dismiss);
							}
						}

						var enter_complete = function ()
						{
							METHODS.set_widths($(this));
							PLUGIN.enter();
						};

						if
						(
							typeof options.delay === 'number'
							&&
							options.delay > 0
						)
						{
							PLUGIN.enter();

							enter_complete = function ()
							{
								METHODS.set_widths($(this));
							};
						}
						else
						{
							options.delay = 0;
						}

						setTimeout(function ()
						{
							noatice.prependTo(PLUGIN.wrapper)
							.animate(
							{
								"width": '100%'
							},
							{
								"complete": enter_complete,
								"duration": options.duration.enter,
								"easing": options.easing.enter,
								"queue": false
							});
						},
						options.delay);
					}
					else
					{
						noatice.triggerHandler(PLUGIN.dismiss);
						PLUGIN.enter();
					}
				}
				else
				{
					PLUGIN.running = false;
				}
			},
			
			/**
			 * Setup the tooltips.
			 * 
			 * @since 0.1.2 Improved condition.
			 * @since 0.1.1 Verify sibling on blur.
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.tooltips
			 * @param  object elements Elements to setup the tooltips for.
			 * @return void
			 */
			"tooltips": function (elements)
			{
				elements = elements || $('.noatice-tooltip[title], [data-noatice-tooltip]');
				
				if (elements.length > 0)
				{
					elements.filter('.noatice-tooltip[title]')
					.each(function ()
					{
						var current = $(this);
						current.data('noatice-tooltip', current.attr('title')).removeAttr('title');
					});

					elements
					.on('focus mouseenter', function ()
					{
						var focused = $(this),
						tooltip = focused.data('noatice-sibling');

						if (!tooltip)
						{
							tooltip = $('<div class="noatice" />').data('noatice-sibling', focused).append($('<span class="noatice-arrow" />')).noatice_message(focused.data('noatice-tooltip'))
							.on('noatice-position', function ()
							{
								var positioning = $(this).css('width', ''),
								tooltip_width = positioning.width(),
								sibling = positioning.data('noatice-sibling'),
								offset = sibling.offset(),
								width = sibling.outerWidth();
								
								positioning
								.css(
								{
									"left": (offset.left - ((tooltip_width - width) / 2)) + 'px',
									"top": (offset.top - positioning.innerHeight() - 9) + 'px',
									"width": (tooltip_width + 1) + 'px'
								});
							});
							
							focused.data('noatice-sibling', tooltip);
						}
						
						if (tooltip.closest(document.documentElement).length === 0)
						{
							tooltip.appendTo(PLUGIN.body);
						}
						
						tooltip.stop(true).triggerHandler('noatice-position');
						tooltip.fadeIn('fast');
					})
					.on('blur mouseleave', function ()
					{
						var blurred = $(this);
						
						var sibling = (blurred.is(':focus'))
						? false
						: blurred.data('noatice-sibling');
						
						if (sibling)
						{
							sibling.stop(true)
							.fadeOut('fast', function ()
							{
								$(this).detach();
							});
						}
					});
				}
			}
		});

		/**
		 * Default Noatice options.
		 * 
		 * @since 0.1.3 Changed variable name.
		 * @since 0.1.0
		 * 
		 * @access jQuery.noatice.options
		 * @var    object
		 */
		var OPTIONS = PLUGIN.options = PLUGIN.options || {};

		$.extend(OPTIONS,
		{
			/**
			 * Default options for noatices.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.options.defaults
			 * @var    string
			 */
			"defaults":
			{
				/**
				 * CSS class applied to the noatice.
				 * 
				 * @since 0.1.0
				 * 
				 * @access jQuery.noatice.options.defaults.css_class
				 * @var    string
				 */
				"css_class": '',

				/**
				 * Time in milliseconds to delay the noatice entering.
				 * 
				 * @since 0.1.0
				 * 
				 * @access jQuery.noatice.options.defaults.delay
				 * @var    integer
				 */
				"delay": 0,

				/**
				 * True or an integer if the noatice can be dismissed. If an integer is provided, the noatice will be dismissed automatially after that many milliseconds.
				 * 
				 * @since 0.1.0
				 * 
				 * @access jQuery.noatice.options.defaults.dismissable
				 * @var    mixed
				 */
				"dismissable": 5000,

				/**
				 * Duration settings for noatices.
				 * 
				 * @since 0.1.0
				 * 
				 * @access jQuery.noatice.options.defaults.duration
				 * @var    object
				 */
				"duration":
				{
					/**
					 * Duration for noatices moving down (600 recommended).
					 * 
					 * @since 0.1.0
					 * 
					 * @access jQuery.noatice.options.defaults.duration.down
					 * @var    mixed
					 */
					"down": 400,

					/**
					 * Duration for entering noatices (600 recommended).
					 * 
					 * @since 0.1.0
					 * 
					 * @access jQuery.noatice.options.defaults.duration.enter
					 * @var    mixed
					 */
					"enter": 400,

					/**
					 * Duration for exiting noatices (400 recommended).
					 * 
					 * @since 0.1.0
					 * 
					 * @access jQuery.noatice.options.defaults.duration.exit
					 * @var    mixed
					 */
					"exit": 400
				},

				/**
				 * Easing settings for noatices.
				 * 
				 * @since 0.1.0
				 * 
				 * @access jQuery.noatice.options.defaults.easing
				 * @var    object
				 */
				"easing":
				{
					/**
					 * Easing for noatices moving down (easeOutBounce recommended).
					 * 
					 * @since 0.1.0
					 * 
					 * @access jQuery.noatice.options.defaults.easing.down
					 * @var    mixed
					 */
					"down": 'swing',

					/**
					 * Easing for entering noatices (easeOutElastic recommended).
					 * 
					 * @since 0.1.0
					 * 
					 * @access jQuery.noatice.options.defaults.easing.enter
					 * @var    string
					 */
					"enter": 'swing',

					/**
					 * Easing for exiting noatices (easeOutExpo recommended).
					 * 
					 * @since 0.1.0
					 * 
					 * @access jQuery.noatice.options.defaults.easing.exit
					 * @var    string
					 */
					"exit": 'swing'
				},

				/**
				 * DOM ID for the noatice.
				 * 
				 * @since 0.1.0
				 * 
				 * @access jQuery.noatice.options.defaults.id
				 * @var    string
				 */
				"id": '',

				/**
				 * Message displayed in the noatice.
				 * 
				 * @since 0.1.0
				 * 
				 * @access jQuery.noatice.options.defaults.message
				 * @var    string
				 */
				"message": ''
			},

			/**
			 * Body CSS class for RTL layouts.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.options.rtl_class
			 * @var    string
			 */
			"rtl_class": 'rtl'
		});

		/**
		 * General noatice methods.
		 * 
		 * @since 0.1.3 Changed variable name.
		 * @since 0.1.0
		 * 
		 * @access jQuery.noatice.methods
		 * @var    object
		 */
		var METHODS = PLUGIN.methods = PLUGIN.methods || {};

		$.extend(METHODS,
		{
			/**
			 * Set the default widths for a noatice.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.methods.set_widths
			 * @param  object noatice Noatice to set default widths for.
			 * @return void
			 */

			"set_widths": function (noatice)
			{
				noatice.css('width', 'auto').children().css('width', '');
			}
		});

		/**
		 * Functionality for adding noatices.
		 * 
		 * @since 0.1.3 Changed variable name.
		 * @since 0.1.0
		 * 
		 * @access jQuery.noatice.add
		 * @var    object
		 */
		var ADD = PLUGIN.add = PLUGIN.add || {};

		$.extend(ADD,
		{
			/**
			 * Add a noatice to the queue.
			 * 
			 * @since 0.1.3 Removed deprecated functionality.
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.add.base
			 * @param  mixed options Options for the added noatice or an array of noatice option objects.
			 * @return void
			 */
			"base": function (options)
			{
				if (!Array.isArray(options))
				{
					options = [options];
				}

				$.each(options, function (index, value)
				{
					if ($.isPlainObject(value))
					{
						PLUGIN.queue.push($.extend({}, OPTIONS.defaults, value));
					}
				});

				if (!PLUGIN.running)
				{
					PLUGIN.enter();
				}
			},

			/**
			 * Add a general noatice to the queue.
			 * 
			 * @since 0.1.2 Improved condition.
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.add.error
			 * @param  string css_class              CSS class applied to the noatice.
			 * @param  string message                Message to display in the noatice.
			 * @param  mixed  options_or_dismissable Options for this noatice or the dismissable value.
			 * @return void
			 */
			"general": function (css_class, message, options_or_dismissable)
			{
				var options = ($.isPlainObject(options_or_dismissable))
				? options_or_dismissable
				: {"dismissable": options_or_dismissable};

				ADD.base($.extend(options,
				{
					"css_class": (css_class === '')
					? 'noatice-general'
					: css_class,

					"message": message
				}));
			},

			/**
			 * Add an error noatice to the queue.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.add.error
			 * @param  string message                Message to display in the noatice.
			 * @param  mixed  options_or_dismissable Options for this noatice or the dismissable value.
			 * @return void
			 */
			"error": function (message, options_or_dismissable)
			{
				ADD.general('noatice-error', message, options_or_dismissable);
			},

			/**
			 * Add an info noatice to the queue.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.add.info
			 * @param  string message                Message to display in the noatice.
			 * @param  mixed  options_or_dismissable Options for this noatice or the dismissable value.
			 * @return void
			 */
			"info": function (message, options_or_dismissable)
			{
				ADD.general('noatice-info', message, options_or_dismissable);
			},

			/**
			 * Add a success noatice to the queue.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.add.success
			 * @param  string message                Message to display in the noatice.
			 * @param  mixed  options_or_dismissable Options for this noatice or the dismissable value.
			 * @return void
			 */
			"success": function (message, options_or_dismissable)
			{
				ADD.general('noatice-success', message, options_or_dismissable);
			},

			/**
			 * Add a warning noatice to the queue.
			 * 
			 * @since 0.1.0
			 * 
			 * @access jQuery.noatice.add.warning
			 * @param  string message                Message to display in the noatice.
			 * @param  mixed  options_or_dismissable Options for this noatice or the dismissable value.
			 * @return void
			 */
			"warning": function (message, options_or_dismissable)
			{
				ADD.general('noatice-warning', message, options_or_dismissable);
			}
		});
		
		$(document).ready(PLUGIN.init);
	}
})(jQuery);
