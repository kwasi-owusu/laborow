<?php
class Cities
{
    public static function ctrRegions()
    {
        $region     = strip_tags(trim($_POST['region']));
        if (isset($region)) {

            //send to model
            $val = $region;
            $tbl = "cities";


            require_once '../model/Countries.php';

            $rqsModel = Countries::allStates($val, $tbl);

            if (isset($rqsModel)) {

                foreach ($rqsModel as $st) {
                    $st_nm = $st['name'];
                    $st_id = $st['id'];

                    echo "<option value='" . $st_id . "'>$st_nm</option>";
                }
            } else {

                echo "<option value='999'>No City Available</option>";
            }
        }
    }
}

Cities::ctrRegions();