window.carbon = window.carbon || {};

(function($) {

	var carbon = window.carbon;

	if (typeof carbon.fields === 'undefined') {
		return false;
	}

	/*
	|--------------------------------------------------------------------------
	| SelectRecipe Field MODEL
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
	carbon.fields.Model.SelectRecipe = carbon.fields.Model.Select.extend();


	/*
	|--------------------------------------------------------------------------
	| SelectRecipe Field VIEW
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
	carbon.fields.View.SelectRecipe = carbon.fields.View.extend({

		// Add the events from the parent view and also include new ones
		events: function() {
			return _.extend({}, carbon.fields.View.prototype.events, {
				'change select': 'changeField',
			});
		},

		initialize: function() {
			// Initialize the parent view
			carbon.fields.View.prototype.initialize.apply(this); // do not delete

			// Listen for changes on the model and modify the DOM
			// this.listenTo(this.model, 'change:example_property', this.handleChange);

			// Wait for the field to be added to the DOM and run an init method
			this.on('field:rendered', this.initField);
		},

		/*
		 * Initialize the code responsible for the DOM manipulations
		 */
		initField: function() {
			if ( typeof window.crbSelectRecipeField == 'undefined' ) {
				this.initRecipesCache();
			};

			this.pullRecipesInfo();
		},

		/*
		 * Change field event
		 */
		changeField: function() {
			this.updateLastUse();
			// this.toggleFieldWarning();
		},

		/**
		 * lastUseStatus - either "green", "yellow", "red"
		 */
		updateLastUse: function() {
			var model = this.model;
			var _recipeID = model.get( 'value' );
			var _class_id = model.get( 'class_id' );

			var recipe = window.crbSelectRecipeField[_class_id][_recipeID];

			if ( recipe.ajaxCompleted && ! recipe.ajaxStarted ) {
				model.set( 'lastUseText', recipe.lastUseText );
				model.set( 'lastUseStatus', recipe.lastUseStatus );
			}
		},

		/**
		 * Lazy Load All Recipes
		 */
		pullRecipesInfo: function() {
			// This is executed asynchronously, only once foreach field in the complex
			if ( this.isAjaxCompleted() ) {
				this.updateOptionsFromCache();
				this.initSelect2();
				this.changeField();

				return;
			};

			var model = this.model;
			var _options = model.get('options').slice(0);

			this.walkFirstRecipe( _options );
		},

		/**
		 * Do AJAX with the first element of the options
		 *
		 * Recursive:
		 * With each Iteration the "_options" losses it's first element
		 */
		walkFirstRecipe: function( _options ) {
			var _this = this;
			var model = this.model;
			var _class_id = model.get( 'class_id' );

			/**
			 * Allow for the initialization to be hold indefinetly
			 * attemting initialization each second, until all AJAX requests are completed.
			 */
			if ( _options.length <= 0 ) {
				// console.log('Timeout Start for: ' + _this.$el.find('select').attr('name') );
				setTimeout( function() {
					// console.log('Timeout End for: ' + _this.$el.find('select').attr('name') );
					_this.pullRecipesInfo();
				}, 1000 );

				return;
			};

			var currentOption = _options.shift();
			var _recipeID = currentOption.value;

			var recipe = window.crbSelectRecipeField[_class_id][_recipeID];

			// Check if the ajax for this recipe has already been started, or it has already completed.
			if ( ! recipe.ajaxCompleted && ! recipe.ajaxStarted ) {
				_this.getRecipeInfo( _options, _recipeID, _class_id );
			} else {
				// Skip to the next entry
				_this.walkFirstRecipe( _options );
			};
		},

		/**
		 * Do AJAX for a single Recipe, passthrough the _options back to the recursive function
		 */
		getRecipeInfo: function( _options, _recipeID, _class_id ) {
			var _this = this;
			var recipe = window.crbSelectRecipeField[_class_id][_recipeID];

			$.ajax({
				url: php_passed_vars.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'crb_select_recipe_field',
					recipe_id: _recipeID,
					class_id: _class_id
				},
				beforeSend: function() {
					window.crbSelectRecipeField[_class_id][_recipeID].ajaxStarted = true;
				}
			})
			.done(function( responce ) {
				// console.log('ajax completed for: ' + _this.$el.find('select').attr('name') );

				recipe.lastUseText = responce.text;
				recipe.lastUseStatus = responce.status;
				recipe.ajaxStarted = false;
				recipe.ajaxCompleted = true;

				window.crbSelectRecipeField[_class_id][_recipeID] = recipe;

				_this.walkFirstRecipe( _options );
			});
		},

		/**
		 * Display Field Errors
		 */
		toggleFieldWarning: function() {
			var _this = this;
			var model = this.model;
			var _class_id = model.get( 'class_id' );
			var _text = model.get('lastUseText');
			var _status = model.get('lastUseStatus');

			var $holder = this.$el.closest('.carbon-field');
			var $errorHolder = $holder.find('.carbon-error-select-recipe');

			$holder
				.removeClass('crb-select-recipe-green')
				.removeClass('crb-select-recipe-yellow')
				.removeClass('crb-select-recipe-red')
				.addClass('crb-select-recipe-' + _status);

			$errorHolder.html(_text);
		},

		/**
		 * Initialize the Select2 plugin.
		 * This is called only once per Select field.
		 */
		initSelect2: function() {
			var $select = this.$el.find('select');
			var _options = this.model.get('options');

			$select.select2({
				data: _options,
				templateResult: crbTemplateResult,
				templateSelection: crbTemplateSelection,
			});
		},

		/**
		 * Cache
		 * stored in "window.crbSelectRecipeField"
		 *
		 * Functions:
		 *    initRecipesCache
		 *    updateOptionsFromCache
		 *    isAjaxCompleted
		 */
		initRecipesCache: function() {
			var model = this.model;
			var _this = this;
			var _recipeID = model.get( 'value' );
			var _class_id = model.get( 'class_id' );

			window.crbSelectRecipeField = {};
			window.crbSelectRecipeField[_class_id] = {};

			var options = model.get('options');
			$.each( options, function( index, option ) {
				var _recipeID = option.value;

				window.crbSelectRecipeField[_class_id][_recipeID] = {
					lastUseText : '',
					lastUseStatus : '',
					ajaxCompleted : false,
					ajaxStarted : false,
				};
			} );
		},

		updateOptionsFromCache: function() {
			var model = this.model;
			var _this = this;
			var _class_id = model.get( 'class_id' );
			var options = model.get('options');

			var recipes = window.crbSelectRecipeField[_class_id];

			$.each( options, function( index, option ) {
				var _recipeID = option.value;
				var recipe = recipes[_recipeID];

				if ( recipe.ajaxCompleted && ! recipe.ajaxStarted ) {
					option.lastUseText = recipe.lastUseText;
					option.lastUseStatus = recipe.lastUseStatus;
				};
			} );

			model.set( 'options', options );
		},

		isAjaxCompleted: function() {
			var model = this.model;
			var _this = this;
			var _class_id = model.get( 'class_id' );

			var recipes = window.crbSelectRecipeField[_class_id];

			var status = true;

			$.each( recipes, function ( index, recipe ) {
				if ( ! recipe.ajaxCompleted || recipe.ajaxStarted ) {
					status = false;
					return false;
				};
			} );

			return status;
		},
	});

	function crbTemplateResult(result, container) {
		$(container)
			.removeClass('recipe-green')
			.removeClass('recipe-red')
			.removeClass('recipe-yellow')
			.addClass('recipe-' + result.lastUseStatus);

		return result.text;
	};

	function crbTemplateSelection(selection, container) {
		$(container)
			.removeClass('recipe-green')
			.removeClass('recipe-red')
			.removeClass('recipe-yellow')
			.addClass('recipe-' + selection.lastUseStatus);

		return selection.text;
	};

}(jQuery));
