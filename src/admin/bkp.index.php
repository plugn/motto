<?php

        require_once "./global.php";
        $user = $conn->query_first($sql = "select * from halls where id=id limit 0,1");

        echo $sql.'; result:  '.var_export( $user,1 );



?>