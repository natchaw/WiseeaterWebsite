<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#fullpage').fullpage({
        //options here
        scrollingSpeed: 1000,
        navigation: false,
        slidesNavigation: false,
		responsive : 1000
    });
    //methods
    $.fn.fullpage.setAllowScrolling(true);
});

</script>
<!-- end Simple Custom CSS and JS -->
