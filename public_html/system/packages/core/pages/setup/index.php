<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Database;

// redirect user away from this page (if necessary)
if (Core::isComposeConfigured()) {
    Core::redirectTo('');
}

// add \compose\ setup steps
$steps = [
    1 => [
        'title' => 'Configure Google Sign-In',
        'content_file' => __DIR__ . '/steps/step1.php'
    ],
    2 => [
        'title' => 'Create an Administrator account',
        'content_file' => __DIR__ . '/steps/step2.php'
    ],
    3 => [
        'title' => 'Configure \\compose\\',
        'content_file' => __DIR__ . '/steps/step3.php'
    ]
];

// get list of setup plugins files
$setup_addon_files_per_pkg = Core::getPackagesModules('setup', null);
foreach ($setup_addon_files_per_pkg as $pkg_id => $addon_files) {
    $package_name = Core::getPackageDetails($pkg_id, 'name');
    foreach ($addon_files as $addon_file) {
        $steps[count($steps) + 1] = [
            'title' => sprintf('Package: <b>%s</b>', $package_name),
            'content_file' => $addon_file
        ];
    }
}

// add Complete step
$steps[count($steps) + 1] = [
    'title' => 'Complete',
    'content_file' => __DIR__ . '/steps/step_complete.php'
];

// count number of steps
$num_steps = count($steps);

// look for edit actions
$force_step = null;
if (isset($_GET['force_step']) && array_key_exists(intval($_GET['force_step']), $steps)) {
    $force_step = intval($_GET['force_step']);
}

// open first_setup DB
$first_setup_db = new Database('core', 'first_setup');

$cur_step = $num_steps;
for ($i = 1; $i <= $num_steps; $i++) {
    if ($force_step == $i || !$first_setup_db->key_exists('step' . $i)) {
        $cur_step = $i;
        break;
    }
}
?>


<table style="width:100%; border-bottom:1px solid #ddd; margin:20px 0 20px 0">
    <tr>
        <td style="width:100%">
            <h1 class="text-center">
                <img src="<?php echo Configuration::$BASE ?>images/compose-black-logo.svg">
                <br/>
                Welcome!
            </h1>
        </td>
    </tr>
</table>

<?php
function _compose_first_setup_step_in_progress() {
    ?>
    <div class="progress">
        <div class="progress-bar progress-bar-striped active" role="progressbar"
             aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
        </div>
    </div>
    <?php
}//_compose_first_setup_step_in_progress

for ($step_no = 1; $step_no <= $num_steps; $step_no++) {
    $collapse = $step_no == $cur_step ? 'show' : '';
    $icon = ($step_no == $cur_step) ? 'square-half' : (($step_no < $cur_step) ? 'check-square' : 'square');
    $card_style = ($step_no < $cur_step) ? 'bg-success' : 'bg-light';
    // ---
    ?>
    <div class="card card-text <?php echo $card_style ?>">
        <div class="card-header" role="tab">
            <a id="a_setup_step<?php echo $step_no ?>" role="button" aria-expanded="true"
               aria-controls="setup_step<?php echo $step_no ?>">
                <h5 class="card-title">
                    <span class="bi bi-<?php echo $icon ?>" aria-hidden="true"></span>
                    &nbsp;
                    <strong>Step <?php echo $step_no ?>
                        :</strong> <?php echo $steps[$step_no]['title'] ?>
                    <?php
                    if ($step_no < $cur_step) {
                        ?>
                        <a role="button" class="btn btn-sm"
                           href="setup?force_step=<?php echo $step_no ?>" style="float:right">
                            <span class="bi bi-pencil" aria-hidden="true"></span>
                            &nbsp;
                            Edit
                        </a>
                        <?php
                    } elseif ($step_no > $cur_step && $first_setup_db->key_exists('step' . ($step_no - 1))) {
                        ?>
                        <a role="button" class="btn btn-sm"
                           href="setup?force_step=<?php echo $step_no ?>" style="float:right">
                            <span class="bi bi-mail-forward" aria-hidden="true"></span>
                            &nbsp;
                            Return
                        </a>
                        <?php
                    }
                    ?>
                </h5>
            </a>
        </div>
        <div id="setup_step<?php echo $step_no ?>"
             class="card-collapse collapse <?php echo $collapse ?> text-black bg-light" role="tabpanel"
             aria-labelledby="setup_step<?php echo $step_no ?>">
            <div class="card-body">
                <?php
                if ($step_no == $cur_step) {
                    $_COMPOSE_SETUP_STEP_NO = $step_no;
                    include_once($steps[$step_no]['content_file']);
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}
?>
