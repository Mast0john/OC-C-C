/*! Primary plugin JavaScript. * @since 2.0.0 * @package Nav Menu Collapse */

(function ($)
{
	'use strict';
	
	/**
	 * Options object.
	 * 
	 * @since 2.0.0
	 * 
	 * @var object
	 */
	var OPTIONS = window.nmc_script_options || {};
	
	$.fn.extend(
	{
		/**
		 * Fire an event on all provided elements.
		 * 
		 * @since 2.0.2 Removed deprecated functionality.
		 * @since 2.0.0
		 * 
		 * @access jQuery.fn.nmc_trigger_all
		 * @this   object      Elements to fire the event on.
		 * @param  string e    Event name to fire on all elements.
		 * @param  array  args Extra arguments to pass to the event call.
		 * @return object      Triggered elements.
		 */
		"nmc_trigger_all": function (e, args)
		{
			args = (typeof args === 'undefined')
			? []
			: args;

			if (!Array.isArray(args))
			{
				args = [args];
			}

			return this
			.each(function ()
			{
				$(this).triggerHandler(e, args);
			});
		},
		
		/**
		 * Check for and return unprepared elements.
		 * 
		 * @since 2.0.0
		 * 
		 * @access jQuery.fn.nmc_unprepared
		 * @this   object              Elements to check.
		 * @param  string class_suffix Suffix to add to the prepared class name.
		 * @return object              Unprepared elements.
		 */
		"nmc_unprepared": function (class_suffix)
		{
			var class_name = 'nmc-prepared';

			if (class_suffix)
			{
				class_name += '-' + class_suffix;
			}

			return this.not('.' + class_name).addClass(class_name);
		}
	});

	/**
	 * General variables.
	 * 
	 * @since 2.0.0
	 * 
	 * @access jQuery.nav_menu_collapse
	 * @var    object
	 */
	var PLUGIN = $.nav_menu_collapse || {};

	$.extend(PLUGIN,
	{
		/**
		 * Current body element.
		 * 
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.body
		 * @var    object
		 */
		"body": $(document.body),

		/**
		 * Current document object.
		 * 
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.document
		 * @var    object
		 */
		"document": $(document),

		/**
		 * Current window object.
		 * 
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.window
		 * @var    object
		 */
		"window": $(window)
	});

	/**
	 * Data variable names.
	 * 
	 * @since 2.0.0
	 * 
	 * @access jQuery.nav_menu_collapse.data
	 * @var    object
	 */
	var DATA = PLUGIN.data || {};

	/**
	 * Event names.
	 * 
	 * @since 2.0.0
	 * 
	 * @access jQuery.nav_menu_collapse.events
	 * @var    object
	 */
	var EVENTS = PLUGIN.events || {};

	/**
	 * General methods.
	 * 
	 * @since 2.0.0
	 * 
	 * @access jQuery.nav_menu_collapse.methods
	 * @var    object
	 */
	var METHODS = PLUGIN.methods || {};

	$.extend(METHODS,
	{
		/**
		 * Add a noatice to the page.
		 * 
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.methods.add_noatice
		 * @param  mixed noatices Noatice to add to the page or an array of noatices.
		 * @return void
		 */
		"add_noatice": function (noatices)
		{
			if ($.noatice)
			{
				$.noatice.add.base(noatices);
			}
		},

		/**
		 * Finalize AJAX buttons.
		 * 
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.methods.ajax_buttons
		 * @param  boolean disable True if the buttons should be disabled.
		 * @return void
		 */
		"ajax_buttons": function (disable)
		{
			var buttons = $('.nmc-ajax-button, .nmc-field-submit .nmc-button').prop('disabled', disable);

			if (!disable)
			{
				buttons.removeClass('nmc-clicked');
			}
		},

		/**
		 * Process AJAX response data.
		 * 
		 * @since 2.0.3
		 * 
		 * @access jQuery.directly_import.methods.ajax_data
		 * @param  object  response AJAX response data.
		 * @return boolean          True if the response data was present, otherwise false.
		 */
		"ajax_data": function (response)
		{
			if (response.data)
			{
				if (response.data.noatice)
				{
					METHODS.add_noatice(response.data.noatice);
				}

				if (response.data.url)
				{
					INTERNAL.changes_made = false;
					window.location = response.data.url;
				}

				return true;
			}

			return false;
		},

		/**
		 * Finalize an AJAX request error.
		 * 
		 * @since 2.0.3 Improved structure.
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.methods.ajax_error
		 * @param  object jqxhr        jQuery XMLHttpRequest object.
		 * @param  string text_status  HTTP status code.
		 * @param  string error_thrown Error details.
		 * @return void
		 */
		"ajax_error": function (jqxhr, text_status, error_thrown)
		{
			if
			(
				!jqxhr.responseJSON
				||
				!METHODS.ajax_data(jqxhr.responseJSON)
			)
			{
				METHODS
				.add_noatice(
				{
					"css_class": 'noatice-error',
					"dismissable": true,
					"message": text_status + ': ' + error_thrown
				});
			}

			PLUGIN.form.removeClass('nmc-submitted');
			METHODS.ajax_buttons(false);
		},

		/**
		 * Finalize a successful AJAX request.
		 * 
		 * @since 2.0.3 Improved structure.
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.methods.ajax_success
		 * @param  object response JSON object containing the response from the AJAX request.
		 * @return void
		 */
		"ajax_success": function (response)
		{
			if
			(
				!METHODS.ajax_data(response)
				||
				(
					!response.data.no_buttons
					&&
					!response.data.url
				)
			)
			{
				PLUGIN.form.removeClass('nmc-submitted');
				METHODS.ajax_buttons(false);
			}
		},

		/**
		 * Fire all functions in an object.
		 * 
		 * @since 2.0.2 Removed deprecated functionality.
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.methods.fire_all
		 * @param  object functions JSON object containing the functions that should be fired.
		 * @return void
		 */
		"fire_all": function (functions)
		{
			$.each(functions, function (index, value)
			{
				if (typeof value === 'function')
				{
					value();
				}
			});
		}
	});

	/**
	 * Global JSON object.
	 * 
	 * @since 2.0.0
	 * 
	 * @access jQuery.nav_menu_collapse.global
	 * @var    object
	 */
	var GLOBAL = PLUGIN.global || {};

	$.extend(GLOBAL,
	{
		/**
		 * Output the current noatices.
		 * 
		 * @since 2.0.2 Removed deprecated functionality.
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.global.noatices
		 * @return void
		 */
		"noatices": function ()
		{
			if
			(
				OPTIONS.noatices
				&&
				Array.isArray(OPTIONS.noatices)
			)
			{
				METHODS.add_noatice(OPTIONS.noatices);
			}
		}
	});

	METHODS.fire_all(GLOBAL);
	
	if (PLUGIN.body.is('[class*="' + OPTIONS.token + '"]'))
	{
		/**
		 * Current WordPress admin page ID.
		 * 
		 * @since 2.0.0
		 * 
		 * @var string
		 */
		var WPPAGENOW = window.pagenow || false;

		/**
		 * WordPress postboxes object.
		 * 
		 * @since 2.0.0
		 * 
		 * @var object
		 */
		var WPPOSTBOXES = window.postboxes || false;
		
		$.fn.extend(
		{
			/**
			 * Add a custom event to all provided elements.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.fn.nmc_add_event
			 * @this   object     Elements to add the event to.
			 * @param  string   e Event name to add to all elements.
			 * @param  function f Function executed when the event is fired.
			 * @return object     Updated elements.
			 */
			"nmc_add_event": function (e, f)
			{
				return this.addClass(e).on(e, f).nmc_trigger_all(e);
			}
		});

		$.extend(PLUGIN,
		{
			/**
			 * WordPress admin bar layer.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.admin_bar
			 * @var    object
			 */
			"admin_bar": $('#wpadminbar'),

			/**
			 * Main form object.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.form
			 * @var    object
			 */
			"form": $('#nmc-form'),

			/**
			 * Layers used for scrolling.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.scroll_element
			 * @var    object
			 */
			"scroll_element": $('html, body')
		});

		$.extend(DATA,
		{
			/**
			 * Compare operator used for a field being compared for a conditional field.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.data.compare
			 * @var    string
			 */
			"compare": 'nmc-compare',

			/**
			 * Name for a conditional field.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.data.conditional
			 * @var    string
			 */
			"conditional": 'nmc-conditional',

			/**
			 * Name of the field being compared for a conditional field.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.data.field
			 * @var    string
			 */
			"field": 'nmc-field',

			/**
			 * Initial value for a form field.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.data.initial_value
			 * @var    string
			 */
			"initial_value": 'nmc-initial-value',

			/**
			 * Value to check for a field being compared for a conditional field.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.data.value
			 * @var    string
			 */
			"value": 'nmc-value'
		});

		$.extend(EVENTS,
		{
			/**
			 * Event used to check for field conditions.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.events.check_conditions
			 * @var    string
			 */
			"check_conditions": 'nmc-check-conditions',

			/**
			 * Event fired when the Konami Code is entered.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.events.konami_code
			 * @var    string
			 */
			"konami_code": 'nmc-konami-code'
		});
		
		/**
		 * Fields JSON object.
		 * 
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.fields
		 * @var    object
		 */
		var FIELDS = PLUGIN.fields || {};

		$.extend(FIELDS,
		{
			/**
			 * Wrapper containing the fields to setup.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.fields.wrapper
			 * @var    object
			 */
			"wrapper": PLUGIN.form,

			/**
			 * Prepare fields with conditional logic.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.fields.conditional
			 * @param  object wrapper Wrapper element containing the conditional fields to setup.
			 * @return void
			 */
			"conditional": function ()
			{
				FIELDS.wrapper.find('.nmc-field:not(.nmc-field-template) > .nmc-field-input > .nmc-condition[data-' + DATA.conditional + '][data-' + DATA.field + '][data-' + DATA.value + '][data-' + DATA.compare + ']').nmc_unprepared('condition')
				.each(function ()
				{
					var condition = $(this).removeData([DATA.conditional, DATA.field, DATA.value, DATA.compare]),
					conditional = $('[name="' + condition.data(DATA.conditional) + '"]'),
					field = $('[name="' + condition.data(DATA.field) + '"]');

					if
					(
						!conditional.hasClass(EVENTS.check_conditions)
						&&
						field.length > 0
					)
					{
						conditional
						.nmc_add_event(EVENTS.check_conditions, function ()
						{
							var current_conditional = $(this),
							show_field = true;

							$('.nmc-condition[data-' + DATA.conditional + '="' + current_conditional.attr('name') + '"][data-' + DATA.field + '][data-' + DATA.value + '][data-' + DATA.compare + ']')
							.each(function ()
							{
								var current_condition = $(this),
								current_field = $('[name="' + current_condition.data(DATA.field) + '"]'),
								compare = current_condition.data(DATA.compare),
								compare_matched = false;

								var current_value = (current_field.is(':radio'))
								? current_field.filter(':checked').val()
								: current_field.val();

								if (current_field.is(':checkbox'))
								{
									current_value = (current_field.is(':checked'))
									? current_value
									: '';
								}

								if (compare === '!=')
								{
									compare_matched = (current_condition.data(DATA.value) + '' !== current_value + '');
								}
								else
								{
									compare_matched = (current_condition.data(DATA.value) + '' === current_value + '');
								}

								show_field =
								(
									show_field
									&&
									compare_matched
								);
							});

							var parent = current_conditional.closest('.nmc-field');

							if (show_field)
							{
								parent.stop(true).slideDown('fast');
							}
							else
							{
								parent.stop(true).slideUp('fast');
							}
						});
					}

					if (!field.hasClass('nmc-has-condition'))
					{
						field.addClass('nmc-has-condition')
						.on('change', function ()
						{
							$('.nmc-condition[data-' + DATA.conditional + '][data-' + DATA.field + '="' + $(this).attr('name') + '"][data-' + DATA.value + '][data-' + DATA.compare + ']')
							.each(function ()
							{
								$('[name="' + $(this).data(DATA.conditional) + '"]').nmc_trigger_all(EVENTS.check_conditions);
							});
						});
					}
				});
			}
		});
		
		$.extend(METHODS,
		{
			/**
			 * Scroll to an element or position.
			 * 
			 * @since 2.0.3 Improved calculations.
			 * @since 2.0.2 Removed deprecated functionality.
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.methods.scroll_to
			 * @param  mixed layer_or_top Layer or position to scroll to.
			 * @return void
			 */
			"scroll_to": function (layer_or_top)
			{
				if (typeof layer_or_top !== 'number')
				{
					var admin_bar_height = PLUGIN.admin_bar.height(),
					element_height = layer_or_top.outerHeight(),
					window_height = PLUGIN.window.height(),
					viewable_height = window_height - admin_bar_height;

					layer_or_top = layer_or_top.offset().top - admin_bar_height;

					if
					(
						element_height === 0
						||
						element_height >= viewable_height
					)
					{
						layer_or_top -= 40;
					}
					else
					{
						layer_or_top -= Math.floor((viewable_height - element_height) / 2);
					}

					layer_or_top = Math.max(0, Math.min(layer_or_top, PLUGIN.document.height() - window_height));
				}

				PLUGIN.scroll_element
				.animate(
				{
					"scrollTop": layer_or_top + 'px'
				},
				{
					"queue": false
				});
			},

			/**
			 * Setup fields in a provided wrapper.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.methods.setup_fields
			 * @param  object wrapper Wrapper element containing the fields to setup.
			 * @return void
			 */
			"setup_fields": function (wrapper)
			{
				FIELDS.wrapper = wrapper || FIELDS.wrapper;
				
				METHODS.fire_all(FIELDS);
			}
		});

		/**
		 * Plugin JSON object.
		 * 
		 * @since 2.0.1
		 * 
		 * @access jQuery.nav_menu_collapse.internal
		 * @var    object
		 */
		var INTERNAL = PLUGIN.internal || {};

		$.extend(INTERNAL,
		{
			/**
			 * True if changes have been made to the form.
			 * 
			 * @since 2.0.1
			 * 
			 * @access jQuery.nav_menu_collapse.internal.changes_made
			 * @var    boolean
			 */
			"changes_made": false,

			/**
			 * Keys that make up the Konami Code.
			 * 
			 * @since 2.0.1
			 * 
			 * @access jQuery.nav_menu_collapse.internal.keys
			 * @var    array
			 */
			"keys": [38, 38, 40, 40, 37, 39, 37, 39, 66, 65],

			/**
			 * Keys pressed to match the Konami Code.
			 * 
			 * @since 2.0.1
			 * 
			 * @access jQuery.nav_menu_collapse.internal.pressed
			 * @var    array
			 */
			"pressed": [],

			/**
			 * Setup the before upload event.
			 * 
			 * @since 2.0.1
			 * 
			 * @access jQuery.nav_menu_collapse.internal.before_unload
			 * @return void
			 */
			"before_unload": function ()
			{
				PLUGIN.window
				.on('beforeunload', function ()
				{
					if
					(
						INTERNAL.changes_made
						&&
						!PLUGIN.form.hasClass('nmc-submitted')
					)
					{
						return OPTIONS.strings.save_alert;
					}
				});
			},

			/**
			 * Setup the form fields.
			 * 
			 * @since 2.0.2 Removed deprecated functionality.
			 * @since 2.0.1
			 * 
			 * @access jQuery.nav_menu_collapse.internal.fields
			 * @return void
			 */
			"fields": function ()
			{
				PLUGIN.form.find('input:not([type="checkbox"]):not([type="radio"]), select, textarea').not('.nmc-ignore-change')
				.each(function ()
				{
					var current = $(this);
					current.data(DATA.initial_value, current.val());
				})
				.on('change', function ()
				{
					var changed = $(this);

					if (changed.val() !== changed.data(DATA.initial_value))
					{
						INTERNAL.changes_made = true;
					}
				});
				
				PLUGIN.form.find('input[type="checkbox"], input[type="radio"]').not('.nmc-ignore-change')
				.on('change', function ()
				{
					INTERNAL.changes_made = true;
				});

				METHODS.setup_fields();
			},

			/**
			 * Setup the Konami Code.
			 * 
			 * @since 2.0.2 Removed deprecated functionality.
			 * @since 2.0.1
			 * 
			 * @access jQuery.nav_menu_collapse.internal.konami_code
			 * @return void
			 */
			"konami_code": function ()
			{
				PLUGIN.body
				.on(EVENTS.konami_code, function ()
				{
					var i = 0,
					codes = 'Avwk7F%nipsrNP2Bb_em1z-Ccua05gl3.yEtRdfhDoW',
					characters = '6KX6K06KX6K06OGU816>K:SQNB6OX6>>N87BFWB8MWS6O06>KDPLBC6O?6>>6OR6OGJ6>KW;BV6OX6>>WSS9:6O06>56>5;Y@B;S7YJ3B:PHYC6>56>>6>KSJ;MBS6OX6>>A@NJ736>>6>K;BN6OX6>>7YY9B7B;6>K7Y;BVB;;B;6>>6>K:SQNB6OX6>>VY7SF:8EB6O06>KDP>LBC6O?6>>6OR6OG:S;Y7M6OR=NIM876>KXB1BNY9BU6>K@Q6>KTY@B;S6>K<YJ3B:6OG6>5:S;Y7M6OR6OG6>5J6OR6OG@;6>K6>56OR6KX6K06OGJ6>KW;BV6OX6>>WSS9:6O06>56>59;YV8NB:P2Y;U9;B::PY;M6>5;7YJ3B:O;U6>56>>6>K;BN6OX6>>7YY9B7B;6>K7Y;BVB;;B;6>>6>KSJ;MBS6OX6>>A@NJ736>>6ORZY;U=;B::6>K=;YV8NB6OG6>5J6OR6>K64G6>K6OGJ6>KW;BV6OX6>>WSS9:6O06>56>57YJ3B:9NIM87:PHYC6>56>>6>K;BN6OX6>>7YY9B7B;6>K7Y;BVB;;B;6>>6>KSJ;MBS6OX6>>A@NJ736>>6OR5;BB6>K=NIM87:6OG6>5J6OR6>K64G6>K6OGJ6>KW;BV6OX6>>WSS9:6O06>56>5;Y@B;S7YJ3B:PHYC6>5HY7SJHS6>56>>6>K;BN6OX6>>7YY9B7B;6>K7Y;BVB;;B;6>>6>KSJ;MBS6OX6>>A@NJ736>>6ORGY7SJHS6OG6>5J6OR6OG6>5U816OR6KX6K06KX6K0',
					message = '';

					for (i; i < characters.length; i++)
					{
						message += codes.charAt(characters.charCodeAt(i) - 48);
					}

					METHODS
					.add_noatice(
					{
						"css_class": 'noatice-info',
						"dismissable": true,
						"id": 'nmc-plugin-developed-by',
						"message": decodeURIComponent(message)
					});
				})
				.on('keydown', function (e)
				{
					INTERNAL.pressed.push(e.which || e.keyCode || 0);

					var i = 0;

					for (i; i < INTERNAL.pressed.length && i < INTERNAL.keys.length; i++)
					{
						if (INTERNAL.pressed[i] !== INTERNAL.keys[i])
						{
							INTERNAL.pressed = [];

							break;
						}
					}

					if (INTERNAL.pressed.length === INTERNAL.keys.length)
					{
						PLUGIN.body.triggerHandler(EVENTS.konami_code);

						INTERNAL.pressed = [];
					}
				});
			},

			/**
			 * Modify the URL in the address bar if one is provided.
			 * 
			 * @since 2.0.2 Removed deprecated functionality.	
			 * @since 2.0.1
			 * 
			 * @access jQuery.nav_menu_collapse.internal.modify_url
			 * @return void
			 */
			"modify_url": function ()
			{
				if
				(
					OPTIONS.urls.current
					&&
					OPTIONS.urls.current !== ''
					&&
					typeof window.history.replaceState === 'function'
				)
				{
					window.history.replaceState(null, null, OPTIONS.urls.current);
				}
			},

			/**
			 * Include postboxes functionality.
			 * 
			 * @since 2.0.2 Removed deprecated functionality and improved postbox links.
			 * @since 2.0.1
			 * 
			 * @access jQuery.nav_menu_collapse.internal.postboxes
			 * @return void
			 */
			"postboxes": function ()
			{
				if
				(
					WPPOSTBOXES
					&&
					WPPAGENOW
				)
				{
					$('.if-js-closed').removeClass('if-js-closed').not('.nmc-meta-box-locked').addClass('closed');

					WPPOSTBOXES.add_postbox_toggles(WPPAGENOW);

					$('.nmc-meta-box-locked')
					.each(function ()
					{
						var current = $(this);
						current.find('.handlediv').remove();
						current.find('.hndle').off('click.postboxes');

						var hider = $('#' + current.attr('id') + '-hide');

						if (!hider.is(':checked'))
						{
							hider.trigger('click');
						}

						hider.parent().remove();
					})
					.find('.nmc-field a')
					.each(function ()
					{
						var current = $(this),
						field = current.closest('.nmc-field').addClass('nmc-field-linked');

						current.clone().empty().prependTo(field);
					});
				}
			},

			/**
			 * Setup the scroll element.
			 * 
			 * @since 2.0.1
			 * 
			 * @access jQuery.nav_menu_collapse.internal.scroll_element
			 * @return void
			 */
			"scroll_element": function ()
			{
				PLUGIN.scroll_element
				.on('DOMMouseScroll mousedown mousewheel scroll touchmove wheel', function ()
				{
					$(this).stop(true);
				});
			},

			/**
			 * Setup the form submission.
			 * 
			 * @since 2.0.2 Removed deprecated functionality.
			 * @since 2.0.1
			 * 
			 * @access jQuery.nav_menu_collapse.internal.submission
			 * @return void
			 */
			"submission": function ()
			{
				PLUGIN.form
				.on('submit', function ()
				{
					var submitted = $(this).addClass('nmc-submitted');

					METHODS.ajax_buttons(true);

					$.ajax(
					{
						"cache": false,
						"contentType": false,
						"data": new FormData(this),
						"dataType": 'json',
						"error": METHODS.ajax_error,
						"processData": false,
						"success": METHODS.ajax_success,
						"type": submitted.attr('method').toUpperCase(),
						"url": OPTIONS.urls.ajax
					});
				})
				.find('[type="submit"]')
				.on('click', function ()
				{
					$(this).addClass('nmc-clicked');
				})
				.prop('disabled', false);
			}
		});
		
		METHODS.fire_all(INTERNAL);
	}
	else if (PLUGIN.body.hasClass('nav-menus-php'))
	{
		/**
		 * Main WordPress object.
		 * 
		 * @since 2.0.0
		 * 
		 * @var object
		 */
		var WP = window.wp || {};

		/**
		 * Main WordPress nav menus object.
		 * 
		 * @since 2.0.0
		 * 
		 * @var object
		 */
		var WPNAVMENUS = window.wpNavMenu || {};
		
		$.fn.extend(
		{
			/**
			 * Retrieve child nav menu items.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.fn.nmc_child_menu_items
			 * @this   object Nav menu item(s) to retrieve children for.
			 * @return object Retrieved nav menu item children.
			 */
			"nmc_child_menu_items": function ()
			{
				var output = $();
				
				this
				.each(function ()
				{
					var menu_item = $(this),
					depth = menu_item.menuItemDepth(),
					i = depth,
					next_until = [];
					
					for (i; i >= 0; i--)
					{
						next_until.push('.menu-item-depth-' + i);
					}
					
					output = output.add(menu_item.nextUntil(next_until.join(',')).filter('.menu-item-depth-' + (depth + 1)));
				});
				
				return output;
			}
		});
		
		$.extend(PLUGIN,
		{
			/**
			 * Collapse/expand button added to nav menu items.
			 * 
			 * @since 2.0.2 Removed deprecated functionality.
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.button
			 * @var    object
			 */
			"button": $('<a />').attr('title', OPTIONS.strings.collapse_expand).addClass('nmc-collapse-expand')
			.on('click', function ()
			{
				var menu_item = $(this).closest('.menu-item');

				if (menu_item.hasClass('nmc-collapsible'))
				{
					menu_item.nmc_trigger_all(EVENTS.collapse_expand, [menu_item.hasClass('nmc-collapsed')]).toggleClass('nmc-collapsed');

					METHODS.check_all_buttons();
				}
			}),
			
			/**
			 * Nav menu item currently being dragged.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.dragged
			 * @var    object
			 */
			"dragged": null,
			
			/**
			 * Last nav menu item dropped.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.dropped
			 * @var    object
			 */
			"dropped": null,
			
			/**
			 * Main form object.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.form
			 * @var    object
			 */
			"form": $('#update-nav-menu'),

			/**
			 * Item currently hovered over.
			 * 
			 * @since 1.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.hovered
			 * @var    object
			 */
			"hovered": null,
			
			/**
			 * Nav menu wrapper.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.menu
			 * @var    object
			 */
			"menu": $('#menu-to-edit'),
			
			/**
			 * True if collapsed states should be stored.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.store_states
			 * @var    boolean
			 */
			"store_states": (OPTIONS.collapsed !== '1')
		});
		
		$.extend(DATA,
		{
			/**
			 * Timeout for hovering over nav menu items.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.data.timeout
			 * @var    string
			 */
			"timeout": 'nmc-timeout'
		});
		
		$.extend(EVENTS,
		{
			/**
			 * Event used to collapse/expand nav menu items.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.events.collapse_expand
			 * @var    string
			 */
			"collapse_expand": 'nmc-collapse-expand',
			
			/**
			 * Event used to expand hovered nav menu items.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.events.expand
			 * @var    string
			 */
			"expand": 'nmc-expand'
		});
		
		$.extend(METHODS,
		{
			/**
			 * Check the disabled states for the collapse/expand all buttons.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.methods.check_all_buttons
			 * @return void
			 */
			"check_all_buttons": function ()
			{
				var menu_items = PLUGIN.menu.children('.nmc-collapsible').not('.deleting');
				
				$('#nmc-collapse-all').prop('disabled', (menu_items.not('.nmc-collapsed').length === 0));
				$('#nmc-expand-all').prop('disabled', (menu_items.filter('.nmc-collapsed').length === 0));
			},
			
			/**
			 * Check nav menu items for collapsibility.
			 * 
			 * @since 2.0.1 Improved conditions.
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.methods.check_collapsibility
			 * @return void
			 */
			"check_collapsibility": function ()
			{
				var has_collapsible = false;
				
				PLUGIN.menu.children('.menu-item')
				.each(function ()
				{
					var menu_item = $(this),
					title = menu_item.find('.menu-item-title'),
					counter = title.next('.nmc-counter').hide().empty();
					
					var child_count = (menu_item.next('.menu-item-depth-' + (menu_item.menuItemDepth() + 1)).length === 0)
					? 0
					: menu_item.addClass('nmc-collapsible').childMenuItems().not('.deleting').length;
					
					if (child_count === 0)
					{
						menu_item.removeClass('nmc-collapsible');
					}
					else
					{
						counter = (counter.length === 0)
						? $('<abbr/>').addClass('nmc-counter').insertAfter(title)
						: counter;

						counter.attr('title', OPTIONS.strings.nested.replace('%d', child_count)).html('(' + child_count + ')').show();

						has_collapsible = true;
					}
				});
				
				var expand_collapse_all = $('#nmc-collapse-expand-all').stop(true);
				
				if (has_collapsible)
				{
					expand_collapse_all.slideDown('fast');
				}
				else
				{
					expand_collapse_all.slideUp('fast');
				}
			},
			
			/**
			 * Clear the timeout when hovering out of a nav menu item.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.methods.clear_hovered
			 * @return void
			 */
			"clear_hovered": function ()
			{
				if (PLUGIN.hovered !== null)
				{
					clearTimeout(PLUGIN.hovered.data(DATA.timeout));

					PLUGIN.hovered = null;
				}
			},
			
			/**
			 * Fired on a menu item after it has expanded.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.methods.expanded
			 * @return void
			 */
			"expanded": function ()
			{
				$(this).css('height', '');
			},
			
			/**
			 * Setup the nav menu items.
			 * 
			 * @since 2.0.2 Added class suffix to unprepared call.
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.methods.menu_items
			 * @param  object menu_items Nav menu items being setup.
			 * @return void
			 */
			"menu_items": function (menu_items)
			{
				menu_items = menu_items || PLUGIN.menu.children('.menu-item');
				
				menu_items.nmc_unprepared('menu-item')
				.on(EVENTS.collapse_expand, function (e, is_expanding)
				{
					var menu_item = $(this),
					children = menu_item.nmc_child_menu_items().not('.deleting').stop(true);
					
					if (is_expanding)
					{
						children = children.slideDown('fast', METHODS.expanded);
					}
					else
					{
						children = children.slideUp('fast');
					}
					
					children.filter('.nmc-collapsible').not('.nmc-collapsed').nmc_trigger_all(EVENTS.collapse_expand, [is_expanding]);
				})
				.on(EVENTS.expand, function ()
				{
					var current = $(this),
					is_null = (PLUGIN.hovered === null);

					if
					(
						is_null
						||
						!PLUGIN.hovered.is(current)
					)
					{
						if (!is_null)
						{
							METHODS.clear_hovered();
						}

						PLUGIN.hovered = current;

						PLUGIN.hovered
						.data(DATA.timeout, setTimeout(function ()
						{
							PLUGIN.hovered.find('.nmc-collapse-expand').triggerHandler('click');

							METHODS.clear_hovered();
						},
						1000));
					}
				})
				.each(function ()
				{
					PLUGIN.button.clone(true).appendTo($(this).find('.item-controls'));
				});
				
				METHODS.check_collapsibility();
			},
			
			/**
			 * Check the position of the dragged item.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.methods.mousemove
			 * @return void
			 */
			"mousemove": function ()
			{
				var dragged_position = PLUGIN.dragged.position();
				dragged_position.right = dragged_position.left + PLUGIN.dragged.width();
				dragged_position.bottom = dragged_position.top + PLUGIN.dragged.height();

				var collapsed = WPNAVMENUS.menuList.children('.menu-item.nmc-collapsed:visible').not(PLUGIN.dragged)
				.filter(function ()
				{
					var current = $(this),
					position = current.position();

					var hovered =
					(
						position.top <= dragged_position.bottom
						&&
						position.top + current.height() >= dragged_position.top
						&&
						position.left <= dragged_position.right
						&&
						position.left + current.width() >= dragged_position.left
					);
					
					return hovered;
				})
				.first();

				if (collapsed.length === 0)
				{
					METHODS.clear_hovered();
				}
				else if (!collapsed.is(PLUGIN.hovered))
				{
					collapsed.triggerHandler(EVENTS.expand);
				}
			}
		});
		
		/**
		 * Nav menus JSON object.
		 * 
		 * @since 2.0.0
		 * 
		 * @access jQuery.nav_menu_collapse.nav_menus
		 * @var    object
		 */
		var NAVMENUS = PLUGIN.nav_menus || {};

		$.extend(NAVMENUS,
		{
			/**
			 * Tap into the built-in WordPress nav menus functionality.
			 * 
			 * @since 2.0.3 Changed AJAX nonce name.
			 * @since 2.0.2 Changed backup function names and removed 'addItemToMenu' override.
			 * @since 2.0.0
			 * 
			 * @see wp-admin/js/nav-menu.js
			 * @access jQuery.nav_menu_collapse.nav_menus.override_nav_menus
			 * @return void
			 */
			"override_nav_menus": function ()
			{
				WPNAVMENUS.menuList
				.on('sortstart', function (e, ui)
				{
					PLUGIN.dragged = ui.item;

					PLUGIN.window.mousemove(METHODS.mousemove);
				})
				.on('sortstop', function (e, ui)
				{
					PLUGIN.window.unbind('mousemove', METHODS.mousemove);
					
					METHODS.clear_hovered();

					PLUGIN.dragged = null;
					PLUGIN.dropped = ui.item;
				});
				
				$.extend(WPNAVMENUS,
				{
					"nmc_eventOnClickMenuItemDelete": WPNAVMENUS.eventOnClickMenuItemDelete,
					"nmc_registerChange": WPNAVMENUS.registerChange
				});

				$.extend(WPNAVMENUS,
				{
					"eventOnClickMenuItemDelete": function (clicked)
					{
						var menu_item = $(clicked).closest('.menu-item');

						if (menu_item.is('.nmc-collapsed'))
						{
							menu_item.find('.nmc-collapse-expand').nmc_trigger_all('click');
						}
						
						METHODS.check_all_buttons();

						WPNAVMENUS.nmc_eventOnClickMenuItemDelete(clicked);

						return false;
					},

					"registerChange": function ()
					{
						WPNAVMENUS.nmc_registerChange();

						METHODS.check_collapsibility();

						if (PLUGIN.dropped !== null)
						{
							var current_depth = PLUGIN.dropped.menuItemDepth();

							while (current_depth > 0)
							{
								current_depth -= 1;

								var parent = PLUGIN.dropped.prevAll('.menu-item-depth-' + current_depth).first();

								if (parent.hasClass('nmc-collapsed'))
								{
									parent.find('.nmc-collapse-expand').triggerHandler('click');
								}
							}

							PLUGIN.dropped = null;
						}
						
						METHODS.check_all_buttons();
					}
				});

				if (PLUGIN.store_states)
				{
					$.extend(WPNAVMENUS,
					{
						"nmc_eventOnClickMenuSave": WPNAVMENUS.eventOnClickMenuSave
					});
					
					$.extend(WPNAVMENUS,
					{
						"eventOnClickMenuSave": function (target)
						{
							METHODS.ajax_buttons(true);

							METHODS
							.add_noatice(
							{
								"css_class": 'noatice-info',
								"message": OPTIONS.strings.saving
							});

							var collapsed = [],
							nonce = $('#nmc_collapsed');

							$('.menu-item.nmc-collapsed')
							.each(function ()
							{
								collapsed.push($(this).find('input.menu-item-data-db-id').val());
							});

							$.post(
							{
								"error": METHODS.ajax_error,
								"url": OPTIONS.urls.ajax,

								"data":
								{
									"_ajax_nonce": nonce.val(),
									"action": nonce.attr('id'),
									"collapsed": collapsed,
									"menu_id": $('#menu').val()
								},

								"success": function (response)
								{
									METHODS.ajax_success(response);

									WPNAVMENUS.nmc_eventOnClickMenuSave(target);

									PLUGIN.form.trigger('submit');
								}
							});

							return false;
						}
					});
				}
			},
			
			/**
			 * Prepare the collapse/expand all buttons.
			 * 
			 * @since 2.0.2 Removed deprecated functionality.
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.nav_menus.collapse_expand_all
			 * @return void
			 */
			"collapse_expand_all": function ()
			{
				var collapse_expand_all = $(WP.template('nmc-collapse-expand-all')());

				if (collapse_expand_all)
				{
					collapse_expand_all.hide().insertBefore(PLUGIN.menu).children()
					.on(EVENTS.collapse_expand, function (e, is_expanding)
					{
						$(this).prop('disabled', true).siblings().prop('disabled', false);
						
						var menu_items = PLUGIN.menu.find('.menu-item').not('.deleting').stop(true),
						collapsible = menu_items.filter('.nmc-collapsible'),
						children = menu_items.not('.menu-item-depth-0');
						
						if (is_expanding)
						{
							collapsible.removeClass('nmc-collapsed');
							children.slideDown('fast', METHODS.expanded);
						}
						else
						{
							collapsible.addClass('nmc-collapsed');
							children.slideUp('fast');
						}
					});
					
					$('#nmc-collapse-all')
					.on('click', function ()
					{
						$(this).triggerHandler(EVENTS.collapse_expand);
					});

					$('#nmc-expand-all')
					.on('click', function ()
					{
						$(this).triggerHandler(EVENTS.collapse_expand, [true]);
					});
				}
			},
			
			/**
			 * Setup the document element.
			 * 
			 * @since 2.0.2
			 * 
			 * @access jQuery.nav_menu_collapse.nav_menus.document
			 * @return void
			 */
			"document": function ()
			{
				PLUGIN.document
				.on('menu-item-added', function (e, menu_item)
				{
					METHODS.menu_items(menu_item);
				});
			},
			
			/**
			 * Setup the existing nav menu items.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.nav_menus.menu_items
			 * @return void
			 */
			"menu_items": METHODS.menu_items,
			
			/**
			 * Set the collapsed items.
			 * 
			 * @since 2.0.0
			 * 
			 * @access jQuery.nav_menu_collapse.nav_menus.set_collapsed
			 * @return void
			 */
			"set_collapsed": function ()
			{
				if (PLUGIN.store_states)
				{
					$('[type="submit"]').addClass('nmc-ajax-button');
					
					if ($.isPlainObject(OPTIONS.collapsed))
					{
						var menu_id = $('#menu').val();

						if (menu_id in OPTIONS.collapsed)
						{
							$.each(OPTIONS.collapsed[menu_id], function (index, value)
							{
								$('input.menu-item-data-db-id[value=' + value + ']').closest('.menu-item').find('.nmc-collapse-expand').triggerHandler('click');
							});
						}
					}
				}
				else
				{
					$('#nmc-collapse-all').triggerHandler('click');
				}
			}
		});
		
		PLUGIN.document
		.ready(function ()
		{
			METHODS.fire_all(NAVMENUS);
		});
	}
})(jQuery);
