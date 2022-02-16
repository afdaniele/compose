<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use system\classes\Cache;
use system\classes\Core;

function settings_cache_tab() {
    if (!Cache::enabled()) {
        ?>
        <h4 class="text-center">The cache is disabled</h4>
        <?php
    } else {
        $cache_stats = Cache::getStats()->getRawData();
        // clear cache (if requested)
        if (isset($_GET['clear_cache']) && boolval($_GET['clear_cache'])) {
            Cache::clearAll();
            Core::redirectTo('settings#sel:cache_collapse');
        }
        ?>
        <style>
            .cache_stats_box {
                color: #4bc0c0;
                border: 1px solid lightgray;
                margin: 10% 15%;
                padding: 10% 0;
            }
        </style>

        <p style="margin-bottom: 30px">
            This tab shows statistics about the cache usage.
            <button
                    class="btn btn-warning"
                    type="button"
                    data-toggle="tooltip"
                    data-placement="bottom"
                    data-original-title="Clear cache"
                    onclick="_clear_cache();"
                    style="float: right">
                &nbsp;
                <i class="bi bi-refresh" aria-hidden="true"></i>
                &nbsp;
                Clear cache
            </button>
        </p>

        <div class="col-md-3">
            <h4 class="text-center">
                Hits vs Misses
            </h4>
            <div id="settings_cache_tab_hits_vs_misses_container">
                <canvas style="width:100%; min-height:96px; height:96px; max-height:96px"></canvas>
            </div>
        </div>
        <div class="col-md-3">
            <h4 class="text-center" style="margin-bottom:">
                Num. Entries
            </h4>
            <h3 class="text-center cache_stats_box">
                <?php echo $cache_stats['num_entries'] ?>
            </h3>
        </div>
        <div class="col-md-3">
            <h4 class="text-center">
                Mem. Used
            </h4>
            <h3 class="text-center cache_stats_box">
                <?php echo human_filesize($cache_stats['mem_size']) ?>
            </h3>
        </div>
        <div class="col-md-3">
            <h4 class="text-center">
                Mem. Usage
            </h4>
            <div id="settings_cache_tab_mem_usage_container">
                <canvas style="width:100%; min-height:96px; height:96px; max-height:96px"></canvas>
            </div>
        </div>

        <script type="text/javascript">

            function _clear_cache() {
                window.location = "<?php echo Core::getURL('settings', null, null, null, ['clear_cache' => 1]) ?>";
            }

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
                                label: function (tooltipItem, data) {
                                    var label = data.labels[tooltipItem.index] || '';
                                    var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                    return "{0}: {1}%".format(label, value);
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
