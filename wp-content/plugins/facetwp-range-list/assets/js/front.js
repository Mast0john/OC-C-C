(function($) {
    FWP.hooks.addAction('facetwp/refresh/range_list', function ($this, facet_name) {
        var selected_values = [];
        $this.find('.facetwp-radio.checked').each(function () {
            selected_values.push($(this).attr('data-value'));
        });
        FWP.facets[facet_name] = selected_values;
    });

    FWP.hooks.addFilter('facetwp/selections/range_list', function (output, params) {
        var choices = [];
        $.each(params.selected_values, function (idx, val) {
            var choice = params.el.find('.facetwp-radio[data-value="' + val + '"]').clone();
            choice.find('.facetwp-counter').remove();
            choices.push({
                value: val,
                label: choice.text()
            });
        });
        return choices;
    });

    $(document).on('click', '.facetwp-type-range_list .facetwp-radio:not(.disabled)', function () {
        var is_checked = $(this).hasClass('checked');
        $(this).closest('.facetwp-facet').find('.facetwp-radio').removeClass('checked');
        if (!is_checked) {
            $(this).addClass('checked');
        }
        FWP.autoload();
    });
})(jQuery);
