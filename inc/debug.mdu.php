<?php

    echo"<center><b>CuteNews Debug Information:</b><hr><br />";
    echo"Script Version/ID:&nbsp&nbsp;$config_version_name / $config_version_id<br />";
    echo"\$config_http_script_dir:&nbsp;&nbsp;$config_http_script_dir<br /><br />";
    echo"<hr>";
    phpinfo();
    echo"<hr><textarea cols=85 rows=24>";
    print_r(ini_get_all());
    echo"</textarea>";


    exit();
