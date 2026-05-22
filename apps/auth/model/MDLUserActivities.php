<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

class MDLUserActivities extends Connection{

    public function userActivitiesMDL($activity_data, $activity_table) {
        $newPDO = new Connection();
        $thisPDO = $newPDO->Connect();

        $stmt = $thisPDO->prepare("INSERT INTO $activity_table(activity_module, activity_desc, user_id) VALUES(?, ?, ?)");
        $stmt->execute(
            array(
                $activity_data['activity_module'],
                $activity_data['activity_desc'],
                $activity_data['user_id']
            )
        );

    }

}