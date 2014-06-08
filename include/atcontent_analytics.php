<?php
    wp_register_script( 'atcontentAnalytics',  plugins_url( '../assets/analytics.js?v=0', __FILE__ ), array(), true );
    wp_enqueue_script( 'atcontentAnalytics' );
?>
<script>
    function ac_ga_s(category, action)
    {
        window.ac_ga = window.ac_ga || [];
        ac_ga.push({
            category: category,
            action: action,
            label: '<?php echo AC_VERSION; ?>'
        });
    }
</script>