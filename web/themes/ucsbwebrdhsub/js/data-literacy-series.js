/**
 * @file
 * Overrride UI behaviors
 */

(function ($) {
    $(document).ready(function() {
        const dlsMediaThumbnails = document.querySelectorAll('.dls-media-thumbnail');
        dlsMediaThumbnails.forEach((thumbnail) => {
            const anchor = thumbnail.querySelector('a');

            if(anchor){
                anchor.setAttribute('target', '_blank');
                anchor.setAttribute('rel', 'noopener noreferrer');
            }


          });
    });

})(jQuery);