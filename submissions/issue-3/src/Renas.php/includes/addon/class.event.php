<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#这里提供随机名字生成的类
################################################

class xEvent
{
    function __construct()
    {
        global $db;
        $this->db = $db;
        
        $this->id = null;
        $this->name = '';
        $this->stat = false;
        $this->startTime = null;
        $this->endTime = null;

        $this->data = array();
    }

    /**
     * 加载活动数据
     * 
     * @param int $id
     * 要加载的活动id
     */
    public function load(
        int $id
    ) {
        $query = $this->db->getArr(
            'events',
            array(
                "`id` = '{$id}'"
            ),
            null,1
        );

        if($query === false) {
            \fLog("Error: event({$id}) doesn't exist in database");
            return false;
        }

        $this->id = $id;
        $this->name = $query[0]['name'];
        $this->stat = $query[0]['stat'];
        $this->startTime = $query[0]['startTime'];
        $this->endTime = $query[0]['endTime'];
        $this->data = json_decode($query[0]['data'], true);

        return true;
    }

    /**
     * 检查活动是否开启
     * 
     * @param bool $redirect = false
     * 是否进行重定向跳转
     * 
     * @return int
     * = 0：活动正在进行中
     * = 1：这个活动被手动关闭
     * = 2：这个活动还没开始
     * = 3：这个活动已经结束
     */
    public function check(
        bool $redirect = false
    ) {
        $html = new \xHtml;
        if($this->stat == 0) {
            if($redirect === true) {
                $html->redirect(
                    _ROOT,
                    'pageTitle.plaza',
                    'redirect.message.event.inactive'
                );
                \fDie();
            }
            return 1;
        }

        if(
            !is_null($this->startTime)
            && $this->startTime > time()
        ) { //活动还没开始
            if($redirect === true) {
                $html->redirect(
                    _ROOT,
                    'pageTitle.plaza',
                    'redirect.message.event.notStart'
                );
                \fDie();
            }
            return 2;
        }

        if(
            !is_null($this->endTime)
            && $this->startTime > time()
        ) { //活动已经结束
            if($redirect === true) {
                $html->redirect(
                    _ROOT,
                    'pageTitle.plaza',
                    'redirect.message.event.isEnd'
                );
                \fDie();
            }
            return 3;
        }

        return 0;
    }

    /**
     * 渲染这个活动
     */
    public function render() {
        $html = new \xHtml;

        $html->set('--eventCode', $this->name);

        # 渲染时间
        switch (TRUE) {
            case ( //有开始和结束时间
                !is_null($this->startTime) 
                && !is_null($this->endTime)
            ):
                if(
                    $this->startTime > time() //还没开始
                ) {
                    $html->set('--url', '#');
                    $html->set('--stat', '{?common.event.upcoming?}');
                    $html->set('--statCss', 'event-upcoming');
                    $html->set('--dueTime', '{?common.event.startAt?} '.\fFormatTime($this->startTime));
                    $html->set('--dueTimeDisplay', 'event-dueTime-upcoming');
                }

                elseif(
                    $this->endTime < time() //已经结束
                ) {
                    $html->set('--url', '#');
                    $html->set('--stat', '{?common.event.overdue?}');
                    $html->set('--statCss', 'event-overdue');
                    $html->set('--dueTime', '');
                    $html->set('--dueTimeDisplay', 'hidden');
                }

                else { //还在进行中
                    $html->set('--url', "{?!dirRoot?}event/{$this->name}");
                    $html->set('--stat', '{?common.event.ongoing?}');
                    $html->set('--statCss', 'event-ongoing');
                    $html->set('--dueTime', '{?common.event.endTill?} '.\fFormatTime($this->endTime));
                    $html->set('--dueTimeDisplay', 'event-dueTime-ongoing');
                }
                
                break;

            case ( //只有结束时间
                is_null($this->startTime) 
                && !is_null($this->endTime)
            ):
                if(
                    $this->endTime < time() //已经结束
                ) {
                    $html->set('--url', '#');
                    $html->set('--stat', '{?common.event.overdue?}');
                    $html->set('--statCss', 'event-overdue');
                    $html->set('--dueTime', '{?common.event.overdue?}');
                    $html->set('--dueTimeDisplay', 'hidden');
                }

                else { //还在进行中
                    $html->set('--url', "{?!dirRoot?}event/{$this->name}");
                    $html->set('--stat', '{?common.event.ongoing?}');
                    $html->set('--statCss', 'event-ongoing');
                    $html->set('--dueTime', '{?common.event.endTill?} '.\fFormatTime($this->endTime));
                    $html->set('--dueTimeDisplay', 'event-dueTime-ongoing');
                }

                break;

            case (
                !is_null($this->startTime) 
                && is_null($this->endTime)
            ):
                if(
                    $this->startTime > time() //还未开始
                ) {
                    $html->set('--url', '#');
                    $html->set('--stat', '{?common.event.upcoming?}');
                    $html->set('--statCss', 'event-upcoming');
                    $html->set('--dueTime', '{?common.event.startAt?} '.\fFormatTime($this->startTime));
                    $html->set('--dueTimeDisplay', 'event-dueTime-upcoming');
                }

                else { //还在进行中
                    $html->set('--url', "{?!dirRoot?}event/{$this->name}");
                    $html->set('--stat', '{?common.event.ongoing?}');
                    $html->set('--statCss', 'event-ongoing');
                    $html->set('--dueTime', '');
                    $html->set('--dueTimeDisplay', 'hidden');
                }

                break;

            default:
                $html->set('--url', "{?!dirRoot?}event/{$this->name}");
                $html->set('--stat', '{?common.event.ongoing?}');
                $html->set('--statCss', 'event-ongoing');
                $html->set('--dueTime', '');
                $html->set('--dueTimeDisplay', 'hidden');
                break;
        }

        $html->loadTpl('event/card.frame.html');
        return $html->render();
    }

