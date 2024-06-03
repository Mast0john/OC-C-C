(function($) {
    $(function() {
        FWP.hooks.addAction('facetwp/refresh/alpha', function($this, facet_name) {
            FWP.facets[facet_name] = $this.find('.facetwp-alpha.selected').attr('data-id') || '';
        });
    });

    $(document).on('click', '.facetwp-alpha.available', function() {
        $parent = $(this).closest('.facetwp-facet');
        $parent.find('.facetwp-alpha').removeClass('selected');
        var facet_name = $parent.attr('data-name');
        $(this).addClass('selected');

        if ('' !== $(this).attr('data-id')) {
            FWP.frozen_facets[facet_name] = 'soft';
        }
        FWP.refresh();
    });
})(jQuery);
