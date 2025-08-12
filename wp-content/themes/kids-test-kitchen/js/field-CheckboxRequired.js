window.carbon = window.carbon || {};

(function($) {
	var carbon = window.carbon;

	if (typeof carbon.fields === 'undefined') {
		return false;
	}

	/*--------------------------------------------------------------------------
	 * CheckboxRequired
	 *------------------------------------------------------------------------*/

	// CheckboxRequired VIEW
	carbon.fields.View.CheckboxRequired = carbon.fields.View.extend({
		sync: function(event) {
			var value = this.$('input[type="checkbox"]:checked').val() || '';

			this.model.set('value', value);
		}
	});

}(jQuery));