    /**
     * 渲染活动列表
     */
    public static function renderList(
        $active = true,
        $inactive = false,
        $upcoming = true,
        $overdue = false
    ) {
        global $db;
        $html = new \xHtml;
        $currentTime = time();

        $condition = array();
        //拼装查询条件
        $con_active = array();
        $con_start = array(
            "`startTime` is NULL",
            "`startTime` < '{$currentTime}'"
        );
        $con_end = array(
            "`endTime` is NULL",
            "`endTime` > '{$currentTime}'"
        );

        if($active === true) $con_active[] = "`stat` = '1'";
        if($inactive === true) $con_active[] = "`stat` = '0'";
        $condition[] = '('.implode(' OR ', $con_active).')';

        if($upcoming === true) $con_start[] = "`startTime` > '{$currentTime}'";
        $condition[] = '('.implode(' OR ', $con_start).')';

        if($overdue === true) $con_end[] = "`endTime` < '{$currentTime}'";
        $condition[] = '('.implode(' OR ', $con_end).')';


        //查询符合条件的活动
        $events = $db->getArr(
            'events',
            $condition
        );

        if($events === false) {
            return '';
        }

        $comp = array();
        foreach ($events as $k => $record) {
            $temp = array(
                '--eventCode' => $record['name']
            );

            # 渲染时间
            switch (TRUE) {
                case ( //有开始和结束时间
                    !is_null($record['startTime']) 
                    && !is_null($record['endTime'])
                ):
                    if(
                        $record['startTime'] > time() //还没开始
                    ) {
                        $temp['--url'] = "{?!dirRoot?}event/{$record['name']}";
                        $temp['--stat'] = '{?common.event.upcoming?}';
                        $temp['--statCss'] = 'event-upcoming';
                        $temp['--dueTime'] = '{?common.event.startAt?} '.\fFormatTime($record['startTime']);
                        $temp['--dueTimeDisplay'] = 'event-dueTime-upcoming';
                    }

                    elseif(
                        $record['endTime'] < time() //已经结束
                    ) {
                        $temp['--url'] = "{?!dirRoot?}event/{$record['name']}";
                        $temp['--stat'] = '{?common.event.overdue?}';
                        $temp['--statCss'] = 'event-overdue';
                        $temp['--dueTime'] = '';
                        $temp['--dueTimeDisplay'] = 'hidden';
                    }

                    else { //还在进行中
                        $temp['--url'] = "{?!dirRoot?}event/{$record['name']}";
                        $temp['--stat'] = '{?common.event.ongoing?}';
                        $temp['--statCss'] = 'event-ongoing';
                        $temp['--dueTime'] = '{?common.event.endTill?} '.\fFormatTime($record['endTime']);
                        $temp['--dueTimeDisplay'] = 'event-dueTime-ongoing';
                    }
                    
                    break;

                case ( //只有结束时间
                    is_null($record['startTime']) 
                    && !is_null($record['endTime'])
                ):
                    if(
                        $record['endTime'] < time() //已经结束
                    ) {
                        $temp['--url'] = "{?!dirRoot?}event/{$record['name']}";
                        $temp['--stat'] = '{?common.event.overdue?}';
                        $temp['--statCss'] = 'event-overdue';
                        $temp['--dueTime'] = '{?common.event.overdue?}';
                        $temp['--dueTimeDisplay'] = 'hidden';
                    }

                    else { //还在进行中
                        $temp['--url'] = "{?!dirRoot?}event/{$record['name']}";
                        $temp['--stat'] = '{?common.event.ongoing?}';
                        $temp['--statCss'] = 'event-ongoing';
                        $temp['--dueTime'] = '{?common.event.endTill?} '.\fFormatTime($record['endTime']);
                        $temp['--dueTimeDisplay'] = 'event-dueTime-ongoing';
                    }

                    break;

                case (
                    !is_null($record['startTime']) 
                    && is_null($record['endTime'])
                ):
                    if(
                        $record['startTime'] > time() //还未开始
                    ) {
                        $temp['--url'] = "{?!dirRoot?}event/{$record['name']}";
                        $temp['--stat'] = '{?common.event.upcoming?}';
                        $temp['--statCss'] = 'event-upcoming';
                        $temp['--dueTime'] = '{?common.event.startAt?} '.\fFormatTime($record['startTime']);
                        $temp['--dueTimeDisplay'] = 'event-dueTime-upcoming';
                    }

                    else { //还在进行中
                        $temp['--url'] = "{?!dirRoot?}event/{$record['name']}";
                        $temp['--stat'] = '{?common.event.ongoing?}';
                        $temp['--statCss'] = 'event-ongoing';
                        $temp['--dueTime'] = '';
                        $temp['--dueTimeDisplay'] = 'hidden';
                    }

                    break;

                default:
                    $temp['--url'] = "{?!dirRoot?}event/{$record['name']}";
                    $temp['--stat'] = '{?common.event.ongoing?}';
                    $temp['--statCss'] = 'event-ongoing';
                    $temp['--dueTime'] = '';
                    $temp['--dueTimeDisplay'] = 'hidden';
                    break;
            }

            $comp[] = $temp;
        }

        return $html->duplicate('event/banner.html', $comp);
    }
}

?>