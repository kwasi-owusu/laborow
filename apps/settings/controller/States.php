<?php

class States
{
    public static function ctrStates()
    {
        $country     = strip_tags(trim($_POST['country']));
        if (isset($country)) {

            //send to model
            $val = $country;
            $tbl = "states";


            require_once '../model/Countries.php';

            $rqsModel = Countries::allStates($val, $tbl);

            if (isset($rqsModel)) {

                foreach ($rqsModel as $st) {
                    $st_nm = $st['name'];
                    $st_id = $st['id'];

                    echo "<option value='" . $st_id . "'>$st_nm</option>";
                }
            } else {

                echo "<option value='999'>No State Available</option>";
            }
        }
    }
}

States::ctrStates();
