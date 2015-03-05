<?php
    require_once( "include/atcontent_userinit.php" );
    $posts_id = array();
    $response = atcontent_api_readership( site_url(), json_encode( $posts_id ), $ac_api_key );
?>
<div class="b-cols">
    <div id="dashboard-table" class="b-dashboard-table_nonbg b-dashboard-table" style="margin-bottom: 0px">
        <div id="tip_two_step" class="ac_tip_show" style="padding: 10px;">
            <p> Well done, <?php echo $ac_show_name; ?></p>
            <ol style="font-size: 1.2em;">
                <li>Get relevant posts from "<a href="<?php echo admin_url( "admin.php?page=atcontent_reposts" ); ?>">Content Feed</a>" page.<br>
                <small>Readers will come to your blog more often to read your fresh content and share it on social networks! It almost double your readers engangment!</small></li>
                <li>Set tags and follow relevant bloggers at <br> <a href="http://atcontent.com/following-wp/" target="_blank">this page</a>!<br>
                <small>You will get a better chance on repost and reach a much wider quality audience.</small></li>
            </ol>
            <p>
                <?php if ( isset( $response["IsOK"] ) && $response["IsOK"] == true && intval( $response["repostViews"] ) == 0 ) { ?>
                    Don't be puzzled of zeros. Check this page in 7 days or so.
                <?php } ?>
            </p>
        </div>		
        <div id="stat_text_step" style="float: left; margin-left: 15px; margin-bottom: 20px;">
            <fieldset id="stat-fieldset">
                <legend>Your AtContent Stats</legend>
            </fieldset>
            <div id="follow_steps_block" >
                <p>On average, bloggers increase audience at least 2.5x in just 3 weeks</p> 
            </div>
			
            <div style="width:417px;">
                <?php
                    if ( isset( $response["IsOK"] ) && $response["IsOK"] == true ) {
                ?>
                    <div class="b-dashboard-brief">
                        <div class="b-dashboard-brief__left b-dashboard-brief__left_front">
                            <div class="b-dashboard-brief__value b-dashboard-brief__value_orange">
                            <?php if ( intval( $response["repostViews"] ) != 0 ) { ?>
                            <span class="b-dashboard-brief__plus">+</span>
                            <?php } ?>
                            <?php echo $response["repostViews"]; ?>
                        </div>
                        <div class="b-dashboard-brief__description">
                            view<span data-role="plural">s</span> via AtContent
                            <br>
                            for the last <?php echo $response["days"] . " " . ( intval( $response["days"] > 1 ) ? "days" : "day" ); ?>
                        </div>
                        <div class="b-dashboard-brief__value b-dashboard-brief__value_small b-dashboard-brief__value_blue">
                            <?php echo $response["originalViews"]; ?>
                        </div>
                        <div class="b-dashboard-brief__description b-dashboard-brief__description_small">
                            views on your blog
                        </div>
                        <p><a class="button" href="http://atcontent.com/studio/statistics?wp=0" target="_blank" >Get details</a></p>
                    </div>					
                    <div class="b-dashboard-brief__right b-dashboard-brief__right_front">
                        <?php if ( intval( $response["originalViews"] ) + intval( $response["repostViews"] ) == 0 ) { ?>
                        <div class="b-dashboard-brief__empty-chart"></div>
                        <?php } else { ?>
                        <div id="atcontent_chart" class="b-dashboard-brief__chart"></div>
                        <script src="//www.google.com/jsapi"></script>
                        <script>
                            google.load('visualization', '1.0', {
                                'packages': ['corechart']
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
                                    fill:'none',
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
                                jQuery('rect').attr('fill', '#f1f1f1');
                            });
                        </script>
                        <?php } ?>
                    </div>
                </div>				
            <div class="clear"></div>
            <?php
                } else {
                    echo '<tr><th>Error getting data. Please, reload page</th></tr>';
                }
            ?>
            </div>
        </div>
    </div>  
</div>