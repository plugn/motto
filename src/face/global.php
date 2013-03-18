<?

        //Первое место где надо исправить - путь к конфигу

        //Потом надо еще подправить конфиг

        require_once "/web/relcom/e1.ru/www/afisha/events/configure.php";
        require_once "UR/db_mysql.hp";
        require_once "UR/html.hp";
        require_once "UR/extends.hp";

        require_once "UR/mysql_get_login_data.hp";
        $mysql_host = 'db';
        $mysql_database = 'kino';
        list( $mysql_login, $mysql_passwd ) = mysql_get_login_data( $mysql_host );

        $conn = &new DB_Sql();
        $conn->connect( $mysql_database, $mysql_login, $mysql_passwd, $mysql_host );



        function get_image( $image_id ) {
               global $conn;
                return $conn->query_first("select * from image where image_id = $image_id");

        }



?>