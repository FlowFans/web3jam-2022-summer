<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 自动分批处理待完成的facility建造
################################################

// $db = new \xDatabase;

$currentTime = time();
$query = $db->getArr( //取早于当前时间的建造数据
    'facility_building',
    array(
        "`endTime` < '{$currentTime}'"
    ),
    null,
    $GLOBALS['setting']['automator']['facilityPerBatch'],
    null,
    '`endTime`',
    'ASC'
);

if($query !== false) {
    $u = new \user\xAdapter;
    foreach($query as $k => $data) {
        $data['builders'] = json_decode($data['builders'], true);
        // fPrint($data);
        $u->load($data['uid']);
        $check = $u->facility->upgrade($data['facilityName']);
        if($check == 0) {
            $charName = array();
            $c = new \meshal\char\xAdapter;
            if(!empty($data['builders'])) {
                foreach($data['builders'] as $k => $charId) {
                    $c->load($charId);
                    $charName[] = $c->name;

                    //更新数据
                    $db->update(
                        'characters',
                        array(
                            'stat' => null
                        ),
                        array(
                            "`id` = '{$charId}'"
                        ),
                        1
                    );
                }
            }

            \fMsg( //给用户发消息
                $data['uid'],
                'facility',
                'message.facility.buildingComplete',
                array(
                    '$characters' => implode('{?common.comma?}', $charName),
                    '$facilityName' => $data['facilityName'],
                    '$facilityLevel' => $data['facilityLevel']
                )
            );

            //删除建造中的数据
            $db->delete(
                'facility_building',
                array(
                    "`uid` = '{$data['uid']}'",
                    "`facilityName` = '{$data['facilityName']}'",
                    "`facilityLevel` = '{$data['facilityLevel']}'"
                )
            );
        }
    }
}
?>