window.carbon = window.carbon || {};

(function($) {

	var carbon = window.carbon;

	if (typeof carbon.fields === 'undefined') {
		return false;
	}

	/*
	|--------------------------------------------------------------------------
	| Wickedpicker Field MODEL
	|--------------------------------------------------------------------------
	|
	| This class represents the model for the field.
	|
	| A model is responsible for holding the fields current state (data).
	| It also has all the logic surrounding the data management, like:
	|  - conversion
	|  - validation
	|  - access control
	|
	*/
	carbon.fields.Model.Wickedpicker = carbon.fields.Model.extend({

		// Set some default values if need. They will be stored in the model attributes.
		/*defaults: {
			'value': '',
			'hours': 0,
			'minutes': 0,
			'mer': 'AM'
		},*/

		initialize: function() {
			carbon.fields.Model.prototype.initialize.apply(this);  // do not delete

			// Model data manipulations can be done here. For example:
			var _this = this;
			var options = this.get('options') || [];

			var hidden_value = this.get('value');
			_this.hidden_to_select( hidden_value );
		},

		select_to_hidden: function( formatted_value ) {
			var date = new Date('1970/01/01 ' + formatted_value);

			return this.pad( date.getHours() ) + ':' + this.pad( date.getMinutes() );
		},

		hidden_to_select: function( hidden_value ) {
			var hours, minutes, mer;
			if (hidden_value == null) {
				// default time
				hidden_value = '15:00';
				this.set( 'value', hidden_value );
			}
			var date = new Date('1970/01/01 ' + hidden_value);

			hours = date.getHours() % 24;
			mer = 'AM';

			//At 00 hours we need to show 12 am
			if( hours == 0 ) {
				hours = 12;
			} else if( hours == 12 ) {
				mer = 'PM';
			} else if( hours >= 12 ) {
				hours = hours % 12;
				mer = 'PM';
			}

			hours = this.pad( hours );
			minutes = this.pad( date.getMinutes() );
			mer = mer;

			this.set( 'hours', hours );
			this.set( 'minutes', minutes );
			this.set( 'mer', mer );

			return {
				hours: hours,
				minutes: minutes,
				mer: mer,
			};
		},

		pad: function( numericValue ) {
			numericValue = parseInt( numericValue );
			if ( numericValue < 10 ) {
				return ( '0' + numericValue.toString () );
			} else {
				return ( numericValue );
			}
		},

		/*
		 * The validate method is an internal Backbone method.
		 * It will check if the field model data is valid.
		 *
		 * @see http://backbonejs.org/#Model-validate
		 */
		validate: function( attrs, options ) {
			var hasErrors = false;

			if (
				attrs.hours.toString().length <= 0 ||
				attrs.minutes.toString().length <= 0 ||
				attrs.mer.toString().length <= 0
			) {
				hasErrors = [];

				if ( attrs.hours.toString().length <= 0 ) {
					hasErrors.push('Please select Hours correctly.');
				}

				if ( attrs.minutes.toString().length <= 0 ) {
					hasErrors.push('Please select Minutes correctly.');
				}

				if ( attrs.mer.toString().length <= 0 ) {
					hasErrors.push('Please select AM/PM correctly.');
				}

				hasErrors = hasErrors.join( '<br />' );
			};


			return hasErrors;
		}

	});


	/*
	|--------------------------------------------------------------------------
	| Wickedpicker Field VIEW
	|--------------------------------------------------------------------------
	|
	| Holds the field DOM interactions (rendering, error state, etc..).
	| The field view also SYNCs the user entered data with the model.
	|
	| Views reflect what the applications data models look like.
	| They also listen to events and react accordingly.
	|
	| @element: .[id]
	| @holder:  carbon.views[id]
	|
	*/
	carbon.fields.View.Wickedpicker = carbon.fields.View.extend({

		// Add the events from the parent view and also include new ones
		events: function() {
			return _.extend({}, carbon.fields.View.prototype.events, {
				'change select': 'sync',
				'crb_change_time_autopush input.crb-timepicki-unformatted-value': 'syncHidden',
			});
		},


		initialize: function() {
			// Initialize the parent view
			carbon.fields.View.prototype.initialize.apply(this); // do not delete

			// Listen for changes on the model and modify the DOM
			// this.listenTo(this.model, 'change:example_property', this.handleChange);

			// Wait for the field to be added to the DOM and run an init method
			// this.on('field:rendered', this.initField);
		},

		/*
		 * Initialize the code responsible for the DOM manipulations
		 */
		initField: function() {
			var hours = this.model.get( 'hours' );
			var minutes = this.model.get( 'minutes' );
			var mer = this.model.get( 'mer' );

			// Add your logic here
		},

		/*
		 * Syncs the user entered value with the model.
		 * By default this method is fired when the input value has changed.
		 *
		 * If the field has more then one input, this method should be overwritten!
		 */

		sync: function(event) {
			// console.log('sync');
			var $hours = this.$el.find('select.hours');
			var $minutes = this.$el.find('select.minutes');
			var $mer = this.$el.find('select.mer');
			var $hiddenInput = this.$el.find('input.crb-timepicki-unformatted-value');

			var hours = $hours.val();
			var minutes = $minutes.val();
			var mer = $mer.val();

			// Update Model
			this.model.set( 'hours', hours );
			this.model.set( 'minutes', minutes );
			this.model.set( 'mer', mer );

			if ( ! this.model.isValid() ) {
				this.toggleError();
				return;
			};

			var formatted_value = hours + ':' + minutes + ' ' + mer;

			var hidden_value = this.model.select_to_hidden( formatted_value );

			this.model.set( 'value', hidden_value );
			$hiddenInput.val( hidden_value );
			$hiddenInput.trigger('crb_change_user_select');
		},

		syncHidden: function(event) {
			// console.log('syncHidden');
			var $hours = this.$el.find('select.hours');
			var $minutes = this.$el.find('select.minutes');
			var $mer = this.$el.find('select.mer');
			var $hiddenInput = this.$el.find('input.crb-timepicki-unformatted-value');

			var hidden_value = $hiddenInput.val();

			this.model.hidden_to_select( hidden_value );

			var hours = this.model.get( 'hours' );
			var minutes = this.model.get( 'minutes' );
			var mer = this.model.get( 'mer' );

			$hours.val( hours ).find('option[value="' + hours + '"]').prop( 'selected', true );
			$minutes.val( minutes ).find('option[value="' + minutes + '"]').prop( 'selected', true );
			$mer.val( mer ).find('option[value="' + mer + '"]').prop( 'selected', true );
		},

	});

}(jQuery));