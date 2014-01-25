<div class="b-cols" style="float:left">
    
        <div id="tip_two_step" class="ac_tip_show" style="margin-left: 15px; display: none;">
            <p>This is your AtContent stats.  Wanna get better? <a target="_blank" href="http://atcontent.com/" onclick="hide_welcome()">Improve your blog!</a></p>
            <a href="javascript:hide_welcome()" style="font-size: 10px;">No thanks.</a>
        </div>
        <div id="stat_text_step" style="float: left; margin-left: 15px;margin-bottom: 20px;">
            <fieldset id="stat-fieldset"><legend>Statistics for my blog</legend></fieldset> 
        <div id="dashboard-table" class="b-dashboard-table" style="margin-bottom: 0px;">
                        <div style="width:416px;">
        <?php
            if ( strlen( $ac_api_key ) > 0 ) {
                $posts = $wpdb->get_results( 
                    "
                    SELECT ID, post_title, post_author
                    FROM {$wpdb->posts}
                    WHERE post_status = 'publish' 
                        AND post_author = {$userid} AND post_type = 'post'
                    "
                );

                $posts_id = array();

                wp_cache_flush();

                foreach ( $posts as $post ) 
                {
                    $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
                    if ( strlen( $ac_postid ) > 0 ) { 
                        array_push( $posts_id, $ac_postid );
                    }
                    wp_cache_flush();
                }

                $response = atcontent_api_readership( site_url(), json_encode( $posts_id ), $ac_api_key );

                if ( $response["IsOK"] == true ) {
        ?>
                <div class="b-dashboard-brief">
                    <div class="b-dashboard-brief__left b-dashboard-brief__left_front">
                        <div class="b-dashboard-brief__value b-dashboard-brief__value_orange">
                            <span class="b-dashboard-brief__plus">+</span>
                            <?php echo $response["repostViews"]; ?>
                        </div>
                        <div class="b-dashboard-brief__description">
                            view<span data-role="plural">s</span> via AtContent
                            <br>
                            for the last 12 hours
                        </div>
                        <div class="b-dashboard-brief__value b-dashboard-brief__value_small b-dashboard-brief__value_blue">
                            <?php echo $response["originalViews"]; ?>
                        </div>
                        <div class="b-dashboard-brief__description b-dashboard-brief__description_small">
                            views on your blog
                        </div>
                        <p><a class="button" href="https://atcontent.com/Studio/Statistics" target="_blank" onclick="getDetailsClick()">Get details</a></p>
                    </div>
                    <div class="b-dashboard-brief__right b-dashboard-brief__right_front">
                        <?php if ( intval( $response["originalViews"] ) + intval( $response["repostViews"] ) == 0 ) { ?>
                        <div class="b-dashboard-brief__empty-chart"></div>
                        <?php } else { ?>
                        <div id="atcontent_chart" class="b-dashboard-brief__chart"></div>
                        <?php } ?>
                    </div>
                </div>
                <script src="//www.google.com/jsapi"></script>
                <script>
                    google.load('visualization', '1.0', {
                        'packages': ['corechart', 'table']
                    });
                    google.setOnLoadCallback(function () {
                        var options, data, chart, element, rows;
                
                        element = document.getElementById('atcontent_chart');

                        options = {
                            colors: ['#13669d', '#ee8900'],
                            chartArea: {
                                width: '90%',
                                height: '90%'
                            },
                            title: '',
                            titleTextStyle: {
                                bold: false
                            },
                            fontName: 'Segoe UI',
                            legend: {
                                position: 'none'
                            },
                            pieSliceTextStyle: {
                                fontSize: 15
                            }
                        };

                        data = new google.visualization.DataTable ();
                        data.addColumn('string', 'Type');
                        data.addColumn('number', 'Views');
                
                        rows = [
                            ['Views on your blog', <?php echo $response["originalViews"]; ?>],
                            ['Views via AtContent', <?php echo $response["repostViews"]; ?>]
                        ];
                
                        data.addRows(rows);

                        chart = new google.visualization.PieChart (element);
                        chart.draw(data, options);
                    });
                </script>
            <div class="clear"></div>
            <?php
                    } else {
                        echo '<tr><th>Error getting data</th></tr>';
                    }
                }
            ?>
            </div>
        </div>
        <div id="tip_one_step" class="ac_tip_show" style="display: none;">
            <p>Congratulations you are just connected your blog to AtContent!</p>
            <p>Adjust site settings to get better results in promotion of your posts.</p>
        </div>
            
    </div>    
</div>