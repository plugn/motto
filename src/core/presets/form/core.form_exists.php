<?php

    /* PRESETS are NOT TUNABLE from OUTSIDE. This is CORE-level  distribution. */

    require ( '_environment.php' );
    $form->getHTML( array(  'tpl'       => 'plain:Hidden',
                                  'tplvar'    => $guide->form_exists_var,
                                  'field'     => $guide->form_exists_var, // вот такое совпадение
                                  'value'     => 1,                   ) );


?>