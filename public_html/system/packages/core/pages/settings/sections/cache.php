<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Saturday, January 13th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

use \system\classes\Cache as Cache;

function settings_cache_tab(){
    if( !Cache::enabled() ){
        ?>
        <h4 class="text-center">The cache is disabled</h4>
        <?php
    }else{
        $cache_stats = Cache::getStats()->getRawData();
        ?>
        <style type="text/css">
            .cache_stats_box{
                color: #4bc0c0;
                border: 1px solid lightgray;
                margin: 10% 15%;
                padding: 10% 0;
            }
        </style>

        <p>
            This tab shows statistics about the cache usage.
        </p>

        <div class="col-md-3">
            <h4 class="text-center">
                Hits vs Misses
            </h4>
            <div id="settings_cache_tab_hits_vs_misses_container">
                <canvas style="width:100%"></canvas>
            </div>
        </div>
        <div class="col-md-3">
            <h4 class="text-center" style="margin-bottom:">
                Num. Entries
            </h4>
            <h3 class="text-center cache_stats_box">
                <?php echo $cache_stats['num_entries']?>
            </h3>
        </div>
        <div class="col-md-3">
            <h4 class="text-center">
                Mem. Used
            </h4>
            <h3 class="text-center cache_stats_box">
                <?php echo human_filesize($cache_stats['mem_size'])?>
            </h3>
        </div>
        <div class="col-md-3">
            <h4 class="text-center">
                Mem. Usage
            </h4>
            <div id="settings_cache_tab_mem_usage_container">
                <canvas style="width:100%"></canvas>
            </div>
        </div>

        <script type="text/javascript">
            $('#cache_collapse').on('shown.bs.collapse', function () {
                // create Hits vs Misses donut chart
                chart_config = {
                    type: 'pie',
                    data: {
                        datasets: [{
                            data: [ <?php printf("%.2f, %.2f", $cache_stats['num_hits'], $cache_stats['num_misses']) ?> ],
                            backgroundColor: [
                                window.chartColors.green,
        						window.chartColors.yellow
                            ]
                        }],
                        labels: ['Hits', 'Misses']
                    },
                    options: {
                        cutoutPercentage: 50,
                        maintainAspectRatio: false,
                        legend: false
                    }
                };
                // create chart obj
                ctx = $("#settings_cache_tab_hits_vs_misses_container canvas")[0].getContext('2d');
                chart = new Chart(ctx, chart_config);
                //
                // create Mem Usage donut chart
                chart_config = {
                    type: 'pie',
                    data: {
                        datasets: [{
                            data: [
                                <?php
                                $used = 100 * (float)$cache_stats['num_entries'] / (float)$cache_stats['num_slots'];
                                $free = 100 - $used;
                                printf("%.2f, %.2f", $used, $free);
                                ?>
                            ],
                            backgroundColor: [
                                window.chartColors.yellow,
                                window.chartColors.green
                            ]
                        }],
                        labels: ['Used', 'Free']
                    },
                    options: {
                        cutoutPercentage: 50,
                        maintainAspectRatio: false,
                        legend: false,
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var label = data.labels[tooltipItem.index] || '';
                                    var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                    return "{0}: {1}%".format( label, value );
                                }
                            }
                        }
                    }
                };
                // create chart obj
                ctx = $("#settings_cache_tab_mem_usage_container canvas")[0].getContext('2d');
                chart = new Chart(ctx, chart_config);
            });
        </script>
        <?php
    }
}
?>
