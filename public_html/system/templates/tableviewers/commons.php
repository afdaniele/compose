<?php
function generateDescriptionListView($labelName, $fieldValue, $labelColumn, $valueColumn) {
    ?>
    <dl class="row">
        <?php
        for ($i = 0; $i < sizeof($labelName); $i++) {
            $label_col = (is_array($labelColumn)) ? $labelColumn[$i] : $labelColumn;
            $value_col = (is_array($valueColumn)) ? $valueColumn[$i] : $valueColumn;
            $label = $labelName[$i];
            $value = (!is_null($fieldValue[$i]) && strlen($fieldValue[$i]) > 0) ? $fieldValue[$i] : '&nbsp;';
            ?>
            <dt class="col-sm-<?php echo $label_col ?>"><?php echo $label ?></dt>
            <dd class="col-sm-<?php echo $value_col ?>"><?php echo $value ?></dd>
            <?php
        }
        ?>
    </dl>
    <?php
}//generateDescriptionListView
?>
